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
 * Abstract class for Filter Aggregation.
 *
 */
abstract class FilterAggregation extends AbstractAggregation
{
    /**
     * {@inheritdoc}
     */
    protected $acceptedOptions = array(
        'field',
    );

    /**
     * {@inheritdoc}
     */
    protected $options = array(
    );

    /**
     * {@inheritdoc}
    */
    public function build($id, array $filters)
    {
        $agg = new \Elastica\Aggregation\Filter($id);

        // use id if field is not set at this point
        if (empty($this->options['field'])) {
            $this->options['field'] = $id;
        }

        $agg->setFilter($this->getAggFilter($this->options['field']));
        return $agg;
    }

    /**
     * {@inheritdoc}
     */
    public function parseResults(array $results)
    {
        return array(
            'count' => empty($results['doc_count']) ? 0 : $results['doc_count'],
        );
    }

    /**
     * Get aggregation filter definition
     * @param string $field
     * @return \Elastica\Filter\AbstractFilter
     */
    abstract protected function getAggFilter($field);
}
