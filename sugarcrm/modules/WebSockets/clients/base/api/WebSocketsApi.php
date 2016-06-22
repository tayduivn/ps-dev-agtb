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

use Sugarcrm\Sugarcrm\Socket\Client as SugarSocketClient;

/**
 * Class WebSocketsApi
 *
 * RESTAPI to work with the WebSockets module configs.
 */
class WebSocketsApi extends SugarApi
{
    /** @var  array */
    protected $exceptionsData;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->exceptionsData['messages'] = return_module_language($GLOBALS['current_language'], 'WebSockets');
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
            'webSocketsConfigGet' => array(
                'reqType' => 'GET',
                'path' => array('WebSockets', 'config'),
                'pathVars' => array('module', ''),
                'method' => 'configGet',
                'shortHelp' => 'Retrieves the config settings for a websockets module',
                'longHelp' => 'include/api/help/module_config_get_help.html',
            ),
            'webSocketsConfigUpdate' => array(
                'reqType' => 'PUT',
                'path' => array('WebSockets', 'config'),
                'pathVars' => array('module', '', ''),
                'method' => 'configSave',
                'shortHelp' => 'Updates the config entries for the websockets module',
                'longHelp' => 'include/api/help/module_config_put_help.html',
            ),
        );
    }

    /**
     * Get function for the WebSockets config admin settings.
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
            'websockets_client_port' => 3001,
            'websockets_client_protocol' => 'http',
            'websockets_client_host' => '',
            'websockets_server_port' => 2999,
            'websockets_server_protocol' => 'http',
            'websockets_server_host' => '',
        );

        if (!empty($cfg->config['websockets']['client']['url'])) {
            $url = parse_url($cfg->config['websockets']['client']['url']);

            if (!empty($url['port'])) {
                $config['websockets_client_port'] = $url['port'];
            }
            if (!empty($url['scheme'])) {
                $config['websockets_client_protocol'] = $url['scheme'];
            }
            if (!empty($url['host'])) {
                $config['websockets_client_host'] = $url['host'];
            }
        }

        if (!empty($cfg->config['websockets']['server']['url'])) {
            $url = parse_url($cfg->config['websockets']['server']['url']);

            if (!empty($url['port'])) {
                $config['websockets_server_port'] = $url['port'];
            }
            if (!empty($url['scheme'])) {
                $config['websockets_server_protocol'] = $url['scheme'];
            }
            if (!empty($url['host'])) {
                $config['websockets_server_host'] = $url['host'];
            }
        }

        return $config;
    }

    /**
     * Saves configuration of websockets server and returns current updated configuration.
     * In case of error exception will be thrown.
     *
     * @param ServiceBase $api The service base
     * @param $args Arguments array built by the service base
     * @return array
     * @throws SugarApiException
     * @throws SugarApiExceptionMissingParameter
     * @throws SugarApiExceptionNotAuthorized
     */

    public function configSave(ServiceBase $api, $args)
    {
        $this->checkAdmin($api);
        $this->requireArgs(
            $args,
            array(
                'websockets_client_port',
                'websockets_client_protocol',
                'websockets_client_host',
                'websockets_server_port',
                'websockets_server_protocol',
                'websockets_server_host',
            )
        );

        $values = $this->prepareUrls($args);
        if (!empty($values['client_url']) || !empty($values['server_url'])) {
            if (!empty($values['client_url']) && !empty($values['server_url'])) {
                $this->checkClientUrl($values);
                $this->checkServerUrl($values);
            } else {
                throw new SugarApiException(
                    $this->exceptionsData['messages']['ERR_WEBSOCKETS_BOTH_CLIENT_SERVER_ERROR'],
                    $this->exceptionsData['args']
                );
            }
        }

        $cfg = $this->getConfigurator();
        $cfg->config['websockets']['server']['url'] = $values['server_url'];
        $cfg->config['websockets']['client']['url'] = $values['client_url'];
        $cfg->config['websockets']['client']['isBalancer'] = $values['client_is_balancer'];

        // set new config values
        $cfg->handleOverride();

        return $this->configGet($api, $args);
    }

    /**
     * Prepare url string from data array.
     *
     * @param array $args Arguments array of url parts
     * @return array
     */
    protected function prepareUrls($args)
    {
        $urls = array(
            'client_url' => '',
            'server_url' => '',
            'client_is_balancer' => false,
        );
        if (!empty($args['websockets_client_host'])) {
            $urls['client_url'] = $args['websockets_client_protocol'] . '://' . $args['websockets_client_host']
                . ':' . $args['websockets_client_port'];
        }
        if (!empty($args['websockets_server_host'])) {
            $urls['server_url'] = $args['websockets_server_protocol'] . '://' . $args['websockets_server_host']
                . ':' . $args['websockets_server_port'];
        }

        return $urls;
    }

    /**
     * Checks client url.
     *
     * @param array $urls Arguments array of urls
     * @return bool
     * @throws SugarApiException
     */
    protected function checkClientUrl(&$urls)
    {
        $clientSettings = $this->getWebSocketsClient()->checkWSSettings($urls['client_url']);
        if (!$clientSettings['available'] || $clientSettings['type'] != 'client') {
            throw new SugarApiException(
                $this->exceptionsData['messages']['ERR_WEBSOCKETS_CLIENT_ERROR'],
                $this->exceptionsData['args']
            );
        } else {
            $urls['client_is_balancer'] = $clientSettings['isBalancer'];
        }

        return true;
    }

    /**
     * Checks server url.
     *
     * @param array $urls Arguments array of urls
     * @return bool
     * @throws SugarApiException
     */
    protected function checkServerUrl($urls)
    {
        $serverSettings = $this->getWebSocketsClient()->checkWSSettings($urls['server_url']);
        if (!$serverSettings['available'] || $serverSettings['type'] != 'server') {
            throw new SugarApiException(
                $this->exceptionsData['messages']['ERR_WEBSOCKETS_SERVER_ERROR'],
                $this->exceptionsData['args']
            );
        }

        return true;
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
        if (!$api->user->isAdmin() && !$api->user->isDeveloperForModule('WebSockets')) {
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
     * Return SugarSocketClient.
     *
     * @return SugarSocketClient
     */
    protected function getWebSocketsClient()
    {
        return SugarSocketClient::getInstance();
    }
}
