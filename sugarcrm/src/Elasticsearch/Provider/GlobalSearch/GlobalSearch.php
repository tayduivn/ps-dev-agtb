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
        'varchar' => array(
            'gs_string_default',
            'gs_string_ngram'
        ),
        'name' => array(
            'gs_string_default',
            'gs_string_ngram'
        ),
        'text' => array(
            'gs_string_default',
            'gs_string_ngram'
        ),
        'datetime' => array(
            'gs_datetime',
        ),
        'int' => array(
            'gs_string_default',
            'gs_string_ngram',
            //'gs_int_default',
        ),
    );

    /**
     * {@inheritdoc}
     */
    protected $mappingDefs = array(
        'gs_string_ngram' => array(
            'type' => 'string',
            'index' => 'analyzed',
            'index_analyzer' => 'gs_analyzer_ngram',
            'search_analyzer' => 'gs_analyzer_default',
            'store' => false,
        ),
        'gs_string_default' => array(
            'type' => 'string',
            'index' => 'analyzed',
            'index_analyzer' => 'gs_analyzer_default',
            'search_analyzer' => 'gs_analyzer_default',
            'store' => false,
        ),
        'gs_datetime' => array(
            'type' => 'date',
            'format' => 'YYYY-MM-dd HH:mm:ss',
            'index' => 'no',
            'store' => false,
        ),
        'gs_int_default' => array(
            'type' => 'integer',
            'index' => 'no',
            'store' => false,
        ),
    );

    /**
     * List of mapping defs which will be weighted during boost time
     * @var array
     */
    protected $weightedBoost = array(
        'gs_string_default' => 1,
        'gs_string_ngram' => 0.35,
    );

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
                'gs_analyzer_default',
                'whitespace',
                array('lowercase')
            )
            ->addCustomAnalyzer(
                'gs_analyzer_standard',
                'standard',
                array('standard', 'lowercase')
            )
            ->addCustomAnalyzer(
                'gs_analyzer_ngram',
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
        foreach ($this->getFtsFields($module) as $field => $defs) {

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
     * {@inheritdoc}
     */
    public function processBeanPreIndex(\SugarBean $bean)
    {
        $this->setAutoIncrementValues($bean);
    }

    /**
     * Update a bean's auto-increment fields' values from database,
     * since they are not available before saving to database.
     * @param \SugarBean $bean
     */
    public function setAutoIncrementValues(\SugarBean $bean)
    {
        //retrieve the auto-incremented fields' names for a module
        $incFields = $this->getFtsAutoIncrementFields($bean->module_name);

        if (!empty($incFields)) {
            foreach ($incFields as $fieldName) {
                //If the field is empty, retrieve its value from database
                if (!isset($bean->$fieldName)) {
                    $fieldValue = $this->retrieveFieldByQuery($bean, $fieldName);
                    if (isset($fieldValue)) {
                        $bean->$fieldName = $fieldValue;
                    }
                }
            }
        }
    }

    /**
     * Retrieve the value of a given field.
     * @param \SugarBean $bean
     * @param $fieldName : the name of the field
     * @return $string
     */
    public function retrieveFieldByQuery(\SugarBean $bean, $fieldName)
    {
        $sq = new \SugarQuery();
        $sq->select(array($fieldName));
        $sq->from($bean);
        $sq->where()->equals("id", $bean->id);
        $result = $sq->execute();

        // expect only one record
        if (!empty($result)) {
            return $result[0][$fieldName];
        } else {
            return null;
        }
    }


    /**
     * {@inheritdoc}
     */
    public function search()
    {
        $builder = new QueryBuilder($this->container);
        $builder
            ->setUser($this->user)
            ->setModules($this->modules)
            ->setLimit($this->limit)
            ->setOffset($this->offset)
        ;

        // Use MultiMatch if we are actually searching or fallback to MatchAll
        if (!empty($this->term)) {
            $builder->setQuery($this->getQuery());
        } else {
            $builder->setQuery($this->getMatchAllQuery());
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
        $query->setTieBreaker(1.0); // TODO make configurable
        return $query;
    }

    /**
     * Get search field wrapper
     * @return array
     */
    protected function getSearchFields()
    {
        $sf = new SearchFields($this, $this->newBoostHandler());
        $sf->setBoost($this->fieldBoost);
        return $sf->getSearchFields($this->modules);
    }

    /**
     * Instantiate boost handler
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\BoostHandler
     */
    protected function newBoostHandler()
    {
        $boost = new BoostHandler();
        $boost->setWeighted($this->weightedBoost);
        return $boost;
    }

    /**
     * Get match all query
     * @return \Elastica\Query\MatchAll
     */
    protected function getMatchAllQuery()
    {
        return new \Elastica\Query\MatchAll();
    }

    /**
     * Get highlighter object
     * @return PlainHighLighter
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
            '*.gs_string_ngram' => array(),
        );
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
     * @return GlobalSearch
     */
    public function fromAll()
    {
        $this->modules = $this->getUserModules();
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
     * Add filter
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
    public function highlighter($toggle)
    {
        $this->highlighter = (bool) $toggle;
        return $this;
    }
}
