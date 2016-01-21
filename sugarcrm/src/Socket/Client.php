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
namespace Sugarcrm\Sugarcrm\Socket;

/**
 * Class Client allows us to send messages to connected clients.
 *
 * By default message will be sent to all connected clients,
 * @see Client::recipient method to specify recipients.
 *
 * Examples:
 * <code>
 * // to all
 * Client::getInstance()->send('test');
 *
 * // with data
 * Client::getInstance()->send('progress', array('processId' => 123, 'progress' => 80));
 *
 * // to all users in specified channel
 * Client::getInstance()->channel('channelName')->send('test');
 * // to specified group in specified channel
 * Client::getInstance()->channel('channelName')->recipient(Client::RECIPIENT_USER_TYPE, 'admin')->send('test');
 *
 * // to specified group
 * Client::getInstance()->recipient(Client::RECIPIENT_USER_ID, $userId)->send('test');
 * Client::getInstance()->recipient(Client::RECIPIENT_TEAM_ID, $teamId)->send('test');
 * Client::getInstance()->recipient(Client::RECIPIENT_USER_TYPE, 'admin')->send('test');
 * </code>
 */
class Client
{
    /**
     * Constants for types of recipients
     */
    const RECIPIENT_ALL = 'all';
    const RECIPIENT_USER_ID = 'userId';
    const RECIPIENT_TEAM_ID = 'teamId';
    const RECIPIENT_USER_TYPE = 'userType';

    /**
     * @var Client
     */
    protected static $instance = null;

    /**
     * Name of recipient for message, by default message will be send to all sockets
     * To specify recipient use recipient() method with type of recipient
     *
     * @var array
     */
    protected $to = array(
        'type' => self::RECIPIENT_ALL,
        'id' => null,
        'channel' => null
    );

    /**
     * The method should be used if we need to send message to specified user, team, or type of user
     *
     * @param Client::RECIPIENT_ALL|Client::RECIPIENT_USER_ID|Client::RECIPIENT_TEAM_ID|Client::RECIPIENT_USER_TYPE $type
     * @param string $id
     * @return Client
     * @throws \SugarApiExceptionInvalidParameter
     */
    public function recipient($type, $id = null)
    {
        $this->to['type'] = $type;
        $this->to['id'] = $id;

        return $this;
    }

    /**
     * @param string $channel
     * @return $this
     */
    public function channel($channel)
    {
        $this->to['channel'] = $channel;
        return $this;
    }

    /**
     * Returns object of Client, customized if it's present
     *
     * @param bool $reset
     * @return Client
     */
    public static function getInstance($reset = false)
    {
        if ($reset || !static::$instance) {
            $class = \SugarAutoLoader::customClass('Sugarcrm\Sugarcrm\Socket\Client');
            static::$instance = new $class;
        }
        return static::$instance;
    }

    /**
     * Returns true if socket client is configured
     * @return bool
     */
    public function isConfigured()
    {
        return $this->getSugarConfig()->get('websockets.server.url') == true;
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
        // Should not use cache in memory because this code run in daemon.
        $config = $admin->getConfigForModule('auth', 'base', true);
        if (empty($config['external_token_socket'])) {
            $token = create_guid();
            $admin->saveSetting('auth', 'external_token_socket', $token, 'base');
        } else {
            $token = $config['external_token_socket'];
        }
        return $token;
    }

    /**
     * Sending $message with $data to socket
     *
     * @param string $message
     * @param mixed $data
     * @return bool was message sent or not
     */
    public function send($message, $data = null)
    {
        $token = $this->retrieveToken();

        $params = json_encode(
            array(
                'to' => $this->to + array('url' => $this->getSugarConfig()->get('site_url')),
                'token' => $token,
                'data' => array(
                    'message' => $message,
                    'args' => $data,
                )
            )
        );
        $client = $this->getHttpHelper();
        $url = $this->getSugarConfig()->get('websockets.server.url') . '/forward';

        $client->getRemoteData($url, $params);
        return $client->isSuccess();
    }

    /**
     * Check WebSocket settings.
     * @param $url
     * @return array
     */
    public function checkWSSettings($url)
    {
        $availability = false;
        $isBalancer = false;
        $type = false;
        $httpClient = $this->getHttpHelper();

        if (filter_var($url, FILTER_VALIDATE_URL) && $httpClient->ping($url)) {
            $fileContent = $httpClient->getRemoteData($url);

            if (isset($fileContent['type']) && $fileContent['type'] == 'balancer') {
                $isBalancer = true;
                $fileContent = $httpClient->getRemoteData($fileContent['location']);
            }

            if (isset($fileContent['type']) && in_array($fileContent['type'], array('client', 'server'))) {
                $availability = true;
                $type = $fileContent['type'];
            }
        }
        return array('url' => $url, 'type' => $type, 'available' => $availability, 'isBalancer' => $isBalancer);
    }
}
