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
require_once('include/SugarSearchEngine/SugarSearchEngineAbstractBase.php');
require_once('include/SugarSearchEngine/Elastic/SugarSearchEngineElasticResultSet.php');
require_once('include/SugarSearchEngine/SugarSearchEngineMetadataHelper.php');
require_once('include/SugarSearchEngine/SugarSearchEngineHighlighter.php');

/**
 * Engine implementation for ElasticSearch
 */
class SugarSearchEngineElastic extends SugarSearchEngineAbstractBase
{
    private $_config = array();
    private $_client = null;
    private $_indexName = "";

    const DEFAULT_INDEX_TYPE = 'SugarBean';
    const WILDCARD_CHAR = '*';

    private $_indexType = 'SugarBean';

    public function __construct($params = array())
    {
        $this->_config = $params;
        $this->_indexName = strtolower($GLOBALS['sugar_config']['unique_key']);

        //Elastica client uses own auto-load schema similar to ZF.
        spl_autoload_register(array($this, 'loader'));
        if (empty($this->_config['timeout']))
        {
            $this->_config['timeout'] = 15;
        }
        $this->_client = new Elastica_Client($this->_config);
    }


    public function indexBean($bean, $batch = TRUE)
    {
        if(!$this->isModuleFtsEnabled($bean->module_dir) )
            return;

        if(!$batch)
            $this->indexSingleBean($bean);
        else
        {
            $GLOBALS['log']->info("Adding bean to doc list with id: {$bean->id}");

            //Group our beans by index type for bulk insertion
            $indexType = $this->getIndexType($bean);
            if(! isset($this->_documents[$indexType]) )
                $this->_documents = array();

            //Create and store our document index which will be bulk inserted later, do not store beans as they are heavy.
            $this->_documents[] = $this->createIndexDocument($bean);
        }
    }

    /**
     *
     * Return the 'type' for the index.  By using the bean type we can specify mappings on a per bean basis if we need
     * to in the future.
     *
     * @param $bean
     * @return string
     */
    protected function getIndexType($bean)
    {
        if(!empty($bean->module_dir))
            return $bean->module_dir;
        else
            return self::DEFAULT_INDEX_TYPE;
    }

    protected function getOwnerField($bean)
    {
        // when running full indexing, $bean may be a stdClass and not a SugarBean
        if ($bean instanceof SugarBean)
        {
            return $bean->getOwnerField();
        }
        else if (isset($bean->assigned_user_id))
        {
            return $bean->assigned_user_id;
        }
        else if (isset($bean->created_by))
        {
            return $bean->created_by;
        }
        return null;
    }

    /**
     *
     * @param SugarBean $bean
     * @param $searchFields
     * @return Elastica_Document|null
     */
    public function createIndexDocument($bean, $searchFields = null)
    {
        if($searchFields == null)
            $searchFields = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsPerModule($bean);

        $keyValues = array();
        foreach($searchFields as $fieldName => $fieldDef)
        {
            //All fields have already been formatted to db values at this point so no further processing necessary
            if( !empty($bean->$fieldName) )
            {
                // 1. elasticsearch does not handle multiple types in a query very well
                // so let's use only strings so it won't be indexed as other types
                // 2. for some reason, bean fields are encoded, decode them first
                $keyValues[$fieldName] = strval(html_entity_decode($bean->$fieldName,ENT_QUOTES));
            }
        }

        //Always add our module
        $keyValues['module'] = $bean->module_dir;
        $keyValues['team_set_id'] = str_replace("-", "",$bean->team_set_id);

        // to index owner
        $ownerField = $this->getOwnerField($bean);
        if ($ownerField)
        {
            $keyValues['doc_owner'] = strval($ownerField);
        }

        if( empty($keyValues) )
            return null;
        else
            return new Elastica_Document($bean->id, $keyValues, $this->getIndexType($bean));
    }

    protected function indexSingleBean($bean)
    {
        if (isSearchEngineDown())
        {
            $this->addRecordsToQueue(array('bean_id'=>$bean->id, 'bean_module'=>get_class($bean)));
            return;
        }
        $GLOBALS['log']->info("Preforming single bean index");
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
            if ($this->checkException($e))
            {
                $recordsToBeQueued = $this->getRecordsFromDocs(array($doc));
                $this->addRecordsToQueue($recordsToBeQueued);
            }
        }

    }

    /**
     * (non-PHPdoc)
     * @see SugarSearchEngineInterface::delete()
     */
    public function delete(SugarBean $bean)
    {
        if (isSearchEngineDown())
        {
            return;
        }
        if(empty($bean->id))
            return;

        try
        {
            $GLOBALS['log']->info("Going to delete {$bean->id}");
            $index = new Elastica_Index($this->_client, $this->_indexName);
            $type = new Elastica_Type($index, $this->getIndexType($bean));
            $type->deleteById($bean->id);
        }
        catch(Exception $e)
        {
            $GLOBALS['log']->fatal("Unable to delete index: {$e->getMessage()}");
            $this->checkException($e);
        }
    }

    /**
     * (non-PHPdoc)
     * @see SugarSearchEngineInterface::bulkInsert()
     */
    public function bulkInsert(array $docs)
    {
        if (isSearchEngineDown())
        {
            $recordsToBeQueued = $this->getRecordsFromDocs($docs);
            $this->addRecordsToQueue($recordsToBeQueued);
            return;
        }

        try
        {
            $index = new Elastica_Index($this->_client, $this->_indexName);
            $batchedDocs = array();
            $x = 0;
            foreach($docs as $singleDoc)
            {
                if($x != 0 && $x % self::MAX_BULK_THRESHOLD == 0)
                {
                    $index->addDocuments($batchedDocs);
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
                $index->addDocuments($batchedDocs);
            }
        }
        catch(Exception $e)
        {
            $GLOBALS['log']->fatal("Error performing bulk update operation: {$e->getMessage()}");
            if ($this->checkException($e))
            {
                $recordsToBeQueued = $this->getRecordsFromDocs($batchedDocs);
                $this->addRecordsToQueue($recordsToBeQueued);
            }
        }

    }

    protected function getRecordsFromDocs($docs)
    {
        $records = array();
        $i = 0;
        foreach ($docs as $doc)
        {
            $records[$i]['bean_id'] = $doc->getId();
            $records[$i]['bean_module'] = BeanFactory::getBeanName($doc->getType());
            $i++;
        }
        return $records;
    }

    /**
     * Check the server status
     */
    public function getServerStatus()
    {
        $isValid = FALSE;
        $displayText = "";
        $timeOutValue = $this->_client->getConfig('timeout');
        try
        {
            $this->_client->setConfigValue('timeout', 2);
            $results = $this->_client->getStatus()->getServerStatus();
            if(!empty($results['ok']) )
            {
                $isValid = TRUE;
                $displayText = $GLOBALS['app_strings']['LBL_EMAIL_SUCCESS'];
            }
            else
                $displayText = $results;
        }
        catch(Exception $e)
        {
            $GLOBALS['log']->fatal("Unable to get server status with error: {$e->getMessage()}");
            $displayText = $e->getMessage();
        }
        //Reset previous timeout value.
        $this->_client->setConfigValue('timeout', $timeOutValue);
        return array('valid' => $isValid, 'status' => $displayText);
    }

    protected function getSearchFields($options)
    {
        $fields = array();
        if(!empty($options['moduleFilter'])) {
            foreach ($options['moduleFilter'] as $mod) {
                $fieldDef = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsPerModule($mod);
                foreach ($fieldDef as $fieldName => $def) {
                    if (!in_array($fieldName, $fields)) {
                        $fields[] = $fieldName;
                    }
                }
            }
        } else {
            $allFieldDef = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsForAllModules();
            foreach ($allFieldDef as $fieldDef) {
                foreach ($fieldDef as $fieldName => $def) {
                    if (!in_array($fieldName, $fields)) {
                        $fields[] = $fieldName;
                    }
                }
            }
        }

        return $fields;
    }

    protected function constructHighlightArray($fields, $options)
    {
        if (isset($options['preTags']))
        {
            $preTags = $options['preTags'];
        }
        else
        {
            $preTags = SugarSearchEngineHighlighter::$preTag;
        }

        if (isset($options['postTags']))
        {
            $postTags = $options['postTags'];
        }
        else
        {
            $postTags = SugarSearchEngineHighlighter::$postTag;
        }

        $fieldArray = array();
        $highlightProperties = new stdClass();
        if (isset($options['fragmentSize']))
        {
            $highlightProperties->fragment_size = $options['fragmentSize'] + strlen($preTags) + strlen($postTags);
        }
        else
        {
            $highlightProperties->fragment_size = SugarSearchEngineHighlighter::$fragmentSize + strlen($preTags) + strlen($postTags);
        }

        if (isset($options['fragmentNumber']))
        {
            $highlightProperties->number_of_fragments = $options['fragmentNumber'];
        }
        else
        {
            $highlightProperties->number_of_fragments = SugarSearchEngineHighlighter::$fragmentNumber;
        }

        foreach ($fields as $field)
        {
            $fieldArray[$field] = $highlightProperties;
        }

        $highlighArray = array('fields'=>$fieldArray,
            'order'=>'score',
            'pre_tags'=>array($preTags),
            'post_tags'=>array($postTags));

        return $highlighArray;
    }

    protected function canAppendWildcard($queryString)
    {
        $queryString = trim(html_entity_decode($queryString, ENT_QUOTES));
        if( substr($queryString, -1) ===  self::WILDCARD_CHAR) {
            return false;
        }

        // for fuzzy search, do not append wildcard
        if( strpos($queryString, '~') !==  false) {
            return false;
        }

        // for range searches, do not append wildcard
        if (preg_match('/\[.*TO.*\]/', $queryString) || preg_match('/{.*TO.*}/', $queryString))
        {
            return false;
        }

        // for group searches, do not append wildcard
        if (preg_match('/\(.*\)/', $queryString))
        {
            return false;
        }

        // when using double quotes, do not append wildcard
        if( strpos($queryString, '"') !==  false) {
            return false;
        }

        return true;
    }

    /*
     * A sample team filter looks like this:
       {"or": [
         {"term":{"team_set_id":"1"}},
         {"term":{"team_set_id":"46ca01386366bc910d074fb2f8200f03"}},
         {"term":{"team_set_id":"East"}},
         {"term":{"team_set_id":"West"}}]
       }
    */
    protected function constructTeamFilter()
    {
        $teamFilter = new Elastica_Filter_Or();

        $teamIDS = TeamSet::getTeamSetIdsForUser($GLOBALS['current_user']->id);

        //TODO: Determine why term filters aren't working with the hyphen present.
        //Term filters dont' work for terms with '-' present so we need to clean
        $teamIDS = array_map(array($this,'cleanTeamSetID'), $teamIDS);

        foreach ($teamIDS as $teamID)
        {
            $termFilter = new Elastica_Filter_Term();
            $termFilter->setTerm('team_set_id',$teamID);
            $teamFilter->addFilter($termFilter);
        }

        return $teamFilter;
    }

    protected function getTypeTermFilter($module)
    {
        $typeTermFilter = new Elastica_Filter_Term();
        $typeTermFilter->setTerm('_type', $module);

        return $typeTermFilter;
    }

    protected function getOwnerTermFilter()
    {
        $ownerTermFilter = new Elastica_Filter_Term();
        $ownerTermFilter->setTerm('doc_owner', $GLOBALS['current_user']->id);

        return $ownerTermFilter;
    }

    protected function constructModuleLevelFilter($module)
    {
        $requireOwner = ACLController::requireOwner($module, 'list');

        $class = $GLOBALS['beanList'][$module];
        $seed = new $class();
        $hasAdminAccess = $GLOBALS['current_user']->isAdminForModule($seed->getACLCategory());

        if ($hasAdminAccess)
        {
            // user has admin access for this module, skip team filter
            if ($requireOwner)
            {
                // need to be document owner to view
                $moduleFilter = new Elastica_Filter_And();

                // type term filter
                $typeTermFilter = $this->getTypeTermFilter($module);
                $moduleFilter->addFilter($typeTermFilter);

                // owner term filter
                $ownerTermFilter = $this->getOwnerTermFilter();
                $moduleFilter->addFilter($ownerTermFilter);
            }
            else
            {
                // do not need to be document owner to view
                // a single type term filter is all we need
                $moduleFilter = $this->getTypeTermFilter($module);
            }
        }
        else
        {
            // user does not have admin access, need team filter
            $moduleFilter = new Elastica_Filter_And();

            // team filter
            $teamFilter = $this->constructTeamFilter();
            $moduleFilter->addFilter($teamFilter);

            // type term filter
            $typeTermFilter = $this->getTypeTermFilter($module);
            $moduleFilter->addFilter($typeTermFilter);

            if ($requireOwner)
            {
                // need to be document owner to view, owner term filter
                $ownerTermFilter = $this->getOwnerTermFilter();
                $moduleFilter->addFilter($ownerTermFilter);
            }
        }
        return $moduleFilter;
    }

    protected function constructMainFilter($finalTypes)
    {
        $mainFilter = new Elastica_Filter_Or();
        foreach ($finalTypes as $module)
        {
            $moduleFilter = $this->constructModuleLevelFilter($module);
            $mainFilter->addFilter($moduleFilter);
        }

        return $mainFilter;
    }

    /**
     * @param $queryString
     * @param int $offset
     * @param int $limit
     * @return null|SugarSeachEngineElasticResultSet
     */
    public function search($queryString, $offset = 0, $limit = 20, $options = array())
    {
        if (isSearchEngineDown())
        {
            return null;
        }

        $appendWildcard = false;
        if( !empty($options['append_wildcard']) && $this->canAppendWildcard($queryString) )
        {
            $appendWildcard = true;
        }
        $queryString = sql_like_string($queryString, self::WILDCARD_CHAR, self::WILDCARD_CHAR, $appendWildcard);

        $GLOBALS['log']->info("Going to search with query $queryString");
        $results = null;
        try
        {
            // trying to match everything, make a MatchAll query
            if($queryString == '*')
            {
                $queryObj = new Elastica_Query_MatchAll();
            }
            else
            {
                $qString = html_entity_decode($queryString, ENT_QUOTES);
                $queryObj = new Elastica_Query_QueryString($qString);
                $queryObj->setAnalyzeWildcard(true);
                $queryObj->setAutoGeneratePhraseQueries(false);
                if( !empty($options['append_wildcard']) )
                    // see https://github.com/elasticsearch/elasticsearch/issues/1186 for details
                    $queryObj->setRewrite('top_terms_5');

                // set query string fields
                $fields = $this->getSearchFields($options);
                $queryObj->setFields($fields);
            }
            $s = new Elastica_Search($this->_client);
            //Only search across our index.
            $index = new Elastica_Index($this->_client, $this->_indexName);
            $s->addIndex($index);

            $finalTypes = array();
            if(!empty($options['moduleFilter']))
            {
                if( is_admin($GLOBALS['current_user']) ) {
                    $finalTypes = $options['moduleFilter'];
                }
                else
                {
                    foreach ($options['moduleFilter'] as $moduleName)
                    {
                        $class = $GLOBALS['beanList'][$moduleName];
                        $seed = new $class();
                        // only add the module to the list if it can be viewed
                        if ($seed->ACLAccess('ListView'))
                        {
                            $finalTypes[] = $moduleName;
                        }
                    }
                }
                if (!empty($finalTypes))
                {
                    $s->addTypes($finalTypes);
                }
            }

            if( !is_admin($GLOBALS['current_user']) )
            {
                // main filter
                $mainFilter = $this->constructMainFilter($finalTypes);

                $query = new Elastica_Query($queryObj);
                $query->setFilter($mainFilter);
            }
            else
            {
                $query = new Elastica_Query($queryObj);
            }
            $query->setParam('from',$offset);

            // set query highlight
            $fields = $this->getSearchFields($options);
            $highlighArray = $this->constructHighlightArray($fields, $options);
            $query->setHighlight($highlighArray);

            //Add a type facet so we can see how our results are grouped.
            if( !empty($options['apply_module_facet']) )
            {
                $typeFacet = new Elastica_Facet_Terms('_type');
                $typeFacet->setField('_type');
                // need to add filter for facet too
                if (isset($mainFilter))
                {
                    $typeFacet->setFilter($mainFilter);
                }
                $query->addFacet($typeFacet);
            }
            $esResultSet = $s->search($query, $limit);
            $results = new SugarSeachEngineElasticResultSet($esResultSet);

        }
        catch(Exception $e)
        {
            $GLOBALS['log']->fatal("Unable to perform search with error: {$e->getMessage()}");
            $this->checkException($e);
            return null;
        }

        return $results;
    }

    /**
     * Remove the '-' from our team sets.
     *
     * @param $teamSetID
     * @return mixed
     */
    protected function cleanTeamSetID($teamSetID)
    {
        return str_replace("-", "", $teamSetID);
    }

    protected function loader($className)
    {
        // FIXME: convert to use autoloader
        $fileName = str_replace('_', '/', $className);
        $path = 'include/SugarSearchEngine/Elastic/' . $fileName . '.php';
        if( file_exists($path) )
            require_once($path);
        else
            return FALSE;
    }

    /**
     * Create the index and mapping.
     *
     * @param boolean $recreate OPTIONAL Deletes index first if already exists (default = false)
     *
     */
    public function createIndex($recreate = false)
    {
        if (isSearchEngineDown())
        {
            return;
        }

        try
        {
            // create an elastic index
            $index = new Elastica_Index($this->_client, $this->_indexName);
            $index->create(array(), $recreate);

             // create field mappings
            require_once('include/SugarSearchEngine/Elastic/SugarSearchEngineElasticMapping.php');
            $elasticMapping = new SugarSearchEngineElasticMapping($this);
            $elasticMapping->setFullMapping();
        }
        catch(Exception $e)
        {
            $GLOBALS['log']->error("Unable to create index with error: {$e->getMessage()}");
            $this->checkException($e);
        }

    }

    /**
     * Get Elastica client
     * @return Elastica_Client
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * Get the name of the index
     * @return string
     */
    public function getIndexName()
    {
        return $this->_indexName;
    }
}
