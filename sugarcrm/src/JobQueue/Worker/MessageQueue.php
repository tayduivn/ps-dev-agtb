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

namespace Sugarcrm\Sugarcrm\JobQueue\Worker;

use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\JobQueue\Adapter\MessageQueue\AdapterInterface;
use Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException;
use Sugarcrm\Sugarcrm\JobQueue\Exception\RuntimeException;
use Sugarcrm\Sugarcrm\JobQueue\Serializer\SerializerInterface;
use Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface;

/**
 * Class MessageQueue
 * @package JobQueue
 */
class MessageQueue implements WorkerInterface
{
    /**
     * @var int
     */
    protected $returnCode;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var array $handlers Storage for binding routes to handlers.
     */
    protected $handlers = array();

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Initialize worker and set an adapter to work with.
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
     * {@inheritdoc}
     */
    public function returnCode()
    {
        return $this->returnCode;
    }

    /**
     * {@inheritdoc}
     * @throw InvalidArgumentException
     */
    public function registerHandler($route, $function)
    {
        if (empty($route) || !is_string($route)) {
            throw new InvalidArgumentException("Cannot register Handler for non-string route name");
        }
        if (!is_callable($function)) {
            throw new InvalidArgumentException("Cannot register Handler '{$route}' as it is not callable");
        }
        $this->adapter->bind($route);
        $this->addHandler($route, $function);
    }

    /**
     * {@inheritdoc}
     */
    public function unregisterHandler($route)
    {
        $this->adapter->unbind($route);
        unset($this->handlers[$route]);
    }

    /**
     * {@inheritdoc}
     * @throws RuntimeException
     */
    public function work()
    {
        $this->logger->info('[MessageQueue]: work.');
        if (!$this->handlers) {
            throw new RuntimeException('No handlers registered.');
        }
        $message = $this->adapter->getMessage();
        if (!$message) {
            $this->logger->info('[MessageQueue]: no jobs in queue.');
            $this->returnCode = self::RETURN_CODE_NO_JOBS;
            return false;
        }
        $job = $this->adapter->getJob($message);
        if (!$job) {
            $this->logger->error('[MessageQueue]: cannot receive a job by message.');
            return false;
        }
        $this->returnCode = self::RETURN_CODE_SUCCESS;
        $workload = $this->serializer->unserialize($job);
        if (!($workload instanceof WorkloadInterface)) {
            $this->logger->error('[MessageQueue]: invalid workload instance.');
            return false;
        }
        $function = $this->getHandler($workload->getRoute());
        if ($function) {
            call_user_func($function, $workload);
            $this->adapter->resolve($message);
        } else {
            $this->returnCode = self::RETURN_CODE_NO_REGISTERED_FUNCTIONS;
            $this->logger->warning("[MessageQueue]: no registered function for handler '{$workload->getRoute()}'.");
            return false;
        }
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
     * Bind handler to route in internal storage.
     * @param string $route
     * @param callable $function
     */
    protected function addHandler($route, $function)
    {
        $this->logger->debug("[MessageQueue]: register route '{$route}'.");
        $this->handlers[$route] = $function;
    }

    /**
     * Get handler by its route from internal storage.
     * @param string $route
     * @return null|callable
     */
    protected function getHandler($route)
    {
        return isset($this->handlers[$route]) ? $this->handlers[$route] : null;
    }
}
