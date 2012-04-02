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

require_once 'modules/SchedulersJobs/SchedulersJob.php';
require_once 'tests/SugarTestUserUtilities.php';
require_once 'tests/SugarTestAccountUtilities.php';

class RunnableSchedulersJobsTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $jobs = array();
    private $jobRan = FALSE;

    public function setUp()
    {
        $this->db = DBManagerFactory::getInstance();
        $this->jobRan = FALSE;
    }

    public function tearDown()
    {
        if(!empty($this->jobs)) {
            $jobs = implode("','", $this->jobs);
            $this->db->query("DELETE FROM job_queue WHERE id IN ('$jobs')");
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $ids = SugarTestAccountUtilities::getCreatedAccountIds();
        if(!empty($ids)) {
            SugarTestAccountUtilities::removeAllCreatedAccounts();
        }
    }

    protected function createJob($data)
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        foreach($data as $key => $val) {
            $job->$key = $val;
        }
        $job->save();
        $this->jobs[] = $job->id;
        return $job;
    }



    public function testRunnableJobRunClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $job = $this->createJob(array("name" => "Test Func", "status" => SchedulersJob::JOB_STATUS_RUNNING,
            "target" => "class::TestRunnableJob", "assigned_user_id" => $GLOBALS['current_user']->id));
        $job->runJob();
        $job->retrieve($job->id);

        $this->assertTrue($job->runnable_ran);

        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");

        // function with args
        $job = $this->createJob(array("name" => "Test Func 2", "status" => SchedulersJob::JOB_STATUS_RUNNING,
                    "target" => "class::TestRunnableJob",
                    "data" => "function data", "assigned_user_id" => $GLOBALS['current_user']->id));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertTrue($job->runnable_ran);
        $this->assertEquals($job->runnable_data, "function data", "Argument 2 doesn't match");
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
    }
}


class TestRunnableJob implements RunnableSchedulerJob
{
    private $job;

    public function run($data)
    {
        $this->job->runnable_ran = true;
        $this->job->runnable_data = $data;
        $this->job->succeedJob();
    }

    public function setJob(SchedulersJob $job)
    {
        $this->job = $job;
    }
}