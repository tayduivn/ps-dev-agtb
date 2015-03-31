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
 * Aggregation builder interface
 *
 */
interface AggregationInterface
{
    /**
     * @return \Elastica\Aggregation\AbstractAggregation
     */
    public function getAgg();

    /**
     *
     * Returns an Elastica Aggregation object
     * @param  string                          $fieldName
     * @param  \Elastica\Filter\Bool $filter
     * @return \Elastica\Aggregation\AbstractAggregation
     */
    public function buildAgg($fieldName, \Elastica\Filter\Bool $filter);

    /**
     *
     * Returns an Elastica Filter object
     * @param  string $fieldName
     * @param  array  $values
     * @return \Elastica\Filter\AbstractFilter
     */
    public function buildFilter($fieldName, array $values);
}
