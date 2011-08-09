<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

class Bug40209Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    var $user;
    var $account;
    var $lead;
    var $contact;

    public function setUp()
    {
        global $_POST;
        $_POST = array();
        //create user
        $this->user = $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        //create account
        $this->account = new Account();
        $this->account->name = 'bug40209 account '.date('Y-m-d-H-i-s');
        $this->account->save();

        //create contact
        $this->contact = new Contact();
        $this->lead = SugarTestLeadUtilities::createLead();

    }

    public function tearDown()
    {
        //delete records created from db
        $GLOBALS['db']->query("DELETE FROM accounts WHERE id= '{$this->account->id}'");
        $GLOBALS['db']->query("DELETE FROM leads WHERE id= '{$this->lead->id}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->contact->id}'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        //unset values
        unset($GLOBALS['current_user']);
        unset($this->user);
        unset($this->account);
        unset($this->contact);
    }



    //run test to make sure accounts related to leads record are copied over to contact recor during conversion (bug 40209)
    public function testConvertAccountCopied()
    {
        $_POST = array();

        //set the request parameters and convert the lead
        $_REQUEST['module'] = 'Leads';
        $_REQUEST['action'] = 'ConvertLead';
        $_REQUEST['record'] = $this->lead->id;
        $_REQUEST['handle'] = 'save';
        $_REQUEST['selectedAccount'] = $this->account->id;

        //require view and call display class so that convert functionality is called
        require_once('modules/Leads/views/view.convertlead.php');
        $vc = new ViewConvertLead();
        $vc->display();

        //retrieve the lead again to make sure we have the latest converted lead in memory
        $this->lead->retrieve($this->lead->id);

        //retrieve the new contact id from the conversion
        $contact_id = $this->lead->contact_id;

        //throw error if contact id was not retrieved and exit test
        $this->assertNotEmpty($contact_id, "contact id was not created during conversion process.  An error has ocurred, aborting rest of test.");

        //make sure the new contact has the account related and that it matches the lead account
        $this->contact->retrieve($contact_id);
        $this->assertEquals($this->lead->account_id, $this->contact->account_id, "Account id from converted lead does not match the new contact account id, there was an error during conversion.");
    }
}