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

/**
 *
 * GlobalSearch API using Elasticsearch
 *
 */
class GlobalSearchApi extends SugarApi
{
    /**
     * @var integer Default offset
     */
    protected $defaultOffset = 0;

    /**
     * @var integer Default limit
     */
    protected $defaultLimit = 20;

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
                'method' => 'globalSearch',
                'shortHelp' => '',
                'longHelp' => '',
                'noLoginRequired' => false,
            ),

            // /<modules>/globalsearch
            'modulesGlobalSearch' => array(
                'reqType' => 'GET',
                'path' => array('?', 'globalsearch'),
                'pathVars' => array('modules', ''),
                'method' => 'globalSearch',
                'shortHelp' => '',
                'longHelp' => '',
                'noLoginRequired' => false,
            ),

        );
    }

    /**
     * @param \RestService $api
     * @param array $args
     * @return array
     */
    public function globalSearch(\RestService $api, array $args)
    {
        $api->action = 'list';

        $engine = $this->getSearchEngine();
        $parsed = $this->parseArguments($args);

        $engine
            ->from($parsed['modules'])
            ->term($parsed['term'])
            ->limit($parsed['limit'])
            ->offset($parsed['offset'])
            ->fieldBoost(true)
            ->highlighter(true)
        ;

        $resultSet = $engine->search();

        $nextOffset = $this->getNextOffset(
            $resultSet->getTotalHits(),
            $parsed['limit'],
            $parsed['offset']
        );

        return array(
            'next_offset' => $nextOffset,
            'total' => $resultSet->getTotalHits(),
            'query_time' => $resultSet->getQueryTime(),
            'records' => $this->formatResults($api, $resultSet),
        );
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

        return $this->engine;
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
     * Parse arguments
     * @param array $args
     * @return array
     */
    protected function parseArguments(array $args)
    {
        // Modules can be a comma separated list
        if (empty($args['modules'])) {
            $modules = array();
        } else {
            $modules = explode(',', $args['modules']);
        }

        return array(
            'term' => empty($args['q']) ? false : $args['q'],
            'limit' => (int) isset($args['max_num']) ?  $args['max_num'] : $this->defaultLimit,
            'offset' => (int) isset($args['offset']) ? $args['offset'] : $this->defaultOffset,
            'modules' => $modules,
        );
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
        $formattedResults = array();

        /* @var $result ResultInterface */
        foreach ($results as $result) {

            // get bean data based on available fields in the result
            $args = array('fields' => $result->getDataFields());
            $data = $this->formatBean($api, $args, $result->getBean());

            // add search specific meta info
            $formatted = array(
                'data' => $data,
                'score' => $result->getScore(),
                'highlights' => $result->getHighlights(),
            );

            $formattedResults[] = $formatted;
        }

        return $formattedResults;
    }
}
