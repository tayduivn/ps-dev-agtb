<?php
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
require_once('clients/base/api/FilterApi.php');

class DashboardListApi extends FilterApi
{
    protected static $mandatory_fields = array(
        'id',
        'name',
        'view'
    );

    /**
     * Rest Api Registration Method
     *
     * @return array
     */
    public function registerApiRest()
    {
        return array(
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
        );
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
        if (empty($args['filter'])||!is_array($args_filter)) {
            $args['filter'] = array();
        }

        // Tack on some required filters.
        $module = empty($args['module']) ? 'Home' : $args['module'];
        $args['filter'][]['dashboard_module'] = $module;

        $args['module'] = 'Dashboards';

        if (!empty($args['view'])) {
            $args['filter'][]['view'] = $args['view'];
        }
        $args['fields'] = 'id,name,view';

        $ret = $this->filterList($api, $args);
        
        // Add dashboard URL's
        foreach ($ret['records'] as $idx => $dashboard) {
            $ret['records'][$idx]['url'] = $api->getResourceURI('Dashboards/'.$dashboard['id']);
        }
        
        return $ret;
    }

    /**
     * Redefine the getoptions to pull in the correct Dashboard filters
     */
    protected function parseArguments(ServiceBase $api, array $args, SugarBean $seed = null)
    {
        if (!isset($args['order_by'])) {
            $args['order_by'] = 'date_entered:DESC';
        }
        $options = parent::parseArguments($api, $args, $seed);
        
        return $options;
    }

}
