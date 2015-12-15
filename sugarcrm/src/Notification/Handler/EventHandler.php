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

namespace Sugarcrm\Sugarcrm\Notification\Handler;

use Sugarcrm\Sugarcrm\Notification\JobQueue\BaseHandler;
use Sugarcrm\Sugarcrm\Notification\CarrierRegistry;
use Sugarcrm\Sugarcrm\Notification\EventInterface;
use Sugarcrm\Sugarcrm\Notification\SubscriptionsRegistry;

/**
 * Handler detects recipients for specified events, groups them by carrier
 * and creates CarrierBulkMessageHandler for each unique carrier from received data.
 * And all that execute in JobQueue.
 *
 * Class EventHandler
 * @package Notification
 */
class EventHandler extends BaseHandler
{
    /**
     * @var EventInterface
     */
    protected $event;

    /**
     * Receives event to process.
     *
     * @param EventInterface $event event for processing.
     */
    public function initialize(EventInterface $event)
    {
        $this->event = $event;
    }

    /**
     * Detects users, their carriers and creates CarrierBulkMessageHandler per carrier.
     *
     * @return string SchedulersJob resolution.
     */
    public function run()
    {
        $carrierRegistry = $this->getCarrierRegistry();
        $userCarrierPreference = $this->getSubscriptionsRegistry()->getUsers($this->event);
        $carriers = array();

        foreach ($userCarrierPreference as $userId => $userRow) {
            foreach ($userRow['config'] as $carrierConfig) {
                $carrierName = $carrierConfig[0];
                if (!array_key_exists($carrierName, $carriers)) {
                    $carriers[$carrierName] = array(
                        'carrier' => $carrierRegistry->getCarrier($carrierName),
                        'data' => array()
                    );
                }
                if (!array_key_exists($userId, $carriers[$carrierName]['data'])) {
                    $carriers[$carrierName]['data'][$userId] = array(
                        'filter' => $userRow['filter'],
                        'options' => array()
                    );
                }
                $carrier = $carriers[$carrierName]['carrier'];
                $addressType = $carrier->getAddressType();
                $user = $this->getUser($userId);

                $carriers[$carrierName]['data'][$userId]['options'][] =
                    $addressType->getTransportValue($user, $carrierConfig[1]);
                $carriers[$carrierName]['data'][$userId]['options']
                    = array_values(array_unique($carriers[$carrierName]['data'][$userId]['options']));
            }
        }

        $manager = $this->getJobQueueManager();
        foreach ($carriers as $carrierName => $carrierRow) {
            $manager->NotificationCarrierBulkMessage(null, $this->event, $carrierName, $carrierRow['data']);
        }
        return \SchedulersJob::JOB_SUCCESS;
    }

    /**
     * Get user.
     *
     * @param string $userId user id
     * @return \User user instance
     */
    protected function getUser($userId)
    {
        return \BeanFactory::getBean('Users', $userId);
    }

    /**
     * Return Carrier Registry.
     *
     * @return CarrierRegistry
     */
    protected function getCarrierRegistry()
    {
        return CarrierRegistry::getInstance();
    }

    /**
     * Return Subscriptions Registry.
     *
     * @return SubscriptionsRegistry
     */
    protected function getSubscriptionsRegistry()
    {
        return new SubscriptionsRegistry();
    }
}
