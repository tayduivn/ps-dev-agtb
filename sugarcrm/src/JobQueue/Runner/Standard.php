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

namespace Sugarcrm\Sugarcrm\JobQueue\Runner;

declare(ticks = 1);

/**
 * Class Standard
 * @package JobQueue
 */
class Standard extends AbstractRunner
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->acquireLock();
        $this->startWorker();
    }

    /**
     * Added check on lock lifetime.
     * {@inheritdoc}
     */
    public function isWorkProcessActual()
    {
        if (!parent::isWorkProcessActual()) {
            return (time() - $this->lock->getLock()) > $this->lockLifetime;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     * Listen to PCNTL signals.
     */
    protected function registerTicks()
    {
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, array($this, 'handlePCNTLSignals'));
            pcntl_signal(SIGINT, array($this, 'handlePCNTLSignals'));
        }
    }

    /**
     * Signal handler function.
     * Process SIGINT and SIGTERM.
     * @param int $signo PCNTL signal.
     */
    protected function handlePCNTLSignals($signo)
    {
        $this->logger->debug("Handle signal {$signo}.");
        switch ($signo) {
            case SIGINT:
            case SIGTERM:
                $this->logger->info('Terminate worker by signal.');
                $this->stopWork = true;
                break;
            default:
        }
    }
}
