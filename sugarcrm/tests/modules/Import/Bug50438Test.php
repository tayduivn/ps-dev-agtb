<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


/*
 * 
 */

class Bug50438Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $call;
    var $contact;

    public function setUp()
    {
        global $currentModule ;
		$mod_strings = return_module_language($GLOBALS['current_language'], "Contacts");
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        //create a contact
        $this->contact = new Contact();
        $this->contact->first_name = 'Joe UT ';
        $this->contact->last_name = 'Smith UT 50438';
        $this->contact->disable_custom_fields = true;
        $this->contact->save();



        //create a call
        $this->call = new Call();
        $this->call->name = 'Call for Unit Test 50438';
        $this->call->status = 'Planned';
        $this->call->disable_custom_fields = true;
        $this->call->save();

    }

    public function tearDown()
    {

        $GLOBALS['db']->query("DELETE FROM calls WHERE id='{$this->call->id}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE user_id='{$this->contact->id}'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->call);
        unset($this->contact);
        unset( $GLOBALS['current_user']);
        unset( $GLOBALS['mod_strings']);
    }


    /**
     * @ticket 45907
     */
    public function testParentsAreRelatedDuringImport()
    {

        //set the call parent information
        $this->call->parent_type = 'Contacts';
        $this->call->parent_id = $this->contact->id;

        //set the call bean to simulate import in progress
        $this->call->in_import = true;

        //save the bean
        $this->call->save();

        //refetch the bean, and get related contacts
        $this->call->retrieve($this->call->id);
        $this->call->load_relationship('contacts');
        $related_contacts = $this->call->contacts->get();

        //test that the contact id is in the array of related contacts.
        $this->assertContains($this->contact->id, $related_contacts,' Contact was not related during simulated import despite being set in related parent id');
    }

}
