<?php
//FILE SUGARCRM flav=pro ONLY
require_once 'modules/Teams/Team.php';
require_once 'modules/Users/User.php';
require_once "modules/Notes/Note.php";
require_once "modules/Tasks/Task.php";
require_once "modules/Bugs/Bug.php";
//BEGIN SUGARCRM flav!=sales ONLY
require_once "modules/Campaigns/Campaign.php";
//END SUGARCRM flav!=sales ONLY

/**
 * @ticket 20955
 */
class Bug20955Test extends Sugar_PHPUnit_Framework_TestCase
{
	public $_user = null;
	public $_team = null;

	public function setUp() 
    {
		global $current_user;
		$time = date($GLOBALS['timedate']->get_db_date_time_format());

		$this->_team = SugarTestTeamUtilities::createAnonymousTeam();

		$this->_user = SugarTestUserUtilities::createAnonymousUser();//new User();
		$this->_user->first_name = "leon";
		$this->_user->last_name = "zhang";
		$this->_user->user_name = "leon zhang";
		$this->_user->default_team=$this->_team->id;
		$this->_user->save();
		$current_user=$this->_user;
	}

	public function tearDown() 
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
	}

	public function testDisabledNewNoteDefaultTeam()
    {
		global $current_user;
		$temp_note=new Note();  
		$temp_note->save();
		return $this->assertEquals($temp_note->team_id, $current_user->default_team, "The note default team is not the current user's default team! ");
	}

	public function testDisabledNewTaskDefaultTeam() 
    {
		global $current_user;
		$temp_task=new Task();  
		$temp_task->save();
		return $this->assertEquals($temp_task->team_id,$current_user->default_team, "The task default team is not the current user's default team! ");
	}

	public function testDisabledNewBugDefaultTeam() 
    {
		global $current_user;
		$temp_bug=new Bug();  
		$temp_bug->save();
		return $this->assertEquals($temp_bug->team_id,$current_user->default_team, "The bug default team is not the current user's default team! ");
	}

	//BEGIN SUGARCRM flav!=sales ONLY
	public function testDisabledNewCampaignDefaultTeam() 
    {
		global $current_user;
		$temp_campaign=new Campaign();  
		$temp_campaign->save();
		return $this->assertEquals($temp_campaign->team_id,$current_user->default_team, "The campaign default team is not the current user's default team! ");
	}
	//END SUGARCRM flav!=sales ONLY
}

