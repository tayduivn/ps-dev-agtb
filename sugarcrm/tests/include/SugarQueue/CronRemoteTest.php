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
require_once 'include/SugarQueue/SugarCronRemoteJobs.php';

class CronRemoteTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        // clean up queue
		$GLOBALS['db']->query("DELETE FROM job_queue WHERE status='queued'");
		$GLOBALS['sugar_config']['job_server'] = "http://test.job.server/";
    }

    public static function tearDownAdterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['sugar_config']['job_server']);
    }

    public function setUp()
    {
        $this->jq = $jobq = new SugarCronRemoteJobs();
        $this->client = new CronHttpMock();
        $this->jq->setClient($this->client);
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM job_queue WHERE scheduler_id='unittest'");
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

        $this->client->return = json_encode(array("ok" => $job->id));

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertTrue($this->jq->runOk());

        $this->assertEquals("http://test.job.server/submitJob", $this->client->call_url);
        parse_str($this->client->call_data, $qdata);
        $data = json_decode($qdata['data'], true);
        $this->assertEquals($jobid, $data['job']);
        $this->assertEquals($this->jq->getMyId(), $data['client']);
        $this->assertEquals($GLOBALS['sugar_config']['site_url'], $data['instance']);

        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_STATUS_RUNNING, $job->status, "Wrong status");
        $this->assertEquals($this->jq->getMyId(), $job->client, "Wrong client");
    }

    public function testServerFailure()
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

        $this->client->return = ''; // return nothing

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertFalse($this->jq->runOk());
        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
    }

    public function testServerFailureWithError()
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

        $this->client->return = 'This is not the server you are looking for';

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertFalse($this->jq->runOk());
        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertContains('This is not the server you are looking for', $job->message, "Wrong message");
    }
}

class CronHttpMock extends SugarHttpClient
{
     public $call_url;
     public $call_data;
     public $return;
     public function callRest($url, $data) {
         $this->call_url = $url;
         $this->call_data = $data;
         return $this->return;
     }
}