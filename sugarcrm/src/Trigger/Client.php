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
namespace Sugarcrm\Sugarcrm\Trigger;

/**
 * Class Client allows us to setup trigger to remote trigger server.
 * When time comes trigger server sends http request
 * to specified uri and with specified http method and params.
 *
 * Before use it recommend to check is trigger server URL was specified.
 *
 * For adding new trigger use @see Client::push().
 * If trigger with specified $id don't exist on trigger server then one will be added.
 * If trigger exists the $method, callback $uri and $args will be updated.
 *
 * For deleting trigger use @see Client::delete().
 *
 * Examples:
 * <code>
 * // instantiate client
 * $triggerServerClient = Client::getInstance();
 *
 * // check is trigger server URL was specified
 * $isConfigured = $triggerServerClient->isConfigured();
 *
 * // add new trigger
 * $isSuccess = $triggerServerClient->push(
 *      '20db6fcd-0ce9-4da4-87d1-fae1d563b5a2',
 *      '2015-09-30T12:00:00',
 *      'get',
 *      'trigger/callback');
 *
 * // update existing trigger
 * $args = array(
 *      'param1' => 'some param',
 *      'numberParam' => 1
 * );
 * $isSuccess = $triggerServerClient->push(
 *      '20db6fcd-0ce9-4da4-87d1-fae1d563b5a2',
 *      '2015-09-30T12:25:00',
 *      'post',
 *      'trigger/callback/post',
 *      $args);
 *
 * // delete trigger
 * $isSuccess = $triggerServerClient->delete('20db6fcd-0ce9-4da4-87d1-fae1d563b5a2');
 * </code>
 *
 * @package Sugarcrm\Sugarcrm\Trigger
 */
class Client
{

    /**
     * Returns object of Client
     * @return Client
     * @codeCoverageIgnore
     */
    public static function getInstance()
    {
        $class = \SugarAutoLoader::customClass('Sugarcrm\Sugarcrm\Trigger\Client');
        return new $class();
    }

    /**
     * @return HttpHelper
     */
    protected function getHttpHelper()
    {
        return new HttpHelper();
    }

    /**
     * @return \Administration
     * @codeCoverageIgnore
     */
    protected function getAdministrationBean()
    {
        return \BeanFactory::getBean('Administration');
    }

    /**
     * @return \SugarConfig
     */
    protected function getSugarConfig()
    {
        return \SugarConfig::getInstance();
    }

    /**
     * @return String
     */
    protected function retrieveToken()
    {
        $admin = $this->getAdministrationBean();
        $config = $admin->getConfigForModule('auth');
        if (empty($config['trigger_server_token'])) {
            $token = $this->createGuid();
            $admin->saveSetting('auth', 'trigger_server_token', $token, 'base');
        } else {
            $token = $config['trigger_server_token'];
        }
        return $token;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    protected function createGuid()
    {
        return create_guid();
    }

    /**
     * @return bool
     */
    public function isConfigured()
    {
        $triggerServerUrl = $this->getSugarConfig()->get('trigger_server.url');
        return !empty($triggerServerUrl);
    }

    /**
     * Add or update trigger. If method is GET or DELETE then args will be received in callback as query string.
     * Otherwise args will be received as form data.
     * @param string $id unique trigger's id
     * @param string $stamp date in GMT. Format: Y-m-dTH:i:s or any compatible with javascript Date() function
     * @param string $method callback method. Available GET, POST, PUT, DELETE
     * @param string $uri callback uri (relative)
     * @param array|null $args additional callback arguments
     * @return bool was trigger added or updated
     */
    public function push($id, $stamp, $method, $uri, $args = null)
    {
        $token = $this->retrieveToken();
        $method = strtolower($method);

        $params = array(
            'url' => $this->getSugarConfig()->get('site_url'),
            'id' => $id,
            'token' => $token,
            'stamp' => $stamp,
            'trigger' => array(
                'url' => $uri,
                'method' => $method
            )
        );
        if ($args) {
            if (in_array($method, array('get', 'delete'))) {
                $params['trigger']['url'] .= '?'.http_build_query($args);
            } else {
                $params['trigger']['args'] = $args;
            }
        }

        $client = $this->getHttpHelper();
        $triggerServerUrl = $this->getSugarConfig()->get('trigger_server.url');

        return $client->send('post', $triggerServerUrl, json_encode($params));
    }

    /**
     * Delete trigger
     * @param string $id unique trigger's id needed to delete
     * @return bool was trigger deleted
     */
    public function delete($id)
    {
        $token = $this->retrieveToken();

        $params = array(
            'url' => $this->getSugarConfig()->get('site_url'),
            'id' => $id,
            'token' => $token
        );

        $client = $this->getHttpHelper();
        $triggerServerUrl = $this->getSugarConfig()->get('trigger_server.url');

        return $client->send('delete', $triggerServerUrl, json_encode($params));
    }

    /**
     * Check Trigger server settings
     * @param string $url
     * @return bool if settings are valid and trigger server is available
     */
    public function checkTriggerServerSettings($url)
    {
        $httpClient = $this->getHttpHelper();
        return (filter_var($url, FILTER_VALIDATE_URL) !== false && $httpClient->ping($url));
    }
}
