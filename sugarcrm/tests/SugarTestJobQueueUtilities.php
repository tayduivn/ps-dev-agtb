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

require_once("include/SugarQueue/SugarJobQueue.php");

/**
 * SugarTestJobQueueUtilities
 *
 * utility class for job queues
 *
 */
class SugarTestJobQueueUtilities
{
    private static $_jobQueue;
    private static $_createdJobs = array();

    private function __construct() {}

    /**
     * createAndRunJob
     *
     * This creates and executes the job, returns a new job object
     *
     * @param $name the name of the job
     * @param $target the target function/method
     * @param $data any extra data for the job
     * @param $user the user object to assign to this job
     * @return new job object
     */
    public static function createAndRunJob($name, $target, $data, $user)
    {
        $job = BeanFactory::getBean('SchedulersJobs');
        $job->name = $name;
        $job->target = $target;
        $job->data = $data;
        $job->retry_count = 0;
        $job->assigned_user_id = $user->id;
        self::$_jobQueue = new SugarJobQueue();
        self::$_jobQueue->submitJob($job);
        $job->runJob();
        self::$_createdJobs[] = $job;
        return $job;
    }

    /**
     * removeAllCreatedJobs
     *
     * remove jobs created by this test utility
     *
     * @return boolean true on successful removal
     */
    public static function removeAllCreatedJobs()
    {
        if(empty(self::$_createdJobs))
            return true;
        $jobIds = self::getCreatedJobIds();
        $GLOBALS['db']->query(
            sprintf("DELETE FROM job_queue WHERE id IN ('%s')",
                implode("','", $jobIds))
        );
        self::$_createdJobs = array();
        return true;
    }

    /**
     * getCreatedJobIds
     *
     * get array of job ids created by this utility
     *
     * @return array list of job ids
     */
    public static function getCreatedJobIds()
    {
        $jobIds = array();
        foreach (self::$_createdJobs as $job) {
            $jobIds[] = $job->id;
        }
        return $jobIds;
    }

    public static function setCreatedJobs(array $jobs)
    {
        foreach ($jobs as $job) {
            self::$_createdJobs[] = $job;
        }
    }
}
?>