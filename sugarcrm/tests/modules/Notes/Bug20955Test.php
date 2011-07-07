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

