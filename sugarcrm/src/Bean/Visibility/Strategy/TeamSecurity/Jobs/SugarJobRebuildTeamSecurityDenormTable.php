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

namespace Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Jobs;

use Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denorm\Manager;

/**
 *
 * Handle the rebuild of Team Security denormalized table.
 *
 */
class SugarJobRebuildTeamSecurityDenormTable implements \RunnableSchedulerJob
{
    /**
     * @var \SchedulersJob
     */
    protected $job;

    /**
     * @var \Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denorm\Manager
     */
    protected $denormManager;

    /**
     * Ctor
     * @param Manager $manager
     */
    public function __construct(Manager $manager = null)
    {
        $this->denormManager = $manager ?: Manager::getInstance();
    }

    /**
     * {@inheritdoc}
     */
    public function setJob(\SchedulersJob $job)
    {
        $this->job = $job;
    }

    /**
     * {@inheritdoc}
     */
    public function run($data)
    {
        $start = time();
        list($status, $message) = $this->denormManager->rebuild();
        $duration = time() - $start;

        $message .= sprintf(' (%s second(s) taken)', $duration);

        if ($status) {
            return $this->job->succeedJob($message);
        }

        return $this->job->failJob($message);
    }
}
