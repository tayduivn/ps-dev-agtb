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
 * The implementation class for Terms Aggregation.
 *
 */
class TermsAggregation extends AbstractAggregation
{
    /**
     * @var \Elastica\Aggregation\Filter or \Elastica\Aggregation\Filter
     */
    protected $agg;

    /**
     * Constructor.
     * @param int $size : the size of term buckets
     */
    public function __construct($size = 21)
    {
        $defaultOpts =  array(
            'order' => array('_count', 'desc'),
            'size' => $size,
        );
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

        $agg = new \Elastica\Aggregation\Terms($fieldName);

        //extract the field due to the difference of cross_module and per_module fields
        $names = explode(Mapping::PREFIX_SEP, $fieldName);
        if (sizeof($names)==2) {
            $field = $names[1];
        } else {
            $field = $fieldName;
        }
        $agg->setField($field);
        $agg->setOrder($this->options['order'][0], $this->options['order'][1]);
        $agg->setSize($this->options['size']);

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
        $filter = new \Elastica\Filter\Terms($fieldName, $values);
        return $filter;
    }
}
