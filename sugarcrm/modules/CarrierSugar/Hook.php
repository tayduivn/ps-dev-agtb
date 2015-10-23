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

use Sugarcrm\Sugarcrm\Socket\Client as SocketServerClient;

require_once('include/api/SugarApi.php');

/**
 * Class CarrierSugarHook
 * Is used to forward Notifications bean after it created.
 */
class CarrierSugarHook extends SugarApi
{
    /**
     * Trigger when Notification created, .
     * Method is called via Sugar logic-hooks mechanism, after bean saved.
     *
     * @param \SugarBean $bean Bean object.
     * @param string $event Logic hook event name.
     * @param array $arguments Arguments about event from logic-hook call.
     */
    public function hook(\Notifications $bean, $event, $arguments)
    {
        if (!$arguments['isUpdate']) {
            $this->send($bean);
        }
    }

    /**
     * Method pushes Notification bean to SocketServer.
     *
     * @param Notifications $notification
     */
    protected function send(\Notifications $notification)
    {
        if ($this->isSocketConfigured()) {
            $notificationArr = $this->prepareMessage($notification);
            $this->getSocketServerClient()
                ->recipient(SocketServerClient::RECIPIENT_USER_ID, $notification->assigned_user_id)
                ->send('notification', $notificationArr);
        }
    }

    /**
     * Test if SocketServer is available.
     * @return bool true if available, otherwise false.
     */
    protected function isSocketConfigured()
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
     * Convert the bean to array version.
     *
     * @param Notifications $notification
     * @return array An array version of the notification
     */
    protected function prepareMessage(\Notifications $notification)
    {
        //Need to replace $GLOBALS['current_user'] for the user who receives message notification
        //for having access to \Notifications bean as an owner.
        $currentUserOld = array_key_exists('current_user', $GLOBALS) ? $GLOBALS['current_user'] : null;
        $currentUser = BeanFactory::getBean('Users', $notification->assigned_user_id);

        $optionsFields = array('fields' => array_keys($notification->getFieldDefinitions()));
        $service = $this->getServiceBase($currentUser);
        $GLOBALS['current_user'] = $currentUser;
        $notificationArr = $this->formatBean($service, $optionsFields, $notification);
        $GLOBALS['current_user'] = $currentUserOld;
        return $notificationArr;
    }

    /**
     * Return Service Base instance.
     *
     * @param User $apiUser
     * @return ServiceBase
     */
    protected function getServiceBase(User $apiUser)
    {
        $restServiceClass = SugarAutoLoader::customClass('RestService');
        $service = new $restServiceClass();
        $service->user = $apiUser;
        return $service;
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
