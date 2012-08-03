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
require_once 'include/SugarQueue/SugarCronJobs.php';
require_once 'modules/SchedulersJobs/SchedulersJob.php';

class CronTest extends Sugar_PHPUnit_Framework_TestCase
{
    static public $jobCalled = false;

    public static function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        // clean up queue
		$GLOBALS['db']->query("DELETE FROM job_queue WHERE status='queued'");
    }

    public static function tearDownAdterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function setUp()
    {
        $this->jq = $jobq = new SugarCronJobs();
        self::$jobCalled = false;
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM job_queue WHERE scheduler_id='unittest'");
    }

    public function testThrottle()
    {
        $this->jq->throttle();
        $this->assertFalse($this->jq->throttle(), "Should prohibit second time");
        // wait a bit
        sleep(2);
        $this->jq->min_interval = 1;
        $this->assertTrue($this->jq->throttle(), "Should allow after delay");
    }

    public static function cronJobFunction()
    {
        self::$jobCalled = true;
        return true;
    }

    public static function cronJobLongFunction()
    {
        self::$jobCalled = true;
        sleep(2);
        return true;
    }

    public function testQueueJob()
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->name = "Unit test Job";
        $job->target = "function::CronTest::cronJobFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid = $job->id;

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertTrue(self::$jobCalled, "Job was not called");
        $this->assertTrue($this->jq->runOk(), "Wrong OK flag");
        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
    }

    public function testQueueFailJob()
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->name = "Unit test Job";
        $job->target = "function::test::test";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid = $job->id;

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertFalse($this->jq->runOk(), "Wrong OK flag");
        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
    }

    public function testJobsCount()
    {
        // job 1 - oldest, should be executed
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->date_entered = '2010-01-01 12:00:00';
        $job->name = "Unit test Job 1";
        $job->target = "function::CronTest::cronJobFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid1 = $job->id;
        // job 2 - newer, should not be executed
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->date_entered = '2012-01-01 12:00:00';
        $job->name = "Unit test Job 2";
        $job->target = "function::CronTest::cronJobFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid2 = $job->id;

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->max_jobs = 1; // only one job per cycle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertTrue(self::$jobCalled, "Job was not called");
        $job = new SchedulersJob();
        $job->retrieve($jobid1);
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        // test that second one wasn't run
        $job = new SchedulersJob();
        $job->retrieve($jobid2);
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, "Wrong status");
    }

    public function testJobsTimeCutoff()
    {
        // job 1 - oldest, should be executed
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->date_entered = '2010-01-01 12:00:00';
        $job->name = "Unit test Job 1";
        $job->target = "function::CronTest::cronJobLongFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid1 = $job->id;
        // job 2 - newer, should not be executed
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->date_entered = '2012-01-01 12:00:00';
        $job->name = "Unit test Job 2";
        $job->target = "function::CronTest::cronJobLongFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid2 = $job->id;

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->max_jobs = 10;
        $this->jq->max_runtime = 1; // only 1 sec runtime
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertTrue(self::$jobCalled, "Job was not called");
        $job = new SchedulersJob();
        $job->retrieve($jobid1);
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        // test that second one wasn't run
        $job = new SchedulersJob();
        $job->retrieve($jobid2);
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, "Wrong status");
    }

    public function testJobsCleanup()
    {
        // job 1 - oldest, should be executed
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->date_entered = '2010-01-01 12:00:00';
        $job->date_modified = '2010-01-01 12:00:00';
        $job->name = "Unit test Job 1";
        $job->target = "function::CronTest::cronJobFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid1 = $job->id;

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertFalse(self::$jobCalled, "Job was called");
        $this->assertFalse($this->jq->runOk(), "Wrong OK flag");
        $job = new SchedulersJob();
        $job->retrieve($jobid1);
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
    }

}
