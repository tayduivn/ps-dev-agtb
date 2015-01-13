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

use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\QueryBuilder;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\AbstractProvider;

/**
 *
 * Elasticsearch GlobalSearch Provider
 *
 */
class GlobalSearch extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    protected $sugarTypes = array(
        'varchar' => array('gs_string'),
        'name' => 'gs_string',
        'phone' => 'gs_string',
        'int' => 'gs_string',
        'text' => 'gs_string',
    );

    /**
     * {@inheritdoc}
     */
    protected $mappingDefs = array(
        'gs_string' => array(
            'type' => 'string',
            'index' => 'analyzed',
            'index_analyzer' => 'gs_default_index_analyzer',
            'search_analyzer' => 'gs_default_search_analyzer',
            'store' => false,
        ),
    );

    /**
     * @var boolean Module aggregation
     */
    protected $moduleAgg = false;

    /**
     * Enable/disable module aggregation facet
     * @param boolean $toggle
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\GlobalSearch
     */
    public function moduleAgg($toggle)
    {
        $this->moduleAgg = (bool) $toggle;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function buildProviderMapping(Mapping $mapping)
    {
        $module = $mapping->getModule();
        $indexFields = $this->getBeanIndexFields($module);
        $this->buildMappingFromSugarType($mapping, $indexFields);
    }

    /**
     * {@inheritdoc}
     */
    public function buildProviderAnalysis(AnalysisBuilder $analysisBuilder)
    {
        $analysisBuilder
            ->addFilter(
                'gs_filter_ngram',
                'nGram',
                array('min_gram' => 2, 'max_gram' => 15)
            )
            ->addCustomAnalyzer(
                'gs_default_search_analyzer',
                'whitespace',
                array('lowercase')
            )
            ->addCustomAnalyzer(
                'gs_default_index_analyzer',
                'whitespace',
                array('lowercase', 'gs_filter_ngram')
            )
        ;
    }

    /**
     * {inheritdoc}
     */
    public function getBeanIndexFields($module)
    {
        $indexFields = array();
        $ftsFields = $this->getFtsFields($module);
        foreach ($ftsFields as $field => $defs) {

            // ensure a type has been defined
            if (empty($defs['type'])) {
                $this->container->logger->warning("GS: No sugar type defined for {$module}.{$field}");
                continue;
            }

            // skip unsupported fields
            if (!$this->isSupportedSugarType($defs['type'])) {
                $this->container->logger
                    ->warning("GS: Skipping unsupported type '{$defs['type']}' on {$module}.{$field}");
                continue;
            }

            $indexFields[$field] = $defs['type'];
        }
        return $indexFields;
    }

    /**
     * Query field constructor
     * @param boolean $boost Apply field boost values
     * @return array
     */
    protected function getSearchFields($boost = false)
    {
        // if no boost are required, just use a field wildcard
        if (!$boost) {
            return array('*.gs_string');
        }

        foreach ($this->modules as $module) {
            foreach ($this->getFtsFields($module) as $name => $params) {

                // TODO: this part is hacky and needs to be redone based on the
                // hashes of available fields per provider somehow.
                if (!$type = $this->getMappingForSugarType($params['type'])) {
                    continue;
                } else {
                    $type = array_pop(array_keys($type));
                }

                $field = "{$module}.{$name}.{$type}";
                if ($boost && !empty($params['full_text_search']['boost'])) {
                    $field .= "^" . (float) $params['full_text_search']['boost'];
                }
                $fields[] = $field;
            }
        }
        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function search()
    {
        $builder = new QueryBuilder();
        $builder
            ->setUser($this->user)
            ->setModules($this->modules)
            ->setLimit($this->limit)
            ->setOffset($this->offset)
        ;

        // Set query only when search term(s) are available
        if (!empty($this->term)) {
            $builder->setQuery($this->getQuery());
        }

        // Set highlighter
        if ($this->highlighter) {
            $builder->setHighLighter($this->getHighlighter());
        }

        // Apply module aggregation
        if ($this->moduleAgg) {
            $builder->addAggregator($this->getModuleAggregator($this->modules));
        }

        return $builder->executeSearch();
    }

    /**
     * Get query object
     * @return \Elastica\Query\MultiMatch
     */
    protected function getQuery()
    {
        $query = new \Elastica\Query\MultiMatch();
        $query->setType(\Elastica\Query\MultiMatch::TYPE_CROSS_FIELDS);
        $query->setQuery($this->term);
        $query->setFields($this->getSearchFields($this->fieldBoost));
        return $query;
    }

    /**
     * Get highlighter object
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\PlainHighLighter
     */
    protected function getHighlighter()
    {
        return new PlainHighlighter($this->getHighlighterFields());
    }

    /**
     * Get highlighter fields
     * @return array
     */
    protected function getHighlighterFields()
    {
        // Just select all eligible global search fields here
        return array(
            '*.gs_string' => array(),
        );
    }

    /**
     * Get module aggregator
     * @param array $modules
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\ModuleAggregation
     */
    protected function getModuleAggregator(array $modules)
    {
        $agg = new ModuleAggregation();
        $agg->setSize(count($modules));
        return $agg;
    }

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
    protected $highlighter = false;

    /**
     * Set search term
     * @param string $term Search term
     * @return AbstractSearchProvider
     */
    public function term($term)
    {
        $this->term = $term;
        return $this;
    }

    /**
     * Set modules to search for
     * @param array $modules
     * @return AbstractSearchProvider
     */
    public function from(array $modules = array())
    {
        if (empty($modules)) {
            return $this->fromAll();
        }

        foreach ($modules as $module) {
            if ($this->container->metaDataHelper->isModuleAvailableForUser($module, $this->user)) {
                $this->modules[] = $module;
            }
        }
        return $this;
    }

    /**
     * Query all available modules
     * @return AbstractSearchProvider
     */
    public function fromAll()
    {
        $this->modules = $this->getUserModules();
        return $this;
    }

    /**
     * Set limit (query size)
     * @param integer $limit
     * @return AbstractSearchProvider
     */
    public function limit($limit)
    {
        $this->limit = (int) $limit;
        return $this;
    }

    /**
     * Set offset
     * @param integer $offset
     * @return AbstractSearchProvider
     */
    public function offset($offset)
    {
        $this->offset = (int) $offset;
        return $this;
    }

    /**
     * Add filter
     * @return AbstractSearchProvider
     */
    public function filter()
    {
        // TODO
        return $this;
    }

    /**
     * Enable field boosts (disabled by default)
     * @param boolean $toggle
     * @return AbstractSearchProvider
     */
    public function fieldBoost($toggle)
    {
        $this->fieldBoost = (bool) $toggle;
        return $this;
    }

    /**
     * Enable/disable highlighter (disabled by default)
     * @param boolean $toggle
     * @return AbstractSearchProvider
     */
    public function highlighter($toggle)
    {
        $this->highlighter = (bool) $toggle;
        return $this;
    }
}
