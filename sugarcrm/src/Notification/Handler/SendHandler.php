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

use Sugarcrm\Sugarcrm\Notification\Carrier\CarrierInterface;
use Sugarcrm\Sugarcrm\JobQueue\Handler\RunnableInterface;
use Sugarcrm\Sugarcrm\JobQueue\Handler\SubtaskCapableInterface;
use Sugarcrm\Sugarcrm\JobQueue\Manager\Manager;

/**
 * Class SendHandler.
 * Is used to send messages to a user via various carriers.
 * @package Sugarcrm\Sugarcrm\Notification
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
     * Carrier used by the job to send messages.
     * @var CarrierInterface
     */
    protected $carrier;

    /**
     * Indicates a recipient in various forms. @see AddressTypeInterface.
     * @var mixed
     */
    protected $transportValue;

    /**
     * Message pack (all required data and metadata) to be sent.
     * @var array
     */
    protected $message = array();

    /**
     * Create handler with specific carrier, recipients and message.
     * @param CarrierInterface $carrier Carrier that job uses to send messages.
     * @param mixed $transportValue recipient data (normally here it is user ID).
     * @param array $message message pack.
     */
    public function __construct(CarrierInterface $carrier, $transportValue, array $message)
    {
        $this->carrier = $carrier;
        $this->transportValue = $transportValue;
        $this->message = $message;
    }

    /**
     * Send message to a specified destination via a specified carrier.
     * {@inheritdoc}
     */
    public function run()
    {
        $result = $this->carrier->getTransport()->send($this->transportValue, $this->message);

        if ($result === true) {
            return \SchedulersJob::JOB_SUCCESS;
        } else {
            return \SchedulersJob::JOB_FAILURE;
        }
    }
}
