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
 * The implementation class for MyItems Aggregation.
 *
 */
class MyItemsAggregation extends FilterAggregation
{
    /**
     * Constructor.
     * @param string $userId the id of the user
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * To be defined by the derived class
     * @param string $field the name of the field
     * @return \Elastica\Filter\AbstractFilter
     */
    protected function getFilter($field)
    {
        $filter = new \Elastica\Filter\Terms($field, array($this->userId));
        return $filter;
    }
}
