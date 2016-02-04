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

namespace Sugarcrm\Sugarcrm\JobQueue\Manager;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sugarcrm\Sugarcrm\JobQueue\Client\ClientInterface;
use Sugarcrm\Sugarcrm\JobQueue\Exception\UnexpectedResolutionException;
use Sugarcrm\Sugarcrm\JobQueue\LockStrategy\LockStrategyInterface;
use Sugarcrm\Sugarcrm\JobQueue\Observer\ObserverInterface;
use Sugarcrm\Sugarcrm\JobQueue\Runner\RunnerInterface;
use Sugarcrm\Sugarcrm\JobQueue\Serializer\SerializerInterface;
use Sugarcrm\Sugarcrm\JobQueue\Worker\WorkerInterface;
use Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface;

/**
 * Class AbstractManager
 * @package JobQueue
 */
abstract class AbstractManager implements ClientInterface, RunnerInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Setup logger.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = ($logger === null) ? new NullLogger() : $logger;
    }

    /**
     * Get client.
     * @return ClientInterface
     */
    abstract protected function getClient();

    /**
     * Get worker.
     * @return WorkerInterface
     */
    abstract protected function getWorker();

    /**
     * Get observer.
     * @return \SplObjectStorage
     */
    abstract protected function getObserver();

    /**
     * Get runner.
     * @return RunnerInterface
     */
    abstract public function getRunner();

    /**
     * Get serializer.
     * @return SerializerInterface
     */
    abstract protected function getSerializer();

    /**
     * Return a specific dispatcher by name.
     * @param string $handlerName In the register.
     * @return callable|null
     */
    abstract protected function getDispatcher($handlerName);

    /**
     * Return a specific lock strategy.
     * @return LockStrategyInterface
     */
    abstract protected function getLockStrategy();

    /**
     * Implementation of 'fail' job status/resolution.
     * @return string
     */
    abstract protected function getFailMark();

    /**
     * Check if passed observer can be applied to handler.
     * @param ObserverInterface $observer
     * @param string $handlerName
     * @return bool
     */
    abstract protected function applyObserver($observer, $handlerName);

    /**
     * Start running. Endpoint to start manager.
     */
    public function run()
    {
        $this->logger->info('Run a process.');
        $this->getRunner()->run();
    }

    /**
     * Proxy method to handle jobs.
     * This method is called each time when we need handle some job.
     * @param WorkloadInterface $workload
     * @return string Resolution.
     */
    public function proxyHandler($workload)
    {
        $resolution = null;
        $handlerName = $workload->getHandlerName();
        $dispatcher = $this->getDispatcher($handlerName);
        if (!$dispatcher) {
            $this->logger->error("The handler {$handlerName} is not registered.");
            return $this->getFailMark();
        }
        $callable = $dispatcher->dispatch();

        try {
            $this->onRun($workload);
        } catch (\Exception $ex) {
            $this->logger->notice('Killed by observer.', array('exception' => $ex));
            $resolution = $this->getFailMark();

            if ($ex instanceof UnexpectedResolutionException) {
                $resolution = $ex->getResolution();
            }
        }

        try {
            if (!$resolution) {
                $resolution = call_user_func($callable, $workload);
            }
        } catch (\Exception $ex) {
            $this->logger->notice("Cannot run the handler {$handlerName}.", array('exception' => $ex));
            $resolution = $this->getFailMark();
        }
        $this->onResolve($workload, $resolution);

        $this->logger->info("Finish '{$handlerName}' with resolution '{$resolution}'.");
        return $resolution;
    }

    /**
     * Attach observer.
     * {@inheritdoc}
     */
    public function addJob(WorkloadInterface $workload)
    {
        $client = $this->getClient();
        $this->onAdd($workload);
        $client->addJob($workload);
    }

    /**
     * Execute observers on add job.
     * @param WorkloadInterface $workload
     */
    protected function onAdd($workload)
    {
        foreach ($this->getObserver() as $observer) {
            if ($this->applyObserver($observer, $workload->getHandlerName())) {
                $this->logger->debug('Call onAdd for ' . get_class($observer));
                $observer->onAdd($workload);
            }
        }
    }

    /**
     * Execute observers on run job.
     * @param WorkloadInterface $workload
     */
    protected function onRun($workload)
    {
        foreach ($this->getObserver() as $observer) {
            if ($this->applyObserver($observer, $workload->getHandlerName())) {
                $this->logger->debug('Call onRun for ' . get_class($observer));
                $observer->onRun($workload);
            }
        }
    }

    /**
     * Execute observers on resolve job.
     * @param WorkloadInterface $workload
     * @param string $resolution
     */
    protected function onResolve($workload, $resolution)
    {
        foreach ($this->getObserver() as $observer) {
            if ($this->applyObserver($observer, $workload->getHandlerName())) {
                $this->logger->debug('Call onResolve for ' . get_class($observer));
                $observer->onResolve($workload, $resolution);
            }
        }
    }

    /**
     * Shutdown handler.
     */
    public function shutdownHandler()
    {
        $this->getRunner()->shutdownHandler();
    }
}
