<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

require_once('clients/base/api/UnifiedSearchApi.php');

class PersonUnifiedSearchApi extends UnifiedSearchApi {
    public function registerApiRest() {
        return array(
            'UserSearch' => array(
                'reqType' => 'GET',
                'path' => array('Users'),
                'pathVars' => array('module_list'),
                'method' => 'globalSearch',
                'shortHelp' => 'Search User records',
                'longHelp' => 'include/api/help/module_get_help.html',
            ),
            'EmployeeSearch' => array(
                'reqType' => 'GET',
                'path' => array('Employees'),
                'pathVars' => array('module_list'),
                'method' => 'globalSearch',
                'shortHelp' => 'Search Employee records',
                'longHelp' => 'include/api/help/module_get_help.html',
            ),
        );
    }

    /**
     * This function is the global search
     * @param $api ServiceBase The API class of the request
     * @param $args array The arguments array passed in from the API
     * @return array result set
     */
    public function globalSearch(ServiceBase $api, array $args) {
        $api->action = 'list';
        // This is required to keep the loadFromRow() function in the bean from making our day harder than it already is.
        $GLOBALS['disable_date_format'] = true;
        $options = $this->parseSearchOptions($api,$args);
        $options['custom_where'] = $this->getCustomWhereForModule($args['module_list']);

        $searchEngine = new SugarSpot();
        $options['resortResults'] = true;
        $recordSet = $this->globalSearchSpot($api,$args,$searchEngine,$options);
        
        return $recordSet;
    }

    /**
     * Gets the proper query where clause to use to prevent special user types from
     * being returned in the result
     * 
     * @param string $module The name of the module we are looking for
     * @return string
     */
    protected function getCustomWhereForModule($module) {

        if ($module == 'Employees') {
            return "users.employee_status = 'Active' AND users.show_on_employees = 1";
        } 
        
        return "users.status = 'Active' AND users.portal_only = 0";
    }
}
