<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once 'tests/service/SOAPTestCase.php';

/**
 * Test attaching contact to acccount which is not visible to current user. Should create a new account instead.
 *
 */
class Bug27066Test extends SOAPTestCase
{

    public $team1;
    public $team2;

    public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
   		parent::setUp();
   		$this->team1 = SugarTestTeamUtilities::createAnonymousTeam();
   		$this->team2 = SugarTestTeamUtilities::createAnonymousTeam();
   		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
   		SOAPTestCase::$_user = $this->user = SugarTestUserUtilities::createAnonymousUser(false, false);
   		$this->user->is_admin = 0;
   		$this->user->default_team = $this->team1->id;
   		$this->user->team_id = $this->team1->id;
   		$this->user->save();
   		$this->user->load_relationship('teams');
   		$this->user->teams->add($this->team1);
   		$this->user->save();
   		$GLOBALS['db']->commit();

        $this->_login(); // Logging in just before the SOAP call as this will also commit any pending DB changes
        $GLOBALS['current_user'] = SOAPTestCase::$_user;
        $this->account = SugarTestAccountUtilities::createAccount();
        $this->account->name = 'Account Test 27066';
        $this->account->team_id = $this->team2->id;
        $this->account->save();
        $GLOBALS['db']->commit();
    }

    public function tearDown()
    {
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        $GLOBALS['db']->query("DELETE FROM contacts where first_name='Contact Test 27066'");
        $GLOBALS['db']->query("DELETE FROM accounts where name='Account Test 27066'");
        unset($GLOBALS['current_user']);
    }


    public function testContactAccount()
    {
        $client = array('session'=>$this->_sessionId, 'module_name' => 'Contacts',
            'name_value_lists' => array(
            array(
                array('name'=>'first_name','value'=>'Contact Test 27066'),
                array('name'=>'account_name','value'=>$this->account->name),
            )),
        );

        $result = $this->_soapClient->call('set_entries', $client);
        $this->assertArrayHasKey("ids", $result, "Bad result");

        $new_contact = $result["ids"][0];
        // switch to admin user
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, true);
        $contact = new Contact();
        $contact->retrieve($new_contact);
        $contact->load_relationship("accounts");
        $ids = $contact->accounts->get();
        $this->assertNotEmpty($ids, "No accounts");
        $this->assertEquals(1, count($ids), "Too many accounts");
        SugarTestAccountUtilities::setCreatedAccount($ids);
        // Should create a new account!
        $this->assertNotEquals($this->account->id, $ids[0]);
    }


}

