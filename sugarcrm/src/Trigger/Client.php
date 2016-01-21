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
 * For deleting trigger by tags use @see Client::deleteByTags().
 *
 * Examples:
 *
 * <code>
 * // instantiate client
 * $triggerServerClient = Client::getInstance();
 *
 * // check is trigger server URL was specified
 * $isConfigured = $triggerServerClient->isConfigured();
 *
 * // check is trigger server is available
 * $isAvailable = $triggerServerClient->isAvailable();
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
 * $tags = array('bean-20db6fcd-0ce9-4da4-87d1-fae1d563b5a2', 'user-c92af13d-b29a-3d3f-f457-562e59ef3412');
 * $isSuccess = $triggerServerClient->push(
 *      '20db6fcd-0ce9-4da4-87d1-fae1d563b5a2',
 *      '2015-09-30T12:25:00',
 *      'post',
 *      'trigger/callback/post',
 *      $args,
 *      $tags);
 *
 * // delete trigger
 * $isSuccess = $triggerServerClient->delete('20db6fcd-0ce9-4da4-87d1-fae1d563b5a2');
 *
 * // delete triggers by tags
 * $isSuccess = $triggerServerClient->deleteByTags(array('bean-20db6fcd-0ce9-4da4-87d1-fae1d563b5a2'));
 * $isSuccess = $triggerServerClient->deleteByTags(array(
 *      'bean-20db6fcd-0ce9-4da4-87d1-fae1d563b5a2',
 *      'user-c92af13d-b29a-3d3f-f457-562e59ef3412'
 * ));
 *
 * </code>
 *
 * @package Sugarcrm\Sugarcrm\Trigger
 */
class Client
{
    const POST_URI = '/';
    const DELETE_URI = '/';
    const DELETE_BY_TAGS_URI = '/by_tags';
    const POST_METHOD = 'post';
    const DELETE_METHOD = 'delete';
    const DELETE_BY_TAGS_METHOD = 'delete';

    /**
     * @var Client
     */
    protected static $instance = null;

    /**
     * Returns object of Client
     *
     * @param bool $reset
     * @return Client
     * @codeCoverageIgnore
     */
    public static function getInstance($reset = false)
    {
        if ($reset || !static::$instance) {
            $class = \SugarAutoLoader::customClass('Sugarcrm\Sugarcrm\Trigger\Client');
            static::$instance = new $class;
        }
        return static::$instance;
    }

    /**
     * Gets token from config.
     * If token does not exists the method creates
     * new one and sets it to config for future calls.
     *
     * @return string
     */
    protected function retrieveToken()
    {
        $admin = $this->getAdministrationBean();
        // we can't use the config with the cache,
        // because the trigger's token may be changed at any moment.
        $config = $admin->getConfigForModule('auth', 'base', true);
        if (empty($config['external_token_trigger'])) {
            $token = $this->createGuid();
            $admin->saveSetting('auth', 'external_token_trigger', $token, 'base');
        } else {
            $token = $config['external_token_trigger'];
        }
        return $token;
    }

    /**
     * Checks trigger server's url is set up.
     *
     * @return bool
     */
    public function isConfigured()
    {
        $triggerServerUrl = $this->getSugarConfig()->get('trigger_server.url');
        return !empty($triggerServerUrl);
    }

    /**
     * Checks trigger server's availability.
     *
     * @return bool
     */
    public function isAvailable()
    {
        $triggerServerUrl = $this->getSugarConfig()->get('trigger_server.url');
        $httpClient = $this->getHttpHelper();
        return $httpClient->ping($triggerServerUrl);
    }

    /**
     * Adds or updates trigger. If method is GET or DELETE then args will not be used.
     * Otherwise args will be received as raw data in JSON format.
     *
     * @param string $id unique trigger's id
     * @param string $stamp date in GMT. Format: Y-m-dTH:i:s or any compatible with javascript Date() function
     * @param string $method callback method. Available GET, POST, PUT, DELETE
     * @param string $uri callback uri (relative)
     * @param array|null $args additional callback arguments
     * @param array|null $tags trigger tags
     * @return bool was trigger added or updated
     */
    public function push($id, $stamp, $method, $uri, $args = null, $tags = null)
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
        if ($args && !in_array($method, array('get', 'delete'))) {
            $params['trigger']['args'] = $args;
        }
        if ($tags) {
            $params['tags'] = $tags;
        }

        $client = $this->getHttpHelper();
        $triggerServerUrl = $this->getSugarConfig()->get('trigger_server.url');
        $triggerServerUrl = rtrim($triggerServerUrl, '/') . static::POST_URI;

        return $client->send(static::POST_METHOD, $triggerServerUrl, json_encode($params));
    }

    /**
     * Deletes trigger by id.
     *
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
        $triggerServerUrl = rtrim($triggerServerUrl, '/') . static::DELETE_URI;

        return $client->send(static::DELETE_METHOD, $triggerServerUrl, json_encode($params));
    }

    /**
     * Deletes triggers by tags.
     *
     * @param  $tags array of tags
     * @return bool was trigger deleted
     */
    public function deleteByTags($tags)
    {
        if (empty($tags) || !is_array($tags)) {
            return false;
        }

        $token = $this->retrieveToken();

        $params = array(
            'url' => $this->getSugarConfig()->get('site_url'),
            'token' => $token,
            'tags' => $tags
        );

        $client = $this->getHttpHelper();
        $triggerServerUrl = $this->getSugarConfig()->get('trigger_server.url');
        $triggerServerUrl = rtrim($triggerServerUrl, '/') . static::DELETE_BY_TAGS_URI;

        return $client->send(static::DELETE_BY_TAGS_METHOD, $triggerServerUrl, json_encode($params));
    }

    /**
     * Checks Trigger server settings.
     *
     * @param string $url
     * @return bool if settings are valid and trigger server is available
     */
    public function checkTriggerServerSettings($url)
    {
        $httpClient = $this->getHttpHelper();
        return (filter_var($url, FILTER_VALIDATE_URL) !== false && $httpClient->ping($url));
    }

    /**
     * Wrapper method for ::create_guid() function.
     *
     * @return string
     * @codeCoverageIgnore
     */
    protected function createGuid()
    {
        return create_guid();
    }

    /**
     * Factory method for HttpHelper class.
     *
     * @return HttpHelper
     */
    protected function getHttpHelper()
    {
        return new HttpHelper();
    }

    /**
     * Factory method for Administration class.
     *
     * @return \Administration
     * @codeCoverageIgnore
     */
    protected function getAdministrationBean()
    {
        return \BeanFactory::getBean('Administration');
    }

    /**
     * Factory method for SugarConfig class.
     *
     * @return \SugarConfig
     */
    protected function getSugarConfig()
    {
        return \SugarConfig::getInstance();
    }
}
