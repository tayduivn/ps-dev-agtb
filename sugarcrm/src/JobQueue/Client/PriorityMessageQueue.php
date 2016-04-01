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
use Sugarcrm\Sugarcrm\JobQueue\Adapter\AdapterRegistry;
use Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException;
use Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException;
use Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface;
use Sugarcrm\Sugarcrm\JobQueue\Serializer\SerializerInterface;

/**
 * Class PriorityMessageQueue
 * @package JobQueue
 */
class PriorityMessageQueue implements ClientInterface
{
    /**
     * Message queue clients pull. Handler name is in *lower* case.
     * @var array of ClientInterface.
     */
    protected $pull = array();

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Initialize a pull of clients based on config:
     * [
     *      [
     *          'adapter' => 'adapter_1',
     *          'config' => [],
     *          'priority' => 2, // The key is not using for pushing.
     *          'handlers' => [
     *              'handler_1',
     *              'handler_2',
     *          ],
     *      ],
     *      [
     *          'adapter' => 'adapter_2',
     *          'config' => [],
     *          'handlers' => [
     *              'handler_1',
     *          ],
     *      ],
     *      // Is not used as a client. Worker only.
     *      [
     *          'adapter' => 'adapter_3',
     *          'config' => [],
     *          'handlers' => [],
     *      ],
     *      [
     *          'adapter' => 'adapter_4',
     *          'config' => [],
     *          'default' => true, // Default to push into for the rest handlers.
     *      ],
     * ];
     * @param array $config
     * @param AdapterRegistry $adapterRegistry
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        array $config,
        AdapterRegistry $adapterRegistry,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        if (empty($config)) {
            throw new InvalidArgumentException('Priority map is required.');
        }
        $this->logger = $logger;
        $this->adapterRegistry = $adapterRegistry;
        $this->serializer = $serializer;
        $this->populatePull($config, $adapterRegistry);
    }

    /**
     * Populate the pull of adapters.
     * @param array $config
     * @param AdapterRegistry $adapterRegistry
     */
    protected function populatePull(array $config, AdapterRegistry $adapterRegistry)
    {
        foreach ($config as $adapterConfig) {
            if (empty($adapterConfig['adapter']) || !isset($adapterConfig['config'])) {
                throw new InvalidArgumentException(
                    'Invalid priority config format: missed "adapter" or "config" part.'
                );
            }
            if (isset($adapterConfig['handlers']) && !is_array($adapterConfig['handlers'])) {
                throw new InvalidArgumentException(
                    'Invalid priority config format: the "handlers" key should be array.'
                );
            }
            if (!isset($adapterConfig['handlers'])) {
                $adapterConfig['handlers'] = array();
            }
            $adapterClass = $adapterRegistry->get($adapterConfig['adapter']);
            if (!$adapterClass) {
                new LogicException("Cannot find an adapter class for {$adapterConfig['adapter']}.");
            }

            foreach ($adapterConfig['handlers'] as $handlerName) {
                $this->pull[strtolower($handlerName)][] = $this->instantiateClient(
                    $adapterClass,
                    $adapterConfig['config']
                );
            }

            if (!empty($adapterConfig['default'])) {
                $this->pull['default'][] = $this->instantiateClient(
                    $adapterClass,
                    $adapterConfig['config']
                );
            }
        }
    }

    /**
     * Instantiate client with passed adapter class.
     * @param string $adapter Class name.
     * @param mixed $config
     * @return MessageQueue
     */
    protected function instantiateClient($adapter, $config)
    {
        $adapter = new $adapter($config, $this->logger);
        return new MessageQueue($adapter, $this->serializer, $this->logger);
    }

    /**
     * Add a job to handler's priority queue(s).
     * {@inheritdoc}
     */
    public function addJob(WorkloadInterface $workload)
    {
        $handlerName = strtolower($workload->getHandlerName());
        $specificPull = isset($this->pull[$handlerName]) ? $this->pull[$handlerName] : $this->pull['default'];

        /* @var ClientInterface $client */
        foreach ($specificPull as $client) {
            $client->addJob($workload);
        }
    }
}
