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

namespace Sugarcrm\Sugarcrm\Notification;

use Sugarcrm\Sugarcrm\Notification\JobQueue\Manager;

/**
 * When event has been triggered it should be dispatched to Job Queue.
 * For those things we should use dispatch method of Dispatcher
 *
 * Class Dispatcher
 * @package Notification
 */
class Dispatcher
{
    /**
     * Receives event and schedules task in job queue to process it.
     *
     * @param EventInterface $event event for processing.
     */
    public function dispatch(EventInterface $event)
    {
        $manager = $this->getJobQueueManager();
        $manager->NotificationEvent(null, $event);
    }

    /**
     * Return Customized JobQueue Manager.
     * Manager helps unserialize classes in unsupported file paths.
     *
     * @return Manager
     */
    protected function getJobQueueManager()
    {
        return new Manager();
    }
}
