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
 *Portions created by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights
 *Reserved.
 ********************************************************************************/

require_once('include/SugarSearchEngine/SugarSearchEngineFactory.php');
require_once('include/SugarQueue/SugarJobQueue.php');
require_once('modules/SchedulersJobs/SchedulersJob.php');

/**
 *
 */
class SugarSearchEngineFullIndexer implements RunnableSchedulerJob
{

    /**
     * @var SchedulersJob
     */
    private $schedulerJob;

    /**
     * @var \SugarSearchEngineAbstractBase
     */
    private $SSEngine;

    /**
     * @var array
     */
    private $results;

    /**
     * The max number of beans we process before starting to bulk insert so we dont hit memory issues.
     */
    const MAX_BULK_THRESHOLD = 5000;

    /**
     * The max number of beans we delete at a time
     */
    const MAX_BULK_DELETE_THRESHOLD = 100;

    /**
     * Name of the scheduler to perform a full index
     * @var string
     */
    public static $schedulerName = "Full Text Search Indexer";

    /**
     * The name of the queue table
     */
    const QUEUE_TABLE = 'fts_queue';

    /**
     * @param SugarSearchEngineAqbstractBase $engine
     */
    public function __construct(SugarSearchEngineAbstractBase $engine = null)
    {
        if($engine != null)
            $this->SSEngine = $engine;
        else
            $this->SSEngine = SugarSearchEngineFactory::getInstance();

        $this->results = array();
    }

    /**
     * Remove all records that may be currently queued for FTS ingestion
     *
     */
    protected function clearFTSIndexQueue()
    {
        $GLOBALS['log']->debug("Clearing FTS Index Queue");
        $truncateQuery = $GLOBALS['db']->truncateTableSQL('fts_queue');
        $GLOBALS['db']->query($truncateQuery);
    }

    /**
     * Remove existing FTS Consumers that may have been created by a previous scheduled index.
     *
     */
    protected function removeExistingFTSConsumers()
    {
        $GLOBALS['log']->info("Removing existing FTS Consumers");

        $jobBean = BeanFactory::getBean('SchedulersJobs');

        $res = $GLOBALS['db']->query("SELECT id FROM {$jobBean->table_name} WHERE name like 'FTSConsumer%' AND deleted = 0");
        while($row = $GLOBALS['db']->fetchByAssoc($res))
        {
            $jobBean->mark_deleted($row["id"]);
        }
    }

    /**
     * Initiate the FTS indexer.  Once initiated, all work will be done by the FTS consumers which will be invoked
     * by the job queue system.
     *
     * @param array $modules
     * @return SugarSearchEngineFullIndexer
     */
    public function initiateFTSIndexer($modules = array())
    {
        $GLOBALS['log']->fatal("Populating Full System Index Queue");
        if(! $this->SSEngine instanceof SugarSearchEngineAbstractBase)
        {
            $GLOBALS['log']->fatal("No FTS engine enabled, not doing anything");
            return $this;
        }

        //Clear the existing queue
        $this->clearFTSIndexQueue();

        //Remove any consumers that may be set to run
        $this->removeExistingFTSConsumers();

        $startTime = microtime(true);
        $allModules = !empty($modules) ? $modules : array_keys(SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsForAllModules());

        $totalCount = 0;
        foreach($allModules as $module)
        {
            $totalCount += $this->populateIndexQueueForModule($module);
        }

        $totalTime = number_format(round(microtime(true) - $startTime, 2), 2);
        $this->results['totalTime'] = $totalTime;
        $GLOBALS['log']->info("Total time to populate full system index queue: $totalTime (s)");
        $avgRecs = ($totalCount != 0) ? number_format(round(($totalCount / $totalTime), 2), 2) : 0;
        $GLOBALS['log']->info("Total number of records queued: $totalCount , records per sec. $avgRecs");

        return $this;

    }

    /**
     * Populate the index queue with all records from a particular module
     *
     * @param $module
     */
    public function populateIndexQueueForModule($module)
    {
        $GLOBALS['log']->fatal("Going to populate index queue for module {$module} ");
        $db = DBManagerFactory::getInstance('fts');
        $obj = BeanFactory::getBean($module, null);
        $beanName = BeanFactory::getBeanName($module);
        $tableName = self::QUEUE_TABLE;
        $query = "INSERT INTO {$tableName} (bean_id,bean_module) SELECT id, '{$beanName}' FROM {$obj->table_name}";
        $db->query($query, true, "Error populating index queue for fts");
        //For each module we populate the fts queue with, create a consumer to digest the beans as well.
        $this->createJobQueueConsumerForModule($module);
    }

    /**
     * Create a job queue FTS consumer for a specific module
     *
     * @param $module
     * @return String Id of newly created job
     */
    public function createJobQueueConsumerForModule($module)
    {
        $GLOBALS['log']->fatal("Creating FTS Job queue consumer for: {$module} ");
        $job = new SchedulersJob();
        $job->data = $module;
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->name = "FTSConsumer {$module}";
        $job->target = "class::SugarSearchEngineFullIndexer";
        $queue = new SugarJobQueue();
        $queue->submitJob($job);

        return $job->id;
    }

    public function __get($name)
    {
        if($name == 'table_name')
            return self::QUEUE_TABLE;
        else
            return $this->$name;
    }

    /**
     * Index the entire system. This should only be called from a worker process as this is a time intensive process and
     * does not take advantage of the job queue system.  Currently this call is only used when populating demo data and should be used
     * sparingly.
     */
    public function performFullSystemIndex()
    {
        //Do nothing if no FTS has been setup.
        if(! $this->SSEngine instanceof SugarSearchEngineAbstractBase)
        {
            $GLOBALS['log']->fatal("No FTS engine enabled, not doing anything");
            return $this;
        }
        $this->initiateFTSIndexer();
        require_once 'include/SugarQueue/SugarCronJobs.php';
        $jobq = new SugarCronJobs();
        $jobq->runCycle();
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

    /**
     * Main function that handles the indexing of a bean and is called by the job queue system.
     *
     * @param $data
     */
    public function run($module)
    {
        $GLOBALS['log']->info("Going to index all records in module {$module} ");
        $queuTableName = self::QUEUE_TABLE;
        $beanName = BeanFactory::getBeanName($module);
        $db = DBManagerFactory::getInstance('fts');
        $fieldDefinitions = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsPerModule($module);
        $count = 0;

        $selectAllQuery = "SELECT bean_id FROM {$queuTableName} WHERE bean_module='{$beanName}' AND processed = 0";
        $result = $db->limitQuery($selectAllQuery,0, self::MAX_BULK_THRESHOLD, true, "Unable to retrieve records from FTS queue");
        $processedBeans = array();
        $docs = array();
        while ($row = $db->fetchByAssoc($result, FALSE) )
        {
            $beanID = $row['bean_id'];
            $bean = BeanFactory::getBean($module, $beanID);
            if($bean !== FALSE)
            {
                $GLOBALS['log']->info("About to index bean: $beanID");
                $docs[] = $this->SSEngine->createIndexDocument($bean, $fieldDefinitions);
                $processedBeans[] = $beanID;
                $count++;
            }

            if($count != 0 && $count % self::MAX_BULK_THRESHOLD == 0)
            {
                $this->SSEngine->bulkInsert($docs);
                $this->markBeansProcessed($processedBeans);
                $docs = $processedBeans = array();
                sugar_cache_reset();
                gc_collect_cycles();
                $lastMemoryUsage = isset($lastMemoryUsage) ? $lastMemoryUsage : 0;
                $currentMemUsage = memory_get_usage();
                $totalMemUsage = $currentMemUsage - $lastMemoryUsage;
                $GLOBALS['log']->info("Flushing records, count: $count mem. usage:" .  memory_get_usage() . " , mem. delta: " . $totalMemUsage);
                $lastMemoryUsage = $currentMemUsage;
            }
        }

        if(count($docs) > 0)
        {
            $this->SSEngine->bulkInsert($docs);
            $this->markBeansProcessed($processedBeans);
        }

        //Keep track of how many we've done
        $this->schedulerJob->message += $count;

        //If no items were processed we've exhausted the list and can therefore succeed job.
        if( count($count) == 0)
        {
            $this->schedulerJob->succeedJob();
        }
        else
        {
            //Mark the job that as pending so we can be invoked later.
            $this->schedulerJob->resolveJob(SchedulersJob::JOB_PENDING);
        }

        $GLOBALS['log']->info("FTS Consumer {$this->schedulerJob->name} processed {$count} record(s)");

        return TRUE;

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
        foreach($beanIDs as $beanID)
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
        $tableName = self::QUEUE_TABLE;
        $inClause = implode("','", $deleteIDs);
        $query = "UPDATE $tableName SET processed = 1 WHERE bean_id in ('{$inClause}')";
        $GLOBALS['log']->info("MARK BEAN QUERY IS: $query");
        $GLOBALS['db']->query($query);
    }

    /**

     * Return statistics about how many records per module were indexed.
     *
     * @return array
     */
    public function getStatistics()
    {
        $results = array();
        $jobBean = BeanFactory::getBean('SchedulersJobs');

        $res = $GLOBALS['db']->query("SELECT id FROM {$jobBean->table_name} WHERE name like 'FTSConsumer%' AND deleted = 0");
        while($row = $GLOBALS['db']->fetchByAssoc($res))
        {
            $jobBean->retrieve($row["id"]);
            $results[$jobBean->data] = $jobBean->message;
        }
        return $results;
    }

    /**
     * TODO: Need to update
     * Determine if a pre-existing scheduler for fts exists.  If so return the id, else false.
     *
     * @static
     * @return mixed
     */
    public static function isFTSIndexScheduled()
    {
        $sched = new Scheduler();
        $sched = $sched->retrieve_by_string_fields(array('name'=> self::$schedulerName));

        if($sched == NULL)
            return FALSE;
        else
            return $sched->id;

    }

    /**
     * Determine if a system has been indexed
     *
     * @static
     * @return bool
     */
    public static function isFTSIndexScheduleCompleted()
    {
        $completed = FALSE;
        $jobBean = BeanFactory::getBean('SchedulersJobs');

        $res = $GLOBALS['db']->query("SELECT id FROM {$jobBean->table_name} WHERE name like 'FTSConsumer%' AND deleted = 0");
        while($row = $GLOBALS['db']->fetchByAssoc($res))
        {
            $completed = TRUE;//At least one job must have been executed
            $jobBean->retrieve($row["id"]);
            if($jobBean->status != SchedulersJob::JOB_STATUS_DONE)
               return FALSE;
        }

        return $completed;
    }
}