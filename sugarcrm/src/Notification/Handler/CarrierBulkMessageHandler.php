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

namespace Sugarcrm\Sugarcrm\Notification\Handler;

use Sugarcrm\Sugarcrm\Notification\JobQueue\BaseHandler;
use Sugarcrm\Sugarcrm\Notification\CarrierRegistry;
use Sugarcrm\Sugarcrm\Notification\EventInterface;
use Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderRegistry;

/**
 * Detects suitable message builder and generates messages for each user.
 * For each generated message it creates SendHandler.
 *
 * Class EventHandler
 * @package Notification
 */
class CarrierBulkMessageHandler extends BaseHandler
{
    /**
     * @var EventInterface
     */
    protected $event;

    /**
     * @var string
     */
    protected $carrierName;

    /**
     * @var array
     */
    protected $usersOptions;

    /**
     * @param EventInterface $event event for processing.
     * @param string $carrierName carrier name
     * @param array $usersOptions list of user options
     */
    public function initialize(EventInterface $event, $carrierName, array $usersOptions)
    {
        $this->event = $event;
        $this->carrierName =  $carrierName;
        $this->usersOptions = $usersOptions;
    }

    /**
     * Detects suitable message builder and generates messages for each user.
     * For each generated message it creates SendHandler.
     *
     * @return string SchedulersJob resolution.
     */
    public function run()
    {
        $messageBuilder = $this->getMessageBuilderRegistry()->getBuilder($this->event);
        $messageSignature = $this->getCarrierRegistry()->getCarrier($this->carrierName)->getMessageSignature();
        $jobQueueManager = $this->getJobQueueManager();

        foreach ($this->usersOptions as $userId => $userData) {
            $user = \BeanFactory::getBean('Users', $userId);
            $message = $messageBuilder->build($this->event, $userData['filter'], $user, $messageSignature);
            foreach ($userData['options'] as $transportVal) {
                $jobQueueManager->NotificationSend($userId, $this->carrierName, $transportVal, $message);
            }
        }
        return \SchedulersJob::JOB_SUCCESS;
    }

    /**
     * @return MessageBuilderRegistry
     */
    protected function getMessageBuilderRegistry()
    {
        return MessageBuilderRegistry::getInstance();
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
}
