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
 * Aggregation Factory loads a specific aggregation based on the given type.
 *
 */
class AggregationFactory
{
    /**
     *
     * Local cache
     * @var array
     */
    protected static $loaded = array();

    /**
     *
     * Aggregation object loader
     * @param string $type
     * @return AbstractAggregation
     */
    public static function get($type)
    {
        if (isset(self::$loaded[$type])) {
            return self::$loaded[$type];
        }

        self::$loaded[$type] = false;

        //Example class: TermAggregation, RangeAggregation, FilterAggregation, etc.
        $className = ucfirst($type)."Aggregation";
        $classFullName = "Sugarcrm\\Sugarcrm\\Elasticsearch\\Query\\Aggregation\\{$className}";
        self::$loaded[$type] = new $classFullName();
        return self::$loaded[$type];
    }
}
