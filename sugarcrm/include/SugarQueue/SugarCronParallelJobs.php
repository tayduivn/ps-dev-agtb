<?php
//FILE SUGARCRM flav=pro ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
//FILE SUGARCRM flav=pro ONLY
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
require_once 'include/SugarQueue/SugarCronJobs.php';
require_once 'include/SugarHttpClient.php';

/**
 * CRON driver for job queue that forks a process for each job
 * @api
 */
class SugarCronParallelJobs extends SugarCronJobs
{
    public $allow_fork = true;

    public function runShell($job)
    {

        // Beware of PHP bug #60185 - maybe problematic here, though works fine on my tests.
        global $sugar_config;
        chdir(dirname(__FILE__). "/../../");
        $command = sprintf("nohup %s -f run_job.php %s %s 1>/dev/null 2>/dev/null &", $sugar_config['cron']['php_binary'], $job->id, $this->getMyId());
        shell_exec($command);
        return true;
    }

    protected function fail($job, $message)
    {
        $job->failJob($message);
        $this->jobFailed($job);
    }

    protected function forkJob($job)
    {
        DBManagerFactory::disconnectAll();
        $pid = pcntl_fork();

        if($pid) {
            // parent - reconnect and exit
            $GLOBALS['db'] = DBManagerFactory::getInstance();
            $GLOBALS['log']->debug('After fork - parent');
            return;
        } else {
          // the child process
          $GLOBALS['log']->debug('After fork - child');
          $sid = posix_setsid();
          if($sid < 0) {
              $this->fail($job, "Could not change session ID");
          }
          // Reestablish DB connections after fork
          $GLOBALS['db'] = DBManagerFactory::getInstance();
          parent::executeJob($job);
          $this->job = null;
          exit(0);
        }
    }

    protected function checkPHPBinary($job)
    {
        global $sugar_config;
        if(empty($sugar_config['cron']['php_binary']) || !file_exists($sugar_config['cron']['php_binary'])) {
            $this->fail($job, "PHP binary not set, please set \$sugar_config['cron']['php_binary']");
            return false;
        }
        return true;
    }

    protected function runWindows($job)
    {
        if(!$this->checkPHPBinary($job)) {
            return;
        }
        if(!extension_loaded("COM")) {
            $this->fail("Cannot run PHP binary, please enable COM extension");
        }
        $WshShell = new COM("WScript.Shell");
        chdir(dirname(__FILE__). "/../../");
        $command = sprintf("%s -f run_job.php %s %s", $sugar_config['cron']['php_binary'], $job->id, $this->getMyId());
        $WshShell->Run($command, 0, false); // no window, don't wait for return
        $WshShell->Release();
    }

    /**
     * Execute given job
     * @param SchedulersJob $job
     */
    public function executeJob($job)
    {
        if(stripos(PHP_OS, "win") !== false && stripos(PHP_OS, "darwin") === false) {
            // windows
            $this->runWindows($job);
            return;
        }
        if($this->allow_fork && function_exists("pcntl_fork") && function_exists("posix_setsid")) {
            $this->forkJob($job);
            return;
        }
        if(!$this->checkPHPBinary($job)) {
            return;
        }
        if(!$this->runShell($job)) {
            $this->fail($job, "Could not launch job {$job->id}");
        }
    }

}

