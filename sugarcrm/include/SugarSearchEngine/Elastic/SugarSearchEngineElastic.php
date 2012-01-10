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
require_once('include/SugarSearchEngine/SugarSearchEngineBase.php');
require_once('include/SugarSearchEngine/Solr/PHPSolr/Service.php');
require_once('include/SugarSearchEngine/Elastic/SugarSearchEngineElasticResultSet.php');

class SugarSearchEngineElastic extends SugarSearchEngineBase
{
    private $_server = "";
    private $_config = array();
    private $_client = null;
    private $_indexName = "";
    private $_documents = array();

    const MAX_BULK_THRESHOLD = 100;
    const DEFAULT_INDEX_TYPE = 'SugarBean';

    private $_indexType = 'SugarBean';

    public function __construct($params = array())
    {
        $this->_config = $params;

        //TODO: Support basic auth?
        $scheme = isset($this->_config['scheme']) ? $this->_config['scheme'] : 'http';
        $port = isset($this->_config['port']) ? $this->_config['port'] : '9200';
        $host = isset($this->_config['host']) ? $this->_config['host'] : 'localhost';
        $index = isset($this->_config['index']) ? $this->_config['index'] : ($GLOBALS['sugar_config']['unique_key']);
        $this->_server = "{$scheme}://{$host}:$port/$index";
        $this->_indexName = $GLOBALS['sugar_config']['unique_key'];

        //Elastica client uses own auto-load schema similar to ZF.
        spl_autoload_register(array($this, 'loader'));

        $this->_client = new Elastica_Client();
    }

    public function indexBean($bean, $batch = TRUE)
    {
        $GLOBALS['log']->fatal("GOING TO INDEX BEAN");
        if(!$this->isModuleFtsEnabled($bean->module_dir) )
            return;

        if(!$batch)
            $this->indexSingleBean($bean);
        else
        {
            $GLOBALS['log']->fatal("Adding bean to doc list....");
            //TODO: Create index document at this point so we don't need to store a large number of beans at once.

            //Group our beans by index type for bulk insertion
            $indexType = $this->getIndexType($bean);
            if(! isset($this->_documents[$indexType]) )
                $this->_documents[$indexType] = array();

            $this->_documents[$indexType][] = $this->createIndexDocument($bean);
        }
    }

    /**
     * //TODO: Move to pass class, we do use it in the bulkInsert function so it needs to be defined.
     *
     * Return the 'type' for the index.  By using the bean type we can specify mappings on a per bean basis if we need
     * to in the future.
     *
     * @param $bean
     * @return string
     */
    protected function getIndexType($bean)
    {
        if(!empty($bean->table_name))
            return $bean->table_name;
        else
            return self::DEFAULT_INDEX_TYPE;
    }

    /**
     * TODO: We should probably add this function to the interface as logically it is different than the indexSingleBean function.
     *
     * @param $bean
     * @param $searchFields
     * @return Elastica_Document|null
     */
    protected function createIndexDocument($bean, $searchFields = null)
    {
        if($searchFields == null)
            $searchFields = $this->retrieveFtsEnabledFieldsPerModule($bean);

        $keyValues = array();
        foreach($searchFields as $fieldName => $fieldDef)
        {
            //TODO: We may need to convert data at this point (date formats, etc) or go through SugarFields
            if( isset($bean->$fieldName) )
                $keyValues[$fieldName] = $bean->$fieldName;
        }

        //Always add our module
        $keyValues['module'] = $bean->module_dir;

        //TODO: Also add team ids

        if( empty($keyValues) )
            return null;
        else
            return new Elastica_Document($bean->id, $keyValues);
    }

    protected function indexSingleBean($bean)
    {
        $GLOBALS['log']->fatal("Preforming single bean index");
        try
        {
            $index = new Elastica_Index($this->_client, $this->_indexName);
            $type = new Elastica_Type($index, $this->getIndexType($bean));
            $doc = $this->createIndexDocument($bean);
            if($doc != null)
                $type->addDocument($doc);
        }
        catch(Exception $e)
        {
            $GLOBALS['log']->fatal("Unable to index bean with error: {$e->getMessage()}");
        }

    }

    public function flush()
    {

    }

    public function delete($bean)
    {

    }

    /**
     * TODO: Add to interface?
     */
    public function bulkInsert(array $docs)
    {
        try
        {
            $index = new Elastica_Index($this->_client, $this->_indexName);
            $batchedDocs = array();
            $x = 0;
            foreach($docs as $indexType => $elasticaDocs)
            {
                $type = new Elastica_Type($index, $indexType);
                foreach($elasticaDocs as $singleDoc)
                {
                    if($x != 0 && $x % self::MAX_BULK_THRESHOLD == 0)
                    {
                       $type->addDocuments($batchedDocs);
                       $batchedDocs = array();
                    }
                    else
                    {
                       $batchedDocs[] = $singleDoc;
                    }

                    $x++;
                }

                //Commit the stragglers
                if(count($batchedDocs) > 0)
                {
                    $type->addDocuments($batchedDocs);
                }
            }
        }
        //TODO: Add a mechanism to handle failures here.
        catch(Exception $e)
        {
            $GLOBALS['log']->fatal("Error performing bulk update operation: {$e->getMessage()}");
        }

    }
    /**
     * TODO: Add this logic to the base class.
     */
    public function __destruct()
    {
        $GLOBALS['log']->fatal("We are destructing and now adding a document to the index: " . count($this->_documents));
        if (count($this->_documents) > 0 )
        {
            $this->bulkInsert($this->_documents);
        }

    }

    /**
     * @param $queryString
     * @param int $offset
     * @param int $limit
     * @return null|SugarSeachEngineElasticResultSet
     */
    public function search($queryString, $offset = 0, $limit = 20)
    {
        $GLOBALS['log']->fatal("Going to search with query $queryString");
        $results = null;
        try
        {
            $queryObj = new Elastica_Query_QueryString($queryString);
            $queryObj->setAnalyzeWildcard(false);
            $queryObj->setAutoGeneratePhraseQueries(false);
            $query = new Elastica_Query($queryObj);

            $query->setParam('from',$offset);
            if( !is_admin($GLOBALS['current_user']) )
            {
                //TODO: Add team set id filter here.
                //$query->setFilter();
            }

            $s = new Elastica_Search($this->_client);
            $esResultSet = $s->search($query, $limit);
            $results = new SugarSeachEngineElasticResultSet($esResultSet);

        }
        catch(Exception $e)
        {
            $GLOBALS['log']->fatal("Unable to perform search with error: {$e->getMessage()}");
        }
        $GLOBALS['log']->fatal("finished searching with results " . var_export($results, TRUE));
        return $results;
    }

    protected function loader($className)
    {
        $fileName = str_replace('_', '/', $className);
        $path = 'include/SugarSearchEngine/Elastic/' . $fileName . '.php';
        if( file_exists($path) )
            require_once($path);
        else
            return FALSE;
    }
}