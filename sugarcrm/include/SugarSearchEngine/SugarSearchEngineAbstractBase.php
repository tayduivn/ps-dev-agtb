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
require_once('include/SugarSearchEngine/Interface.php');
require_once('include/SugarSearchEngine/SugarSearchEngineMetadataHelper.php');

/**
 * Base class for search engine drivers
 * @api
 */
abstract class SugarSearchEngineAbstractBase implements SugarSearchEngineInterface
{
    /**
     * @var array
     */
    protected $_documents = array();

    /**
     * The max number of documents to bulk insert at a time
     */
    const MAX_BULK_THRESHOLD = 100;


    /**
     * Determine if a module is FTS enabled.
     *
     * @param $module
     * @return bool
     */
    protected function isModuleFtsEnabled($module)
    {
        return SugarSearchEngineMetadataHelper::isModuleFtsEnabled($module);

    }

    /**
     * Bulk insert any documents that have been marked for bulk insertion.
     */
    public function __destruct()
    {
        if (count($this->_documents) > 0 )
        {
            $this->bulkInsert($this->_documents);
        }

    }

    protected function checkException($e)
    {
        if ($e instanceof Elastica_Exception_Client)
        {
            $error = $e->getError();
            switch ($error) {
                case CURLE_UNSUPPORTED_PROTOCOL:
                case CURLE_FAILED_INIT:
                case CURLE_URL_MALFORMAT:
                case CURLE_COULDNT_RESOLVE_PROXY:
                case CURLE_COULDNT_RESOLVE_HOST:
                case CURLE_COULDNT_CONNECT:
                case CURLE_OPERATION_TIMEOUTED:
                    $this->disableFTS();
                    return true;
            }
        }
        return false;
    }

    protected function disableFTS()
    {
        $GLOBALS['log']->fatal('Full Text Search has been disabled because the system is not able to connect to the search engine.');
        searchEngineDown();

        // notification
        $cfg = new Configurator();
        $cfg->config['fts_disable_notification'] = true;
        $cfg->handleOverride();
    }

    protected function addRecordsToQueue($records)
    {
        $GLOBALS['log']->fatal('addRecordsToQueue');
        global $db;
        if (empty($db))
        {
            $db = DBManagerFactory::getInstance('fts');
            $db->resetQueryCount();
        }

        foreach ($records as $rec)
        {
            if (empty($rec['bean_id']) || empty($rec['bean_module']))
            {
                $GLOBALS['log']->error('Error populating fts_queue. Empty bean_id or bean_module.');
                continue;
            }
            $query = "INSERT INTO fts_queue (bean_id,bean_module) values ('{$rec['bean_id']}', '{$rec['bean_module']}')";
            $db->query($query, true, "Error populating index queue for fts");
        }

        // create a cron job consumer to digest the beans
        require_once('include/SugarSearchEngine/SugarSearchEngineSyncIndexer.php');
        $indexer = new SugarSearchEngineSyncIndexer();
        $indexer->removeExistingFTSSyncConsumer();
        $indexer->createJobQueueConsumer();
    }
}