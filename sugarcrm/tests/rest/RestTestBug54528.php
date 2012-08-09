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

require_once('tests/rest/RestTestBase.php');

class RestTestBug54528 extends RestTestBase {
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
        $this->apiuser->team_id = 1;
        $this->apiuser->default_team = 1;
        $this->apiuser->team_set_id = 1;
        $this->apiuser->save();


        // create account
        $account = new Account();
        $account->name = "UNIT TEST account - " . create_guid();
        $account->billing_address_postalcode = sprintf("%08d", 1);
        $account->save();
        $this->account = $account;
        // create contact
        $this->contact = BeanFactory::newBean('Contacts');
        $this->contact->id = "UNIT-TEST-portalContact" . create_guid();
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
    }
    
    public function tearDown()
    {
        if ( isset($this->bug_id) ) {
            $GLOBALS['db']->query("DELETE FROM bugs WHERE id = '{$this->bug_id}'");
            $GLOBALS['db']->query("DELETE FROM bugs_cstm WHERE id = '{$this->bug_id}'");
        }
        if (isset($this->case_id)) {
            $GLOBALS['db']->query("DELETE FROM cases WHERE id = '{$this->case_id}'");
            $GLOBALS['db']->query("DELETE FROM cases_cstm WHERE id = '{$this->case_id}'");
        } 
        if(isset($this->account->id))
        {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->account->id}'");
            $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id = '{$this->account->id}'");            
        } 
        if(isset($this->contact->id))
        {
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$this->contact->id}'");
            $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id = '{$this->contact->id}'");            
        } 
        if(isset($this->apiuser->id))
        {
            $GLOBALS['db']->query("DELETE FROM users WHERE id = '{$this->apiuser->id}'");
            $GLOBALS['db']->query("DELETE FROM users_cstm WHERE id = '{$this->apiuser->id}'");            
        }
        parent::tearDown();
    }

    public function testCreate() {

        // we need to be an admin to get at the relationship data
        $GLOBALS['current_user']->is_admin = 1;
        $args = array(
            'grant_type' => 'password',
            'username' => $this->contact->portal_name,
            'password' => 'unittest',
            'client_id' => 'support_portal',
            'client_secret' => '',
        );
        // reload user

        // Prevents _restCall from automatically logging in
        $this->authToken = 'LOGGING_IN';
        $reply = $this->_restCall('oauth2/token', json_encode($args));

        // flip to the portal auth
        $this->authToken = $reply['reply']['access_token'];
        // create case
        $restReply = $this->_restCall("Cases/",
            json_encode(array('name' => 'UNIT TEST Case')),
            'POST');

        $this->assertTrue(isset($restReply['reply']['id']),
                          "A case was not created (or if it was, the ID was not returned)");

        $this->case_id = $restReply['reply']['id'];

        // load case to check teamset and user id match
        $case = BeanFactory::getBean('Cases',$this->case_id);

        //BEGIN SUGARCRM flav=pro ONLY
        $this->assertEquals($this->apiuser->team_id, $case->team_id, "Team ID doesn't match");
        $this->assertEquals($this->apiuser->team_set_id, $case->team_set_id, "Team Set ID doesn't match");
        //END SUGARCRM flav=pro ONLY

        $this->assertEquals($this->contact->assigned_user_id, $case->assigned_user_id, "Assigned user id doesn't match.");

        $restReply = null;

        $restReply = $this->_restCall("Bugs/",
                                      json_encode(array('name'=>'UNIT TEST Bug')),
                                      'POST');

        $this->assertTrue(isset($restReply['reply']['id']),
                          "A bug was not created (or if it was, the ID was not returned)");

        $this->bug_id = $restReply['reply']['id'];

        $bug = BeanFactory::getBean('Bugs', $this->bug_id);

        //BEGIN SUGARCRM flav=pro ONLY
        $this->assertEquals($this->apiuser->default_team, $bug->team_id, "Team ID doesn't match");
        $this->assertEquals($this->apiuser->team_set_id, $bug->team_set_id, "Team Set ID doesn't match");        
        //END SUGARCRM flav=pro ONLY

        $this->assertEquals($this->contact->assigned_user_id, $bug->assigned_user_id, "Assigned user id doesn't.");

        
    }

}