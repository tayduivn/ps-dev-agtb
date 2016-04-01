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

namespace Sugarcrm\Sugarcrm\JobQueue\Helper;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sugarcrm\Sugarcrm\JobQueue\LockStrategy\CacheFile;

/**
 * Class ProcessControl
 * Sugar-specific class to lock the JQ service entry point during upgrade.
 * @package JobQueue
 */
class ProcessControl
{
    protected $lockFileKey;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * The service needs to be locked.
     * @var string
     */
    protected $serviceName;

    /**
     * The maximum amount of time to wait, in seconds, for processes to be killed.
     * @var int
     */
    protected $maxWaitTime = 600;

    /**
     * Set null logger.
     * @param string $serviceName
     */
    public function __construct($serviceName)
    {
        $this->logger = new NullLogger();
        $this->serviceName = $serviceName;
        $this->lockFileKey = $this->serviceName . '_processControl.lock';
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getLockFileKey()
    {
        return $this->lockFileKey;
    }

    /**
     * Set instance-specific process title if possible.
     * PHP 5.5+.
     */
    public function modifyServiceProcessName()
    {
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($this->generateServiceProcessName());
        }
    }

    /**
     * Generate a name for the service process.
     * For PHP 5.5+ generates instance specific name, else service name.
     * @return string Process name of the service.
     */
    public function generateServiceProcessName()
    {
        $processName = $this->serviceName;
        if (function_exists('cli_set_process_title')) {
            $instKey = \SugarConfig::getInstance()->get('unique_key');
            $processName = $instKey . $this->serviceName;
        }
        return $processName;
    }

    /**
     * Gracefully stop running entry point processes.
     * TODO: Windows OS.
     */
    public function stopServiceProcesses()
    {
        // [] to exclude the grep.
        $processName = $this->generateServiceProcessName();
        $modifiedEPName = substr_replace($processName, "[{$processName[0]}]", 0, 1);
        $managerPIDs = shell_exec("ps aux | grep '{$modifiedEPName}' | awk '{print $2}'");
        if (!$managerPIDs) {
            $this->logger->info('No running processes found.');
            return;
        }
        $this->logger->debug("Found processes {$managerPIDs}.");
        $formattedPIDs = array_map('intval', explode("\n", rtrim($managerPIDs, "\n")));

        $expectedProcessCount = 0;
        $lock = new CacheFile();
        // 7.7 does not have signal handling, kill the process using lock.
        if ($lock->hasLock()) {
            $this->logger->info('Use lock strategy to stop the processes.');
            // Monopolize process.
            $lock->setLock(time());

            // Having lock means only one process. Another instance can have the same name process, wait until at least
            // one process is stopped.
            $expectedProcessCount = count($formattedPIDs) - 1;
        } else {
            foreach ($formattedPIDs as $pid) {
                $this->logger->debug("Send SIGTERM to {$pid}.");
                posix_kill($pid, SIGTERM);
            }
        }

        $waitTime = microtime(true);
        $psStrPIDs = implode(' ', $formattedPIDs);
        do {
            $processesOut = [];
            exec("ps --no-headers  {$psStrPIDs}", $processesOut);
            usleep(800);
        } while (count($processesOut) > $expectedProcessCount && (microtime(true) - $waitTime) < $this->maxWaitTime);

        $lock->clearLock();
    }

    /**
     * Check the lock file existence.
     */
    public function isServiceLocked()
    {
        return file_exists(sugar_cached($this->lockFileKey));
    }

    /**
     * Lock the JobQueue entry point.
     * Touch a lock file.
     */
    public function lockService()
    {
        sugar_touch(sugar_cached($this->lockFileKey));
    }

    /**
     * Unlink the lock file.
     */
    public function unlockService()
    {
        if ($this->isServiceLocked()) {
            unlink(sugar_cached($this->lockFileKey));
        }
    }
}
