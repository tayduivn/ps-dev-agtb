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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Query;

use Sugarcrm\Sugarcrm\Elasticsearch\Container;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\Highlighter\HighlighterInterface;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\Aggregation\AggregationInterface;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\ResultSet;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Client;
use Sugarcrm\Sugarcrm\Elasticsearch\Exception\QueryBuilderException;

/**
 *
 * Query Builder
 *
 */
class QueryBuilder
{
    /**
     * @var \Sugarcrm\Sugarcrm\Elasticsearch\Container
     */
    protected $container;

    /**
     * User context
     * @var \User
     */
    protected $user;

    /**
     * @var \Elastica\Query
     */
    protected $query;

    /**
     * @var array Modules being queried
     */
    protected $modules = array();

    /**
     * List of aggregators
     * @var \Elastica\Aggregation\AbstractAggregation[]
     */
    protected $aggregators = array();

    /**
     * List of query filters
     * @var \Elastica\Filter\AbstractFilter[]
     */
    protected $filters = array();

    /**
     * List of post filters
     * @var \Elastica\Filter\AbstractFilter[]
     */
    protected $postFilters = array();

    /**
     * @var HighlighterInterface
     */
    protected $highLighter;

    /**
     * @var integer
     */
    protected $limit;

    /**
     * @var integer
     */
    protected $offset;

    /**
     * @var array
     */
    protected $sort = array('_score');

    /**
     * Ctor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Set user context
     * @param \User $user
     * @return QueryBuilder
     */
    public function setUser(\User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Set query
     * @param \Elastica\Query $query
     * @return QueryBuilder
     */
    public function setQuery(\Elastica\Query\AbstractQuery $query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Set modules. Note that the consumer class is responsible to register
     * modules based on user access. No additional checks are run within this
     * query builder.
     *
     * @param array $modules
     * @return QueryBuilder
     */
    public function setModules(array $modules)
    {
        $this->modules = $modules;
        return $this;
    }

    /**
     * Set highlighter
     * @param HighlighterInterface $highLighter
     * @return QueryBuilder
     */
    public function setHighLighter(HighlighterInterface $highLighter)
    {
        $this->highLighter = $highLighter;
        return $this;
    }

    /**
     * Add aggregator
     * @param \Elastica\Aggregation\AbstractAggregation $agg
     * @return QueryBuilder
     */
    public function addAggregator(\Elastica\Aggregation\AbstractAggregation $agg)
    {
        $this->aggregators[] = $agg;
        return $this;
    }

    /**
     * Add query filter
     * @param \Elastica\Filter\AbstractFilter $filter
     * @return QueryBuilder
     */
    public function addFilter(\Elastica\Filter\AbstractFilter $filter)
    {
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * Add query filter
     * @param \Elastica\Filter\AbstractFilter $postFilter
     * @return QueryBuilder
     */
    public function addPostFilter(\Elastica\Filter\AbstractFilter $postFilter)
    {
        $this->postFilters[] = $postFilter;
        return $this;
    }

    /**
     * Set limit
     * @param integer $limit
     * @return QueryBuilder
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
        return $this;
    }

    /**
     * Set offset
     * @param integer $offset
     * @return QueryBuilder
     */
    public function setOffset($offset)
    {
        $this->offset = (int) $offset;
        return $this;
    }

    /**
     * Set sort
     * @param array $fields
     * @return QueryBuilder
     */
    public function setSort(array $fields)
    {
        $this->sort = $fields;
        return $this;
    }

    /**
     * Build query
     * @return \Elastica\Query
     */
    public function build()
    {
        // Create a filtered query object
        $filteredQuery = new \Elastica\Query\Filtered();

        // If no query is set, a fallback to MatchAll will happen
        if ($this->query) {
            $filteredQuery->setQuery($this->query);
        }

        // Add filters
        $filteredQuery->setFilter($this->buildFilters($this->filters));

        // Build main query object from filtered query
        $query = $this->buildQuery($filteredQuery);

        // Set limit
        if (isset($this->limit)) {
            $query->setSize($this->limit);
        }

        // Set offset
        if (isset($this->offset)) {
            $query->setFrom($this->offset);
        }

        // Add highlighter
        if ($this->highLighter) {
            $query->setHighlight($this->highLighter->build());
        }

        // Add aggregators
        foreach ($this->aggregators as $agg) {
            $query->addAggregation($agg);
        }

        // Set sort order
        if ($this->sort) {
            $query->setSort($this->sort);
        }

        // Set post filter
        $query->setPostFilter($this->buildPostFilters($this->postFilters));

        return $query;
    }

    /**
     * Execute query against search AP
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Adapter\ResultSet
     */
    public function executeSearch()
    {
        if (empty($this->user)) {
            throw new QueryBuilderException('QueryBuilder executeSearch failed - no user context');
        }

        if (empty($this->modules)) {
            throw new QueryBuilderException('QueryBuilder executeSearch failed - no modules avialable');
        }

        // Build query
        $query = $this->build();

        // Wrap query in search API object
        $search = $this->newSearchObject();
        $search->setQuery($query);
        $search->addIndices($this->getReadIndices($this->modules, $this->user));
        $search->addTypes($this->modules);

        return new ResultSet($search->search(), $this->highLighter);
    }

    /**
     * Create search object
     * @param Client $client Optional client
     * @return \Elastica\Search
     */
    protected function newSearchObject(Client $client = null)
    {
        $client = $client ?: $this->container->client;
        return new \Elastica\Search($client);
    }

    /**
     * Build filters
     * @return \Elastica\Filter\Bool
     */
    protected function buildFilters(array $filters)
    {
        $result = new \Elastica\Filter\Bool();
        // TODO: add visibility

        foreach ($filters as $filter) {
            $result->addMust($filter);
        }

        return $result;
    }

    /**
     * Build post filters
     * @return \Elastica\Filter\Bool
     */
    protected function buildPostFilters(array $postFilters)
    {
        $result = new \Elastica\Filter\Bool();

        foreach ($postFilters as $postFilter) {
            $result->addMust($postFilter);
        }

        return $result;
    }

    /**
     * Build main query object
     * @param \Elastica\Query\AbstractQuery $query
     * @return \Elastica\Query
     */
    protected function buildQuery(\Elastica\Query\AbstractQuery $query)
    {
        return new \Elastica\Query($query);
    }

    /**
     * Build module filter
     * @param string $module
     * @return \Elastica\Filter\Type
     */
    protected function buildModuleFilter($module)
    {
        return new \Elastica\Filter\Type($module);
    }

    /**
     * Return list of indices to read from. Currently only the user context is
     * supported but might be extended with date ranges too for rolling
     * indices depending on the index pool strategies.
     *
     * @param array $modules
     * @param \User $user
     * @return array
     */
    protected function getReadIndices(array $modules, \User $user = null)
    {
        $context = empty($user) ? array() : array('user' => $user);
        $collection = $this->container->indexPool->getReadIndices($modules, $context);
        return iterator_to_array($collection);
    }
}
