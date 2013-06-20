<?php
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
SugarAutoLoader::requireWithCustom('include/SugarSearchEngine/Elastic/SugarSearchEngineElasticMapping.php');

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

    /**
     *
     * Force asynchronous indexing using fts_queue
     * Defaults to false unless configured
     *
     * @see $sugar_config['search_engine']['force_async_index']
     * @var boolean
     */
    protected $forceAsyncIndex;

    /**
     *
     * Ignored elastic field types which are incompatible
     * with current elastic query construction
     * @var array
     */
    protected $ignoreSearchTypes = array(
        'date',
        'integer',
    );

    /**
     *
     * Elastic Mapping object
     * @var SugarSearchEngineElasticMapping
     */
    protected $mapper;

    public function __construct($params = array())
    {
        $this->_config = $params;
        if (!empty($GLOBALS['sugar_config']['unique_key'])) {
            $this->_indexName = strtolower($GLOBALS['sugar_config']['unique_key']);
        } else {
            //Fix a notice error during install when we verify the Elastic Search settings
            $this->_indexName = '';
        }
        $this->forceAsyncIndex = SugarConfig::getInstance()->get('search_engine.force_async_index', false);

        //Elastica client uses own auto-load schema similar to ZF.
        SugarAutoLoader::addPrefixDirectory('Elastica', 'vendor/');
        if (empty($this->_config['timeout'])) {
            $this->_config['timeout'] = 15;
        }
        $this->_client = new \Elastica\Client($this->_config);

        // Elastic mapping
        $mappingClass = SugarAutoLoader::customClass('SugarSearchEngineElasticMapping');
        $this->mapper = new $mappingClass($this);

        parent::__construct();
    }

    /**
     * Check if this is an Elastic client exception, disable FTS if it is
     * @param $e Exception
     * @return boolean tru if it's an Elastic client exception, false otherwise
     */
    protected function checkException($e)
    {
        if ($e instanceof \Elastica\Exception\ClientException) {
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

    /**
     * Either index single bean or add the record to be indexed into _documents for later batch indexing,
     * depending on the $batch parameter
     *
     * @param $bean SugarBean object to be indexed
     * @param $batch boolean whether to do batch index
     */
    public function indexBean($bean, $batch = true)
    {
        if (!$this->isModuleFtsEnabled($bean->module_dir) ) {
            return;
        }

        if (!$batch) {
            if (self::isSearchEngineDown() || $this->forceAsyncIndex) {
                $this->addRecordsToQueue(array(array('bean_id'=>$bean->id, 'bean_module'=>get_class($bean))));
                return;
            }
            $this->indexSingleBean($bean);
        } else {
            $this->logger->info("Adding bean to doc list with id: {$bean->id}");

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
        if (!empty($bean->module_dir)) {
            return $bean->module_dir;
        } else {
            return self::DEFAULT_INDEX_TYPE;
        }
    }

    /**
     *
     * @param SugarBean $bean
     * @param $searchFields
     * @return mixed(\Elastica\Document|null)
     */
    public function createIndexDocument($bean, $searchFields = null)
    {
        if ($searchFields == null) {
            $searchFields = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsPerModule($bean);
        }

        if (!empty($searchFields['email1']) && empty($bean->email1)) {
            $emailAddress = BeanFactory::getBean('EmailAddresses');
            $bean->email1 = $emailAddress->getPrimaryAddress($bean);
        }

        $bean->beforeSseIndexing();

        $keyValues = array();
        foreach ($searchFields as $fieldName => $fieldDef) {
            // when creating a new bean, the auto_increment field can be null, even after save
            if (!isset($bean->$fieldName) && !empty($fieldDef['auto_increment'])) {
                $bean->$fieldName = $this->getFieldValue($fieldName, $bean);
            }

            //All fields have already been formatted to db values at this point so no further processing necessary
            if (!empty($bean->$fieldName)) {
                // 1. elasticsearch does not handle multiple types in a query very well
                // so let's use only strings so it won't be indexed as other types
                // 2. for some reason, bean fields are encoded, decode them first
                // We are handling date range search for Meetings which is type datetimecombo
                if (!isset($fieldDef['type']) || $fieldDef['type'] != 'datetimecombo') {
                    //$keyValues[$fieldName] = strval(html_entity_decode($bean->$fieldName,ENT_QUOTES));
                    // NOTE Bug 53394 resulted in the decoding scheme above. This needs to be reevaluated in the context of using the right analyzers.
                    $keyValues[$fieldName] = $bean->$fieldName;
                } elseif (isset($fieldDef['type']) && $fieldDef['type'] == 'datetimecombo') {
                    // dates have to be in ISO-8601 without the : in the TZ
                    global $timedate;

                    $date = $timedate->fromUser($bean->$fieldName);
                    if (empty($date)) {
                        $date = $timedate->fromDb($bean->$fieldName);
                    }

                    if ($date instanceof SugarDateTime) {
                        $keyValues[$fieldName] = $timedate->asIso($date, null, array('stripTZColon' => true));
                    } else {
                        $GLOBALS['log']->error("TimeDate Conversion Failed for " . get_class($bean) . "->{$fieldName}");
                    }
                } else {
                    $keyValues[$fieldName] = $bean->$fieldName;
                }
            }
        }

        //Always add our module
        $keyValues['module'] = $bean->module_dir;

        if( empty($keyValues) ) {
            return null;
        } else {
            //base document
            $document = new \Elastica\Document($bean->id, $keyValues, $this->getIndexType($bean));
            return $document;
        }
    }

    /**
     * In our current implementation we need to strip the -'s from our guids to be searchable correctly
     * @param string $field_value
     * @return string
     */
    public function formatGuidFields($field_value)
    {
        return str_replace('-', '', strval($field_value));
    }

    /**
     * This indexes one single bean to Elastic Search engine
     * @param SugarBean $bean
     */
    public function indexSingleBean($bean)
    {
        $this->logger->info("Preforming single bean index");
        try {
            $index = new \Elastica\Index($this->_client, $this->_indexName);
            $type = new \Elastica\Type($index, $this->getIndexType($bean));
            $doc = $this->createIndexDocument($bean);
            if ($doc != null) {
                $type->addDocument($doc);
            }
        } catch (Exception $e) {
            $this->reportException("Unable to index bean", $e);
            if ($this->checkException($e)) {
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
        if (self::isSearchEngineDown()) {
            return;
        }
        if (empty($bean->id)) {
            return;
        }

        try {
            $this->logger->info("Going to delete {$bean->id}");
            $index = new \Elastica\Index($this->_client, $this->_indexName);
            $type = new \Elastica\Type($index, $this->getIndexType($bean));
            $type->deleteById($bean->id);
        } catch (Exception $e) {
            $this->reportException("Unable to delete index", $e);
            $this->checkException($e);
        }
    }

    /**
     * (non-PHPdoc)
     * @see SugarSearchEngineInterface::bulkInsert()
     */
    public function bulkInsert(array $docs)
    {
        if (self::isSearchEngineDown()) {
            $recordsToBeQueued = $this->getRecordsFromDocs($docs);
            $this->addRecordsToQueue($recordsToBeQueued);
            return false;
        }

        try {
            $index = new \Elastica\Index($this->_client, $this->_indexName);
            $batchedDocs = array();
            $x = 0;
            foreach ($docs as $singleDoc) {
                if ($x != 0 && $x % $this->max_bulk_doc_threshold == 0) {
                    $index->addDocuments($batchedDocs);
                    $batchedDocs = array();
                } else {
                    $batchedDocs[] = $singleDoc;
                }

                $x++;
            }

            //Commit the stragglers
            if (count($batchedDocs) > 0) {
                $index->addDocuments($batchedDocs);
            }
        } catch (Exception $e) {
            $this->reportException("Error performing bulk update operation", $e);
            if ($this->checkException($e)) {
                $recordsToBeQueued = $this->getRecordsFromDocs($batchedDocs);
                $this->addRecordsToQueue($recordsToBeQueued);
            }
            return false;
        }

        return true;
    }

    /**
     * Given an array of documents, this constructs an array of records that can be saved to FTS queue.
     * @param SugarBean $bean
     * @return array
     */
    protected function getRecordsFromDocs($docs)
    {
        $records = array();
        $i = 0;
        foreach ($docs as $doc) {
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
        global $app_strings, $sugar_config;
        $isValid = false;
        $displayText = "";
        $timeOutValue = $this->_client->getConfig('timeout');
        try {
            //Default test timeout is 5 seconds
            $ftsTestTimeout = (isset($sugar_config['fts_test_timeout'])) ? $sugar_config['fts_test_timeout'] : 5;
            $this->_client->setConfigValue('timeout', $ftsTestTimeout);
            $results = $this->_client->request('', Elastica_Request::GET)->getData();
            if (!empty($results['ok']) ) {
                $isValid = true;
                if (!empty($GLOBALS['app_strings'])) {
                    $displayText = $app_strings['LBL_EMAIL_SUCCESS'];
                } else {
                    //Fix a notice error during install when we verify the Elastic Search settings
                    $displayText = 'Success';
                }
            } else {
                $displayText = $app_strings['ERR_ELASTIC_TEST_FAILED'];
            }
        } catch (Exception $e) {
            $this->reportException("Unable to get server status", $e);
            $displayText = $e->getMessage();
        }
        //Reset previous timeout value.
        $this->_client->setConfigValue('timeout', $timeOutValue);
        return array('valid' => $isValid, 'status' => $displayText);
    }

    /**
     * This function returns an array of fields that can be passed to search engine.
     * @param Array $options
     * @return Array array of fields
     */
    protected function getSearchFields($options)
    {
        $fields = array();

        // determine list of modules/fields
        $allFieldDefs = array();
        if (!empty($options['moduleFilter'])) {
            foreach ($options['moduleFilter'] as $module) {
                $allFieldDefs[$module] = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsPerModule($module);
            }
        } else {
            $allFieldDefs = SugarSearchEngineMetadataHelper::retrieveFtsEnabledFieldsForAllModules();
        }

        // build list of fields with optional boost values (i.e. Accouns.name^3)
        foreach ($allFieldDefs as $module => $fieldDefs) {
            foreach ($fieldDefs as $fieldName => $fieldDef) {

                // skip non-supported field types
                $ftsType = $this->mapper->getFtsTypeFromDef($fieldDef);
                if (!$ftsType || in_array($ftsType['type'], $this->ignoreSearchTypes)) {
                    $this->logger->debug("Elastic: Ignoring unsupported type in query for $module/$fieldName");
                    continue;
                }

                // base field name
                $fieldName = $module . '.' . $fieldName;

                // To enable a field for user search we require a boost value. There may be other fields
                // we index into Elastic but which should not be user searchable. We use the boost value
                // being set or not to distinguish between both scenarios. For example for extended facets
                // and related fields we can store additional fields or non analyzed data. While we need
                // those fields being indexed, we do not want the user to be able to hit those when
                // performing a search.
                if (empty($fieldDef['full_text_search']['boost'])) {
                    $this->logger->debug("Elastic: skipping $module/$fieldName for search field (no boost set)");
                    continue;
                } else {
                    if (!empty($options['addSearchBoosts'])) {
                        $fieldName .= '^' . $fieldDef['full_text_search']['boost'];
                    }
                    $fields[] = $fieldName;
                }
            }
        }

        return $fields;
    }

    /**
     * Given fields and options, this function constructs and returns a highlight array that can be passed to
     * search engine.
     * @param SugarBean $bean
     * @param $searchFields
     * @return mixed(\Elastica\Document|null)
     */
    protected function constructHighlightArray($fields, $options)
    {
        if (isset($options['preTags'])) {
            $preTags = $options['preTags'];
        } else {
            $preTags = SugarSearchEngineHighlighter::$preTag;
        }

        if (isset($options['postTags'])) {
            $postTags = $options['postTags'];
        } else {
            $postTags = SugarSearchEngineHighlighter::$postTag;
        }

        $fieldArray = array();
        $highlightProperties = new stdClass();
        if (isset($options['fragmentSize'])) {
            $highlightProperties->fragment_size = $options['fragmentSize'] + strlen($preTags) + strlen($postTags);
        } else {
            $highlightProperties->fragment_size = SugarSearchEngineHighlighter::$fragmentSize + strlen($preTags) + strlen($postTags);
        }

        if (isset($options['fragmentNumber'])) {
            $highlightProperties->number_of_fragments = $options['fragmentNumber'];
        } else {
            $highlightProperties->number_of_fragments = SugarSearchEngineHighlighter::$fragmentNumber;
        }

        foreach ($fields as $field) {
            $fieldArray[$field] = $highlightProperties;
        }

        $highlighArray = array('fields'=>$fieldArray,
            'order'=>'score',
            'pre_tags'=>array($preTags),
            'post_tags'=>array($postTags));

        return $highlighArray;
    }

    /**
     * This function determines whether we should append wildcard to search string.
     *
     * @param String $queryString
     * @return Boolean
     */
    protected function canAppendWildcard($queryString)
    {
        $queryString = trim(html_entity_decode($queryString, ENT_QUOTES));
        if (substr($queryString, -1) ===  self::WILDCARD_CHAR) {
            return false;
        }

        // for fuzzy search, do not append wildcard
        if (strpos($queryString, '~') !==  false) {
            return false;
        }

        // for range searches, do not append wildcard
        if (preg_match('/\[.*TO.*\]/', $queryString) || preg_match('/{.*TO.*}/', $queryString)) {
            return false;
        }

        // for group searches, do not append wildcard
        if (preg_match('/\(.*\)/', $queryString)) {
            return false;
        }

        // when using double quotes, do not append wildcard
        if (strpos($queryString, '"') !==  false) {
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
    /**
     * This function constructs and returns team filter for elasticsearch query.
     *
     * @return \Elastica\Filter\Terms
     */
    public function getTeamTermFilter()
    {
        global $current_user;
        if(empty($current_user)) {
            // This condition should never happen, but just to be consistent with the SQl side of the house, we are adding a filter that is false for this module
            $termFilter = new \Elastica\Filter\Terms('team_set_id', array('Non existing team_set_id to fake search term that is always false'));
        } else {
            $teamIDS = TeamSet::getTeamSetIdsForUser($current_user->id);
            //TODO: Determine why term filters aren't working with the hyphen present.
            //Term filters dont' work for terms with '-' present so we need to clean
            $teamIDS = array_map(array($this,'cleanTeamSetID'), $teamIDS);
            $termFilter = new \Elastica\Filter\Terms('team_set_id', $teamIDS);
        }

        return $termFilter;
    }

    /**
     * This function constructs and returns type term filter for elasticsearch query.
     *
     * @return \Elastica\Filter\Term
     */
    protected function getTypeTermFilter($module)
    {
        $typeTermFilter = new \Elastica\Filter\Term();
        $typeTermFilter->setTerm('_type', $module);

        return $typeTermFilter;
    }

    /**
     * This function constructs and returns owner term filter for elasticsearch query.
     *
     * @return \Elastica\Filter\Term
     */
    public function getOwnerTermFilter()
    {
        $ownerTermFilter = new \Elastica\Filter\Term();
        $ownerTermFilter->setTerm('doc_owner', $this->formatGuidFields($GLOBALS['current_user']->id));

        return $ownerTermFilter;
    }

    /**
     * This function constructs and returns module level filter for elasticsearch query.
     *
     * @return \Elastica\Filter\BoolAnd
     */
    protected function constructModuleLevelFilter($module, $options = array())
    {
        $moduleFilter = new \Elastica\Filter\Bool();
        $typeTermFilter = $this->getTypeTermFilter($module);
        $moduleFilter->addMust($typeTermFilter);

        $seed = BeanFactory::newBean($module);
        $moduleFilter = $seed->addSseVisibilityFilter($this, $moduleFilter);

        return $moduleFilter;
    }

    /**
     * This function constructs and returns main filter for elasticsearch query.
     *
     * @return \Elastica\Filter\BoolOr
     */
    protected function constructMainFilter($finalTypes, $options = array())
    {
        $mainFilter = new \Elastica\Filter\Bool();
        foreach ($finalTypes as $module) {
            $moduleFilter = $this->constructModuleLevelFilter($module, $options);
            // if we want myitems add more to the module filter
            if (isset($options['my_items']) && $options['my_items'] !== false) {
                $moduleFilter = $this->myItemsSearch($moduleFilter);
            }
            if (isset($options['filter']) && $options['filter']['type'] == 'range') {
                $moduleFilter = $this->constructRangeFilter($moduleFilter, $options['filter']);
            }
            //BEGIN SUGARCRM flav=pro ONLY

            // we only want JUST favorites if the option is 2
            // if the option is 1 that means we want all including favorites,
            // which in FTS is a normal search parameter
            if (isset($options['favorites']) && $options['favorites'] == 2) {
                $favoritesFilter = $this->constructMyFavoritesFilter();
                $moduleFilter->addFilter($favoritesFilter);
            }
            //END SUGARCRM flav=pro ONLY

            $mainFilter->addShould($moduleFilter);

        }

        return $mainFilter;
    }


    //BEGIN SUGARCRM flav=pro ONLY

    /**
     * Construct a favorites filter
     * @param object $moduleFilter
     * @return \Elastica\Filter\Term $moduleFilter
     */

    protected function constructMyFavoritesFilter()
    {
        $ownerTermFilter = new \Elastica\Filter\Term();
        // same bug as team set id, looking into a fix in elastic search to allow -'s without tokenizing

        $ownerTermFilter->setTerm('user_favorites', $this->formatGuidFields($GLOBALS['current_user']->id));

        return $ownerTermFilter;
    }
    //END SUGARCRM flav=pro ONLY

    /**
     * Construct a Range Filter to
     * @param object $moduleFilter
     * @param array $filter
     * @return object $moduleFilter
     */
    protected function constructRangeFilter($moduleFilter, $filter)
    {
        $filter = new \Elastica\Filter\Range($filter['fieldname'], $filter['range']);
        $moduleFilter->addFilter($filter);
        return $moduleFilter;
    }

    /**
     * Add a Owner Filter For MyItems to the current module
     * @param object $moduleFilter
     * @return object
     */
    public function myItemsSearch($moduleFilter)
    {
        $ownerTermFilter = $this->getOwnerTermFilter();
        $moduleFilter->addFilter($ownerTermFilter);
        return $moduleFilter;
    }

    /**
     * @param $queryString
     * @param int $offset
     * @param int $limit
     * @return null|SugarSeachEngineElasticResultSet
     */
    public function search($queryString, $offset = 0, $limit = 20, $options = array())
    {
        if (self::isSearchEngineDown()) {
            return null;
        }

        $appendWildcard = false;
        if (!empty($options['append_wildcard']) && $this->canAppendWildcard($queryString)) {
            $appendWildcard = true;
        }
        $queryString = DBManagerFactory::getInstance()->sqlLikeString(
            $queryString,
            self::WILDCARD_CHAR,
            $appendWildcard
        );

        $this->logger->info("Going to search with query $queryString");
        $results = null;
        try {
            // trying to match everything, make a MatchAll query
            if ($queryString == '*') {
                $queryObj = new \Elastica\Query\MatchAll();

            } else {
                $qString = html_entity_decode($queryString, ENT_QUOTES);
                $queryObj = new \Elastica\Query\QueryString($qString);
                $queryObj->setAnalyzeWildcard(true);
                $queryObj->setAutoGeneratePhraseQueries(false);

                // set query string fields
                $options['addSearchBoosts'] = true;
                $fields = $this->getSearchFields($options);
                $options['addSearchBoosts'] = false;
                if (!empty($options['searchFields'])) {
                    $queryObj->setFields($options['searchFields']);
                } else {
                    $queryObj->setFields($fields);
                }
            }
            $s = new \Elastica\Search($this->_client);
            //Only search across our index.
            $index = new \Elastica\Index($this->_client, $this->_indexName);
            $s->addIndex($index);

            $finalTypes = array();
            if (!empty($options['moduleFilter'])) {
                foreach ($options['moduleFilter'] as $moduleName) {
                    $seed = BeanFactory::newBean($moduleName);
                    // only add the module to the list if it can be viewed
                    if ($seed->ACLAccess('ListView')) {
                        $finalTypes[] = $moduleName;
                    }
                }
                if (!empty($finalTypes)) {
                    $s->addTypes($finalTypes);
                }
            }


            // main filter
            $mainFilter = $this->constructMainFilter($finalTypes, $options);

            $query = new \Elastica\Query($queryObj);
            $query->setFilter($mainFilter);

            if (isset($options['sort']) && is_array($options['sort'])) {
                foreach ($options['sort'] as $sort) {
                    $query->addSort($sort);
                }
            }

            $query->setParam('from', $offset);

            // set query highlight
            $fields = $this->getSearchFields($options);
            $highlighArray = $this->constructHighlightArray($fields, $options);
            $query->setHighlight($highlighArray);

            // add facets
            $this->addFacets($query, $options, $mainFilter);

            $esResultSet = $s->search($query, $limit);
            $results = new SugarSeachEngineElasticResultSet($esResultSet);
        } catch (Exception $e) {
            $this->reportException("Unable to perform search", $e);
            $this->checkException($e);
            return null;
        }
        return $results;
    }

    /**
     *
     * Add facets on elastic query object
     * @param Elastica_Query $query
     * @param array $options
     * @param Elastica_Filter_Bool $mainFilter
     */
    protected function addFacets(Elastica_Query $query, $options = array(), $mainFilter = null)
    {
        // module facet (note: would be less confusing to give another name instead of _type)
        if (!empty($options['apply_module_facet'])) {
            $typeFacet = new Elastica_Facet_Terms('_type');
            $typeFacet->setField('_type');
            // need to add filter for facet too
            if (isset($mainFilter)) {
                $typeFacet->setFilter($mainFilter);
            }
            $query->addFacet($typeFacet);
        }
    }

    /**
     * Remove the '-' from our team sets.
     *
     * @param $teamSetID
     * @return mixed
     */
    protected function cleanTeamSetID($teamSetID)
    {
        return str_replace("-", "", strtolower($teamSetID));
    }

    /**
     * Create the index and mapping.
     *
     * @param boolean $recreate OPTIONAL Deletes index first if already exists (default = false)
     *
     */
    public function createIndex($recreate = false)
    {
        if (self::isSearchEngineDown()) {
            return;
        }

        try {
            // create an elastic index
            $index = new \Elastica\Index($this->_client, $this->_indexName);
            $index->create(array(), $recreate);

             // create field mappings
            $this->mapper->setFullMapping();
        } catch (Exception $e) {
            // ignore the IndexAlreadyExistsException exception
            if (strpos($e->getMessage(), 'IndexAlreadyExistsException') === false) {
                $this->reportException("Unable to create index", $e);
                $this->checkException($e);
            }
        }

    }

    /**
     * Get Elastica client
     * @return \Elastica\Client
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

    /**
     * this defines the field types that can be enabled for full text search
     * @var array
     */
    protected static $ftsEnabledFieldTypes = array('name', 'user_name', 'varchar', 'decimal', 'float', 'int', 'phone', 'text', 'url', 'relate');

    /**
     *
     * Given a field type, determine whether this type can be enabled for full text search.
     *
     * @param string $type Sugar field type
     *
     * @return boolean whether the field type can be enabled for full text search
     */
    public function isTypeFtsEnabled($type)
    {
        return in_array($type, self::$ftsEnabledFieldTypes);
    }

}
