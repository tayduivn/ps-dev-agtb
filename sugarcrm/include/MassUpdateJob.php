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

/**
 * @api
 */
class MassUpdateJob
{

    /**
     * The max number of beans we process before starting to bulk insert so we dont hit memory issues.
     */
    const MAX_BULK_THRESHOLD = 1000;

    /**
     * The max number of beans we delete at a time
     */
    const MAX_BULK_DELETE_THRESHOLD = 1000;

    /**
     * The max number of beans we delete at a time
     */
    const MAX_BULK_INSERT_THRESHOLD = 1000;

    /**
     * Number of time to postpone a job by so it's not executed twice during the same request.
     */
    const POSTPONE_JOB_TIME = 20;

    /**
     * @var DBManager
     */
    protected $db;

    /**
     * The name of the queue table
     */
    const QUEUE_TABLE = 'massupdate_queue';

    /**
     * constructor
     */
    public function __construct()
    {
        $this->db = DBManagerFactory::getInstance('massupdate');
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
            $_REQUEST['uid'] = $mu_params['uid'];
        }
        if (!empty($mu_params['entire'])) {
            $_REQUEST['entire'] = $mu_params['entire'];
            $_REQUEST['select_entire_list'] = 1;
        }
    }

    /**
     * Create a job queue consumer for mass update
     *
     */
    public function createJobQueueConsumer($data)
    {
        $job = new SchedulersJob();
        $job->name = "MassUpdateConsumer";
        $job->target = "function::asyncMassUpdate";

        $data['_jobStatus_'] = 'new';
        $job->data = base64_encode(serialize($data));

        $job->assigned_user_id = $GLOBALS['current_user']->id;

        $queue = new SugarJobQueue();
        $queue->submitJob($job);

        return $job->id;
    }


    /**
     * Main function that handles the asynchronous massupdate.
     *
     * @param $job SchedulersJob object associated with this job
     * @param $data job queue data
     */
    public function run(SchedulersJob $job, $data)
    {
        /*
          - status:new
            - perform search to get all records to be updated, including id, save to massupdate_queue
            - change _jobStatus_ to populated
            - do update/delete for up to MAX_BULK_THRESHOLD records
          - status:populated
            - do update/delete for up to MAX_BULK_THRESHOLD records
         */

        $data = unserialize(base64_decode($data));
        switch ($data['_jobStatus_']) {
            case 'new':
                // find out all the records to be updated and insert them to massupdate_queue table
                self::preProcess($data);
                // if uid is already provided, insert them to massupdate_queue
                if (isset($data['uid'])) {
                    if (is_array($data['uid'])) {
                        $this->insertMassUpdateQueue($job->id, $data['uid']);
                    } else {
                        $this->insertMassUpdateQueue($job->id, explode(',', $data['uid']));
                    }
                }
                // if updating entire list, query based search criteria then insert
                else if (!empty($data['entire'])) {
                    $bean = BeanFactory::newBean($data['module']);
                    $mass = new MassUpdate();
                    $mass->setSugarBean($bean);

                    // to generate the where clause for search
                    if(empty($data['mass'])) {
                        $mass->generateSearchWhere($data['module'], base64_encode(serialize($data['current_query_by_page'])));
                    }

                    // action
                    unset($_POST['mass']);
                    $mass->handleMassUpdate(true); // this updates $_POST['mass']
                    $uidArray = $_POST['mass'];
                    $this->insertMassUpdateQueue($job->id, $uidArray);
                } else {
                    $job->failJob('Neither uid nor entire specified.' . self::QUEUE_TABLE);
                }

                // now the data is populated, change status to "populated"
                $data['_jobStatus_'] = 'populated';
                $job->data = base64_encode(serialize($data));
                $job->save();

                return $this->updateRecords($job, $data);

            case 'populated':
                return $this->updateRecords($job, $data);

            default:
                break;
        }

        return true;
    }

    /**
     * Query massupdate_queue table and update records.
     *
     * @param $job SchedulersJob object associated with this job
     * @param $data array of job data
     */
    protected function updateRecords($job, $data)
    {
        /*
        - limit query up to MAX_BULK_THRESHOLD records from massupdate_queue_bean
        - mass update/delete MAX_BULK_THRESHOLD records, mark them as processed in massupdate_queue_bean
        - if all records processed, change state to done, succeedJob
        - if something fails, change state to failed, failJob
        - if no records left, succedd the job
         */

        // fetch from massupdate_queue
        $sql = "SELECT bean_id FROM " . self::QUEUE_TABLE . " WHERE job_queue_id='{$job->id}' AND processed = 0";
        $result = $this->db->limitQuery($sql, 0, self::MAX_BULK_THRESHOLD, false, 'Unable to retrieve records from '.self::QUEUE_TABLE);
        if (!$result) {
            // query failed, fail to job
            $job->failJob('Failed to query' . self::QUEUE_TABLE);
            return true;
        }

        $count = $this->db->getRowCount($result);
        if ($count == 0) {
            // no records left
            $job->succeedJob('All records processed');
            return true;
        }

        // get bean ids
        $processedBeans = array();
        while ($row = $this->db->fetchByAssoc($result, FALSE))
        {
            $processedBeans[] = $row['bean_id'];
        }

        // mass update
        $newData = $data;
        if (isset($newData['entire'])) {
            unset($newData['entire']);
        }
        $newData['uid'] = implode(',', $processedBeans);
        self::preProcess($newData);

        $bean = BeanFactory::newBean($data['module']);
        $mass = new MassUpdate();
        $mass->setSugarBean($bean);
        $mass->handleMassUpdate(false, true);

        // mark them processed
        $this->markBeansProcessed($processedBeans);

        // update job status
        if ($count < self::MAX_BULK_THRESHOLD) {
            // all records should have been updated
            $job->succeedJob('All records processed');
        } else {
            // may still have records left, postpone the job
            $job->postponeJob('Partially done, postponing for next run', self::POSTPONE_JOB_TIME);
        }
        return true;
    }

    /**
     * Given an array of bean ids insert them into massupdate_queue table.
     *
     * @param $uid array of bean ids to insert
     */
    protected function insertMassUpdateQueue($job_queue_id, $uidArray)
    {
        $now = TimeDate::getInstance()->nowDb();
        // insert up to MAX_BULK_INSERT_THRESHOLD records at one time
        $uidChunks = array_chunk($uidArray, self::MAX_BULK_INSERT_THRESHOLD);
        foreach ($uidChunks as $chunk) {
            $values = '';
            foreach ($chunk as $uid) {
                if (empty($values)) {
                    $values = "('" . $job_queue_id . "','" . $uid . "','" . $now . "')";
                } else {
                    $values .= ",('" . $job_queue_id . "','" . $uid . "','" . $now . "')";
                }
            }

            $sql = "INSERT INTO " . self::QUEUE_TABLE . " (job_queue_id, bean_id, date_created) VALUES " . $values;
            $this->db->query($sql, true, 'Failed to insert into ' . self::QUEUE_TABLE);
        }
    }

    /**
     * Given a set of bean ids processed from the queue table, mark them as being processed.  We will
     * throttle the update query as there is a limit on the size of records that can be passed to an in clause yet
     * we don't want to update them individually for performance reasons.
     *
     * @param $beanIDs array of bean ids to delete
     */
    protected function markBeansProcessed($beanIDs)
    {
        $count = 0;
        $deleteIDs = array();
        foreach ($beanIDs as $beanID)
        {
            $deleteIDs[] = $beanID;
            $count++;
            if($count != 0 && $count % self::MAX_BULK_DELETE_THRESHOLD == 0)
            {
                $this->setBeanIDsProcessed($deleteIDs);
                $deleteIDs = array();
            }
        }

        if( count($deleteIDs) > 0)
            $this->setBeanIDsProcessed($deleteIDs);
    }

    /**
     * Internal function to mark records within queue table as processed.
     *
     * @param $deleteIDs
     */
    private function setBeanIDsProcessed($deleteIDs)
    {
        $now = TimeDate::getInstance()->nowDb();
        $tableName = self::QUEUE_TABLE;
        $inClause = implode("','", $deleteIDs);
        $query = "UPDATE $tableName SET processed = 1, date_modified = '{$now}' WHERE bean_id in ('{$inClause}')";
        $GLOBALS['db']->query($query);
    }

}
