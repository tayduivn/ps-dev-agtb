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
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\ResultSet;
use Sugarcrm\Sugarcrm\SearchEngine\SearchEngine;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Result;

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
     * Register endpoints
     * @return array
     */
    public function registerApiRest()
    {
        return array(

            // Replaces /search from UnifiedSearchApi
            'globalSearch' => array(
                'reqType' => 'GET',
                'path' => array('globalsearch'),
                'pathVars' => array(''),
                'method' => 'globalSearch',
                'shortHelp' => '',
                'longHelp' => '',
                'noLoginRequired' => false,
            ),

            // New endpoint /<module>/search
            'moduleSearch' => array(
                'reqType' => 'GET',
                'path' => array('?', 'globalsearch'),
                'pathVars' => array('module', ''),
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
        $parsed = $this->parseArguments($args);

        if (!$engine = $this->getSearchEngine()) {
            // TODO - what to return ? HTTP code ...
            $GLOBALS['log']->fatal('SearchEngine does not support GlobalSearch');
            return false;
        }

        $engine
            ->from($parsed['modules'])
            ->term($parsed['term'])
            ->limit($parsed['limit'])
            ->offset($parsed['offset'])
            ->fieldBoost(true)
            ->highlighter(true)
        ;

        $results = $engine->search();

        $nextOffset = $this->getNextOffset(
            $results->getTotalHits(),
            $parsed['limit'],
            $parsed['offset']
        );

        return array(
            'next_offset' => $nextOffset,
            'records' => $this->formatResults($api, $results),
        );
    }

    /**
     * Get global search provider
     * @return \Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearchInterface
     */
    protected function getSearchEngine()
    {
        return SearchEngine::getInstance('GlobalSearch');
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
        if (empty($args['module_list'])) {
            $modules = array();
        } else {
            $modules = explode(',', $args['module_list']);
        }

        return array(
            'term' => empty($args['q']) ? false : $args['q'],
            'limit' => empty($args['max_num']) ? $this->defaultLimit : $args['max_num'],
            'offset' => empty($args['offset']) ? $this->defaultOffset : $args['offset'],
            'modules' => $modules,
        );
    }

    /**
     * Format result set
     *
     * @param \RestService $api
     * @param \Sugarcrm\Core\lib\SugarElastic\Adapter\ResultSet $results
     * @return array
     */
    protected function formatResults(\RestService $api, ResultSet $results)
    {
        $formattedResults = array();

        foreach ($results as $result) {

            // Create our seed bean
            $seed = BeanFactory::getBean($result->getType());
            $seed->id = $result->getId();

            // set values directly from Elasticsearch instead of bean retrieve
            $source = $result->getSource();
            foreach ($source as $field => $value) {
                $seed->$field = $value;
            }

            //$args = array('fields' => array_keys($source));
            // FIXME: aparently name field is required here, it shouldnt
            $args = array('fields' => 'name');
            $formatted = $this->formatBean($api, $args, $seed);

            // add search specific meta info
            $formatted['_search'] = array(
                'score' => $result->getScore(),
                'highlighted' => $this->getHighlights($result),
            );

            $formattedResults[] = $formatted;
        }

        return $formattedResults;
    }

    /**
     * Format highlight according to current format
     * TODO: do we need this ?
     * @param Result $result
     * @return array
     */
    protected function getHighlights(Result $result)
    {
        $formatted = array();
        $raw = $result->getHighlights();
        foreach ($raw as $field => $highlight) {
            if (!is_array($highlight) || !isset($highlight[0])) {
                continue;
            }
            $formatted[$field] = array(
                'text' => $highlight[0],
                'module' => $result->getType(),
                'label' => 'LBL_NAME',
            );
        }
        return $formatted;
    }
}
