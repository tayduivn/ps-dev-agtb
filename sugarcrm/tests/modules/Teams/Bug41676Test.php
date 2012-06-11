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
 
class Bug41676Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $testUser;
	var $testAccount;
	var $teamSet;
	
	public function setUp()
	{
		//Make sure we are an admin
		global $current_user;   
    	$current_user = new User();
        $current_user->retrieve('1');		

		$this->testUser = SugarTestUserUtilities::createAnonymousUser();
		$this->testAccount = SugarTestAccountUtilities::createAccount();        
        $this->testUser->is_admin = false; // ensure non-admin user

        $this->teamSet = new TeamSet();
        $this->teamSet->addTeams($this->testUser->getPrivateTeamID());
        

		$this->testAccount->team_id = $this->testUser->getPrivateTeamID();
		$this->testAccount->team_set_id = $this->teamSet->id;
		$this->testAccount->assigned_user_id = $this->testUser->id;
		$this->testAccount->save();
	}
	
	public function tearDown()
	{
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
	    SugarTestAccountUtilities::removeAllCreatedAccounts();
	}
	
    public function testAccountWithDeletedUserAndTeam() 
    {
	    //Simulate deleting the user
        $this->testUser->status = 'Inactive';
        $this->testUser->deleted = 1;
        $this->testUser->employee_status = 'Terminated';
        $this->testUser->save();
        $eapm = loadBean('EAPM');
        $eapm->delete_user_accounts($this->testUser->id); 
        
        //Simulate deleting the team
        $team = new Team();
        $team->retrieve($this->testUser->getPrivateTeamID());
        $team->mark_deleted();
        
        $account = new Account();
        $account->retrieve($this->testAccount->id);
     
        $this->assertEquals($account->team_set_id, $this->teamSet->id, 'Assert that team set id value is correctly set');
        $this->assertEquals($account->assigned_user_id, $this->testUser->id, 'Assert that assigned user id value is correctly set');
	      
        $query = "SELECT * FROM teams WHERE id = '{$team->id}'";
        $results = $GLOBALS['db']->query($query);
        $row = $GLOBALS['db']->fetchByAssoc($results);
        $this->assertEquals($row['deleted'], 1, 'Assert that deleted flag is correctly set');
        
        $query = "SELECT count(*) as total FROM team_memberships WHERE team_id = '{$team->id}' AND deleted = 0";
        $results = $GLOBALS['db']->query($query);
        $row = $GLOBALS['db']->fetchByAssoc($results);
        $this->assertTrue(is_null($row['total']) || $row['total'] == 0, 'Assert that team_memberships table has been correctly set');        
    }

}