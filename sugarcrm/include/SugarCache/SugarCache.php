<?php

class SugarCache
{
    const EXTERNAL_CACHE_NULL_VALUE = "SUGAR_CACHE_NULL_ZZ";
    
    protected static $_cacheInstance;
    
    /**
     * @var true if the cache has been reset during this request, so we no longer return values from 
     *      cache until the next reset
     */
    public static $isCacheReset = false;
    
    private function __construct() {}
    
    /**
     * initializes the cache in question
     */
    protected static function _init()
    {
        $locations = array('include/SugarCache','custom/include/SugarCache');
 	    foreach ( $locations as $location ) {
            if (sugar_is_dir($location) && $dir = opendir($location)) {
                while (($file = readdir($dir)) !== false) {
                    if ($file == ".." 
                            || $file == "."
                            || !is_file("$location/$file")
                            )
                        continue;
                    require_once("$location/$file");
                    $cacheClass = basename($file, ".php");
                    $lastPriority = 1000;
                    if ( class_exists($cacheClass) && is_subclass_of($cacheClass,'SugarCacheAbstract') ) {
                        $GLOBALS['log']->debug("Found cache backend $cacheClass");
                        $cacheInstance = new $cacheClass();
                        if ( $cacheInstance->useBackend() 
                                && $cacheInstance->getPriority() < $lastPriority ) {
                            $GLOBALS['log']->debug("Using cache backend $cacheClass");
                            self::$_cacheInstance = $cacheInstance;
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Returns the instance of the SugarCacheAbstract object, cooresponding to the external
     * cache being used.
     */
    public static function instance()
    {
        if ( !is_subclass_of(self::$_cacheInstance,'SugarCacheAbstract') )
            self::_init();
        
        return self::$_cacheInstance;
    }
}

/**
 * Procedural API for external cache
 */

/**
 * Retrieve a key from cache.  For the Zend Platform, a maximum age of 5 minutes is assumed.
 *
 * @param String $key -- The item to retrieve.
 * @return The item unserialized
 */
function sugar_cache_retrieve($key)
{
    return SugarCache::instance()->$key;
}

/**
 * Put a value in the cache under a key
 *
 * @param String $key -- Global namespace cache.  Key for the data.
 * @param Serializable $value -- The value to store in the cache.
 */
function sugar_cache_put($key, $value)
{
    SugarCache::instance()->$key = $value;
}

/**
 * Clear a key from the cache.  This is used to invalidate a single key.
 *
 * @param String $key -- Key from global namespace
 */
function sugar_cache_clear($key)
{
    unset(SugarCache::instance()->$key);
}

/**
 * Turn off external caching for the rest of this round trip and for all round 
 * trips for the next cache timeout.  This function should be called when global arrays
 * are affected (studio, module loader, upgrade wizard, ... ) and it is not ok to 
 * wait for the cache to expire in order to see the change.
 */
function sugar_cache_reset()
{
    SugarCache::instance()->reset();
}

/**
 * Internal -- Determine if there is an external cache available for use.  
 * 
 * @deprecated
 */
function check_cache()
{
    SugarCache::instance();
}

/**
 * This function is called once an external cache has been identified to ensure that it is correctly
 * working.
 * 
 * @deprecated
 *
 * @return true for success, false for failure.
 */
function sugar_cache_validate()
{
    $instance = SugarCache::instance();
    
    return is_object($instance);
}

/**
 * Internal -- This function actually retrieves information from the caches.
 * It is a helper function that provides that actual cache API abstraction.
 *
 * @param unknown_type $key
 * @return unknown
 * @deprecated
 * @see sugar_cache_retrieve
 */
function external_cache_retrieve_helper($key)
{
    return SugarCache::instance()->$key;
}
