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

use Sugarcrm\Sugarcrm\JobQueue\Handler\RunnableInterface;
use Sugarcrm\Sugarcrm\JobQueue\Manager\Manager;
use Sugarcrm\Sugarcrm\Notification\Carrier\CarrierInterface;
use Sugarcrm\Sugarcrm\Notification\EventInterface;
use Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderRegistry;

/**
 * Detects suitable message builder and generates messages for each user.
 * For each generated message it creates SendHandler.
 *
 * Class EventHandler
 * @package Notification
 */
class CarrierBulkMessageHandler implements RunnableInterface
{
    /**
     * @var EventInterface
     */
    protected $event;

    /**
     * @var CarrierInterface
     */
    protected $carrier;

    /**
     * @var array
     */
    protected $usersOptions;

    /**
     * @param EventInterface $event event for processing.
     * @param CarrierInterface $carrier
     * @param array $usersOptions list of user options
     */
    public function __construct(EventInterface $event, CarrierInterface $carrier, array $usersOptions)
    {
        $this->event = $event;
        $this->carrier = $carrier;
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
        $messageSignature = $this->carrier->getMessageSignature();
        $jobQueueManager = $this->getJobQueueManager();

        foreach ($this->usersOptions as $userId => $userData) {
            $user = \BeanFactory::getBean('Users', $userId);
            $message = $messageBuilder->build($this->event, $userData['filter'], $user, $messageSignature);
            foreach ($userData['options'] as $transportVal) {
                $jobQueueManager->NotificationSend($this->carrier, $transportVal, $message);
            }
        }
        return \SchedulersJob::JOB_SUCCESS;
    }

    /**
     * Return JobQueue Manager.
     *
     * @return Manager
     */
    protected function getJobQueueManager()
    {
        return new Manager();
    }

    /**
     * @return MessageBuilderRegistry
     */
    protected function getMessageBuilderRegistry()
    {
        return MessageBuilderRegistry::getInstance();
    }
}
