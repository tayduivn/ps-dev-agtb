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
require_once 'include/SugarQueue/SugarJobQueue.php';
require_once 'modules/Schedulers/Scheduler.php';

/**
 * CRON driver for job queue
 * @api
 */
class SugarCronJobs
{
    /**
     * Max number of jobs per cron run
     * @var int
     */
    public $max_jobs = 10;
    /**
     * Max time per cron run
     * @var int
     */
    public $max_runtime = 60;
    /**
     * Min time between cron runs
     * @var int
     */
    public $min_interval = 30;

    /**
     * Lock file to ensure the jobs aren't run too fast
     * @var string
     */
    public $lockfile;

    /**
     * Currently running job
     * @var SchedulersJob
     */
    public $job;

    /**
     * Is current queue run OK?
     * @var bool
     */
    public $runOk = true;

    /**
     * Should the driver print reports to stdout?
     * @var bool
     */
    public $verbose = false;

    /**
     * This allows to disable schedulers cycle, e.g. for testing
     * @var bool
     */
    public $disable_schedulers = false;

    public function __construct()
    {
        $this->queue = new SugarJobQueue();
        $this->lockfile = sugar_cached("modules/Schedulers/lastrun");
        if(!empty($GLOBALS['sugar_config']['cron']['max_jobs'])) {
            $this->max_jobs = $GLOBALS['sugar_config']['cron']['max_cron_jobs'];
        }
        if(!empty($GLOBALS['sugar_config']['cron']['max_cron_runtime'])) {
            $this->max_jobs = $GLOBALS['sugar_config']['cron']['max_cron_runtime'];
        }
        if(isset($GLOBALS['sugar_config']['cron']['min_cron_interval'])) {
            $this->min_interval = $GLOBALS['sugar_config']['cron']['min_cron_interval'];
        }
    }

    /**
     * Remember last time it was run
     */
    protected function markLastRun()
    {
        if(!file_put_contents($this->lockfile, time())) {
            $GLOBALS['log']->fatal('Scheduler cannot write PID file.  Please check permissions on '.$this->lockfile);
        }
    }

    /**
     * Check if we aren't running jobs too frequently
     * @return bool OK to run?
     */
    public function throttle()
    {
        if($this->min_interval == 0) {
            return true;
        }
        create_cache_directory($this->lockfile);
        if(!file_exists($this->lockfile)) {
            $this->markLastRun();
            return true;
        } else {
            $ts = file_get_contents($this->lockfile);
            $this->markLastRun();
            $now = time();
            if($now - $ts < $this->min_interval) {
                // run too frequently
                return false;
            }
        }
        return true;
    }

    /**
     * What to do if one of the jobs failed
     * @param SchedulersJob $job
     */
    protected function jobFailed($job = null)
    {
        $this->runOk = false;
        if(!empty($job)) {
            $GLOBALS['log']->fatal("Job {$job->id} ({$job->name}) failed in CRON run");
            if($this->verbose) {
                printf(translate('ERR_JOB_FAILED_VERBOSE', 'SchedulersJobs'), $job->id, $job->name);
            }
        }
    }

    /**
     * Shutdown handler to be called if something breaks in the middle of the job
     */
    public function unexpectedExit()
    {
        if(!empty($this->job)) {
            $this->jobFailed($this->job);
            $this->job->failJob(translate('ERR_FAILED', 'SchedulersJobs'));
            $this->job = null;
        }
    }

    /**
     * Return ID for this client
     * @return string
     */
    public function getMyId()
    {
        return 'CRON'.$GLOBALS['sugar_config']['unique_key'].':'.getmypid();
    }

    /**
     * Execute given job
     * @param SchedulersJob $job
     */
    public function executeJob($job)
    {
        if(!$this->job->runJob()) {
            // if some job fails, change run status
            $this->jobFailed($this->job);
        }
    }

    /**
     * Run CRON cycle:
     * - cleanup
     * - schedule new jobs
     * - execute pending jobs
     */
    public function runCycle()
    {
        // throttle
        if(!$this->throttle()) {
            $GLOBALS['log']->fatal("Job runs too frequently, throttled to protect the system.");
            return;
        }
        // clean old stale jobs
        if(!$this->queue->cleanup()) {
            $this->jobFailed();
        }
        // run schedulers
        if(!$this->disable_schedulers) {
            $this->queue->runSchedulers();
        }
        // run jobs
        $cutoff = time()+$this->max_runtime;
        register_shutdown_function(array($this, "unexpectedExit"));
        $myid = $this->getMyId();
        for($count=0;$count<$this->max_jobs;$count++) {
            $this->job = $this->queue->nextJob($myid);
            if(empty($this->job)) {
                return;
            }
            $this->executeJob($this->job);
            if(time() >= $cutoff) {
                break;
            }
        }
        $this->job = null;
    }

    /**
     * Check if the queue run was fine
     */
    public function runOk()
    {
        return $this->runOk;
    }
}