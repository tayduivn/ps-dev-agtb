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

use Sugarcrm\Sugarcrm\Notification\Carrier\TransportInterface;
use Sugarcrm\Sugarcrm\Socket\Client as SocketServerClient;

/**
 * Class CarrierSocketTransport.
 * Is used to push messages to SocketServer.
 */
class CarrierSocketTransport implements TransportInterface
{
    /**
     * Send message to a specified user.
     * Method just pushes Notification bean to SocketServer and forgets about it.
     * @param string $recipient Sugar User id.
     * @param array $message message pack for delivery.
     * @return bool true if message was sent, otherwise false.
     */
    public function send($recipient, $message)
    {
        $isSent = false;

        if ($this->test()) {
            if (!empty($message['title']) || !empty($message['text'])) {
                // Create a Notification bean.
                $notification = \BeanFactory::getBean('Notifications');
                $notification->name = $message['title'];
                $notification->description = $message['text'];
                $notification->assigned_user_id = $recipient;
                $notification->save();

                // Send it to SocketServer.
                $isSent = $this->getSocketServerClient()
                    ->recipient(SocketServerClient::RECIPIENT_USER_ID, $recipient)
                    ->send(
                        $message['title'],
                        array('module' => $notification->module_name, 'record' => $notification->id)
                    );
            }
        }

        return $isSent;
    }

    /**
     * Test if SocketServer is available.
     * @return bool true if available, otherwise false.
     */
    public function test()
    {
        // Check if url has been saved to Sugar config.
        // Not found means that SocketServer wasn't configured properly in Sugar and is not available at the moment.
        $serverUrl = $this->getSugarConfig()->get('websockets.server.url');
        return !empty($serverUrl);
    }

    /**
     * Get SugarCRM configuration.
     * @return \SugarConfig SugarCRM config.
     */
    protected function getSugarConfig()
    {
        return \SugarConfig::getInstance();
    }

    /**
     * Get SocketServer Client.
     * @return Sugarcrm\Sugarcrm\Socket\Client SocketServer Client.
     */
    protected function getSocketServerClient()
    {
        return SocketServerClient::getInstance();
    }
}
