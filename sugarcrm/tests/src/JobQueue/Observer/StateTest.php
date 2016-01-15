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

namespace Sugarcrm\SugarcrmTests\JobQueue\Observer;

use Sugarcrm\Sugarcrm\JobQueue\Observer\State;
use Sugarcrm\Sugarcrm\JobQueue\Workload\Workload;

class StateTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \SchedulersJob
     */
    protected $parentJob;

    /**
     * @var \SchedulersJob
     */
    protected $subJob;

    /**
     * @var State
     */
    protected $observer;

    /**
     * @var Workload
     */
    protected $workload;

    public function setUp()
    {
        \SugarTestHelper::setUp('current_user', array(true, 1));
        $this->parentJob = \SugarTestSchedulersJobUtilities::createJob();
        $this->subJob = \SugarTestSchedulersJobUtilities::createJob();
        $this->subJob->job_group = $this->parentJob->id;
        $this->subJob->save();

        $this->workload = new Workload('testRoute', array(), array('dbId' => $this->subJob->id));
        $this->observer = new State();
    }

    public function tearDown()
    {
        \SugarTestSchedulersJobUtilities::removeAllCreatedJobs();
        \SugarTestHelper::tearDown();
    }

    /**
     * Should stop job if has been cancelled from interface.
     * @expectedException \Exception
     */
    public function testOnRunCancelledJob()
    {
        $this->subJob->resolution = \SchedulersJob::JOB_CANCELLED;
        $this->subJob->save();
        $this->observer->onRun($this->workload);
    }

    /**
     * Should stop job if has been paused.
     * @expectedException \Exception
     */
    public function testOnRunPausedJob()
    {
        $this->subJob->resolution = \SchedulersJob::JOB_PARTIAL;
        $this->subJob->save();
        $this->observer->onRun($this->workload);
    }

    /**
     * Should stop if parent job is over.
     * @expectedException \Exception
     */
    public function testOnRunParentOver()
    {
        $this->parentJob->status = \SchedulersJob::JOB_STATUS_DONE;
        $this->parentJob->resolution = \SchedulersJob::JOB_SUCCESS;
        $this->parentJob->save();
        $this->observer->onRun($this->workload);
    }

    /**
     * Last subtasks resolves parent.
     */
    public function testOnResolveLastSubtaskResolveParent()
    {
        $this->parentJob->status = \SchedulersJob::JOB_STATUS_RUNNING;
        $this->parentJob->resolution = \SchedulersJob::JOB_RUNNING;
        $this->parentJob->save();

        $this->subJob->status = \SchedulersJob::JOB_STATUS_DONE;
        $this->subJob->resolution = \SchedulersJob::JOB_SUCCESS;
        $this->subJob->save();

        $this->observer->onResolve($this->workload, $this->subJob->resolution);

        $this->parentJob->retrieve();
        $this->assertEquals(\SchedulersJob::JOB_SUCCESS, $this->parentJob->resolution);
    }

    /**
     * Parent job should resolve subtasks in case of cancelling and pausing.
     */
    public function testOnResolveParentAffectsSubtasks()
    {
        $this->parentJob->status = \SchedulersJob::JOB_STATUS_DONE;
        $this->subJob->status = \SchedulersJob::JOB_STATUS_DONE;

        $this->parentJob->resolution = \SchedulersJob::JOB_CANCELLED;
        $this->subJob->resolution = \SchedulersJob::JOB_PARTIAL;
        $this->parentJob->save();
        $this->subJob->save();

        $this->workload->setAttribute('dbId', $this->parentJob->id);

        $this->observer->onResolve($this->workload, $this->parentJob->resolution);
        $this->subJob->retrieve();
        $this->assertEquals($this->parentJob->resolution, $this->subJob->resolution);

        $this->parentJob->resolution = \SchedulersJob::JOB_FAILURE;
        $this->subJob->resolution = \SchedulersJob::JOB_PENDING;
        $this->parentJob->save();
        $this->subJob->save();

        $this->observer->onResolve($this->workload, $this->parentJob->resolution);
        $this->subJob->retrieve();
        $this->assertEquals($this->parentJob->resolution, $this->subJob->resolution);
    }
}
