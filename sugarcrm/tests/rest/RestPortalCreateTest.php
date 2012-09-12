<?php
//FILE SUGARCRM flav=ent ONLY
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

require_once('tests/rest/RestTestBase.php');

class RestPortalCreateTest extends RestTestBase
{
    public function setUp()
    {
        parent::setUp();

        // Create a portal API user
        $this->apiuser = BeanFactory::newBean('Users');
        $this->apiuser->id = "UNIT-TEST-portalCreateUser";
        $this->apiuser->new_with_id = true;
        $this->apiuser->first_name = "Portal";
        $this->apiuser->last_name = "Apiuserson";
        $this->apiuser->username = "_unittest_apiuser";
        $this->apiuser->portal_only = true;
        $this->apiuser->status = 'Active';
        $this->apiuser->save();

        // create account
        $account = new Account();
        $account->name = "UNIT TEST account - " . create_guid();
        $account->billing_address_postalcode = sprintf("%08d", 1);
        $account->save();
        $this->account = $account;
        // create contact
        $this->contact = BeanFactory::newBean('Contacts');
        $this->contact->id = "UNIT-TEST-portalContact";
        $this->contact->new_with_id = true;
        $this->contact->first_name = "Little";
        $this->contact->last_name = "Unittest";
        $this->contact->description = "Little Unittest";
        $this->contact->portal_name = "liltest@unit.com";
        $this->contact->portal_active = '1';
        $this->contact->portal_password = User::getPasswordHash("unittest");
        $this->contact->save();

        $this->contact->load_relationship('accounts');

        // relate
        $this->contact->accounts->add($account->id);
        $GLOBALS['db']->commit();
    }

    public function tearDown()
    {
        if (isset($this->account->id)) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->account->id}'");
        }
        if (isset($this->contact->id)) {
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$this->contact->id}'");
        }
        if (isset($this->apiuser->id)) {
            $GLOBALS['db']->query("DELETE FROM users WHERE id = '{$this->apiuser->id}'");
        }
        if (isset($this->bug->id)) {
            $GLOBALS['db']->query("DELETE FROM bugs WHERE id = '{$this->bug->id}'");
        }
        if (isset($this->case->id)) {
            $GLOBALS['db']->query("DELETE FROM cases WHERE id = '{$this->case->id}'");
        }
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testCreate()
    {
        // we need to be an admin to get at the relationship data
        $GLOBALS['current_user']->is_admin = 1;
        $args = array(
            'grant_type' => 'password',
            'username' => $this->contact->portal_name,
            'password' => 'unittest',
            'client_id' => 'support_portal',
            'client_secret' => '',
        );

        // Prevents _restCall from automatically logging in
        $this->authToken = 'LOGGING_IN';
        $reply = $this->_restCall('oauth2/token', json_encode($args));
        // flip to the portal auth
        $this->authToken = $reply['reply']['access_token'];
        // create case
        $caseReply = $this->_restCall("Cases/",
            json_encode(array('name' => 'UNIT TEST Case')),
            'POST');
        $this->assertEquals($caseReply['reply']['account_id'], $this->account->id);
        $this->case = new aCase();

        $this->case->retrieve($caseReply['reply']['id']);
        $relates = $this->case->get_linked_beans('contacts', 'Contact');
        $this->assertEquals($relates[0]->id, $this->contact->id);

        // create bug
        $bugReply = $this->_restCall("Bugs/",
            json_encode(array('name' => 'UNIT TEST Bug')),
            'POST');
        $this->bug = new Bug();

        $this->bug->retrieve($bugReply['reply']['id']);
        $relates = $this->bug->get_linked_beans('contacts', 'Contact');
        $this->assertEquals($relates[0]->id, $this->contact->id);

        $relatesAccounts = $this->bug->get_linked_beans('accounts', 'Account');
        $this->assertEquals($relatesAccounts[0]->id, $this->account->id);
    }
}