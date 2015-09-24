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
use Sugarcrm\Sugarcrm\Notification\Carrier\CarrierInterface;
use Sugarcrm\Sugarcrm\Notification\EventInterface;

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
     * @param EventInterface $event event for processing.
     * @param CarrierInterface $carrier
     * @param array $userIds list of user ids
     * @param array $userOptions list of user options
     * @param array $userRelationships list of user relationships
     */
    public function __construct(
        EventInterface $event,
        CarrierInterface $carrier,
        array $userIds,
        array $userOptions,
        array $userRelationships
    ) {

    }

    /**
     * Detects suitable message builder and generates messages for each user.
     * For each generated message it creates SendHandler.
     *
     * @return string SchedulersJob resolution.
     */
    public function run()
    {

    }
}
