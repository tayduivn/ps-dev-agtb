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

namespace Sugarcrm\SugarcrmTests\JobQueue\Helper;

use Sugarcrm\Sugarcrm\JobQueue\Helper\Producer;

class ProducerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \SchedulersJob
     */
    protected $parentJob;

    /**
     * @var \SchedulersJob
     */
    protected $childJobFirst;

    /**
     * @var \SchedulersJob
     */
    protected $childJobSecond;

    /**
     * @var Producer
     */
    protected $helper;

    public function setUp()
    {
        \SugarTestHelper::setUp('current_user', array(true, 1));
        $this->parentJob = \SugarTestSchedulersJobUtilities::createJob();
        $this->childJobFirst = \SugarTestSchedulersJobUtilities::createJob();
        $this->childJobFirst->job_group = $this->parentJob->id;
        $this->childJobFirst->save();
        $this->childJobSecond = \SugarTestSchedulersJobUtilities::createJob();
        $this->childJobSecond->job_group = $this->parentJob->id;
        $this->childJobSecond->save();

        $this->helper = new Producer($this->parentJob);
    }

    public function tearDown()
    {
        \SugarTestSchedulersJobUtilities::removeAllCreatedJobs();
        \SugarTestHelper::tearDown();
    }

    public function testParentProgress()
    {
        $this->childJobFirst->status = \SchedulersJob::JOB_STATUS_DONE;
        $this->childJobFirst->save();
        $this->helper->resolve();

        $this->parentJob->retrieve();
        $this->assertEquals(50, $this->parentJob->percent_complete);

        $this->childJobSecond->status = \SchedulersJob::JOB_STATUS_DONE;
        $this->childJobSecond->save();
        $this->helper->resolve();

        $this->parentJob->retrieve();
        $this->assertEquals(100, $this->parentJob->percent_complete);
    }

    /**
     * Parent job can resolve all queued children tasks with selected resolution.
     */
    public function testResolveQueuedChildrenViaParentJob()
    {
        $this->childJobFirst->status = \SchedulersJob::JOB_STATUS_QUEUED;
        $this->childJobFirst->resolution = \SchedulersJob::JOB_PENDING;
        $this->childJobFirst->save();

        $this->childJobSecond->status = \SchedulersJob::JOB_STATUS_QUEUED;
        $this->childJobSecond->resolution = \SchedulersJob::JOB_PARTIAL;
        $this->childJobSecond->save();

        $this->helper->resolveChildren(\SchedulersJob::JOB_CANCELLED);

        $this->childJobFirst->retrieve();
        $this->childJobSecond->retrieve();

        $this->assertEquals(\SchedulersJob::JOB_STATUS_DONE, $this->childJobFirst->status);
        $this->assertEquals(\SchedulersJob::JOB_CANCELLED, $this->childJobFirst->resolution);
        $this->assertEquals(\SchedulersJob::JOB_STATUS_DONE, $this->childJobSecond->status);
        $this->assertEquals(\SchedulersJob::JOB_CANCELLED, $this->childJobSecond->resolution);
    }

    /**
     * First failed child should finish parent job.
     */
    public function testParentFailable()
    {
        $this->parentJob->fallible = true;
        $this->parentJob->save();

        $this->childJobFirst->status = \SchedulersJob::JOB_STATUS_DONE;
        $this->childJobFirst->resolution = \SchedulersJob::JOB_FAILURE;
        $this->childJobFirst->save();

        $this->helper->resolve();

        $this->parentJob->retrieve();
        $this->assertEquals(\SchedulersJob::JOB_FAILURE, $this->parentJob->resolution);
    }

    /**
     * First failed child should NOT fail parent job.
     */
    public function testParentNotFailable()
    {
        $this->parentJob->fallible = false;
        $this->parentJob->save();

        $this->childJobFirst->status = \SchedulersJob::JOB_STATUS_DONE;
        $this->childJobFirst->resolution = \SchedulersJob::JOB_FAILURE;
        $this->childJobFirst->save();

        $this->helper->resolve();

        $this->parentJob->retrieve();
        $this->assertNotEquals(\SchedulersJob::JOB_FAILURE, $this->parentJob->resolution);
    }

    public function testResolveParent()
    {
        $this->childJobFirst->status = \SchedulersJob::JOB_STATUS_DONE;
        $this->childJobFirst->save();
        $this->childJobSecond->status = \SchedulersJob::JOB_STATUS_DONE;
        $this->childJobSecond->save();

        $this->helper->resolve();

        $this->parentJob->retrieve();
        $this->assertEquals(\SchedulersJob::JOB_STATUS_DONE, $this->parentJob->status);
    }

    public function testGetPausedChildren()
    {
        $this->childJobFirst->status = \SchedulersJob::JOB_STATUS_DONE;
        $this->childJobFirst->resolution = \SchedulersJob::JOB_PARTIAL;
        $this->childJobFirst->save();
        $this->childJobSecond->status = \SchedulersJob::JOB_STATUS_DONE;
        $this->childJobSecond->resolution = \SchedulersJob::JOB_CANCELLED;
        $this->childJobSecond->save();

        $expectedIds = array(array('id' => $this->childJobFirst->id));
        $resolutions = array(\SchedulersJob::JOB_PARTIAL);

        $this->assertTrue($this->helper->hasChildren($resolutions));
        $this->assertEquals($expectedIds, $this->helper->getChildren($resolutions));
    }

    public function testDeleteChildren()
    {
        $this->helper->deleteChildren();

        $this->assertNull($this->childJobFirst->retrieve());
        $this->assertNull($this->childJobFirst->retrieve());
    }
}
