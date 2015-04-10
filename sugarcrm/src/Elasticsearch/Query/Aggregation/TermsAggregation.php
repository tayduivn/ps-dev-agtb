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
 * Generic terms aggregation
 *
 */
class TermsAggregation extends AbstractAggregation
{
    /**
     * {@inheritdoc}
     */
    protected $acceptedOptions = array(
        'field',
        'size',
        'order',
    );

    /**
     * {@inheritdoc}
     */
    protected $options = array(
        'size' => 5,
        'order' => array('_count', 'desc'),
    );

    /**
     * Flag to indicate we use a filtered query
     * @var boolean
     */
    protected $filtered = false;

    /**
     * {@inheritdoc}
     */
    public function build($id, array $filters)
    {
        $terms = new \Elastica\Aggregation\Terms($id);
        $this->applyOptions($terms, $this->options);

        if (empty($filters)) {
            return $terms;
        }

        // if filters are present we need to wrap it in a filtered agg
        $this->filtered = true;
        $agg = new \Elastica\Aggregation\Filter($id);
        $agg->setFilter($this->buildFilters($filters));
        $agg->addAggregation($terms);
        return $agg;
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilter($filterDefs)
    {
        if (!is_array($filterDefs)) {
            return false;
        }

        $filter = new \Elastica\Filter\Term();
        $filter->setTerm($this->options['field'], $filterDefs);
        return $filter;
    }

    /**
     * {@inheritdoc}
     */
    public function parseResults($id, array $results)
    {
        // When we wrapped in a filte we need to go one level deeper
        if ($this->filtered) {
            $buckets = $results[$id]['buckets'];
        } else {
            $buckets = $results['buckets'];
        }

        $parsed = array();
        foreach ($buckets as $bucket) {
            $parsed[$bucket['key']] = $bucket['doc_count'];
        }

        return $parsed;
    }
}
