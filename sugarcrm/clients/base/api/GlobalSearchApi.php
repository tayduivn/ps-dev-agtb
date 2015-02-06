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

use Sugarcrm\Sugarcrm\Elasticsearch\Container;
use Sugarcrm\Sugarcrm\SearchEngine\SearchEngine;
use Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\ResultSetInterface;
use Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\ResultInterface;
use Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\GlobalSearchInterface;

/**
 *
 * GlobalSearch API using Elasticsearch
 *
 * Available URL parameters:
 *  - q = search term
 *  - limit = defaults to 20, how many results to return
 *  - offset = defaults to 0, used for paging
 *  - modules = comma separated list of modules (*)
 *
 *  (*) Instead of using the modules URL parameter, its possible and encouraged
 *  to use list the modules directly in the URL instead using a comma separated
 *  list like `/Accounts,Contacts/globalsearch?q=stuff`
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
    protected $modules = array();

    /**
     * @var string Search term
     */
    protected $term = '';

    /**
     * @var \Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearchInterface
     */
    private $engine;

    /**
     * Register endpoints
     * @return array
     */
    public function registerApiRest()
    {
        return array(

            // /globalsearch
            'globalSearch' => array(
                'reqType' => 'GET',
                'path' => array('globalsearch'),
                'pathVars' => array(''),
                'method' => 'globalSearchEntry',
                'shortHelp' => '',
                'longHelp' => '',
                'noLoginRequired' => false,
            ),

            // /<modules>/globalsearch
            'modulesGlobalSearch' => array(
                'reqType' => 'GET',
                'path' => array('?', 'globalsearch'),
                'pathVars' => array('modules', ''),
                'method' => 'globalSearchEntry',
                'shortHelp' => '',
                'longHelp' => '',
                'noLoginRequired' => false,
            ),
        );
    }

    /**
     * GlobalSearch entrypoint
     * @param \RestService $api
     * @param array $args
     * @return array
     */
    public function globalSearchEntry(\RestService $api, array $args)
    {
        $api->action = 'list';

        // Set properties from arguments
        $this->parseArguments($args);

        // Get search results
        $resultSet = $this->executeGlobalSearch($this->getSearchEngine());

        return array(
            'next_offset' => $this->getNextOffset($resultSet->getTotalHits(), $this->limit, $this->offset),
            'total' => $resultSet->getTotalHits(),
            'query_time' => $resultSet->getQueryTime(),
            'records' => $this->formatResults($api, $resultSet),
        );
    }

    /**
     *
     * @param array $args
     */
    protected function parseArguments(array $args)
    {
        // Modules can be a comma separated list
        if (!empty($args['modules'])) {
            $this->modules = explode(',', $args['modules']);
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
    }

    /**
     * Execute search
     * @param GlobalSearchInterface $engine
     * @return \Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\ResultSetInterface
     */
    protected function executeGlobalSearch(GlobalSearchInterface $engine)
    {
        $engine
            ->from($this->modules)
            ->term($this->term)
            ->limit($this->limit)
            ->offset($this->offset)
            ->fieldBoost($this->fieldBoost)
            ->highlighter($this->highlights)
        ;

        return $engine->search();
    }

    /**
     * Get global search provider
     * @throws SugarApiExceptionSearchUncapable
     * @throws SugarApiExceptionSearchUnavailable
     * @return \Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\GlobalSearchInterface
     */
    protected function getSearchEngine()
    {
        if ($this->engine === null) {

            // Instantiate search engine with GlobalSearch capability
            try {
                $this->engine = SearchEngine::getInstance('GlobalSearch');
            } catch (\Exception $e) {
                throw new SugarApiExceptionSearchRuntime(null, array($e->getMessage()));
            }

            // Check capability
            if (!$this->engine) {
                throw new SugarApiExceptionSearchUncapable(null, array('GlobalSearch'));
            }
        }

        // Alth0ugh this check run implicitly, we can already bail out here if unavailabe
        if (!$this->engine->isAvailable()) {
            throw new SugarApiExceptionSearchUnavailable();
        }

        return $this->engine->getEngine();
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
            $args = array('fields' => $result->getDataFields());
            $data = $this->formatBean($api, $args, $result->getBean());

            // add search specific meta info
            $entry = array(
                'data' => $data,
                'score' => $result->getScore(),
            );

            // Add highlights if requested
            if ($this->highlights) {
                $entry['highlights'] = $result->getHighlights();
            }

            $formatted[] = $entry;
        }

        return $formatted;
    }
}
