<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once('modules/Configurator/Configurator.php');

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory as Adapters;

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
        'module'=>'getSupportedCalDavModules',
        'interval'=>'getOldestSyncDates',
        'call_direction'=>'getCallDirections',
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

        $this->adminconfigSave($values);

        return $this->configGet($api, $args);
    }

    /**
     * Admin config save CalDav settings
     *
     * @param array $values then returned checkArgs
     */
    public function adminConfigSave($values)
    {
        if (!empty($values['update'])) {
            $cfg = $this->getConfigurator();

            foreach ($values['update'] as $val) {
                $cfg->config['default_' . $val] = $values[$val];
            }
            // set new config values
            $cfg->handleOverride();
        }
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

        $caldav_config = array(
            'modules' => $this->getSupportedCalDavModules(),
            'intervals' => $this->getOldestSyncDates(),
            'call_directions' => $this->getCallDirections(),
            'values' => array(
                'caldav_module' => $current_user->getPreference('caldav_module'),
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

        $this->userConfigUpdate($values);
        $this->userConfigDelete($values);

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

        if (!empty($values['update'])) {
            foreach ($values['update'] as $val) {
                $current_user->setPreference($val, $values[$val]);
            }
            $current_user->save();
        }
    }

    /**
     * User config delete/set default CalDav settings
     *
     * @param array $values then returned checkArgs
     */
    public function userConfigDelete($values)
    {
        global $current_user;

        if (!empty($values['delete'])) {
            foreach ($values['delete'] as $val) {
                $current_user->removePreference($val);
            }
            $current_user->save();
        }
    }

    /**
     * Return enable CalDav modules
     *
     * @return array CalDav modules
     */
    public function getSupportedCalDavModules()
    {
        $adapters = new Adapters;
        $modules = $adapters->getSupportedModules();
        return array_combine($modules, $modules);
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
        $out['update'] = array();
        $out['delete'] = array();

        foreach ($this->valuesList as $valueName) {
            if (isset($args['caldav_' . $valueName])) {
                $values = $this->{$this->contentValuesList[$valueName]}();
                if (isset($values[$args['caldav_' . $valueName]])) {
                    if ($out['caldav_' . $valueName] != $args['caldav_' . $valueName]) {
                        $out['update'][] = 'caldav_' . $valueName;
                    } else {
                        $out['delete'][] = 'caldav_' . $valueName;
                    }
                    $out['caldav_' . $valueName] = $args['caldav_' . $valueName];
                }
            }
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
}
