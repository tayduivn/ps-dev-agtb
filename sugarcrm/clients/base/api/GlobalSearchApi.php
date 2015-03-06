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
use Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\ResultSetInterface;
use Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\ResultInterface;
use Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\GlobalSearchInterface;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Result;

/**
 *
 * GlobalSearch API
 *
 * (Note: the usage of /search will be deprecated in favor of /globalsearch)
 *
 * Available parameters:
 *  - q = search term
 *  - limit = defaults to 20, how many results to return
 *  - offset = defaults to 0, used for paging
 *  - module_list = comma separated list of modules (*)
 *  - highlights = true/false (defauls to true)
 *  - sort = example {"date_modified":"desc", ...} default to relevance
 *
 *  (*) Instead of using the module_list parameter, its possible and encouraged
 *  to use the list of modules directly in the URL instead using a comma
 *  separated list like `/Accounts,Contacts/globalsearch?q=stuff`. If both this
 *  notation and module_list URL parameter is used at the same time, than the
 *  URL notation takes precedence over the URL module_filter parameter.
 *
 *  The /globalsearch entry point accepts additional parameters in the request
 *  body using JSON format. In case of duplicate settings, the URL parameters
 *  take precedence over the settings in the request body. Its encouraged to
 *  pass the parameters directly in the request body to prevent too long URLs.
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
     * @var string Search term
     */
    protected $term = '';

    /**
     * @var array Sort fields
     */
    protected $sort = array();

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
                'method' => 'globalSearchEntryPoint',
                'shortHelp' => '',
                'longHelp' => '',
                'noLoginRequired' => false,
            ),

            // /<module_list>/globalsearch
            'modulesGlobalSearch' => array(
                'reqType' => array('GET', 'POST'),
                'path' => array('?', 'globalsearch'),
                'pathVars' => array('module_list', ''),
                'method' => 'globalSearchEntryPoint',
                'shortHelp' => '',
                'longHelp' => '',
                'noLoginRequired' => false,
            ),
        );
    }

    /**
     * GlobalSearch entry point
     * @param \RestService $api
     * @param array $args
     * @return array
     */
    public function globalSearchEntryPoint(\RestService $api, array $args)
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

        // Set search term
        if (!empty($args['q'])) {
            $this->term = $args['q'];
        }

        // Set limit
        if (isset($args['limit'])) {
            $this->limit = (int) $args['limit'];
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
            ->term($this->term)
            ->limit($this->limit)
            ->offset($this->offset)
            ->fieldBoost($this->fieldBoost)
            ->highlighter($this->highlights)
            ->sort($this->sort)
        ;

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
        return $this->formatBean($api, $args, $result->getBean());
    }
}
