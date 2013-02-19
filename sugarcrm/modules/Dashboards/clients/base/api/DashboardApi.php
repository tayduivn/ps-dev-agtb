<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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

require_once('include/api/SugarApi.php');

class DashboardApi extends SugarApi
{
    /**
     * Rest Api Registration Method
     *
     * @return array
     */
    public function registerApiRest()
    {
        $dashboardApi = array(
            'dashboard' => array(
                'reqType' => 'GET',
                'path' => array('Dashboards'),
                'pathVars' => array(),
                'method' => 'dashboard',
                'shortHelp' => 'Home page dashboard',
                'longHelp' => 'include/api/help/dashboardapi.html',
            ),
        );
        return $dashboardApi;
    }

    /**
     * Build get the dashboard for the current user
     *
     * @param ServiceBase $api      The Api Class
     * @param array $args           Service Call Arguments
     * @return mixed
     */
    public function dashboard($api, $args)
    {
        global $current_user;
        $dashboards = BeanFactory::newBean('Dashboards')->getDashboardsForUser($current_user, $args);

        $sortedResults = array();
        foreach ( $dashboards['records'] as $dashboard ) {
            $sortedResults[] = array('id'=>$dashboard->id,'name'=>$dashboard->name, 'url' => $api->getResourceURI('Dashboards/'.$dashboard->id));
        }

        return array(
            "next_offset" => $dashboards['next_offset'],
            "records" => $sortedResults
        );
    }
}
