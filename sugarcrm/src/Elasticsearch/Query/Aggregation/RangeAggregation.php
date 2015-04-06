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

namespace Sugarcrm\Sugarcrm\Elasticsearch\Query\Aggregation;

use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;

/**
 *
 * The implementation class for Range Aggregation.
 *
 */
class RangeAggregation extends AbstractAggregation
{
    /**
     * @var \Elastica\Aggregation\Filter or \Elastica\Aggregation\Filter
     */
    protected $agg;

    /**
     *
     * List of supported range defintion in range aggregation
     * @var array
     */
    protected $esAggDefs = array(
        'to' => 'to',
        'from' => 'from',
        'key' => 'key',
    );

    /**
     *
     * List of supported range defintion for range filter
     * @var array
     */
    protected $esFilterDefs = array(
        'to' => 'to',
        'from' => 'from'
    );

    /**
     * Constructor.
     * @param array $options : options of the aggregations
     */
    public function __construct($options = array())
    {
        $defaultOpts =  array(
            'ranges' => array(),
            'typeExt' => '',
        );
        parent::__construct(array_merge($defaultOpts, $options));
    }

    /**
     * {@inheritdoc}
     */
    public function getAgg()
    {
        return $this->agg;
    }

    /**
     * {@inheritdoc}
     */
    public function buildAgg($fieldName, \Elastica\Filter\Bool $filter)
    {

        $agg = new \Elastica\Aggregation\Range($fieldName);

        //extract the field due to the difference of cross_module and per_module fields
        $names = explode(Mapping::PREFIX_SEP, $fieldName);
        if (sizeof($names)==2) {
            $field = $names[1];
        } else {
            $field = $fieldName;
        }
        $agg->setField($field . $this->options['typeExt']);
        foreach ($this->getRangeDefinitions($this->esAggDefs) as $range) {
            $agg->addRange($range['from'], $range['to'], $range['key']);
        }

        //If the filter is set, create the filter for the aggregation
        $filterArray = $filter->toArray();
        if (!empty($filterArray['bool'])) {
            $filterAgg = new \Elastica\Aggregation\Filter($fieldName);
            $filterAgg->setFilter($filter);
            $filterAgg->addAggregation($agg);
            $this->agg=$filterAgg;
            return $filterAgg;
        }

        //Otherwise, just return the term aggregation
        $this->agg=$agg;
        return $agg;

    }

    /**
     * {@inheritdoc}
     */
    public function buildFilter($fieldName, array $values)
    {
        // combine selected filters in an or clause
        $filter = new \Elastica\Filter\Bool();
        $rangeDefs = $this->getRangeDefinitions($this->esFilterDefs);
        foreach ($values as $filterKey) {
            if (isset($rangeDefs[$filterKey])) {
                $rangeFilter = new \Elastica\Filter\Range();
                $rangeFilter->addField($fieldName, $rangeDefs[$filterKey]);
                $filter->addShould($rangeFilter);
            }
        }
        return $filter;
    }

    /**
     *
     * Filter elastic range definition based on ES definitions.
     * @param array $def the definitions of fields
     * @return array
     */
    protected function getRangeDefinitions($def)
    {
        $ranges = array();
        foreach ($this->options['ranges'] as $key => $range) {
            $ranges[$key] = array_intersect_key($range, $def);
        }
        return $ranges;
    }
}
