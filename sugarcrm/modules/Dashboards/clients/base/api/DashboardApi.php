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

require_once('data/BeanFactory.php');
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
            'getDashboardsForModule' => array(
                'reqType' => 'GET',
                'path' => array('Dashboards', '<module>'),
                'pathVars' => array('', 'module'),
                'method' => 'getDashboards',
                'shortHelp' => 'Get dashboards for a module',
                'longHelp' => 'include/api/help/get_dashboards.html',
            ),
            'getDashboardsForHome' => array(
                'reqType' => 'GET',
                'path' => array('Dashboards'),
                'pathVars' => array(''),
                'method' => 'getDashboards',
                'shortHelp' => 'Get dashboards for home',
                'longHelp' => 'include/api/help/get_dashboards.html',
            ),
            'createDashboardForModulePost' => array(
                'reqType' => 'POST',
                'path' => array('Dashboards', '<module>'),
                'pathVars' => array('', 'module'),
                'method' => 'createDashboard',
                'shortHelp' => 'Create a new dashboard for a module',
                'longHelp' => 'include/api/help/create_dashboard.html',
            ),
            'createDashboardForHomePost' => array(
                'reqType' => 'POST',
                'path' => array('Dashboards'),
                'pathVars' => array(''),
                'method' => 'createDashboard',
                'shortHelp' => 'Create a new dashboard for home',
                'longHelp' => 'include/api/help/create_dashboard.html',
            ),
            'createDashboardForModulePut' => array(
                'reqType' => 'PUT',
                'path' => array('Dashboards', '<module>'),
                'pathVars' => array('', 'module'),
                'method' => 'createDashboard',
                'shortHelp' => 'Create a new dashboard for a module',
                'longHelp' => 'include/api/help/create_dashboard.html',
            ),
            'createDashboardForHomePut' => array(
                'reqType' => 'PUT',
                'path' => array('Dashboards'),
                'pathVars' => array(''),
                'method' => 'createDashboard',
                'shortHelp' => 'Create a new dashboard for home',
                'longHelp' => 'include/api/help/create_dashboard.html',
            ),
        );
        return $dashboardApi;
    }

    /**
     * Get the dashboards for the current user
     *
     * @param ServiceBase $api      The Api Class
     * @param array $args           Service Call Arguments
     * @return mixed
     */
    public function getDashboards($api, $args)
    {
        global $current_user;
        
        if (!isset($args['module'])) {
            $args['module'] = 'Home';
        }

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
    
    /**
     * Create a new dashboard
     *
     * @param ServiceBase $api      The Api Class
     * @param array $args           Service Call Arguments
     * @return mixed
     */
    public function createDashboard($api, $args) {
        if (!isset($args['module'])) {
            $args['module'] = 'Home';
        }

        $bean = BeanFactory::newBean('Dashboards');
        
        if (!$bean->ACLAccess('save')) {
            // No create access so we construct an error message and throw the exception
            $failed_module_strings = return_module_language($GLOBALS['current_language'], 'Dashboards');
            $moduleName = $failed_module_strings['LBL_MODULE_NAME'];
            $args = null;
            if(!empty($moduleName)){
                $args = array('moduleName' => $moduleName);
            }
            throw new SugarApiExceptionNotAuthorized('EXCEPTION_CREATE_MODULE_NOT_AUTHORIZED', $args);
        }

        $id = $this->updateBean($bean, $api, $args);
        $args['record'] = $id;
        $args['module'] = 'Dashboards';
        $bean = $this->loadBean($api, $args, 'view');
        $data = $this->formatBean($api, $args, $bean);
        return $data;
    }
    
    protected function matchModule( $module ) {
        $GLOBALS['log']->fatal(print_r(array_keys($GLOBALS['beanList'])));
        return isset($GLOBALS['beanList'][$module]) || $module == 'Home';
    }
}
