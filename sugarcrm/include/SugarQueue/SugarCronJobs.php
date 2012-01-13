<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once 'include/SugarQueue/SugarQueue.php';
require_once 'modules/Schedulers/Scheduler.php';

/**
 * CRON driver for job queue
 * @api
 */
class SugarCronJobs
{
    public $max_jobs = 5;
    public $max_runtime = 30;
    /**
     * Currently running job
     * @var SchedulersJob
     */
    public $job;

    public function __construct()
    {
        $this->queue = new SugarJobQueue();
    }

    public function throttle()
    {
        $stampfile = sugar_cached("modules/Schedulers/lastrun");
        create_cache_directory($stampfile);
        if(!file_exists($stampfile)) {
            return true;
        } else {
            $ts = file_get_contents($stampfile);
            $now = time();
            if(!file_put_contents($stampfile, $now)) {
                $GLOBALS['log']->fatal('Scheduler cannot write PID file.  Please check permissions on '.$stampfile);
            }
            if($now - $ts < $this->min_interval) {
                // run too frequently
                return false;
            }
        }
        return true;
    }

    public function unexpectedExit()
    {
        if(!empty($this->job)) {
            // TODO: label
            $this->job->failJob("Unexpected failure, please check PHP logs and sugarcrm.log");
            $this->job = null;
        }
    }

    public function runCycle()
    {
        // clean old stale jobs
        $this->queue->cleanup();
        // throttle
        if(!$this->throttle()) {
            $GLOBALS['log']->fatal("Job run too frequently, throttled to protect the system.");
            return;
        }
        // run schedulers
        $this->queue->runSchedulers();
        // run jobs
        register_shutdown_function(array($this, "unexpectedExit"));
        for($count=0;$count<$this->max_jobs;$count++) {
            $this->job = $this->queue->nextJob();
            if(empty($this->job)) {
                return;
            }
            $this->job->runJob();
        }
        $this->job = null;

    }
}