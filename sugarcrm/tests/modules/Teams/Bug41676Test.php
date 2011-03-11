<?php
//FILE SUGARCRM flav=pro ONLY 
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
        
        $query = "SELECT count(*) as total FROM team_memberships WHERE team_id = '{$team->id} AND deleted = 0";
        $results = $GLOBALS['db']->query($query);
        $row = $GLOBALS['db']->fetchByAssoc($results);
        $this->assertTrue(is_null($row['total']) || $row['total'] == 0, 'Assert that team_memberships table has been correctly set');        
    }

}