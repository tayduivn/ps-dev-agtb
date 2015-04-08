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
     * {@inheritdoc}
     */
    public function build($id, array $filters)
    {
        $terms = new \Elastica\Aggregation\Terms($id);

        // use id if field is not set at this point
        if (empty($this->options['field'])) {
            $this->options['field'] = $id;
        }

        $this->applyOptions($terms, $this->options);
        return $terms;
    }

    /**
     * {@inheritdoc}
     */
    public function parseResults(array $results)
    {
        if (!isset($results['buckets'])) {
            return array();
        }

        $parsed = array();
        foreach ($results['buckets'] as $bucket) {
            $parsed[$bucket['key']] = $bucket['doc_count'];
        }

        return $parsed;
    }
}
