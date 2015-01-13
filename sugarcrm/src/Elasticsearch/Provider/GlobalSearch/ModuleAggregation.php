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

use Sugarcrm\Sugarcrm\Elasticsearch\Query\Aggregation\AbstractAggregation;

/**
 *
 * Simple module aggregation replacing module facets
 *
 */
class ModuleAggregation extends AbstractAggregation
{
    /**
     * @var \Elastica\Aggregation\Terms
     */
    protected $agg;

    /**
     * Ctor
     */
    public function __construct()
    {
        $this->agg = $agg = new \Elastica\Aggregation\Terms('module_aggregation');
        $agg->setField('_type');
        $agg->setOrder('_count', 'desc');
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        return $this->agg;
    }

    /**
     * Set size
     * @param integer $size
     */
    public function setSize($size)
    {
        $this->agg->setSize($size);
    }
}
