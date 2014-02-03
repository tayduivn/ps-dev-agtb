<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

require_once 'modules/Teams/Team.php';

/**
 * RS168: Prepare Teams Module.
 */
class RS168Test extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function testRetrieves()
    {
        $team = SugarTestTeamUtilities::createAnonymousTeam();
        $user = SugarTestUserUtilities::createAnonymousUser(true, false);
        $id = $team->retrieve_team_id($team->name);
        $this->assertEquals($team->id, $id);
        $users = $team->get_team_members();
        $this->assertEmpty($users);
        $teams = $team->getArrayAllAvailable(false, $user);
        $this->assertGreaterThan(2, $teams);
    }

    public function testScans()
    {
        $team = SugarTestTeamUtilities::createAnonymousTeam();
        $user2 = SugarTestUserUtilities::createAnonymousUser(true, false);

        $user1 = SugarTestUserUtilities::createAnonymousUser(false, false);
        $user1->reports_to_id = $user2->id;
        $user1->save(false);

        $result = $team->scan_direct_reports_for_access($user2->id);
        $this->assertTrue($result);
        DBManagerFactory::getInstance()->commit();
        $result = $team->scan_direct_reports_team_for_access($user1->id);
        $this->assertFalse($result);
    }

    public function testReassign()
    {
        $team1 = SugarTestTeamUtilities::createAnonymousTeam();
        $team2 = SugarTestTeamUtilities::createAnonymousTeam();
        $team2->reassign_team_records(array($team1->id));
        $team = BeanFactory::getBean('Teams');
        $team->retrieve($team1->id, true, false);
        $this->assertEquals(1, $team->deleted);
    }
}
