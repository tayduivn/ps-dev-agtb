<?php

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
