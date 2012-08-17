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


require_once('include/entryPoint.php');
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
     * The max number of beans we process before starting to bulk insert so we dont hit memory issues.
     */
    const MAX_BULK_THRESHOLD = 5000;

    /**
     * The max number of beans we process before starting to bulk insert so we dont hit memory issues.
     */
    const MAX_BULK_QUERY_THRESHOLD = 15000;

    /**
     * The max number of beans we delete at a time
     */
    const MAX_BULK_DELETE_THRESHOLD = 3000;

    /**
     * Number of time to postpone a job by so it's not executed twice during the same request.
     */
    const POSTPONE_JOB_TIME = 20;

    /**
     * The name of the queue table
     */
    const QUEUE_TABLE = 'fts_queue';

    /**
     * @var DBManager
     */
    protected $db;

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
    }

    public function __get($name)
    {
        if($name == 'table_name')
            return self::QUEUE_TABLE;
        else
            return $this->$name;
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
     * Generate the query necessary to retrieve FTS enabled fields for a bean.
     *
     * @param $module
     * @param $fieldDefinitions
     * @return string
     */
    protected function generateFTSQuery($module, $fieldDefinitions)
    {
        $queuTableName = self::QUEUE_TABLE;
        $bean = BeanFactory::getBean($module, null);
        $id = isset($fieldDefinitions['email1']) ? $bean->table_name.'.id' : 'id';
        $selectFields = array($id,'team_id','team_set_id');
        $ownerField = $bean->getOwnerField(true);
        if (!empty($ownerField))
        {
            $selectFields[] = $ownerField;
        }

        foreach($fieldDefinitions as $value)
        {
            if(isset($value['name'])) {
                if ($value['name'] == 'email1')
                    continue;
                $selectFields[] = $value['name'];
            }
        }

        $ret_array['select'] = " SELECT " . implode(",", $selectFields);
        $ret_array['from'] = " FROM {$bean->table_name} ";
        $custom_join = FALSE;
        if(isset($bean->custom_fields))
        {
            $custom_join = $bean->custom_fields->getJOIN();
            if($custom_join)
                $ret_array['select'] .= ' ' .$custom_join['select'];
        }

        if($custom_join)
            $ret_array['from'] .= ' ' . $custom_join['join'];

        $ret_array['from'] .= " INNER JOIN {$queuTableName} on {$queuTableName}.bean_id = {$bean->table_name}.id AND {$queuTableName}.processed = 0 ";
        $ret_array['where'] = "WHERE {$bean->table_name}.deleted = 0";

        if(isset($fieldDefinitions['email1'])) {
            $ret_array['select'].= ", email_addresses.email_address email1";
            $ret_array['from'].= " LEFT JOIN email_addr_bean_rel on {$bean->table_name}.id = email_addr_bean_rel.bean_id and email_addr_bean_rel.bean_module='{$module}' and email_addr_bean_rel.deleted=0 and email_addr_bean_rel.primary_address=1 LEFT JOIN email_addresses on email_addresses.id=email_addr_bean_rel.email_address_id ";
        }

        return  $ret_array['select'] . $ret_array['from'] . $ret_array['where'];
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
        $GLOBALS['log']->debug("MARK BEAN QUERY IS: $query");
        $GLOBALS['db']->query($query);
    }

}
