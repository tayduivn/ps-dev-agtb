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

/**
 *
 * The implementation class for Filter Aggregation.
 *
 */
abstract class FilterAggregation extends AbstractAggregation
{
    /**
     * @var \Elastica\Aggregation\Filter
     */
    protected $agg;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $defaultOpts =  array();
        parent::__construct($defaultOpts);
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

        $agg = new \Elastica\Aggregation\Filter($fieldName);

        //extract the field due to the difference of cross_module and per_module fields
        $names = explode(".", $fieldName);
        if (sizeof($names)==2) {
            $field = $names[1];
        } else {
            $field = $fieldName;
        }
        $agg->setFilter($this->getFilter($field));

        //If the filter is set, create the filter for the aggregation
        $filterArray = $filter->toArray();
        if (!empty($filterArray['bool'])) {
            $filterAgg = new \Elastica\Aggregation\Filter($fieldName);
            $filterAgg->setFilter($filter);
            $filterAgg->addAggregation($agg);
            $this->agg=$filterAgg;
            return $filterAgg;
        }

        //Otherwise, just return the Filter aggregation
        $this->agg=$agg;
        return $agg;

    }

    /**
     * {@inheritdoc}
     */
    public function buildFilter($fieldName, array $values)
    {
        return $this->getFilter($fieldName);
    }

    /**
     * To be defined by the derived class
     * @param string $field the name of the field
     * @return \Elastica\Filter\AbstractFilter
     */
    abstract protected function getFilter($field);
}
