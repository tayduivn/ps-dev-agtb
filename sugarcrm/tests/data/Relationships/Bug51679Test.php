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

class Bug51679Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $account;
    private $account2;
    private $contact;

    public function setUp()
    {
        global $beanFiles, $beanList, $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $this->account = SugarTestAccountUtilities::createAccount();
        $this->contact = SugarTestContactUtilities::createContact();
        $this->account->load_relationship('contacts');
        $this->account->contacts->add($this->contact);
        $this->account2 = SugarTestAccountUtilities::createAccount();
        $GLOBALS['db']->commit();
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     *
     */
    public function testM2MRelationships()
    {
        require_once('data/Relationships/M2MRelationship.php');
        $def = array(
            'table'=>'accounts_contacts',
            'join_table'=>'accounts_contacts',
            'name'=>'accounts_contacts',
            'lhs_module' => 'accounts',
            'rhs_module' => 'contacts'
        );
        $m2mRelationship = new M2MRelationship($def);
        $m2mRelationship->join_key_lhs = 'account_id';
        $m2mRelationship->join_key_rhs = 'contact_id';
        $result = $m2mRelationship->relationship_exists($this->account, $this->contact);

        $entry_id = $GLOBALS['db']->getOne("SELECT id FROM accounts_contacts WHERE account_id='{$this->account->id}' AND contact_id = '{$this->contact->id}'");
        $this->assertEquals($entry_id, $result);

        $result = $m2mRelationship->relationship_exists($this->account2, $this->contact);
        $this->assertEmpty($result);
    }
}