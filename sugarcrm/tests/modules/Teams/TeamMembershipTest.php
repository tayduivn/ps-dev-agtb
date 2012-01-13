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
 
/**
 * Test cases for the Team object
 */
class TeamMembershipTest extends Sugar_PHPUnit_Framework_TestCase
{
    var $_users = array();
    var $_original_path = null;

    public function setUp() 
    {
    	//TODO fix this test
    	 $this->markTestIncomplete(
              'Need to ensure proper cleanup first.'
            );
        $time = date($GLOBALS['timedate']->get_db_date_time_format());

        $users = array('A', 'B', 'C');
        foreach ($users as $user) {
            $this->_users[$user] = SugarTestUserUtilities::createAnonymousUser();
            $this->_users[$user]->first_name = $user;
            $this->_users[$user]->last_name = $time;
            $this->_users[$user]->user_name = $user . $time;
            $this->_users[$user]->save();
        }
    }

    public function tearDown() 
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    protected function isUserPartOfTeam($user, $team_name, $explicit) 
    {
        $teamFactory = new Team();
        $team = $teamFactory->retrieve($teamFactory->retrieve_team_id($team_name));
        
        $teamMembership = new TeamMembership();
        $teamMembership->retrieve_by_user_and_team($user->id, $team->id);
		
        return $explicit ? $teamMembership->explicit_assign == 1 : $teamMembership->implicit_assign == 1;
    }


    protected function isUserExplicitlyPartOfTeam($user, $team_name) 
    {
        return $this->isUserPartOfTeam($user, $team_name, true);
    }

    protected function isUserImplicitlyPartOfTeam($user, $team_name) 
    {
        return $this->isUserPartOfTeam($user, $team_name, false);
    }

    protected function isUserExplicitlyPartOfGlobalTeam($user) 
    {
        return $this->isUserExplicitlyPartOfTeam($user, 'Global');
    }

    protected function assertUserExplicitlyPartOfTeam($user, $team) 
    {
        $this->assertTrue(
            $this->isUserExplicitlyPartOfTeam($user, $team),
            "User {$user->first_name} is explicitly part of team {$team}"
        );
    }

    protected function assertUserImplicitlyPartOfTeam($user, $team) 
    {
    	$this->assertTrue(
            $this->isUserImplicitlyPartOfTeam($user, $team),
            "User {$user->first_name} is implicitly part of team {$team}"
        );
    }

    protected function assertUserNotImplicitlyPartOfTeam($user, $team) 
    {
        $this->assertFalse(
            $this->isUserImplicitlyPartOfTeam($user, $team),
            "User {$user->first_name} is not implicitly part of team {$team}"
        );
    }

    protected function _userAReportsToUserB() 
    {
        $this->_users['A']->reports_to_id = $this->_users['B']->id;
        $this->_users['A']->save();
        $this->_users['A']->update_team_memberships('');
    }

    protected function _userBReportsToUserC() 
    {
        $this->_users['B']->reports_to_id = $this->_users['C']->id;
        $this->_users['B']->save();
        $this->_users['B']->update_team_memberships('');
    }

    public function testDisabledBaseUserAssumedPartOfOwnTeamAndGlobal() 
    {
        foreach ($this->_users as $user) {
            $this->assertUserExplicitlyPartOfTeam($user, $user->first_name);
            $this->assertUserExplicitlyPartOfTeam($user, 'Global');
        }
    }

    public function testDisabledUserBImplicitlyPartOfUserATeamIfUserAReportsToB() 
    {
        $this->_userAReportsToUserB();
        $this->assertUserImplicitlyPartOfTeam($this->_users['B'], $this->_users['A']->first_name);
    }
    
    public function testDisabledUserBImplicitlyAndExplicitlyPartOfGlobalTeamIfUserAReportsToB() 
    {
        $this->_userAReportsToUserB();
        $this->assertUserImplicitlyPartOfTeam($this->_users['B'], 'Global');
        $this->assertUserExplicitlyPartOfTeam($this->_users['B'], 'Global');
    }

    public function testDisabledUserCImplicitlyPartOfAllOfUserBTeams() 
    {
        $this->_userAReportsToUserB();
        $this->_userBReportsToUserC();

        $this->assertUserImplicitlyPartOfTeam($this->_users['C'], $this->_users['A']->first_name);
        $this->assertUserImplicitlyPartOfTeam($this->_users['C'], $this->_users['B']->first_name);
        $this->assertUserImplicitlyPartOfTeam($this->_users['C'], 'Global');
    }

    public function testDisabledAnyTeamsThatHaveAUserAddedThemRippleImplicitlyToAllUserThatAddUserImplicitlyReportsTo() 
    {
        $this->_userAReportsToUserB();
        $this->_userBReportsToUserC();

        $team = SugarTestTeamUtilities::createAnonymousTeam();
        $team->add_user_to_team($this->_users['A']->id, $this->_users['A']);
        
        $this->assertUserExplicitlyPartOfTeam($this->_users['A'], $team->name);
        $this->assertUserImplicitlyPartOfTeam($this->_users['B'], $team->name);
        $this->assertUserImplicitlyPartOfTeam($this->_users['C'], $team->name);
    }

    public function testDisabledTeamRippleWithExplicitAdd() 
    {
        $this->_userAReportsToUserB();
        $this->_userBReportsToUserC();

        $team = SugarTestTeamUtilities::createAnonymousTeam();
        $team->add_user_to_team($this->_users['A']->id, $this->_users['A']);
        $team->add_user_to_team($this->_users['C']->id, $this->_users['C']);

        $this->assertUserImplicitlyPartOfTeam($this->_users['C'], $team->name);
        $this->assertUserExplicitlyPartOfTeam($this->_users['C'], $team->name);
    }

    public function testDisabledUsersCanBeAddedToTeamWithJustUserId() 
    {
        $this->_userAReportsToUserB();
        $this->_userBReportsToUserC();

        $team = SugarTestTeamUtilities::createAnonymousTeam();
        $team->add_user_to_team($this->_users['A']->id);

        $this->assertUserExplicitlyPartOfTeam($this->_users['A'], $team->name);
    }

    public function testDisabledUserIdGivenPriorityIfProvidedUserDoesNotMatch() 
    {
        $this->_userAReportsToUserB();
        $this->_userBReportsToUserC();

        $team = SugarTestTeamUtilities::createAnonymousTeam();
        $team->add_user_to_team($this->_users['A']->id, $this->_users['B']);
        
        $this->assertUserExplicitlyPartOfTeam($this->_users['A'], $team->name);
    }

    public function testDisabledWhenReportsToIsChangedImplicitMembershipRipple() 
    {
        $this->_userAReportsToUserB();
        $this->_userBReportsToUserC();

        $team = SugarTestTeamUtilities::createAnonymousTeam();
        $team->add_user_to_team($this->_users['A']->id, $this->_users['A']);
        
        $this->assertUserExplicitlyPartOfTeam($this->_users['A'], $team->name);
        $this->assertUserImplicitlyPartOfTeam($this->_users['B'], $team->name);
        $this->assertUserImplicitlyPartOfTeam($this->_users['C'], $team->name);

        $old_boss = $this->_users['A']->reports_to_id;
        $this->_users['A']->reports_to_id = '';
        $this->_users['A']->save();
        $this->_users['A']->update_team_memberships($old_boss);

        $this->assertUserExplicitlyPartOfTeam($this->_users['A'], $team->name);
        $this->assertUserNotImplicitlyPartOfTeam($this->_users['B'], $team->name);
        $this->assertUserNotImplicitlyPartOfTeam($this->_users['C'], $team->name);
    }

    public function testDisabledChangingWhoAUserReportsToUpdatesWhoIsImplicitlyPartOfTheirTeam() 
    {
        $this->_userAReportsToUserB();
        $this->_userBReportsToUserC();

        $team = SugarTestTeamUtilities::createAnonymousTeam();
        $team->add_user_to_team($this->_users['A']->id, $this->_users['A']);
        
        $this->assertUserExplicitlyPartOfTeam($this->_users['A'], $team->name);
        $this->assertUserImplicitlyPartOfTeam($this->_users['B'], $team->name);
        $this->assertUserImplicitlyPartOfTeam($this->_users['C'], $team->name);

        $old_boss = $this->_users['A']->reports_to_id;
        $this->_users['A']->reports_to_id = '';
        $this->_users['A']->save();
        $this->_users['A']->update_team_memberships($old_boss);

        $this->assertUserExplicitlyPartOfTeam($this->_users['A'], $team->name);
        $this->assertUserNotImplicitlyPartOfTeam($this->_users['B'], $team->name);
        $this->assertUserNotImplicitlyPartOfTeam($this->_users['C'], $team->name);

        $this->_users['A']->reports_to_id = $this->_users['C']->id;
        $this->_users['A']->save();
        $this->_users['A']->update_team_memberships('');

        $this->assertUserExplicitlyPartOfTeam($this->_users['A'], $team->name);
        $this->assertUserNotImplicitlyPartOfTeam($this->_users['B'], $team->name);
        $this->assertUserImplicitlyPartOfTeam($this->_users['C'], $team->name);
    }

}
?>