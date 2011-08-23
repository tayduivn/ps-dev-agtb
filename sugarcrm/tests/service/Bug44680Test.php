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
 * @group bug44680
 */
class Bug44680Test extends SOAPTestCase
{
    var $testUser;
	var $testAccount;
	var $teamSet;
    var $testTeam;

	public function setUp()
    {
        $this->_soapURL = $GLOBALS['sugar_config']['site_url'].'/soap.php';
		parent::setUp();
        $this->testUser = SugarTestUserUtilities::createAnonymousUser();
		$GLOBALS['current_user'] = $this->testUser;
		$this->testAccount = SugarTestAccountUtilities::createAccount();

        $this->testTeam = SugarTestTeamUtilities::createAnonymousTeam();

        $this->teamSet = new TeamSet();
        $this->teamSet->addTeams(array($this->testTeam->id, $this->testUser->getPrivateTeamID()));


		$this->testAccount->team_id = $this->testUser->getPrivateTeamID();
		$this->testAccount->team_set_id = $this->teamSet->id;
		$this->testAccount->assigned_user_id = $this->testUser->id;
		$this->testAccount->save();
        $GLOBALS['db']->commit();
    }

    public function  tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        parent::tearDown();
    }

    public function testSetEntryHasAccess()
    {
        $time = mt_rand();
        $oldName = $this->testAccount->name;
        $result = $this->_login();

        $result = $this->_soapClient->call('set_entry',array('session'=> $this->_sessionId,'module_name'=>'Accounts', 'name_value_list'=>array(array('name'=>'id' , 'value'=>$this->testAccount->id),array('name'=>'name' , 'value'=>"$time Account SINGLE"))));

        $this->assertEquals($this->testAccount->id, $result['id'], "Did not update the Account as expected.");
    }

    public function testSetEntryNoAccess()
    {
        $teamSet = new TeamSet();
        $teamSet->addTeams(array($this->testTeam->id));
        $this->testAccount->team_id = $this->testTeam->id;
		$this->testAccount->team_set_id = $teamSet->id;
		$this->testAccount->assigned_user_id = '1';
		$this->testAccount->save();

        $this->testTeam->remove_user_from_team($this->testUser->id);

        $time = mt_rand();
        $oldName = $this->testAccount->name;
        $this->_login();
        $result = $this->_soapClient->call('set_entry',array('session'=> $this->_sessionId,'module_name'=>'Accounts', 'name_value_list'=>array(array('name'=>'id' , 'value'=>$this->testAccount->id),array('name'=>'name' , 'value'=>"$time Account SINGLE"))));
        $this->assertEquals(-1, $result['id'], "Should not have updated the Account.");
    }
}