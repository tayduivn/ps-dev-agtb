<?php
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

require_once 'modules/Dashboards/Dashboard.php';

class SugarTestDashboardUtilities
{
    private static $_createdDashboards = array();

    private function __construct() {}

    public static function createDashboard($id = '', $dashboardValues = array())
    {
        $time = mt_rand();
        $dashboard = BeanFactory::newBean('Dashboards');

        if (isset($dashboardValues['name'])) {
            $dashboard->name = $dashboardValues['name'];
        } else {
            $dashboard->name = 'SugarDashboard' . $time;
        }

        if (isset($dashboardValues['dashboard_module'])) {
            $dashboard->dashboard_module = $dashboardValues['dashboard_module'];
        } else {
            $dashboard->dashboard_module = 'Home';
        }

        if(!empty($id))
        {
            $dashboard->new_with_id = true;
            $dashboard->id = $id;
        }
        $dashboard->save();
        $GLOBALS['db']->commit();
        self::$_createdDashboards[] = $dashboard;
        return $dashboard;
    }

    public static function removeAllCreatedAccounts()
    {
        $dashboard_ids = self::getCreatedDashboardIds();
        if (count($dashboard_ids)) {
            $GLOBALS['db']->query('DELETE FROM dashboards WHERE id IN (\'' . implode("', '", $dashboard_ids) . '\')');
        }
    }

    public static function getCreatedDashboardIds()
    {
        $dashboard_ids = array();
        foreach (self::$_createdDashboards as $dashboard) {
            $dashboard_ids[] = $dashboard->id;
        }
        return $dashboard_ids;
    }
}