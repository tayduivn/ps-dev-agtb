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

require_once('modules/Configurator/Configurator.php');
require_once 'modules/Administration/QuickRepairAndRebuild.php';

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory as AdapterFactory;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AclInterface;
use Sugarcrm\Sugarcrm\JobQueue\Manager\Manager as JQManager;

/**
 * Class CalDavApi
 *
 * RESTAPi to work with the module configs caldav
 */
class CalDavApi extends SugarApi
{
    /**
     * Value list
     *
     * @var array
     */
    protected $valuesList = array(
        'module',
        'interval',
        'call_direction',
    );

    /**
     * Value list
     *
     * @var array
     */
    protected $contentValuesList = array(
        'module' => 'getSupportedCalDavModules',
        'interval' => 'getOldestSyncDates',
        'call_direction' => 'getCallDirections',
    );

    public function registerApiRest()
    {
        return array(
            'caldavConfigGet' => array(
                'reqType' => 'GET',
                'path' => array('caldav', 'config'),
                'pathVars' => array('module', ''),
                'method' => 'configGet',
                'shortHelp' => 'Retrieves the config settings for a caldav module',
                'longHelp' => 'include/api/help/module_config_get_help.html',
            ),
            'caldavConfigUpdate' => array(
                'reqType' => 'PUT',
                'path' => array('caldav', 'config'),
                'pathVars' => array('module', ''),
                'method' => 'configSave',
                'shortHelp' => 'Updates the config entries for the caldav module',
                'longHelp' => 'include/api/help/module_config_put_help.html',
            ),
            'caldavUserConfigGet' => array(
                'reqType' => 'GET',
                'path' => array('caldav', 'config', 'user'),
                'pathVars' => array('module', '', ''),
                'method' => 'userConfigGet',
                'shortHelp' => 'Retrieves the config settings for a caldav module',
                'longHelp' => 'include/api/help/module_config_get_help.html',
            ),
            'caldavUserConfigUpdate' => array(
                'reqType' => 'PUT',
                'path' => array('caldav', 'config', 'user'),
                'pathVars' => array('module', '', ''),
                'method' => 'userConfigSave',
                'shortHelp' => 'Updates the config entries for the caldav module',
                'longHelp' => 'include/api/help/module_config_put_help.html',
            ),
        );
    }

    /**
     * Get function for the caldav config admin settings
     *
     * @param ServiceBase $api
     * @param $args 'platform' is optional and defaults to 'base'
     * @return array
     */
    public function configGet(ServiceBase $api, $args)
    {
        $this->checkAdmin($api);

        $caldav_config = array(
            'modules' => $this->getSupportedCalDavModules(),
            'intervals' => $this->getOldestSyncDates(),
            'call_directions' => $this->getCallDirections(),
            'values' => $this->getDefaultsValues()
        );

        return $caldav_config;
    }

    /**
     * Get function for the caldav config admin settings
     *
     * @param ServiceBase $api
     * @param $args 'platform' is optional and defaults to 'base'
     * @return array
     */
    public function configSave(ServiceBase $api, $args)
    {
        $this->checkAdmin($api);

        $values = $this->checkArgs($args);

        $this->adminConfigSave($values);

        return $this->configGet($api, $args);
    }

    /**
     * Admin config save CalDav settings
     *
     * @param array $values then returned checkArgs
     */
    public function adminConfigSave($values)
    {
        if (!empty($values)) {
            $cfg = $this->getConfigurator();
            $oldEnable = $cfg->config['caldav_enable_sync'];
            foreach ($values as $key => $val) {
                if ('caldav_enable_sync' == $key) {
                    $cfg->config[$key] = $val;
                } else {
                    $cfg->config['default_' . $key] = $val;
                }
            }
            // set new config values
            $cfg->handleOverride();
            if ($cfg->config['caldav_enable_sync'] != $oldEnable) {
                if ($cfg->config['caldav_enable_sync']) {
                    $this->getJQManager()->CalDavRebuild();
                }
                $this->getRepairAndClear()->repairAndClearAll(array('clearAll'), array('Calendar'), false, false);
            }
        }
    }

    /**
     * Get manager object for handler processing.
     *
     * @return \Sugarcrm\Sugarcrm\JobQueue\Manager\Manager
     */
    protected function getJQManager()
    {
        return new JQManager();
    }

    /**
     * Get function for the caldav config user settings
     *
     * @param ServiceBase $api
     * @param $args 'platform' is optional and defaults to 'base'
     * @return array
     */
    public function userConfigGet(ServiceBase $api, $args)
    {
        global $current_user;

        $supportedModules = $this->getSupportedCalDavModules();
        $defaultModule = $current_user->getPreference('caldav_module');
        $caldav_config = array(
            'modules' => $supportedModules,
            'intervals' => $this->getOldestSyncDates(),
            'call_directions' => $this->getCallDirections(),
            'values' => array(
                'caldav_module' => in_array($defaultModule, $supportedModules) ? $defaultModule : null,
                'caldav_interval' => $current_user->getPreference('caldav_interval'),
                'caldav_call_direction' => $current_user->getPreference('caldav_call_direction'),
            )
        );

        return $caldav_config;
    }

    /**
     * Get function for the caldav config user settings
     *
     * @param ServiceBase $api
     * @param $args 'platform' is optional and defaults to 'base'
     * @return array
     */
    public function userConfigSave(ServiceBase $api, $args)
    {
        $values = $this->checkArgs($args);
        if (isset($values['caldav_enable_sync'])) {
            unset($values['caldav_enable_sync']);
        }
        $this->userConfigUpdate($values);

        return $this->userConfigGet($api, $args);
    }

    /**
     * User config update CalDav settings
     *
     * @param array $values then returned checkArgs
     */
    public function userConfigUpdate($values)
    {
        global $current_user;

        if (!empty($values)) {
            foreach ($values as $key => $val) {
                $current_user->setPreference($key, $val);
            }
            $current_user->save();
        }
    }

    /**
     * Return enabled CalDav modules.
     *
     * @return array CalDav modules.
     */
    public function getSupportedCalDavModules()
    {
        $adapterFactory = $this->getAdapterFactory();
        $modules = array();

        foreach ($adapterFactory->getSupportedModules() as $module) {
            if (\SugarACL::checkAccess($module, 'access')) {
                $modules[$module] = $GLOBALS['app_list_strings']['moduleList'][$module];
            }
        }
        return $modules;
    }

    /**
     * Return oldestSyncDates array
     *
     * @return array
     */
    public function getOldestSyncDates()
    {
        global $app_list_strings;
        return $app_list_strings['caldav_oldest_sync_date'];
    }

    /**
     * Return Call Directions array
     *
     * @return array
     */
    public function getCallDirections()
    {
        global $app_list_strings;
        return $app_list_strings['call_direction_dom'];
    }

    /**
     * Returns checked values
     *
     * @param $args 'platform' is optional and defaults to 'base'
     * @return array args
     */
    public function checkArgs($args)
    {
        $out = $this->getDefaultsValues();

        foreach ($this->valuesList as $valueName) {
            if (isset($args['caldav_' . $valueName])) {
                $values = $this->{$this->contentValuesList[$valueName]}();
                if (isset($values[$args['caldav_' . $valueName]])) {
                    $out['caldav_' . $valueName] = $args['caldav_' . $valueName];
                }
            }
        }
        if (isset($args['caldav_enable_sync'])) {
            $out['caldav_enable_sync'] = $args['caldav_enable_sync'];
        }
        return $out;
    }

    /**
     * Return defaults values
     *
     * @return array
     */
    public function getDefaultsValues()
    {
        $cfg = $this->getConfigurator();

        return array(
            'caldav_enable_sync' => $cfg->config['caldav_enable_sync'],
            'caldav_module' => $cfg->config['default_caldav_module'],
            'caldav_interval' => $cfg->config['default_caldav_interval'],
            'caldav_call_direction' => $cfg->config['default_caldav_call_direction'],
        );
    }

    /**
     * Return Configurator
     *
     * @return Configurator
     */
    public function getConfigurator()
    {
        return new Configurator();
    }

    /**
     * Check user
     *
     * @throws SugarApiExceptionNotAuthorized
     *
     * @param ServiceBase $api
     */
    protected function checkAdmin($api)
    {
        //acl check, only allow if they are module admin
        if (!$api->user->isAdmin() && !$api->user->isDeveloperForModule('caldav')) {
            // No create access so we construct an error message and throw the exception
            $failed_module_strings = return_module_language($GLOBALS['current_language'], 'caldav');
            $moduleName = $failed_module_strings['LBL_MODULE_NAME'];

            $args = null;
            if (!empty($moduleName)) {
                $args = array('moduleName' => $moduleName);
            }
            throw new SugarApiExceptionNotAuthorized(
                $GLOBALS['app_strings']['EXCEPTION_CHANGE_MODULE_CONFIG_NOT_AUTHORIZED'],
                $args
            );
        }
    }

    /**
     * Return RepairAndClear.
     *
     * @return RepairAndClear
     */
    protected function getRepairAndClear()
    {
        $repair = new RepairAndClear();
        return $repair;
    }

    /**
     * Return DavCal adapter factory.
     *
     * @return AdapterFactory
     */
    protected function getAdapterFactory()
    {
        $adapters = new AdapterFactory();
        return $adapters;
    }
}
