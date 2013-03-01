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

require_once('clients/base/api/ModuleApi.php');

class ConfigModuleApi extends ModuleApi {

    public function registerApiRest()
    {
        return array (
            'config' => array(
                'reqType' => 'GET',
                'path' => array('<module>','config'),
                'pathVars' => array('module',''),
                'method' => 'config',
                'shortHelp' => 'Retrieves the config settings for a given module',
                'longHelp' => 'include/api/help/config_get_help.html',
            ),
            'configCreate' => array(
                'reqType' => 'POST',
                'path' => array('<module>','config'),
                'pathVars' => array('module',''),
                'method' => 'configSave',
                'shortHelp' => 'Creates the config entries for the given module',
                'longHelp' => 'include/api/help/config_put_help.html',
            ),
            'configUpdate' => array(
                'reqType' => 'PUT',
                'path' => array('<module>','config'),
                'pathVars' => array('module',''),
                'method' => 'configSave',
                'shortHelp' => 'Updates the config entries for given module',
                'longHelp' => 'include/api/help/config_put_help.html',
            ),
        );
    }

    /**
     * Returns the config settings for the given module
     * @param $api
     * @param $args 'module' is required, 'platform' is optional and defaults to 'base'
     */
    public function config($api, $args) {
        $this->requireArgs($args,array('module'));
        $seed = BeanFactory::newBean($args['module']);
        $adminBean = BeanFactory::getBean("Administration");

        //acl check
        if(!$seed->ACLAccess('access')) {
            // No create access so we construct an error message and throw the exception
            $moduleName = null;
            if(isset($args['module'])){
                $failed_module_strings = return_module_language($GLOBALS['current_language'], $args['module']);
                $moduleName = $failed_module_strings['LBL_MODULE_NAME'];
            }
            $args = null;
            if(!empty($moduleName)){
                $args = array('moduleName' => $moduleName);
            }
            throw new SugarApiExceptionNotAuthorized($GLOBALS['app_strings']['EXCEPTION_ACCESS_MODULE_CONFIG_NOT_AUTHORIZED'], $args);
        }

        if (!empty($args['module'])) {
            return $adminBean->getConfigForModule($args['module'], $api->platform);
        }
        return;
    }

    /**
     * Save function for the config settings for a given module.
     * @param $api
     * @param $args 'module' is required, 'platform' is optional and defaults to 'base'
     */
    public function configSave($api, $args) {
        $this->requireArgs($args,array('module'));

        $module = $args['module'];

        // these are not part of the config values, so unset
        unset($args['module']);
        unset($args['__sugar_url']);

        //acl check, only allow if they are module admin
        if(!$api->user->isAdminForModule($module)) {
            // No create access so we construct an error message and throw the exception
            $moduleName = null;
            if(isset($args['module'])){
                $failed_module_strings = return_module_language($GLOBALS['current_language'], $args['module']);
                $moduleName = $failed_module_strings['LBL_MODULE_NAME'];
            }
            $args = null;
            if(!empty($moduleName)){
                $args = array('moduleName' => $moduleName);
            }
            throw new SugarApiExceptionNotAuthorized($GLOBALS['app_strings']['EXCEPTION_CHANGE_MODULE_CONFIG_NOT_AUTHORIZED'], $args);
        }

        $admin = BeanFactory::getBean('Administration');

        foreach ($args as $name => $value) {
            if(is_array($value)) {
                $admin->saveSetting($module, $name, json_encode($value), $api->platform);
            } else {
                $admin->saveSetting($module, $name, $value, $api->platform);
            }
        }

        MetaDataManager::clearAPICache();

        return $admin->getConfigForModule($module, $api->platform);
    }

}
