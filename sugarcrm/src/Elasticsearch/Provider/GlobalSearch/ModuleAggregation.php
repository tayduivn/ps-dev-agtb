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

use Sugarcrm\Sugarcrm\Elasticsearch\Query\Aggregation\TermsAggregation;

/**
 *
 * Simple module aggregation replacing module facets
 *
 */
class ModuleAggregation extends TermsAggregation
{
    /**
     * Ctor
     * @param int $size the size of the module list
     * @param \Elastica\Filter\Bool $filter the filter for the module aggregation
     */
    public function __construct($size, \Elastica\Filter\Bool $filter)
    {
        parent::__construct($size);
        parent::buildAgg('_type', $filter);
    }
}
