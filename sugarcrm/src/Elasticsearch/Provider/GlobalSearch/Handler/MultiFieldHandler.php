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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler;

use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Property\MultiFieldProperty;
use Sugarcrm\Sugarcrm\Elasticsearch\Exception\MappingException;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields;

/**
 *
 * Generic Mapping Handler using multi fields
 *
 */
class MultiFieldHandler extends AbstractHandler implements
    AnalysisHandlerInterface,
    MappingHandlerInterface,
    SearchFieldsHandlerInterface
{
    /**
     * Mappings for sugar types using multi field definition
     * @var array
     */
    protected $typesMultiField = array(
        'varchar' => array(
            'gs_string_default',
            'gs_string_ngram',
        ),
        'name' => array(
            'gs_string_default',
            'gs_string_ngram',
        ),
        'text' => array(
            'gs_string_default',
            'gs_string_ngram',
        ),
        'datetime' => array(
            'gs_datetime',
        ),
        'int' => array(
            'gs_string_default',
            'gs_string_ngram',
        ),
        'phone' => array(
            'gs_phone',
        ),
    );

    /**
     * Multi field definitions
     * @var array
     */
    protected $multiFieldDefs = array(

        /*
         * This is a special analyzer to be able to use fields with
         * not_analyzed values only. This is part of the multi field
         * definition every multi field is not_analyzed by default.
         */
        'not_analyzed' => array(),

        /*
         * Default string analyzer with full word matching base ond
         * the standard analyzer. This will generate hits on the full
         * words tokenized by the standard analyzer.
         */
        'gs_string_default' => array(
            'type' => 'string',
            'index' => 'analyzed',
            'index_analyzer' => 'gs_analyzer_default',
            'search_analyzer' => 'gs_analyzer_default',
            'store' => false,
        ),

        /*
         * String analyzer using ngrams for wildcard matching. The
         * weighting of the hits on this mapping are less than full
         * matches using the default string mapping.
         */
        'gs_string_ngram' => array(
            'type' => 'string',
            'index' => 'analyzed',
            'index_analyzer' => 'gs_analyzer_ngram',
            'search_analyzer' => 'gs_analyzer_default',
            'store' => false,
        ),

        /*
         * Date field mapping. Date fields are not searchable but are
         * needed to be returned as part of the dataset and to be able
         * to perform facets on.
         */
        'gs_datetime' => array(
            'type' => 'date',
            'format' => 'YYYY-MM-dd HH:mm:ss',
            'index' => 'no',
            'store' => false,
        ),

        /*
         * Integer mapping
         */
        'gs_integer' => array(
            'type' => 'integer',
            'index' => 'no',
            'store' => false,
        ),

        /*
         * Phone mapping. The analyzer supports partial matches using
         * ngrams and transforms every phone number in pure numbers
         * only to be able to search for different formats and still
         * get hits. For example the data source for +32 (475)61.64.28
         * will be stored and analyzed as 32475616428 including ngrams
         * based on this result. When phone number fields are included
         * in the search matching will happen when searching for:
         *      +32 475 61.64.28
         *      (32)475-61-64-28
         *      ...
         */
        'gs_phone' => array(
            'type' => 'string',
            'index' => 'analyzed',
            'index_analyzer' => 'gs_analyzer_phone_ngram',
            'search_analyzer' => 'gs_analyzer_phone_full',
            'store' => false,
        ),
    );

    /**
     * Weighted boost definition
     * @var array
     */
    protected $weightedBoost = array(
        'gs_string_ngram' => 0.35,
        'gs_phone' => 0.20,
    );

    /**
     * Highlighter field definitions
     * @var array
     */
    protected $highlighterFields = array(
        '*.gs_string_default' => array(),
        '*.gs_string_ngram' => array(),
        '*.gs_phone' => array(
            'number_of_frags' => 0,
        ),
    );

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->provider->addSupportedTypes(array_keys($this->typesMultiField));
        $this->provider->addWeightedBoosts($this->weightedBoost);
        $this->provider->addHighlighterFields($this->highlighterFields);
    }

    /**
     * {@inheritdoc}
     */
    public function buildAnalysis(AnalysisBuilder $analysisBuilder)
    {
        $analysisBuilder

            // base ngram filter - TODO: make configurable
            ->addFilter(
                'gs_filter_ngram',
                'nGram',
                array('min_gram' => 2, 'max_gram' => 15)
            )

            // char filter keeping only numeric values
            ->addCharFilter(
                'gs_char_num_pattern',
                'pattern_replace',
                array('pattern' => '[^\\d]+', 'replacement' => '')
            )

            ->addCustomAnalyzer(
                'gs_analyzer_default',
                'standard',
                array('lowercase')
            )
            ->addCustomAnalyzer(
                'gs_analyzer_ngram',
                'standard',
                array('lowercase', 'gs_filter_ngram')
            )
            ->addCustomAnalyzer(
                'gs_analyzer_phone_ngram',
                'standard',
                array('gs_filter_ngram'),
                array('gs_char_num_pattern')
            )
            ->addCustomAnalyzer(
                'gs_analyzer_phone_full',
                'standard',
                array(),
                array('gs_char_num_pattern')
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildMapping(Mapping $mapping, $field, array $defs)
    {
        // Skip field if no multi field mapping has been defined or no type available
        if (!isset($defs['type']) || !isset($this->typesMultiField[$defs['type']])) {
            return;
        }

        foreach ($this->typesMultiField[$defs['type']] as $multiField) {
            if ($multiField === 'not_analyzed') {
                $mapping->addNotAnalyzedField($field);
            } else {
                $multiFieldProperty = $this->getMultiFieldProperty($multiField);
                $mapping->addMultiField($field, $multiField, $multiFieldProperty);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchFields(SearchFields $sf, $module, $field, array $defs)
    {
        // Skip field if no multi field mapping has been defined or no type available
        if (!isset($defs['type']) || !isset($this->typesMultiField[$defs['type']])) {
            return;
        }

        // Add fields which are based on strings
        foreach ($this->getStringFieldsForType($defs['type']) as $searchField) {
            if ($searchField === 'not_analyzed') {
                $path = array($field);
                $weightId = $field;
            } else {
                $path = array($field, $searchField);
                $weightId = $searchField;
            }
            $sf->addSearchField($module, $path, $defs, $weightId);
        }
    }

    /**
     * Get search field list for given field type
     * @param unknown $type
     * @return multitype:unknown
     */
    protected function getStringFieldsForType($type)
    {
        $list = array();
        foreach ($this->typesMultiField[$type] as $multiFieldDef) {
            if ($this->isStringBased($multiFieldDef)) {
                $list[] = $multiFieldDef;
            }
        }
        return $list;
    }

    /**
     * Check if given multi field definition is string based
     * @param string $multiFieldDef Multi field definition name
     * @return boolean
     */
    protected function isStringBased($multiFieldDef)
    {
        // special case for not_analyzed fields
        if ($multiFieldDef === 'not_analyzed') {
            return true;
        }

        $defs = $this->multiFieldDefs[$multiFieldDef];
        if (isset($defs['type']) && $defs['type'] === 'string') {
            return true;
        }

        return false;
    }

    /**
     * Get multi field property object
     * @param string $name Multi field property name
     * @throws MappingException
     * @return MultiFieldProperty
     */
    protected function getMultiFieldProperty($name)
    {
        if (!isset($this->multiFieldDefs[$name])) {
            throw new MappingException("Unknown multi field definition '{$name}'");
        }

        if (!isset($this->multiFieldDefs[$name]['type'])) {
            throw new MappingException("Multi field definition '{$name}' missing required type");
        }

        $multiField = new MultiFieldProperty();
        $multiField->setType($this->multiFieldDefs[$name]['type']);
        $multiField->setMapping($this->multiFieldDefs[$name]);

        return $multiField;
    }
}
