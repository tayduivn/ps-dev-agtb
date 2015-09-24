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
use Sugarcrm\Sugarcrm\JobQueue\Handler\SubtaskCapableInterface;
use Sugarcrm\Sugarcrm\JobQueue\Manager\Manager;
use Sugarcrm\Sugarcrm\Notification\Carrier\CarrierInterface;

/**
 * Forward Message to Sugar’s transport.
 *
 * Class SendHandler
 * @package Notification
 */
class SendHandler implements RunnableInterface, SubtaskCapableInterface
{
    /**
     * Fail the task if a single child ends as failed.
     */
    const FALLIBLE = true;

    /**
     * Ability to rerun the task.
     */
    const RERUN = false;

    /**
     * @var Manager
     */
    protected $client;

    /**
     * Receives carrier, transport value (email, phone number etc.) and message
     *
     * @param CarrierInterface $carrier which transport will be used
     * @param $transportVal transport value (email, phone number etc.)
     * @param array $message which will be forwarded
     */
    public function __construct(CarrierInterface $carrier, $transportVal, array $message)
    {
        // TODO: Implement __construct() method.
    }

    /**
     * Uses transport of carrier to forward message to carrier server for delivery.
     *
     * @return string SchedulersJob resolution.
     */
    public function run()
    {
        // TODO: Implement run() method.
        return \SchedulersJob::JOB_SUCCESS;
    }
}
