<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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

require_once('modules/SchedulersJobs/SchedulersJob.php');

/**
 * SugarJobCreateNextTimePeriod.php
 *
 * This class implements RunnableSchedulerJob and provides the support for
 * automating creating time periods.
 *
 */
class SugarJobCreateNextTimePeriod implements RunnableSchedulerJob
{

    /**
     * @var $job the job object
     */
    protected $job;

    /**
     * This method implements setJob from RunnableSchedulerJob and sets the SchedulersJob instance for the class
     *
     * @param SchedulersJob $job the SchedulersJob instance set by the job queue
     *
     */
    public function setJob(SchedulersJob $job)
    {
        $this->job = $job;
    }

    /**
     * This method implements the run function of RunnableSchedulerJob and handles processing a SchedulersJob
     *
     * @param Mixed $data parameter passed in from the job_queue.data column when a SchedulerJob is run
     * @return bool true on success, false on error
     */
    public function run($data)
    {
        $db = DBManagerFactory::getInstance();

        $timedate = TimeDate::getInstance();

        //fetch the last timeperiod
        $query = "select id from timeperiods where is_leaf = 0 and deleted = 0 order by end_date_timestamp desc";
        $id = $db->getOne($query);
        //load it from the bean factory
        $lastTimePeriod = BeanFactory::getBean(TimePeriod::getCurrentTypeClass(), $id);
        //determine leaf type, if none are currently on timeperiod, it will build the default
        $leaf_type = "";
        if($lastTimePeriod->hasLeaves()) {
            $leaves = $lastTimePeriod->getLeaves();
            $leaf_type = $leaves[0]->time_period_type;
        }
        $lastTimePeriod = $lastTimePeriod->createNextTimePeriod();
        $lastTimePeriod->buildLeaves($leaf_type);

        //reschedule myself
        //get current timeperiod
        $currentTimePeriod = BeanFactory::getBean(TimePeriod::getCurrentTypeClass(),TimePeriod::getCurrentId());
        //advance one
        $currentTimePeriod = $currentTimePeriod->getNextTimePeriod();
        $nextEndDate = $timedate->fromDbDate($currentTimePeriod->end_date);
        $this->job->execute_time = $timedate->asUserDate($nextEndDate,true);
        $this->job->save();
        return true;
    }

}