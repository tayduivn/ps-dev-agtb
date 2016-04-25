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

use Sugarcrm\Sugarcrm\Notification\CarrierRegistry;
use Sugarcrm\Sugarcrm\JobQueue\Manager\Manager;
use Sugarcrm\Sugarcrm\Notification\JobQueue\BaseHandler;

/**
 * Class SendHandler.
 * Is used to send messages to a user via various carriers.
 * @package Sugarcrm\Sugarcrm\Notification
 */
class SendHandler extends BaseHandler
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
     * Carrier name used by the job to send messages.
     * @var string
     */
    protected $carrierName;

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
     * @param string $carrier Carrier that job uses to send messages.
     * @param mixed $transportValue recipient data (normally here it is user ID).
     * @param array $message message pack.
     */
    protected function initialize($carrier, $transportValue, array $message)
    {
        $this->logger->debug("NC: run SendHandler for {$this->carrierName} carrier");

        $this->carrierName = $carrier;
        $this->transportValue = $transportValue;
        $this->message = $message;
    }

    /**
     * Send message to a specified destination via a specified carrier.
     * {@inheritdoc}
     */
    public function run()
    {
        $carrier = $this->getCarrierRegistry()->getCarrier($this->carrierName);
        $result = $carrier->getTransport()->send($this->transportValue, $this->message);

        if ($result === true) {
            $this->logger->debug("NC: SendHandler run successfully");
            return \SchedulersJob::JOB_SUCCESS;
        } else {
            $this->logger->debug("NC: SendHandler run failed");
            return \SchedulersJob::JOB_FAILURE;
        }
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
