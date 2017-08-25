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

use Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denorm\DenormManager;

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
     * @var Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denorm\DenormManager
     */
    protected $denormManager;

    /**
     * Ctor
     * @param DenormManager $denormManager
     */
    public function __construct(DenormManager $denormManager = null)
    {
        $this->denormManager = $denormManager ?: DenormManager::getInstance();
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
        // We need to only run if Team Security denormalized table rebuild is required
        if (!$this->denormManager->getIsRebuildRequired()) {
            $msg = 'Team Security denormalized table rebuild not required at this time.';
            return $this->job->succeedJob($msg);
        }

        // Check if Team Security use denormalized table config is enabled
        if (!($this->denormManager->isEnabledAdminActionUpdate() || $this->denormManager->isEnabledUseDenorm())) {
            $msg = 'Team Security use of denormalized table is not enabled. No need to run the job.';
            return $this->job->succeedJob($msg);
        }

        list($success, $duration, $errorMsg) = $this->denormManager->initializeAndRebuild();

        if ($success) {
            $msg = sprintf("Team Security denormalized table rebuild completed in %s second(s)", $duration);
            return $this->job->succeedJob($msg);
        } else {
            $msg = sprintf("Team Security denormalized table rebuild failed with error '%s'", $errorMsg);
            return $this->job->failJob($msg);
        }
    }
}
