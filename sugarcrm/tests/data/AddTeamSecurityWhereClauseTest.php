<?php
//FILE SUGARCRM flav=pro ONLY

/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('data/SugarBean.php');

class AddTeamSecurityWhereClauseTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
	{
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}

	public function tearDown()
	{
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
	}

	public function testAddTeamSecurityWhereClauseForRegularUser()
	{
        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $bean->disable_row_level_security = false;
        $bean->addVisibilityStrategy('TeamSecurity');
        $query = '';

        $bean->add_team_security_where_clause($query);
        $query = preg_replace("/[\t \n]+/", " ", $query);

        $this->assertEquals(
            "INNER JOIN (select tst.team_set_id from team_sets_teams tst INNER JOIN team_memberships team_memberships ON tst.team_id = team_memberships.team_id AND team_memberships.user_id = '{$GLOBALS['current_user']->id}' AND team_memberships.deleted=0 group by tst.team_set_id) foo_tf on foo_tf.team_set_id = foo.team_set_id ",
            $query
            );
    }

    public function testAddTeamSecurityWhereClauseForRegularUserSpecifyTableAlias()
	{
        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $bean->disable_row_level_security = false;
        $bean->addVisibilityStrategy('TeamSecurity');
        $query = '';

        $bean->add_team_security_where_clause($query,'myfoo');
        $query = preg_replace("/[\t \n]+/", " ", $query);

        $this->assertEquals(
            "INNER JOIN (select tst.team_set_id from team_sets_teams tst INNER JOIN team_memberships team_membershipsmyfoo ON tst.team_id = team_membershipsmyfoo.team_id AND team_membershipsmyfoo.user_id = '{$GLOBALS['current_user']->id}' AND team_membershipsmyfoo.deleted=0 group by tst.team_set_id) myfoo_tf on myfoo_tf.team_set_id = myfoo.team_set_id ",
            $query
            );
    }

    public function testAddTeamSecurityWhereClauseForRegularUserSpecifyJoinType()
	{
	    $this->markTestIncomplete("Unused functionality");
	    $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $bean->disable_row_level_security = false;
        $query = '';

        $bean->add_team_security_where_clause($query,'','LEFT OUTER');
        $query = preg_replace("/[\t \n]+/", " ", $query);

        $this->assertEquals(
            "LEFT OUTER JOIN (select tst.team_set_id from team_sets_teams tst LEFT OUTER JOIN team_memberships team_memberships ON tst.team_id = team_memberships.team_id AND team_memberships.user_id = '{$GLOBALS['current_user']->id}' AND team_memberships.deleted=0 group by tst.team_set_id) foo_tf on foo_tf.team_set_id = foo.team_set_id ",
            $query
            );
    }

    public function testAddTeamSecurityWhereClauseForRegularUserWithJoinTeamsParameterTrue()
	{
        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $bean->disable_row_level_security = false;
        $query = '';
        $bean->addVisibilityStrategy('TeamSecurity');

        $bean->add_team_security_where_clause($query,'','INNER',false,true);
        $query = preg_replace("/[\t \n]+/", " ", $query);
        $this->assertEquals(
            "INNER JOIN (select tst.team_set_id from team_sets_teams tst INNER JOIN team_memberships team_memberships ON tst.team_id = team_memberships.team_id AND team_memberships.user_id = '{$GLOBALS['current_user']->id}' AND team_memberships.deleted=0 group by tst.team_set_id) foo_tf on foo_tf.team_set_id = foo.team_set_id INNER JOIN teams ON teams.id = team_memberships.team_id AND teams.deleted=0 ",
            $query
        );
    }

    public function testAddTeamSecurityWhereClauseWhenRowLevelSecurityIsDisabled()
	{
	    $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $bean->disable_row_level_security = true;
        $bean->addVisibilityStrategy('TeamSecurity');
        $query = '';

        $bean->add_team_security_where_clause($query);

        $this->assertEquals(
            '',
            $query
            );
    }

    public function testAddTeamSecurityWhereClauseWhenModuleIsWorkflow()
	{
	    $bean = new SugarBean();
        $bean->module_dir = 'WorkFlow';
        $bean->table_name = 'workflow';
        $bean->addVisibilityStrategy('TeamSecurity');
        $query = '';

        $bean->add_team_security_where_clause($query);

        $this->assertEquals(
            '',
            $query
            );
    }

    public function testAddTeamSecurityWhereClauseForAdmin()
	{
	    $GLOBALS['current_user']->is_admin = 1;

        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $bean->addVisibilityStrategy('TeamSecurity');
        $query = '';

        $bean->add_team_security_where_clause($query);

        $this->assertEquals(
            '',
            $query
            );
    }

    public function testAddTeamSecurityWhereClauseForAdminWhenForceAdminIsTrue()
	{
	    $this->markTestIncomplete("Unused functionality");
	    $GLOBALS['current_user']->is_admin = 1;

        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $bean->addVisibilityStrategy('TeamSecurity');
        $query = '';

        $bean->add_team_security_where_clause($query,'','INNER',true);
        $query = preg_replace("/[\t \n]+/", " ", $query);

        $this->assertEquals(
            "INNER JOIN (select tst.team_set_id from team_sets_teams tst INNER JOIN team_memberships team_memberships ON tst.team_id = team_memberships.team_id AND team_memberships.user_id = '{$GLOBALS['current_user']->id}' AND team_memberships.deleted=0 group by tst.team_set_id) foo_tf on foo_tf.team_set_id = foo.team_set_id ",
            $query
            );
    }

    /**
     * @ticket 26772
     */
	public function testAddTeamSecurityWhereClauseForAdminForModule()
	{
	    $_SESSION[$GLOBALS['current_user']->user_name.'_get_admin_modules_for_user'] = array('Foo');

        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $query = '';
        $bean->addVisibilityStrategy('TeamSecurity');

        $bean->add_team_security_where_clause($query);

        $this->assertEquals(
            '',
            $query
            );

        unset($_SESSION[$GLOBALS['current_user']->user_name.'_get_admin_modules_for_user']);
    }
}