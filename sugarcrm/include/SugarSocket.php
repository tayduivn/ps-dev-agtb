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

use ElephantIO\Engine\SocketIO\Version1X as Socket;

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
     * Pointer to socket object
     *
     * @var Socket
     */
    protected $socket = null;

    /**
     * Name of room for message, by default message will be send to all sockets
     * To specify room use SugarSocket::to method with type of room
     *
     * @var string
     */
    protected $room = 'all';

    /**
     * @param string $host
     */
    protected function __construct($host = '')
    {
        if ($host == '') {
            $host = $this->getHost();
        }
        $this->socket = $this->getSocket($host);
    }

    /**
     * Returns default path to server socket url
     *
     * @return string
     */
    protected function getHost()
    {
        return SugarConfig::getInstance()->get('websockets.server.url');
    }

    /**
     * Returns initialized Socket object
     *
     * @param string $host
     * @return Socket
     */
    protected function getSocket($host)
    {
        return new Socket($host);
    }

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
     * Sending $message with $data to socket
     *
     * @param string $message
     * @param mixed $data
     * @return bool was message sent or not
     */
    public function send($message, $data = null)
    {
        try {
            $this->socket->connect();
            $this->socket->emit('forward', array(
                'room' =>
                    SugarConfig::getInstance()->get('site_url')
                    . ':' . SugarConfig::getInstance()->get('websockets.public_secret')
                    . ':' . $this->room,
                'message' => $message,
                'args' => $data,
            ));
            $this->socket->close();
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }

    /**
     * Returns object of SugarSocket, customized if it's present
     *
     * @param string $host host of socket
     * @return SugarSocket|CustomSugarSocket
     */
    public static function getInstance($host = '')
    {
        SugarAutoLoader::requireWithCustom('include/SugarSocket.php');
        $class = SugarAutoLoader::customClass('SugarSocket');
        return new $class($host);
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
     * @param $log
     */
    public static function checkWSSettings($url, $type, $log = false)
    {
        $statusMessages = array(
            'server' => 'SugarCRM Server Side',
            'client' => 'SugarCRM Client Side'
        );

        $availability = false;

        if ($log) {
            installLog("Beginning to check WebSocket Settings.");
        }

        if (self::ping($url)) {
            $fileContent = file_get_contents($url);
            if (filter_var($fileContent, FILTER_VALIDATE_URL)) {
                $fileContent = file_get_contents($fileContent);
            }
            if ($fileContent == $statusMessages[$type]) {
                $availability = true;
            }
        }

        $status = array('url' => $url, 'type' => $type, 'available' => $availability);

        if ($log) {
            installLog("WebSocket connection results: " . var_export($status, true));
        }

        return $status['available'];
    }
}
