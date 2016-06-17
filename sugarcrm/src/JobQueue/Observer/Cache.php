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

namespace Sugarcrm\Sugarcrm\JobQueue\Observer;

use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface;

/**
 * Class Cache.
 * @package JobQueue
 */
class Cache implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Do nothing.
     * {@inheritdoc}
     */
    public function onAdd(WorkloadInterface $workload)
    {
    }

    /**
     * Clear SugarCRM cache.
     * {@inheritdoc}
     */
    public function onRun(WorkloadInterface $workload)
    {
        $this->logger->info('Clear application cache.');

        global $sugar_config;
        if (is_file('config.php')) {
            require 'config.php';
        }
        if (is_file('config_override.php')) {
            require 'config_override.php';
        }
        \SugarConfig::getInstance()->clearCache();
        \BeanFactory::clearCache();
        sugar_cache_reset();
        // Start populating local cache again.
        \SugarCache::$isCacheReset = false;
    }

    /**
     * Do nothing.
     * {@inheritdoc}
     */
    public function onResolve(WorkloadInterface $workload, $resolution)
    {
        \BeanFactory::clearCache();
        gc_collect_cycles();
    }
}
