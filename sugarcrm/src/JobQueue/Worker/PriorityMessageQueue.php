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

namespace Sugarcrm\Sugarcrm\JobQueue\Worker;

use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\JobQueue\Adapter\AdapterRegistry;
use Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException;
use Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException;
use Sugarcrm\Sugarcrm\JobQueue\Serializer\SerializerInterface;

/**
 * Class PriorityMessageQueue
 * @package JobQueue
 */
class PriorityMessageQueue implements WorkerInterface
{
    /**
     * Message queue workers pull. Handler name is in *lower* case.
     * @var \SplPriorityQueue of WorkerInterface.
     */
    protected $queue;

    /**
     * @var array $handlers Storage for binding routes to handlers.
     */
    protected $handlers = array();

    /**
     * @var int $defaultPriority Default priority.
     */
    protected $defaultPriority = 0;

    /**
     * @var int
     */
    protected $returnCode;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Initialize a pull of message queue workers. Config example:
     * [
     *      [
     *          'adapter' => 'adapter_1',
     *          'config' => [],
     *          'priority' => 2,
     *          'handlers' => [
     *              'handler_1',
     *              'handler_2',
     *          ],
     *      ],
     *      [
     *          'adapter' => 'adapter_2',
     *          'config' => [],
     *          'priority' => 1,
     *          'handlers' => [
     *              'handler_1',
     *          ],
     *      ],
     *      // Is not used as a client. Worker only.
     *      [
     *          'adapter' => 'adapter_3',
     *          'config' => [],
     *          'priority' => 1,
     *          'handlers' => [],
     *      ],
     *      [
     *          'adapter' => 'adapter_4',
     *          'config' => [],
     *          'priority' => 0,
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
        $this->queue = new \SplPriorityQueue();
        $this->queue->setExtractFlags(\SplPriorityQueue::EXTR_DATA);

        $this->logger = $logger;
        $this->adapterRegistry = $adapterRegistry;
        $this->serializer = $serializer;
        $this->populateQueue($config, $adapterRegistry);
    }

    /**
     * Populate the pull of workers.
     * @param array $config
     * @param AdapterRegistry $adapterRegistry
     */
    protected function populateQueue(array $config, AdapterRegistry $adapterRegistry)
    {
        foreach ($config as $adapterConfig) {
            if (empty($adapterConfig['adapter']) || !isset($adapterConfig['config'])) {
                throw new InvalidArgumentException(
                    'Invalid priority config format: missed "adapter" or "config" part.'
                );
            }
            $adapterClass = $adapterRegistry->get($adapterConfig['adapter']);
            if (!$adapterClass) {
                new LogicException("Cannot find an adapter class for {$adapterConfig['adapter']}.");
            }
            $priority = isset($adapterConfig['priority']) ? $adapterConfig['priority'] : $this->defaultPriority;
            $worker = $this->instantiateWorker($adapterClass, $adapterConfig['config']);

            $this->queue->insert($worker, $priority);
        }
    }

    /**
     * @return \SplPriorityQueue
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @return int
     */
    public function getDefaultPriority()
    {
        return $this->defaultPriority;
    }

    /**
     * @param int $defaultPriority
     */
    public function setDefaultPriority($defaultPriority)
    {
        $this->defaultPriority = $defaultPriority;
    }

    /**
     * {@inheritdoc}
     */
    public function returnCode()
    {
        return $this->returnCode;
    }

    /**
     * Register global handlers to execute by default.
     * {@inheritdoc}
     */
    public function registerHandler($route, $function)
    {
        $this->handlers[$route] = $function;
    }

    /**
     * {@inheritdoc}
     */
    public function unregisterHandler($route)
    {
        unset($this->handlers[$route]);
    }

    /**
     * Execute all jobs from *one* worker according to priority from high to low.
     * No jobs code for the worker means that all jobs in all workers are done.
     * {@inheritdoc}
     */
    public function work()
    {
        if ($this->queue->isEmpty()) {
            $this->returnCode = self::RETURN_CODE_NO_JOBS;
            return false;
        }
        /* @var MessageQueue $worker */
        $worker = $this->queue->extract();

        foreach ($this->handlers as $route => $function) {
            $worker->registerHandler($route, $function);
        }

        do {
            $result = $worker->work();
        } while ($result);

        return true;
    }

    /**
     * We return true only because Gearman implementation does it.
     * {@inheritdoc}
     */
    public function wait()
    {
        return true;
    }

    /**
     * Instantiate worker with passed adapter class.
     * @param string $adapter Class name.
     * @param mixed $config
     * @return MessageQueue
     */
    protected function instantiateWorker($adapter, $config)
    {
        $adapter = new $adapter($config, $this->logger);
        $worker = new MessageQueue($adapter, $this->serializer, $this->logger);
        return $worker;
    }
}
