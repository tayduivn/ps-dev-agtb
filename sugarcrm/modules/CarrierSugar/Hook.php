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

use Sugarcrm\Sugarcrm\Socket\Client as SocketClient;

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
     * @param \Notifications $bean Bean object.
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
        if (SocketClient::getInstance()->isConfigured()) {
            $notificationArr = $this->prepareMessage($notification);
            SocketClient::getInstance()
                ->recipient(SocketClient::RECIPIENT_USER_ID, $notification->assigned_user_id)
                ->send('notification', $notificationArr);
        }
    }

    /**
     * Convert the bean to array version.
     *
     * @param Notifications $notification
     * @return array An array version of the notification
     */
    protected function prepareMessage(\Notifications $notification)
    {
        // Need to replace $GLOBALS['current_user'] for the user who receives message notification
        // for having access to \Notifications bean as an owner.
        /** @var User $currentUser */
        $currentUser = array_key_exists('current_user', $GLOBALS) ? $GLOBALS['current_user'] : null;
        /** @var User $notificationUser */
        $notificationUser = null;
        if (!$currentUser || $currentUser->id != $notification->assigned_user_id) {
            $notificationUser = BeanFactory::getBean('Users', $notification->assigned_user_id);
            $GLOBALS['current_user'] = $notificationUser;
        }

        $optionsFields = array('fields' => array_keys($notification->getFieldDefinitions()));
        $service = $this->getServiceBase($currentUser);
        $notificationArr = $this->formatBean($service, $optionsFields, $notification);
        if ($notificationUser) {
            $GLOBALS['current_user'] = $currentUser;
        }
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
}
