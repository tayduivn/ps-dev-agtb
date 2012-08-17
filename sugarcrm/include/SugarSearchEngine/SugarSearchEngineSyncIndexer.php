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

require_once('include/SugarSearchEngine/SugarSearchEngineIndexerBase.php');

/**
 * Indexer job for Search to sync records
 * @api
 */
class SugarSearchEngineSyncIndexer extends SugarSearchEngineIndexerBase
{
    /**
     * Remove existing FTS Consumers that may have been created by a previous scheduled index.
     *
     */
    public function removeExistingFTSSyncConsumer()
    {
        $GLOBALS['log']->fatal("Removing existing FTS Sync Consumers");

        $jobBean = BeanFactory::getBean('SchedulersJobs');

        $res = $GLOBALS['db']->query("SELECT id FROM {$jobBean->table_name} WHERE name like 'FTSSyncConsumer' AND deleted = 0");
        while ($row = $GLOBALS['db']->fetchByAssoc($res))
        {
            $jobBean->mark_deleted($row['id']);
        }
    }

    /**
     * Create a job queue FTS consumer for a specific module
     *
     * @return String Id of newly created job
     */
    public function createJobQueueConsumer()
    {
        $GLOBALS['log']->fatal("Creating FTS Job queue consumer to sync");

        global $timedate;
        if (empty($timedate))
        {
            $timedate = TimeDate::getInstance();
        }

        $job = new SchedulersJob();
        $job->requeue = 1;
        $job->execute_time = $timedate->nowDb();
        $job->name = "FTSSyncConsumer";
        $job->target = "class::SugarSearchEngineSyncIndexer";
        $queue = new SugarJobQueue();
        $queue->submitJob($job);

        return $job->id;
    }

    public function indexRecords($module, $fieldDefinitions)
    {
        $GLOBALS['log']->fatal('Indexing for module '.$module);

        $count = 0;
        $processedBeans = array();
        $docs = array();

        $GLOBALS['log']->info("SugarSyncIndexer will use db to index records");
        $sql = $this->generateFTSQuery($module, $fieldDefinitions);
        $result = $this->db->limitQuery($sql,0, self::MAX_BULK_QUERY_THRESHOLD, true, "Unable to retrieve records from FTS queue");

        while ($row = $this->db->fetchByAssoc($result, FALSE) )
        {
            $beanID = $row['id'];
            $row['module_dir'] = $module;
            $bean = (object) $row;

            if($bean !== FALSE)
            {
                $GLOBALS['log']->debug("About to index bean: $beanID $module");
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
                if( function_exists('gc_collect_cycles') )
                    gc_collect_cycles();

                $lastMemoryUsage = isset($lastMemoryUsage) ? $lastMemoryUsage : 0;
                $currentMemUsage = memory_get_usage();
                $totalMemUsage = $currentMemUsage - $lastMemoryUsage;
                $GLOBALS['log']->fatal("Flushing records, count: $count mem. usage:" .  memory_get_usage() . " , mem. delta: " . $totalMemUsage);
                $lastMemoryUsage = $currentMemUsage;
            }
        }

        if(count($docs) > 0)
        {
            $this->SSEngine->bulkInsert($docs);
        }

        $this->markBeansProcessed($processedBeans);

        return $count;
    }


    /**
     * Check FTS server status and update cache file and notification.
     *
     * @return boolean
     */
    protected function updateFTSServerStatus()
    {
        $GLOBALS['log']->debug('Going to check and update FTS Server status.');
        // check FTS server status
        $result = $this->SSEngine->getServerStatus();
        if ($result['valid'])
        {
            $GLOBALS['log']->debug('FTS Server is OK.');
            // server is ok
            if (isSearchEngineDown())
            {
                $GLOBALS['log']->debug('Restoring FTS Server status.');

                // remove cache/fts/fts_down
                restoreSearchEngine();

                // remove notification
                $cfg = new Configurator();
                $cfg->config['fts_disable_notification'] = false;
                $cfg->handleOverride();
            }

            return true;
        }
        else
        {
            $GLOBALS['log']->fatal('FTS Server is down?');
            // server is down
            if (!isSearchEngineDown())
            {
                $GLOBALS['log']->fatal('Marking FTS Server as down.');
                // fts is not marked as down, so mark it as down
                searchEngineDown();
                $this->createJobQueueConsumer();
            }

            return false;
        }
    }

    /**
     * Main function that handles the indexing of a bean and is called by the job queue system.
     *
     * @param $data
     */
    public function run($data)
    {
        $serverOK = $this->updateFTSServerStatus();
        if (!$serverOK)
        {
            // server is down, postpone the job
            $GLOBALS['log']->fatal('FTS Server is down, postponing the job.');
            $this->schedulerJob->postponeJob('', self::POSTPONE_JOB_TIME);
            return true;
        }

        $GLOBALS['log']->fatal("Going to sync records in fts queue...");

        // Create the index on the server side
        $this->SSEngine->createIndex(false);

        // index records for each enabled module
        $allFieldDef = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsForAllModules();
        foreach ($allFieldDef as $module=>$fieldDefinitions) {
            $count = $this->indexRecords($module, $fieldDefinitions);
        }

        // check the fts queue to see if any records left, if no, then we are done, succeed the job
        // otherwise postpone the job so it can be invoked by the next cron
        $tableName = self::QUEUE_TABLE;
        $res = $this->db->query("SELECT count(*) as cnt FROM {$tableName} WHERE processed = 0");
        if ($row = $GLOBALS['db']->fetchByAssoc($res))
        {
            $count = $row['cnt'];
            if( $count == 0)
            {
                // If no items were processed we've exhausted the list and can therefore succeed job.
                $GLOBALS['log']->fatal('succeed job');
                $this->schedulerJob->succeedJob();

                //Remove any consumers that may be set to run
                //$this->removeExistingFTSSyncConsumer();
                $GLOBALS['log']->fatal("FTS Sync Indexing completed.");
            }
            else
            {
                // Mark the job that as pending so we can be invoked later.
                $GLOBALS['log']->info('FTS Sync Indexing partially done, postponing job for next cron');
                $this->schedulerJob->postponeJob('', self::POSTPONE_JOB_TIME);
            }
        }

        return true;
    }

}
