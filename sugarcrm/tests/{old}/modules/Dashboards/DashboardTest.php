<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use PHPUnit\Framework\TestCase;

class DashboardTest extends TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestDashboardUtilities::removeAllCreatedDashboards();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        SugarTestHelper::tearDown();
    }

    public function testGetDashboards()
    {
         $dashboard = SugarTestDashboardUtilities::createDashboard(
             '',
             array(
                 'dashboard_module' => 'test_module',
                 'view_name' => 'test_view',
             )
         );

         $dashboards = $dashboard->getDashboardsForUser(
             $GLOBALS['current_user'],
             array(
                 'dashboard_module' => 'test_module',
                 'view_name' => 'test_view',
             )
         );

         $this->assertEquals(1, count($dashboards['records']));
    }

    /**
     * Checking legacy behavior of 'view'
     *
     * 1. Creating dashboard with 'view_name'
     * 2. Retrieving dashboard for user with 'view'
     * 3. Asserting that 'view' and 'view_name' equal to original 'view_name'
     */
    public function testGetDashboardsForUser()
    {
        $expected = SugarTestDashboardUtilities::createDashboard(
            '',
            array(
                'assigned_user_id' => $GLOBALS['current_user']->id,
                'dashboard_module' => 'test_module',
                'view_name' => 'test_view',
            )
        );

        $actual = $expected->getDashboardsForUser(
            $GLOBALS['current_user'],
            array(
                'dashboard_module' => 'test_module',
                'view' => 'test_view',
            )
        );

        $this->assertNotEmpty($actual);
        $actual = reset($actual['records']);
        $this->assertEquals($expected->id, $actual->id);
        $this->assertEquals($expected->view_name, $actual->view);
        $this->assertEquals($expected->view_name, $actual->view_name);
    }

    public function testSaveDashboardDefaults()
    {
        $dashboard = SugarTestDashboardUtilities::createDashboard(
            '',
            array(
                'name' => 'Test',
                'dashboard_module' => 'test_module',
                'view_name' => 'test_list',
            )
        );
        $this->assertEquals($GLOBALS['current_user']->id, $dashboard->assigned_user_id);
        $this->assertEquals($GLOBALS['current_user']->getPrivateTeamID(), $dashboard->team_id);
    }
}
