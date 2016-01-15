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

use Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException;
use Sugarcrm\Sugarcrm\JobQueue\Helper\Resolution;
use Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface;
use Sugarcrm\Sugarcrm\Logger\LoggerTransition as Logger;

/**
 * Class Reflection
 * @package JobQueue
 */
class Reflection implements ObserverInterface
{
    /**
     * @var Resolution
     */
    protected $resolutionHelper;

    /**
     * @var \User
     */
    protected $user;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Setup resolution helper.
     * @param \User|null $user
     */
    public function __construct(\User $user = null)
    {
        $this->resolutionHelper = new Resolution();
        $this->user = $user ? $user : $GLOBALS['current_user'];
        $this->logger = new Logger(\LoggerManager::getLogger());
    }

    /**
     * Create an SchedulersJobs record to follow job executing process via module interface.
     * Add the 'JobId' attribute to workload.
     * Setup $bean->job_group if the passed workload already has context 'dbId'.
     * {@inheritdoc}
     */
    public function onAdd(WorkloadInterface $workload)
    {
        $job = \BeanFactory::newBean('SchedulersJobs');

        $job->interface = true;
        $job->name = $workload->getHandlerName();
        $job->target = $workload->getRoute();
        $job->data = base64_encode(serialize($workload->getData()));
        $job->execute_time = null;

        $job->job_group = $workload->getAttribute('dbId');

        $module = $workload->getAttribute('module');
        $job->module = $module ? $module : 'SchedulersJobs';

        $job->fallible = $workload->getAttribute('fallible');
        $job->rerun = $workload->getAttribute('rerun');

        $job->assigned_user_id = $this->user->id;

        $job->save();
        $this->resolutionHelper->setResolution($job, \SchedulersJob::JOB_PENDING);

        $workload->setAttribute('dbId', $job->id);
    }

    /**
     * Mark following record as running.
     * {@inheritdoc}
     */
    public function onRun(WorkloadInterface $workload)
    {
        $job = \BeanFactory::getBean('SchedulersJobs', $workload->getAttribute('dbId'));
        if (!$job->id) {
            $this->logger->notice('Cannot get bean by dbId.');
            return;
        }
        $jobUser = \BeanFactory::getBean('Users', $job->assigned_user_id);
        if (!$jobUser->id) {
            new LogicException("The user '{$job->assigned_user_id}' is not found.");
        }
        $this->sudo($jobUser);
        $job->execute_time = \TimeDate::getInstance()->nowDb();
        $job->save();
        $this->resolutionHelper->setResolution($job, \SchedulersJob::JOB_RUNNING);
    }

    /**
     * Resolve created in onAdd db record.
     * {@inheritdoc}
     */
    public function onResolve(WorkloadInterface $workload, $resolution)
    {
        $job = \BeanFactory::getBean('SchedulersJobs', $workload->getAttribute('dbId'));
        if (!$job->id) {
            $this->logger->notice('Cannot get bean by dbId.');
            return;
        }
        $this->logger->info("Resolving job {$job->id} as {$resolution}.");

        $job->execute_time = \TimeDate::getInstance()->nowDb();
        $job->message = $workload->getAttribute('errorMessage');

        $job->save();
        $this->resolutionHelper->setResolution($job, $resolution);
        $this->clearSugarCache();
        // Should be the last action.
        $this->sudo($this->user);
    }

    /**
     * Change current user to given one.
     * @param \User $user
     */
    protected function sudo(\User $user)
    {
        if ($user->id == $GLOBALS['current_user']->id) {
            return;
        }
        $GLOBALS['current_user'] = $user;
        if (isset($_SESSION)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['authenticated_user_id'] = $user->id;
        }
    }

    /**
     * Clear BeanFactory's and locals cache.
     */
    protected function clearSugarCache()
    {
        \BeanFactory::clearCache();
        sugar_cache_reset();
        // Start populating local cache again.
        \SugarCache::$isCacheReset = false;
    }
}
