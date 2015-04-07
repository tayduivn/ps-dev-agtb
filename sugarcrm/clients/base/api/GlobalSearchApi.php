<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use Sugarcrm\Sugarcrm\SearchEngine\SearchEngine;
use Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\GlobalSearchInterface;
use Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\ResultSetInterface;
use Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\ResultInterface;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Result;
use Sugarcrm\Sugarcrm\SearchEngine\Capability\Aggregation\AggregationInterface;

/**
 *
 * GlobalSearch API
 *
 * (Note: the usage of /search will be deprecated in favor of /globalsearch)
 *
 *  The /globalsearch entry point is able to accept arguments  in the request
 *  body using JSON format. In case of duplicate settings, the URL parameters
 *  take precedence over the settings in the request body. Its encouraged to
 *  pass the arguments directly in the request body to prevent too long URLs.
 *
 *  Its prefered to use the GET method to consume the /globalsearch entry
 *  point. However for REST clients which do not support GET requests with
 *  request bodies, the POST method is also supported.
 *
 */
class GlobalSearchApi extends SugarApi
{
    /**
     * @var integer Offset
     */
    protected $offset = 0;

    /**
     * @var integer Limit
     */
    protected $limit = 20;

    /**
     * @var boolean Collect highlights
     */
    protected $highlights = true;

    /**
     * @var boolean Apply field boosts
     */
    protected $fieldBoost = true;

    /**
     * @var array List of modules to query
     */
    protected $moduleList = array();

    /**
     * @var array List of aggregation filters to query
     */
    protected $aggFilters = array();

    /**
     * @var string Search term
     */
    protected $term = '';

    /**
     * @var array Sort fields
     */
    protected $sort = array();

    /**
     * Get cross module aggregation results
     * @var boolean
     */
    protected $crossModuleAgg = false;

    /**
     * List of modules for which to collect aggregations results
     * @var array
     */
    protected $moduleAggs = array();

    /**
     * Register endpoints
     * @return array
     */
    public function registerApiRest()
    {
        return array(

            // /globalsearch
            'globalSearch' => array(
                'reqType' => array('GET', 'POST'),
                'path' => array('globalsearch'),
                'pathVars' => array(''),
                'method' => 'globalSearch',
                'shortHelp' => 'Global search',
                'longHelp' => 'include/api/help/globalsearch_get_help.html',
                'exceptions' => array(
                    'SugarApiExceptionNotAuthorized',
                    'SugarApiExceptionSearchUnavailable',
                    'SugarApiExceptionSearchRuntime',
                ),
            ),

            // /<module>/globalsearch
            'modulesGlobalSearch' => array(
                'reqType' => array('GET', 'POST'),
                'path' => array('<module>', 'globalsearch'),
                'pathVars' => array('module', ''),
                'method' => 'globalSearch',
                'shortHelp' => 'Global search',
                'longHelp' => 'include/api/help/globalsearch_get_help.html',
                'exceptions' => array(
                    'SugarApiExceptionNotAuthorized',
                    'SugarApiExceptionSearchUnavailable',
                    'SugarApiExceptionSearchRuntime',
                ),
            ),
        );
    }

    /**
     * GlobalSearch endpoint
     * @param \RestService $api
     * @param array $args
     * @return array
     */
    public function globalSearch(\RestService $api, array $args)
    {
        $api->action = 'list';

        // Set properties from arguments
        $this->parseArguments($args);

        // Load global search engine
        $globalSearch = $this->getSearchEngine()->getEngine();

        // Get search results
        try {
            $resultSet = $this->executeGlobalSearch($globalSearch);
        } catch (\Exception $e) {
            throw new SugarApiExceptionSearchRuntime(null, array($e->getMessage()));
        }

        return array(
            'next_offset' => $this->getNextOffset($resultSet->getTotalHits(), $this->limit, $this->offset),
            'total' => $resultSet->getTotalHits(),
            'query_time' => $resultSet->getQueryTime(),
            'records' => $this->formatResults($api, $resultSet),
            'aggregations' => $resultSet->getAggregations(),
        );
    }

    /**
     * Parse arguments
     * @param array $args
     */
    protected function parseArguments(array $args)
    {
        // Modules can be a comma separated list
        if (!empty($args['module_list']) && !is_array($args['module_list'])) {
            $this->moduleList = explode(',', $args['module_list']);
        }

        // If specific module is selected, this overrules the list
        if (!empty($args['module'])) {
            $this->moduleList = array($args['module']);
        }

        // Set aggregation filters
        if (!empty($args['agg_filters'])) {
            $this->aggFilters = $this->parseAggFilters($args['agg_filters']);
        }

        // Set search term
        if (!empty($args['q'])) {
            $this->term = $args['q'];
        }

        // Set limit
        if (isset($args['max_num'])) {
            $this->limit = (int) $args['max_num'];
        }

        // Set offset
        if (isset($args['offset'])) {
            $this->offset = (int) $args['offset'];
        }

        // Enable/disable highlights
        if (isset($args['highlights'])) {
            $this->highlights = (bool) $args['highlights'];
        }

        // Set sorting
        if (isset($args['sort']) && is_array($args['sort'])) {
            $this->sort = $args['sort'];
        }

        // Set cross module aggregations
        if (!empty($args['xmod_agg'])) {
            $this->crossModuleAgg = true;
        }

        // Set module aggregations
        if (isset($args['mod_aggs'])) {
            $this->moduleAggs = explode(',', $args['mod_aggs']);
        }

    }

    /**
     * Parse the list of aggregation filters from the arguments
     * @param string $aggFilterArgs
     * @return array
     */
    protected function parseAggFilters($aggFilterArgs)
    {
        $filters = array();

        //Expected format of the input argument (TBD):
        //agg1,bucket_1a,bucket_1c;agg2,bucket_2b,bucket_2c,bucket_2d
        //Example: assigned_user_id,seed_will_id,seed_sally_id;_type,Leads,Contacts

        //Expected returned format
        //array("agg1" => array("bucket_1a", "bucket_1c"), "agg2" => array("bucket_2b", "bucket_2c", "bucket_2d"), ...)

        //exzTODO: parse the arguments based on the formats
        $aggFilterStrs= explode(";", $aggFilterArgs);
        foreach ($aggFilterStrs as $aggFilterStr) {
            $values = explode(",", $aggFilterStr);
            if (count($values)>0) {
                $filters[$values[0]] = array_slice($values, 1);
            }
        }
        return $filters;
    }

    /**
     * Execute search
     * @param GlobalSearchInterface $engine
     * @return \Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\ResultSetInterface
     */
    protected function executeGlobalSearch(GlobalSearchInterface $engine)
    {
        $engine
            ->from($this->moduleList)
            ->setAggFilters($this->aggFilters)
            ->term($this->term)
            ->limit($this->limit)
            ->offset($this->offset)
            ->fieldBoost($this->fieldBoost)
            ->highlighter($this->highlights)
            ->sort($this->sort)
        ;

        // pass aggregation query settings
        if ($engine instanceof AggregationInterface) {
            $engine->crossModuleAgg($this->crossModuleAgg);
            $engine->moduleAggs($this->moduleAggs);
        }

        return $engine->search();
    }

    /**
     * Get global search provider
     * @throws \SugarApiExceptionSearchRuntime
     * @throws \SugarApiExceptionSearchUnavailable
     * @return \Sugarcrm\Sugarcrm\SearchEngine\SearchEngine
     */
    protected function getSearchEngine()
    {
        // Instantiate search engine with GlobalSearch capability
        try {
            $engine = SearchEngine::getInstance('GlobalSearch');
        } catch (\Exception $e) {
            throw new SugarApiExceptionSearchRuntime(null, array($e->getMessage()));
        }

        // Make sure engine is available
        if (!$engine->isAvailable()) {
            throw new SugarApiExceptionSearchUnavailable();
        }

        return $engine;
    }

    /**
     * Calculate next offset
     * @param integer $total
     * @param integer $limit
     * @param integer $offset
     * @return integer
     */
    protected function getNextOffset($total, $limit, $offset)
    {
        if ($total > ($limit + $offset)) {
            $nextOffset = $limit + $offset;
        } else {
            $nextOffset = -1;
        }
        return $nextOffset;
    }

    /**
     * Format result set
     *
     * @param \RestService $api
     * @param ResultSetInterface $results
     * @return array
     */
    protected function formatResults(\RestService $api, ResultSetInterface $results)
    {
        $formatted = array();

        /* @var $result ResultInterface */
        foreach ($results as $result) {

            // get bean data based on available fields in the result
            $data = $this->formatBeanFromResult($api, $result);

            // set score
            if ($score = $result->getScore()) {
                $data['_score'] = $score;
            }

            // add highlights if available
            if ($highlights = $result->getHighlights()) {
                $data['_highlights'] = $highlights;
            }

            $formatted[] = $data;
        }

        return $formatted;
    }

    /**
     * Wrapper around formatBean based on Result
     * @param \RestService $api
     * @param Result $result
     * @return array
     */
    protected function formatBeanFromResult(\RestService $api, Result $result)
    {
        // pass in field list from available data fields on result
        $args = array('fields' => $result->getDataFields());
        $bean = $result->getBean();

        // Load email information directly from search backend if available
        // to avoid additional database retrievals.
        if (!empty($bean->emailAddress) && isset($bean->email)) {
            $bean->emailAddress->addresses = $bean->email;
            $bean->emailAddress->hasFetched = true;
        }

        return $this->formatBean($api, $args, $bean);
    }
}
