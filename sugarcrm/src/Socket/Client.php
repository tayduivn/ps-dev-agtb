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
    const RECIPIENT_USER_ID = 'userId';
    const RECIPIENT_TEAM_ID = 'teamId';
    const RECIPIENT_USER_TYPE = 'userType';

    /**
     * Name of recipient for message, by default message will be send to all sockets
     * To specify recipient use recipient() method with type of recipient
     *
     * @var string
     */
    protected $to = 'all';

    /**
     * The method should be used if we need to send message to specified user, team, or type of user
     *
     * @param Client::RECIPIENT_USER_ID|Client::RECIPIENT_TEAM_ID|Client::RECIPIENT_USER_TYPE $type
     * @param string $id
     * @return Client|CustomClient
     */
    public function recipient($type, $id)
    {
        $this->to = $type . ':' . $id;
        return $this;
    }

    /**
     * Returns object of Client, customized if it's present
     *
     * @return Client|CustomClient
     */
    public static function getInstance()
    {
        $class = \SugarAutoLoader::customClass('Sugarcrm\Sugarcrm\Socket\Client');
        return new $class();
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
        $admin = \BeanFactory::getBean('Administration');
        $config = $admin->getConfigForModule('auth');

        if (empty($config['socket_token'])) {
            $token = create_guid();
            $admin->saveSetting('auth', 'socket_token', $token, 'base');
        } else {
            $token = $config['socket_token'];
        }

        try {
            $params = json_encode(
                array(
                    'url' => \SugarConfig::getInstance()->get('site_url'),
                    'token' => $token,
                    'data' => array(
                        'to' => $this->to,
                        'message' => $message,
                        'args' => $data
                    )
                )
            );
            $client = new \SugarHttpClient();
            $client->callRest(
                \SugarConfig::getInstance()->get('websockets.server.url') . '/forward',
                $params,
                array(CURLOPT_HTTPHEADER => array("Content-Type: application/json"))
            );
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }

    /**
     * This function checks site availability.
     * @param $url
     * @return bool
     */
    public static function ping($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (200 == $retcode) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check WebSocket settings.
     * @param $url
     * @return array
     */
    public static function checkWSSettings($url)
    {
        $availability = false;
        $isBalancer = false;
        $type = false;
        $httpClient = new \SugarHttpClient();

        if (filter_var($url, FILTER_VALIDATE_URL) && self::ping($url)) {
            $fileContent = json_decode(
                $httpClient->callRest(
                    $url,
                    '',
                    array(CURLOPT_HTTPHEADER => array("Content-Type: application/json"))
                )
            );
            if (isset($fileContent->type) && $fileContent->type == 'balancer') {
                $isBalancer = true;
                $fileContent = json_decode(
                    $httpClient->callRest(
                        $fileContent->location,
                        '',
                        array(CURLOPT_HTTPHEADER => array("Content-Type: application/json"))
                    )
                );
            }
            if (isset($fileContent->type) && in_array($fileContent->type, array('client', 'server'))) {
                $availability = true;
                $type = $fileContent->type;
            }
        }
        return array('url' => $url, 'type' => $type, 'available' => $availability, 'isBalancer' => $isBalancer);
    }
}
