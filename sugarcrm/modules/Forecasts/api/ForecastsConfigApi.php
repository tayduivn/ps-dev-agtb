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

require_once('include/api/ModuleApi.php');

class ForecastsConfigApi extends ModuleApi {

    public function __construct()
    {

    }

    public function registerApiRest()
    {
        $parentApi = parent::registerApiRest();
        //Extend with test method
        $parentApi= array (
            'config' => array(
                'reqType' => 'GET',
                'path' => array('<module>','config'),
                'pathVars' => array('module',''),
                'method' => 'config',
                'shortHelp' => 'forecasts config',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastConfigApi.html#config',
                'noLoginRequired' => true,
            ),
            'configCreate' => array(
                'reqType' => 'POST',
                'path' => array('<module>','config'),
                'pathVars' => array('module',''),
                'method' => 'configSave',
                'shortHelp' => 'create forecasts config',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastConfigApi.html#configCreate',
            ),
            'configUpdate' => array(
                'reqType' => 'PUT',
                'path' => array('<module>','config'),
                'pathVars' => array('module',''),
                'method' => 'configSave',
                'shortHelp' => 'Update forecasts config',
                'longHelp' => 'include/api/html/modules/Forecasts/ForecastConfigApi.html#configUpdate',
            ),
        );
        return $parentApi;
    }

    /**
     * Returns the config settings for the given module
     * @param $api
     * @param $args 'module' is required, 'platform' is optional and defaults to 'base'
     */
    public function config($api, $args) {
        $this->requireArgs($args,array('module'));
        $adminBean = BeanFactory::getBean("Administration");

        $platform = (isset($args['platform']) && !empty($args['platform']))?$args['platform']:'base';

        if (!empty($args['module'])) {
            $data = $adminBean->getConfigForModule($args['module'], $platform);
        } else {
            return;
        }

        return $data;
    }

    /**
     * Save function for the config settings for a given module.
     * @param $api
     * @param $args 'module' is required, 'platform' is optional and defaults to 'base'
     */
    public function configSave($api, $args) {
        $this->requireArgs($args,array('module'));
        $admin = BeanFactory::getBean('Administration');

        $module = $args['module'];
        $platform = (isset($args['platform']) && !empty($args['platform']))?$args['platform']:'base';

        // these are not part of the config values, so unset
        unset($args['module']);
        unset($args['platform']);
        unset($args['__sugar_url']);

        foreach ($args as $name => $value) {
            if(is_array($value)) {
                $admin->saveSetting($module, $name, json_encode($value), $platform);
            } else {
                $admin->saveSetting($module, $name, $value, $platform);
            }
        }
    }


}
