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

use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\JobQueue\LockStrategy\LockStrategyInterface;
use Sugarcrm\Sugarcrm\JobQueue\Worker\WorkerInterface;

/**
 * Class OD
 * @package JobQueue
 */
class OD extends Standard
{
    /**
     * Special functionality for on-demand mode that uses legacy config variables.
     * {@inheritDoc}
     */
    public function __construct($config, WorkerInterface $worker, LockStrategyInterface $lock, LoggerInterface $logger)
    {
        $this->maxRuntime = !empty($config['max_runtime']) ?
            $config['cron']['max_runtime'] :
            $this->maxRuntime;
        parent::__construct($config, $worker, $lock, $logger);
    }

    /**
     * Handle no jobs case.
     * Stop the server.
     */
    protected function noJobsHandler()
    {
        $this->stopWork = true;
    }
}
