<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

/**
 *
 * Facet Factory
 *
 */
class FacetFactory
{
    /**
     *
     * Local cache
     * @var array
     */
    protected static $loaded = array();

    /**
     *
     * Facet object loader
     * @param string $type
     * @return FacetAbstract
     */
    public static function get($type)
    {
        if (isset(self::$loaded[$type])) {
            return self::$loaded[$type];
        }

        self::$loaded[$type] = false;
        $className = "Facet".ucfirst($type);
        $classFile = "include/SugarSearchEngine/Elastic/Facets/{$className}.php";
        if (SugarAutoLoader::requireWithCustom($classFile)) {
            self::$loaded[$type] = new $className();
        }
        return self::$loaded[$type];
    }
}
