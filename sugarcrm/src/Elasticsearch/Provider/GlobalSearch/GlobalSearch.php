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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch;

use Sugarcrm\Sugarcrm\Elasticsearch\Provider\AbstractProvider;
use Sugarcrm\Sugarcrm\Elasticsearch\Container;
use Sugarcrm\Sugarcrm\Elasticsearch\ContainerAwareInterface;
use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\QueryBuilder;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Document;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\HandlerCollection;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\HandlerIterator;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\MultiFieldHandler;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\AutoIncrementHandler;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\EmailAddressHandler;

/**
 *
 * GlobalSearch Provider
 *
 */
class GlobalSearch extends AbstractProvider implements ContainerAwareInterface
{
    // Awaiting PHP 5.4+ support
    //use ContainerAwareTrait;

    ///// Start trait

    /**
     * @var \Sugarcrm\Sugarcrm\Elasticsearch\Container
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer()
    {
        return $this->container;
    }

    //// End trait

    /**
     * @var HandlerCollection
     */
    protected $handlers;

    /**
     * @var Highlighter
     */
    protected $highlighter;

    /**
     * @var Booster
     */
    protected $booster;

    /**
     * List of supported sugar types
     * @var array
     */
    protected $supportedTypes = array();

    /**
     * List of types which should be skipped by getBeanIndexFields
     * when being called from QueueManager.
     * @var array
     */
    protected $skipTypesFromQueue = array();

    /**
     * Ctor
     */
    public function __construct()
    {
        $this->highlighter = new Highlighter();
        $this->booster = new Booster();
        $this->registerHandlers();
    }

    /**
     * Register handlers
     */
    protected function registerHandlers()
    {
        $this->handlers = new HandlerCollection($this);
        $this->handlers->addHandler(new MultiFieldHandler());
        $this->handlers->addHandler(new AutoIncrementHandler());
        $this->handlers->addHandler(new EmailAddressHandler());
    }

    /**
     * Get handlers filtered by interface. If no interface is given an
     * iterator of all available handlers is returned.
     *
     * @param string $interface Filter iterator by given interface
     * @return HandlerInterface[]
     */
    public function getHandlers($interface = null)
    {
        return new HandlerIterator($this->handlers->getIterator(), $interface);
    }

    /**
     * Add supported field types
     * @param array $types
     */
    public function addSupportedTypes(array $types)
    {
        $this->supportedTypes = array_merge(
            $this->supportedTypes,
            array_flip($types)
        );
    }

    /**
     * Check if given field type is supported
     * @param string $type Field type
     * @return boolean
     */
    public function isSupportedType($type)
    {
        return isset($this->supportedTypes[$type]);
    }

    /**
     * Add types to be skipped in queue query
     * @param array $fields
     */
    public function addSkipTypesFromQueue(array $fields)
    {
        $this->skipTypesFromQueue = array_merge(
            $this->skipTypesFromQueue,
            array_flip($fields)
        );
    }

    /**
     * Check if given field type needs to be skipped
     * @param string $type Field type
     * @return boolean
     */
    public function isSkippedType($type)
    {
        return isset($this->skipTypesFromQueue[$type]);
    }

    /**
     * Add highlighter field definitions
     * @param array $fields
     */
    public function addHighlighterFields(array $fields)
    {
        $this->highlighter->setFields($fields);
    }

    /**
     * Add highlighter field remaps
     * @param array $remap
     */
    public function addFieldRemap(array $remap)
    {
        $this->highlighter->setFieldRemap($remap);
    }

    /**
     * Add weighted definition for booster
     * @param array $weighted
     */
    public function addWeightedBoosts(array $weighted)
    {
        $this->booster->setWeighted($weighted);
    }

    /**
     * {@inheritdoc}
     */
    public function buildAnalysis(AnalysisBuilder $analysisBuilder)
    {
        foreach ($this->getHandlers('Analysis') as $analysis) {
            $analysis->buildAnalysis($analysisBuilder);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildMapping(Mapping $mapping)
    {
        foreach ($this->getFtsFields($mapping->getModule()) as $field => $defs) {
            foreach ($this->getHandlers('Mapping') as $handler) {
                $handler->buildMapping($mapping, $field, $defs);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processDocumentPreIndex(Document $document, \SugarBean $bean)
    {
        foreach ($this->getHandlers('ProcessDocument') as $handler) {
            $handler->processDocumentPreIndex($document, $bean);
        }
    }

    /**
     * Get search field wrapper
     * @param array $modules List of modules
     * @return array
     */
    protected function getSearchFields(array $modules)
    {
        $sf = new SearchFields($this->fieldBoost ? $this->booster : null);

        foreach ($modules as $module) {
            foreach ($this->getFtsFields($module) as $field => $defs) {

                // skip fields which are not searchable
                if (!$sf->isFieldSearchable($defs)) {
                    continue;
                }

                foreach ($this->getHandlers('SearchFields') as $handler) {
                    $handler->buildSearchFields($sf, $module, $field, $defs);
                }
            }
        }

        // TODO: add cache per user ?
        return $sf->getSearchFields();
    }

    /**
     * {inheritdoc}
     */
    public function getBeanIndexFields($module, $fromQueue = false)
    {
        $indexFields = array();

        foreach ($this->getFtsFields($module) as $field => $defs) {

            $type = $defs['type'];

            // skip unsupported fields
            if (!$this->isSupportedType($type)) {
                $this->container->logger->warning("GS: Skipping unsupported type '{$type}' on {$module}.{$field}");
                continue;
            }

            // filter fields which need to be skipped when called from queue
            if ($fromQueue && $this->isSkippedType($type)) {
                continue;
            }

            $indexFields[$field] = $type;
        }

        return $indexFields;
    }

    /**
     * @var boolean Module aggregation
     */
    protected $moduleAgg = false;

    /**
     * Enable/disable module aggregation facet
     * @param boolean $toggle
     * @return GlobalSearch
     */
    public function moduleAgg($toggle)
    {
        $this->moduleAgg = (bool) $toggle;
        return $this;
    }

    /**
     * Get module aggregator
     * @param array $modules
     * @return ModuleAggregation
     */
    protected function getModuleAggregator(array $modules)
    {
        $agg = new ModuleAggregation();
        $agg->setSize(count($modules));
        return $agg;
    }

    //// Search interface

    /**
     * @var string Search term
     */
    protected $term;

    /**
     * @var array Module list
     */
    protected $modules = array();

    /**
     * @var integer
    */
    protected $limit = 20;

    /**
     * @var integer
     */
    protected $offset = 0;

    /**
     * @var array Filter list
     */
    protected $filters = array();

    /**
     * @var boolean Apply field level boosts
    */
    protected $fieldBoost = false;

    /**
     * @var boolean Apply highlighter
     */
    protected $useHighlighter = false;

    /**
     * @var array Sort fields
     */
    protected $sort = array('_score');

    /**
     * Set search term
     * @param string $term Search term
     * @return GlobalSearch
     */
    public function term($term)
    {
        $this->term = $term;
        return $this;
    }

    /**
     * Set modules to search for
     * @param array $modules
     * @return GlobalSearch
     */
    public function from(array $modules = array())
    {
        foreach ($modules as $module) {
            if ($this->container->metaDataHelper->isModuleAvailableForUser($module, $this->user)) {
                $this->modules[] = $module;
            }
        }
        return $this;
    }

    /**
     * Set limit (query size)
     * @param integer $limit
     * @return GlobalSearch
     */
    public function limit($limit)
    {
        $this->limit = (int) $limit;
        return $this;
    }

    /**
     * Set offset
     * @param integer $offset
     * @return GlobalSearch
     */
    public function offset($offset)
    {
        $this->offset = (int) $offset;
        return $this;
    }

    /**
     * Add filter (not yet in interface)
     * @return GlobalSearch
     */
    public function filter()
    {
        // TODO
        return $this;
    }

    /**
     * Enable field boosts (disabled by default)
     * @param boolean $toggle
     * @return GlobalSearch
     */
    public function fieldBoost($toggle)
    {
        $this->fieldBoost = (bool) $toggle;
        return $this;
    }

    /**
     * Enable/disable highlighter (disabled by default)
     * @param boolean $toggle
     * @return GlobalSearch
     */
    public function useHighlighter($toggle)
    {
        $this->useHighlighter = (bool) $toggle;
        return $this;
    }

    /**
     * Set order by field
     * @param string $field
     * @return GlobalSearch
     */
    public function sort(array $fields)
    {
        if ($fields === array() || $fields === array('_score')) {
            $this->sort = array('_score');
            return $this;
        }

        // TODO - we need field mapping logic here based on type etc
        // We probably want a separate sorting class with the required logic
        $sortFields = array();
        foreach ($fields as $field => $order) {
            $sortFields[$field] = array(
                'order' => $order,
                'missing' => '_last',
                'ignore_unmapped' => true,
            );
        }
        $this->sort = $sortFields;

        // when sorting is requested other than the default we dont need boosting
        $this->fieldBoost = false;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function search()
    {
        // Make sure modules are selected
        if (empty($this->modules)) {
            $this->modules = $this->getUserModules();
        }

        $builder = new QueryBuilder($this->container);
        $builder
            ->setUser($this->user)
            ->setModules($this->modules)
            ->setLimit($this->limit)
            ->setOffset($this->offset)
        ;

        // Use MultiMatch if we are actually searching or fallback to MatchAll
        if (!empty($this->term)) {
            $builder->setQuery($this->getQuery($this->term, $this->modules));
        } else {
            $builder->setQuery($this->getMatchAllQuery());
            $this->highlighter = false;
        }

        // Set highlighter
        if ($this->useHighlighter) {
            $builder->setHighLighter($this->highlighter);
        }

        // Apply module aggregation
        if ($this->moduleAgg) {
            $builder->addAggregator($this->getModuleAggregator($this->modules));
        }

        // Set sorting
        if ($this->sort) {
            $builder->setSort($this->sort);
        }

        return $builder->executeSearch();
    }

    /**
     * Get query object
     * @param string $term Search term
     * @param array $modules List of modules
     * @return \Elastica\Query\MultiMatch
     */
    protected function getQuery($term, array $modules)
    {
        $query = new \Elastica\Query\MultiMatch();
        $query->setType(\Elastica\Query\MultiMatch::TYPE_CROSS_FIELDS);
        $query->setQuery($term);
        $query->setFields($this->getSearchFields($modules));
        $query->setTieBreaker(1.0); // TODO make configurable
        return $query;
    }

    /**
     * Get match all query
     * @return \Elastica\Query\MatchAll
     */
    protected function getMatchAllQuery()
    {
        return new \Elastica\Query\MatchAll();
    }
}
