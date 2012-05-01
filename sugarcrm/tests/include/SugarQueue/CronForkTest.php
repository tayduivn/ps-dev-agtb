<?php
//FILE SUGARCRM flav=pro ONLY
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
require_once 'include/SugarQueue/SugarCronParallelJobs.php';
require_once 'modules/SchedulersJobs/SchedulersJob.php';

class CronForkTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // Unfortunately, this test can not be run automatically, since it uses parallel processes
        // and long timeouts. I check it in to make possible to run it manually if needed.
        // Manual testing - run this test and see that after 20 seconds job status in the DB changes to
        // success. You'll have to comment out the next line first, of course.
        $this->markTestSkipped("Cannot be run as part of automated suite");
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        // clean up queue
		$GLOBALS['db']->query("DELETE FROM job_queue WHERE status='queued'");
        $this->jq = $jobq = new SugarCronParallelJobs();
        // Uncomment to test shell on systems with pcntl_fork
        // $jobq->allow_fork = false;
    }

    public function tearDown()
    {
        // Disabling delete since we want it for manual test run
   //     $GLOBALS['db']->query("DELETE FROM job_queue WHERE scheduler_id='unittest'");
       sleep(2);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public static function cronJobFunction($job)
    {
        sleep(20);
        $job->succeedJob("OK!");
        return true;
    }

    public function testQueueJob()
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->name = "Unit test Job";
        $job->target = "function::CronForkTest::cronJobFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid = $job->id;

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        // Not doing asserts here - we'll check the DB manually.
    }
}
