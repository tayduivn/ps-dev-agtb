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

namespace Sugarcrm\Sugarcrm\JobQueue\Runner;

use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\JobQueue\LockStrategy\LockStrategyInterface;
use Sugarcrm\Sugarcrm\JobQueue\Worker\WorkerInterface;

/**
 * Class AbstractRunner
 * @package JobQueue
 */
abstract class AbstractRunner implements RunnerInterface
{
    /**
     * @var bool
     * When true, workers will stop look for jobs.
     * If there are child pocesses the parent process will kill off all running children.
     */
    protected $stopWork = false;

    /**
     * @var int
     * Max time to run (seconds). Default is 3600.
     */
    protected $maxRuntime = 3600;

    /**
     * @var int Seconds.
     * Update lock cycle time.
     */
    protected $lockUpdateCycle = 60;

    /**
     * Lifetime of lock value in seconds.
     * Default is 5 minutes.
     *
     * @var int
     */
    protected $lockLifetime = 300;

    /**
     * @var int
     * Timeout if there are no active jobs (seconds).
     */
    protected $noJobsTimeout = 5;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var WorkerInterface
     */
    protected $worker;

    /**
     * @var LockStrategyInterface
     */
    protected $lock;

    /**
     * @var int $lockValue Runner's lock value.
     */
    protected $lockValue;

    /**
     * @param array $config
     * @param WorkerInterface $worker
     * @param LockStrategyInterface $lock
     * @param LoggerInterface $logger
     */
    public function __construct($config, WorkerInterface $worker, LockStrategyInterface $lock, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->worker = $worker;
        $this->lock = $lock;
    }

    /**
     * Removes runner lock on shutdown.
     */
    public function shutdownHandler()
    {
        $this->logger->info('Shutdown runner.');
        if ($this->lock->hasLock()) {
            $lockTime = $this->lock->getLock();
            if ($lockTime != $this->lockValue) {
                return;
            }
            $this->lock->clearLock();
        }
    }

    /**
     * Lock the process using a lock strategy.
     */
    public function acquireLock()
    {
        if (!$this->isWorkProcessActual()) {
            $this->logger->notice('Another instance of JQ already locked process. Exit.');
            exit(0);
        }
        $this->updateLock();
    }

    /**
     * {@inheritdoc}
     */
    abstract public function run();

    /**
     * Start handling workers.
     */
    public function startWorker()
    {
        $this->logger->info('Start worker.');
        $startTime = time();

        while (!$this->stopWork) {
            gc_collect_cycles();
            if ($this->worker->work() ||
                $this->worker->returnCode() === WorkerInterface::RETURN_CODE_IO_WAIT ||
                $this->worker->returnCode() === WorkerInterface::RETURN_CODE_NO_JOBS
            ) {
                // Lock gone or previous work was too long.
                if (!$this->isWorkProcessActual()) {
                    $this->stopWork = true;
                    continue;
                }
                if ($this->worker->returnCode() === WorkerInterface::RETURN_CODE_SUCCESS) {
                    continue;
                }

                if (!$this->worker->wait()) {
                    $this->stopWork = true;
                    continue;
                }
            }

            if (!$this->isWorkProcessActual()) {
                $this->stopWork = true;
                continue;
            }

            /**
             * Check the running time of the current process.
             * If it has been too long, stop working.
             */
            if ($this->maxRuntime > 0 && time() - $startTime > $this->maxRuntime) {
                $this->stopWork = true;
                continue;
            }

            /**
             * Update lock time every minute.
             */
            if ((time() - $this->lockValue) > $this->lockUpdateCycle) {
                $this->updateLock();
            }

            if ($this->worker->returnCode() === WorkerInterface::RETURN_CODE_NO_JOBS ||
                $this->worker->returnCode() === WorkerInterface::RETURN_CODE_TIMEOUT
            ) {
                $this->noJobsHandler();
            }
        }
        $this->shutdownHandler();
    }

    /**
     * Check work process actuality.
     *
     * @return bool
     */
    public function isWorkProcessActual()
    {
        if (!$this->lock->isActual()) {
            return (time() - $this->lock->getLock()) > $this->lockLifetime;
        }
        return true;
    }

    /**
     * Save new lock value for runner.
     */
    public function updateLock()
    {
        $this->lockValue = time();
        $this->lock->setLock($this->lockValue);
    }

    /**
     * Handle no jobs case.
     */
    protected function noJobsHandler()
    {
        if (!empty($this->noJobsTimeout)) {
            sleep($this->noJobsTimeout);
        }
    }
}
