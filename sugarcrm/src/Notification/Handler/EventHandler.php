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
use Sugarcrm\Sugarcrm\Notification\EventInterface;

/**
 * Handler detects recipients for specified events, groups them by carrier
 * and creates CarrierBulkMessageHandler for each unique carrier from received data.
 * And all that execute in JobQueue.
 *
 * Class EventHandler
 * @package Notification
 */
class EventHandler implements RunnableInterface
{
    /**
     * Receives event to process.
     *
     * @param EventInterface $event event for processing.
     */
    public function __construct(EventInterface $event)
    {
        // TODO: Implement __construct() method.
    }

    /**
     * Detects users, their carriers and creates CarrierBulkMessageHandler per carrier.
     *
     * @return string SchedulersJob resolution.
     */
    public function run()
    {
        // TODO: Implement run() method.
    }
}
