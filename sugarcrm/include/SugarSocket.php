<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

/**
 * Class SugarSocket allows us to send messages to connected clients.
 *
 * By default message will be sent to all connected clients,
 * @see SugarSocket::recipient method to specify recipients.
 *
 * Examples:
 * <code>
 * // to all
 * SugarSocket::getInstance()->send('test');
 *
 * // with data
 * SugarSocket::getInstance()->send('progress', array('processId' => 123, 'progress' => 80));
 *
 * // to specified group
 * SugarSocket::getInstance()->recipient(SugarSocket::RECIPIENT_USER_ID, $userId)->send('test');
 * SugarSocket::getInstance()->recipient(SugarSocket::RECIPIENT_TEAM_ID, $teamId)->send('test');
 * SugarSocket::getInstance()->recipient(SugarSocket::RECIPIENT_USER_TYPE, 'admin')->send('test');
 * </code>
 */
class SugarSocket
{
    /**
     * Constants for types of rooms
     */
    const RECIPIENT_USER_ID = 'userId';
    const RECIPIENT_TEAM_ID = 'teamId';
    const RECIPIENT_USER_TYPE = 'userType';

    /**
     * Name of room for message, by default message will be send to all sockets
     * To specify room use SugarSocket::to method with type of room
     *
     * @var string
     */
    protected $room = 'all';

    /**
     * The method should be used if we need to send message to specified user, team, or type of user
     *
     * @param SugarSocket::RECIPIENT_USER_ID|SugarSocket::RECIPIENT_TEAM_ID|SugarSocket::RECIPIENT_USER_TYPE $type
     * @param string $id
     * @return SugarSocket|CustomSugarSocket
     */
    public function recipient($type, $id)
    {
        $this->room = $type . ':' . $id;
        return $this;
    }

    /**
     * Returns object of SugarSocket, customized if it's present
     *
     * @return SugarSocket|CustomSugarSocket
     */
    public static function getInstance()
    {
        SugarAutoLoader::requireWithCustom('include/SugarSocket.php');
        $class = SugarAutoLoader::customClass('SugarSocket');
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
        try {
            $params = json_encode(
                array(
                    'room' =>
                        SugarConfig::getInstance()->get('site_url')
                        . ':' . SugarConfig::getInstance()->get('websockets.public_secret')
                        . ':' . $this->room,
                    'message' => $message,
                    'args' => $data
                )
            );
            $client = new Zend_Http_Client(SugarConfig::getInstance()->get('websockets.server.url') . '/forward');
            $client->setRawData($params, 'application/json')->request('POST');
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
     * @param $type
     * @return bool
     */
    public static function checkWSSettings($url, $type)
    {
        $statusMessages = array(
            'server' => 'SugarCRM Server Side',
            'client' => 'SugarCRM Client Side'
        );
        $availability = false;
        $isBalancer = false;
        $httpClient = new Zend_Http_Client();

        if (filter_var($url, FILTER_VALIDATE_URL) && self::ping($url)) {
            $fileContent = $httpClient->setUri($url)->request()->getBody();
            if (filter_var(json_decode($fileContent), FILTER_VALIDATE_URL)) {
                $isBalancer = true;
                $fileContent = $httpClient->setUri(json_decode($fileContent))->request()->getBody();
            }
            if ($fileContent == $statusMessages[$type]) {
                $availability = true;
            }
        }
        return array('url' => $url, 'type' => $type, 'available' => $availability, 'isBalancer' => $isBalancer);
    }
}
