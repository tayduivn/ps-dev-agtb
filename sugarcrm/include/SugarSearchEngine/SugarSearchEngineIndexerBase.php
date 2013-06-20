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

require_once('include/SugarSearchEngine/SugarSearchEngineFactory.php');
require_once('include/SugarQueue/SugarJobQueue.php');
require_once('modules/SchedulersJobs/SchedulersJob.php');

/**
 * Base class of full text search Indexer
 * @api
 */
abstract class SugarSearchEngineIndexerBase implements RunnableSchedulerJob
{

    /**
     * @var SchedulersJob
     */
    protected $schedulerJob;

    /**
     * @var \SugarSearchEngineAbstractBase
     */
    protected $SSEngine;


    /**
     * The name of the queue table
     */
    const QUEUE_TABLE = 'fts_queue';

    /**
     * @var The max number of beans we process before starting to bulk insert so we dont hit memory issues.
     */
    protected $max_bulk_threshold = 5000;

    /**
     * @var The max number of beans we process before starting to bulk insert so we dont hit memory issues.
     */
    protected $max_bulk_query_threshold = 15000;

    /**
     * @var The max number of beans we delete at a time
     */
    protected $max_bulk_delete_threshold = 3000;

    /**
     * @var Number of time to postpone a job by so it's not executed twice during the same request.
     */
    protected $postpone_job_time = 20;

    /**
     * @var DBManager
     */
    protected $db;

    /**
     * @var table_name
     */
    protected $table_name;

    /**
     * @param SugarSearchEngineAqbstractBase $engine
     */
    public function __construct(SugarSearchEngineAbstractBase $engine = null)
    {
        if($engine != null)
            $this->SSEngine = $engine;
        else
            $this->SSEngine = SugarSearchEngineFactory::getInstance();

        $this->db = DBManagerFactory::getInstance('fts');

        $this->table_name = self::QUEUE_TABLE;

        $this->max_bulk_threshold = SugarConfig::getInstance()->get('search_engine.max_bulk_threshold', $this->max_bulk_threshold);
        $this->max_bulk_query_threshold = SugarConfig::getInstance()->get('search_engine.max_bulk_query_threshold', $this->max_bulk_query_threshold);
        $this->max_bulk_delete_threshold = SugarConfig::getInstance()->get('search_engine.max_bulk_delete_threshold', $this->max_bulk_delete_threshold);
        $this->postpone_job_time = SugarConfig::getInstance()->get('search_engine.postpone_job_time', $this->postpone_job_time);
    }

    /**
     * Set the scheduler job that initiated the run call.
     *
     * @param SchedulersJob $job
     */
    public function setJob(SchedulersJob $job)
    {
        $this->schedulerJob = $job;
    }

    protected function generateFTSQuery($module, $fieldDefs)
    {
        $queuTableName = self::QUEUE_TABLE;
        $bean = BeanFactory::getBean($module);

        // fields filter (team fields taken from old method, needs to get abstracted out)
        $fieldsFilter = array(
            'team_id' => true,
            'team_set_id' => true,
        );

        // add fts enabled fields to the filter
        $addEmailJoin = false;
        foreach ($fieldDefs as $value) {

            // filter email1 field and toggle flag for lv query
            if ($value['name'] == 'email1') {
                $addEmailJoin = true;
            }

            $fieldsFilter[$value['name']] = true;
        }

        // generate list view query based on selected fields
        $ftsQuery = $bean->create_new_list_query(
            "",         // order_by
            "",         // where
            $fieldsFilter,
            array(),    // params
            0,            // show_deleted
            "",         // join_type
            true,       // return_array
            null,       // parent_bean
            true,       // single_select
            $addEmailJoin
        );

        // add join for queue table
        $ftsQuery['from'] .= " INNER JOIN {$queuTableName} on {$queuTableName}.bean_id = {$bean->table_name}.id AND {$queuTableName}.processed = 0 ";

        return $ftsQuery['select'] . $ftsQuery['from'] . $ftsQuery['where'];
    }

    /**
     * Main function that handles the indexing of a bean and is called by the job queue system.
     * Subclasses should implement their own logic.
     *
     * @param $data
     */
    public function run($data)
    {
        return true;
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
            if($count != 0 && $count % $this->max_bulk_delete_threshold == 0)
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
        $tableName = self::QUEUE_TABLE;
        $inClause = implode("','", $deleteIDs);
        $query = "UPDATE $tableName SET processed = 1 WHERE bean_id in ('{$inClause}')";
        $GLOBALS['log']->debug("MARK BEAN QUERY IS: $query");
        $this->db->query($query);
    }

}
