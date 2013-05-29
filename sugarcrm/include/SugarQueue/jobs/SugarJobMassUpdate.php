<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once('include/MassUpdate.php');
require_once('include/SugarQueue/SugarJobQueue.php');
require_once('modules/SchedulersJobs/SchedulersJob.php');
require_once('include/api/RestService.php');
require_once('clients/base/api/FilterApi.php');

/**
 * @api
 */
class SugarJobMassUpdate implements RunnableSchedulerJob
{

    /**
     * the ids of the child jobs
     */
    protected $workJobIds = array();

    /**
     * @var int number of records to be updated/deleted at one time
     */
    protected $chunkSize = 1000;

    /**
     * constructor
     */
    public function __construct()
    {
        if (!empty($GLOBALS['sugar_config']['massupdate_chunk_size'])) {
            $this->chunkSize = $GLOBALS['sugar_config']['massupdate_chunk_size'];
        }
    }

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
     * Setting global variables expected by down stream classes (MassUpdate, SearchForm2, etc)
     *
     */
    public static function preProcess($mu_params)
    {
        // classes downstream rely heavily on $_POST and $_REQUEST
        // until we rewrite the whole thing, we need to modify $_POST and $_REQUEST for mass update to work
        $_POST = array_merge($_POST, $mu_params);
        $_REQUEST['massupdate'] = true;
        if (isset($mu_params['uid'])) {
            if (is_array($mu_params['uid'])) {
                $_REQUEST['uid'] = implode(',', $mu_params['uid']);
            } else {
                $_REQUEST['uid'] = $mu_params['uid'];
            }
        }
        if (!empty($mu_params['entire'])) {
            $_REQUEST['entire'] = $mu_params['entire'];
            $_REQUEST['select_entire_list'] = 1;
        }
    }

    /**
     * Create a job queue consumer for mass update
     *
     * @param Mixed $data job queue data
     * @param String $jobType job type - 'init' for parent job, 'work' for child job
     * @return String id of the created job
     */
    public function createJobQueueConsumer($data, $jobType = "init")
    {
        $job = new SchedulersJob();
        $job->name = 'MassUpdate_'.$jobType;
        $job->target = "class::SugarJobMassUpdate";

        $data['_jobType_'] = $jobType;
        $job->data = json_encode($data);

        $job->assigned_user_id = $GLOBALS['current_user']->id;

        $queue = new SugarJobQueue();
        $queue->submitJob($job);

        return $job->id;
    }


    /**
     * Main function that handles the asynchronous massupdate.
     *
     * @param $data job queue data
     */
    public function run($data)
    {
        /*
          - type:init
            - perform filter to get all records to be updated, including id
            - create child jobs (type=work), each job has up to $this->chunkSize records
          - type:work
            - do update/delete
         */

        $data = json_decode(from_html($data), true);

        if (empty($data) || !is_array($data) || empty($data['_jobType_'])) {
            $this->job->failJob('Invalid job data.');
            return false;
        }

        switch ($data['_jobType_'])
        {
            // this is the parent job, find out all the records to be updated and create child jobs
            case 'init':
                self::preProcess($data);

                // if uid is already provided, use them
                if (isset($data['uid'])) {
                    if (!is_array($data['uid'])) {
                        $data['uid'] = explode(',', $data['uid']);
                    }

                    $uidChunks = array_chunk($data['uid'], $this->chunkSize);
                    foreach ($uidChunks as $chunk) {
                        $tmpData = $data;
                        $tmpData['uid'] = $chunk;
                        $this->workJobIds[] = $this->createJobQueueConsumer($tmpData, 'work');
                    }
                }
                // if updating entire list, use filter
                else if (!empty($data['entire'])) {
                    // call filter api to get the ids then create a job queue for each chunk
                    $filterApi = new FilterApi();
                    $api = new RestService();
                    $api->user = $GLOBALS['current_user'];
                    $nextOffset = 0;
                    $filterArgs = array('module'=>$data['module'], 'fields'=>'id');
                    if (isset($data['filter'])) {
                        $filterArgs['filter'] = $data['filter'];
                    }
                    $uidArray = array();

                    // max_num does not need to set to chunkSize, it can be any size that makes sense
                    $filterArgs['max_num'] = $this->chunkSize;

                    // start getting all the ids
                    while ($nextOffset != -1) { // still have records to be fetched
                        $filterArgs['offset'] = $nextOffset;
                        $result = $filterApi->filterList($api, $filterArgs);
                        $nextOffset = $result['next_offset'];
                        foreach ($result['records'] as $record) {
                            if (!empty($record['id'])) {
                                $uidArray[] = $record['id'];
                            }
                        }
                    }

                    // create one child job for each chunk
                    if (count($uidArray)) {
                        $uidChunks = array_chunk($uidArray, $this->chunkSize);
                        foreach ($uidChunks as $chunk) {
                            $tmpData = $data;
                            $tmpData['uid'] = $chunk;
                            $this->workJobIds[] = $this->createJobQueueConsumer($tmpData, 'work');
                        }
                    }
                } else {
                    $this->job->failJob('Neither uid nor entire specified.');
                    return false;
                }

                $this->job->succeedJob('Child jobs created.');

                // return the ids of the child jobs that have been created
                return $this->workJobIds;

            // this is the child job, do update
            case 'work':
                return $this->updateRecords($this->job, $data);

            default:
                break;
        }

        return true;
    }

    /**
     *  Update records.
     *
     * @param $job SchedulersJob object associated with this job
     * @param $data array of job data
     */
    protected function updateRecords($job, $data)
    {
        if (!isset($data['uid'])) {
            $job->failJob('No uid found.');
            return false;
        }

        // mass update
        $newData = $data;
        if (isset($newData['entire'])) {
            unset($newData['entire']);
        }

        self::preProcess($newData);

        $bean = BeanFactory::newBean($data['module']);
        if (!$bean instanceof SugarBean) {
            $job->failJob('Invalid bean');
            return false;
        }
        $mass = new MassUpdate();
        $mass->setSugarBean($bean);
        $mass->handleMassUpdate(false, true);

        $job->succeedJob('All records processed for this chunk.');

        return true;
    }

}
