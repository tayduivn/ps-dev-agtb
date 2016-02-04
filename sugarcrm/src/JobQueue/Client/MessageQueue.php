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

namespace Sugarcrm\Sugarcrm\JobQueue\Client;

use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\JobQueue\Adapter\MessageQueue\AdapterInterface;
use Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface;
use Sugarcrm\Sugarcrm\JobQueue\Serializer\SerializerInterface;

/**
 * Class MessageQueue
 * @package JobQueue
 */
class MessageQueue implements ClientInterface
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Create MessageQueue client instance and set an adapter to work with.
     * @param AdapterInterface $adapter
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(AdapterInterface $adapter, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->adapter = $adapter;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * Add a background task to MessageQueueClient job server.
     * {@inheritdoc}
     */
    public function addJob(WorkloadInterface $workload)
    {
        $this->logger->info('[MessageQueue]: serialize workload.');
        $this->logger->debug('[MessageQueue]: workload ' . var_export($workload, true));
        $data = $this->serializer->serialize($workload);
        $this->adapter->addJob($workload->getRoute(), $data);
    }
}
