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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once 'modules/Dashboards/Dashboard.php';

class DashboardTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $dashboardId;

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        if(!empty($this->dashboardId)) {
            $GLOBALS['db']->query("DELETE FROM dashboards WHERE id='{$this->dashboardId}'");
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset( $GLOBALS['current_user']);
    }

    public function testGetDashboards()
    {
         $dashboard = new Dashboard();
         $this->dashboardId = $dashboard->id = create_guid();
         $dashboard->new_with_id = 1;
         $dashboard->name = 'Test';
         $dashboard->dashboard_module = 'Home';
         $dashboard->view_name = 'list';
         $dashboard->save();
         $dashboard = new dashboard();
         $options = array('dashboard_module'=>'Home', 'view_name'=>'list');
         $dashboards = $dashboard->getDashboardsForUser($GLOBALS['current_user'], $options);
         $this->assertEquals(1, count($dashboards['records']));
    }

    /**
     * Checking legacy behavior of 'view'
     *
     * 1. Creating dashboard with 'view_name'
     * 2. Retrieving dashboard for user with 'view'
     * 3. Asserting that 'view' and 'view_name' equal to original 'view_name'
     */
    public function testgetDashboardsForUser()
    {
        $expected = new Dashboard();
        $expected->name = create_guid();
        $expected->assigned_user_id = $GLOBALS['current_user'];
        $expected->dashboard_module = 'Accounts';
        $expected->view_name = 'records';
        $expected->save();
        $this->dashboardId = $expected->id;

        $actual = $expected->getDashboardsForUser($GLOBALS['current_user'], array(
                'dashboard_module' => 'Accounts',
                'view' => 'records',
            ));
        $this->assertNotEmpty($actual);
        $actual = reset($actual['records']);
        $this->assertEquals($expected->id, $actual->id);
        $this->assertEquals($expected->view_name, $actual->view);
        $this->assertEquals($expected->view_name, $actual->view_name);
    }
}
