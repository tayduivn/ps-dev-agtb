<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
require_once 'include/SugarQueue/SugarJobQueue.php';
require_once 'modules/SchedulersJobs/SchedulersJob.php';

class JobQueueTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->jq = new TestSugarJobQueue();
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM job_queue WHERE scheduler_id='unittest'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testSubmitJob()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $now = $GLOBALS['timedate']->nowDb();
        $job->name = "Unit test Job 1";
        $job->target = "test::test";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $id = $this->jq->submitJob($job);

        $this->assertNotEmpty($id, "Bad job ID");
        $job = new SchedulersJob();
        $job->retrieve($id);
        $this->assertEquals(SchedulersJob::JOB_PENDING, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, "Wrong status");
        $this->assertEquals($now, $job->execute_time_db, "Wrong execute time");
    }

    public function testGetJob()
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $now = $GLOBALS['timedate']->nowDb();
        $job->name = "Unit test Job 1";
        $job->target = "test::test";
        $id = $this->jq->submitJob($job);

        $this->assertNotEmpty($id, "Bad job ID");
        $job = $this->jq->getJob($id);
        $this->assertEquals(SchedulersJob::JOB_PENDING, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, "Wrong status");
        $this->assertEquals($now, $job->execute_time_db, "Wrong execute time");

        $job = $this->jq->getJob("nosuchjob");
        $this->assertNull($job, "Bad return on non-existing job");
    }

    public function testCleanup()
    {
        $job = new SchedulersJob();
        $job->update_date_modified = false;
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $job->execute_time = $GLOBALS['timedate']->nowDb();
        $job->date_entered = '2010-01-01 12:00:00';
        $job->date_modified = '2010-01-01 12:00:00';
        $job->name = "Unit test Job 1";
        $job->target = "test::test";
        $job->save();
        $jobid = $job->id;
        $this->jq->cleanup();

        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
    }

    public function testDelete()
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $job->name = "Unit test Job 1";
        $job->target = "test::test";
        $job->save();
        $jobid = $job->id;
        $this->jq->deleteJob($jobid);

        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEmpty($job->id, "Job not deleted");
    }

    public function testGetNextJob()
    {
        // should get only jobs with status QUEUED, in date_entered order, and mark them as running
        // Clean up the queue
        $GLOBALS['db']->query("DELETE FROM job_queue WHERE status='".SchedulersJob::JOB_STATUS_QUEUED."'");
        $job = $this->jq->nextJob("unit test");
        $this->assertNull($job, "Extra job found");
        // older job
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->date_entered = '2010-01-01 12:00:00';
        $job->name = "Old Job";
        $job->target = "test::test";
        $job->save();
        $jobid1 = $job->id;
        // another job, later date
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->date_entered = '2012-01-01 12:00:00';
        $job->name = "Newer Job";
        $job->target = "test::test";
        $job->save();
        $jobid2 = $job->id;
        // job with execute date in the future
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = $GLOBALS['timedate']->getNow()->modify("+3 days")->asDb();
        $job->date_entered = '2010-01-01 12:00:00';
        $job->name = "Future Job";
        $job->target = "test::test";
        $job->save();
        $jobid3 = $job->id;
        //running job
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $job->date_entered = '2010-01-01 12:00:00';
        $job->name = "Running Job";
        $job->target = "test::test";
        $job->save();
        $jobid4 = $job->id;
        // done job
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_DONE;
        $job->scheduler_id = 'unittest';
        $job->date_entered = '2010-01-01 12:00:00';
        $job->name = "Done Job";
        $job->target = "test::test";
        $job->save();
        $jobid5 = $job->id;
        // get the first one
        $job = $this->jq->nextJob("unit test");
        $this->assertEquals($jobid1, $job->id, "Wrong job fetched");
        $this->assertEquals(SchedulersJob::JOB_STATUS_RUNNING, $job->status, "Wrong status");
        $this->assertEquals("unit test", $job->client, "Wrong client");
        // check that DB record matches
        $job = new SchedulersJob();
        $job->retrieve($jobid1);
        $this->assertEquals(SchedulersJob::JOB_STATUS_RUNNING, $job->status, "Wrong status");
        $this->assertEquals("unit test", $job->client, "Wrong client");
        // get the second one
        $job = $this->jq->nextJob("unit test");
        $this->assertEquals($jobid2, $job->id, "Wrong job fetched");
        $this->assertEquals(SchedulersJob::JOB_STATUS_RUNNING, $job->status, "Wrong status");
        $this->assertEquals("unit test", $job->client, "Wrong client");
        // try to get the third one, should get null
        $job = $this->jq->nextJob("unit test");
        $this->assertNull($job, "Extra job found");
    }

}

class TestSugarJobQueue extends SugarJobQueue
{
    public function getJob($jobId)
    {
        return parent::getJob($jobId);
    }
}
