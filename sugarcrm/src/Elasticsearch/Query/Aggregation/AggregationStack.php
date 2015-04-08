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
 * Aggregation stack
 *
 */
class AggregationStack implements \IteratorAggregate
{
    /**
     * @var AggregationInterface[]
     */
    protected $stack = array();

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->stack);
    }

    /**
     * Add aggregation object
     * @param string $id Aggregation identifier
     * @param AggregationInterface $aggregation
     */
    public function addAggregation($id, AggregationInterface $aggregation)
    {
        $this->stack[$id] = $aggregation;
    }

    /**
     * Get aggregation by id
     * @param string $id
     * @return AggregationInterface|false
     */
    public function getById($id)
    {
        return (isset($this->stack[$id])) ? $this->stack[$id] : false;
    }
}
