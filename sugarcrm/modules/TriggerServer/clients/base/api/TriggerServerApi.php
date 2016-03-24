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

require_once 'modules/Configurator/Configurator.php';

use Sugarcrm\Sugarcrm\Trigger\Client as TriggerServerClient;

/**
 * Class TriggerServerApi
 *
 * RESTAPI to work with the module Trigger Server configs.
 */
class TriggerServerApi extends SugarApi
{
    /** @var array */
    protected $exceptionsData;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->exceptionsData['messages'] = return_module_language($GLOBALS['current_language'], 'TriggerServer');
        $this->exceptionsData['args'] = array('moduleName' => $this->exceptionsData['messages']['LBL_MODULE_NAME']);
    }

    /**
     * Register endpoints.
     *
     * @return array
     */
    public function registerApiRest()
    {
        return array(
            'triggerServerConfigGet' => array(
                'reqType' => 'GET',
                'path' => array('TriggerServer', 'config'),
                'pathVars' => array('module'),
                'method' => 'configGet',
                'shortHelp' => 'Retrieves the config settings for a triggerserver module',
                'longHelp' => 'include/api/help/module_config_get_help.html',
            ),
            'triggerServerConfigUpdate' => array(
                'reqType' => 'POST',
                'path' => array('TriggerServer', 'config'),
                'pathVars' => array('module', '', ''),
                'method' => 'configSave',
                'shortHelp' => 'Updates the config entries for the triggerserver module',
                'longHelp' => 'include/api/help/module_config_put_help.html',
            )
        );
    }

    /**
     * Gets Trigger Server settings from admin's config and prepare url.
     *
     * @param ServiceBase $api The service base
     * @param array $args Arguments array built by the service base
     * @return array
     */
    public function configGet(ServiceBase $api, $args)
    {
        $this->checkAdmin($api);

        $cfg = $this->getConfigurator();
        $config = array(
            'triggerserver_port' => 3000,
            'triggerserver_protocol' => 'http',
            'triggerserver_host' => '',
        );

        if (!empty($cfg->config['trigger_server']['url'])) {
            $url = parse_url($cfg->config['trigger_server']['url']);

            if (!empty($url['port'])) {
                $config['triggerserver_port'] = $url['port'];
            }
            if (!empty($url['scheme'])) {
                $config['triggerserver_protocol'] = $url['scheme'];
            }
            if (!empty($url['host'])) {
                $config['triggerserver_host'] = $url['host'];
            }
        }

        return $config;
    }

    /**
     * Saves configuration of trigger server and returns current updated configuration.
     * In case of error exception will be thrown.
     *
     * @param ServiceBase $api The service base
     * @param string $args Arguments array built by the service base
     * @return array
     */
    public function configSave(ServiceBase $api, $args)
    {
        $this->checkAdmin($api);
        $this->requireArgs(
            $args,
            array(
                'triggerserver_host',
                'triggerserver_port',
                'triggerserver_protocol',
            )
        );
        $url = $this->prepareUrl($args);
        if ($url) {
            $this->checkTriggerServer($url);
        }

        $cfg = $this->getConfigurator();
        $cfg->config['trigger_server']['url'] = $url;

        // set new config values
        $cfg->handleOverride();

        return $this->configGet($api, $args);
    }

    /**
     * Prepare url string from data array.
     *
     * @param array $args url parts
     * @return string
     */
    protected function prepareUrl($args)
    {
        $url = '';
        if (!empty($args['triggerserver_host'])) {
            $url = $args['triggerserver_protocol'] . '://' . $args['triggerserver_host']
                . ':' . $args['triggerserver_port'];
        }

        return $url;
    }

    /**
     * Checks if url is working Triger server url.
     *
     * @param string $url Trigger Server url
     * @throws SugarApiException
     */
    protected function checkTriggerServer($url)
    {
        if (!$this->getTriggerServerClient()->checkTriggerServerSettings($url)) {
            throw new SugarApiException(
                $this->exceptionsData['messages']['ERR_TRIGGER_SERVER_ERROR'],
                $this->exceptionsData['args']
            );
        }
    }

    /**
     * Checks if user is admin.
     *
     * @param ServiceBase $api The service base
     * @throws SugarApiExceptionNotAuthorized
     */
    protected function checkAdmin($api)
    {
        //acl check, only allow if they are module admin
        if (!$api->user->isAdmin() && !$api->user->isDeveloperForModule('TriggerServer')) {
            // No create access so we construct an error message and throw the exception
            throw new SugarApiExceptionNotAuthorized(
                $GLOBALS['app_strings']['EXCEPTION_CHANGE_MODULE_CONFIG_NOT_AUTHORIZED'],
                $this->exceptionsData['args']
            );
        }
    }

    /**
     * Return Configurator.
     *
     * @return Configurator
     */
    protected function getConfigurator()
    {
        return new Configurator();
    }

    /**
     * Return TriggerServerClient.
     *
     * @return TriggerServerClient
     */
    protected function getTriggerServerClient()
    {
        return TriggerServerClient::getInstance();
    }
}
