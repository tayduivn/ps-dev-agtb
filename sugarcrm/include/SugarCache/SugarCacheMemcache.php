<?php
require_once('include/SugarCache/SugarCacheAbstract.php');

class SugarCacheMemcache extends SugarCacheAbstract
{
    /**
     * @var Memcache server name string
     */
    protected $_host = 'localhost';
    
    /**
     * @var Memcache server port int
     */
    protected $_port = 11211;
    
    /**
     * @var Memcache object
     */
    protected $_memcache = '';
    
    /**
     * @see SugarCacheAbstract::$_priority
     */
    protected $_priority = 900;
     
    /**
     * @see SugarCacheAbstract::useBackend()
     */
    public function useBackend()
    {
        if ( extension_loaded('memcache')
                && empty($GLOBALS['sugar_config']['external_cache_disabled_memcache'])
                && $this->_getMemcacheObject() )
            return true;
            
        return false;
    }
    
    /**
     * @see SugarCacheAbstract::__construct()
     *
     * For this backend, we'll read from the SugarCacheFile::_cacheFileName file into 
     * the SugarCacheFile::$localCache array.
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get the memcache object; initialize if needed
     */
    protected function _getMemcacheObject()
    {
        if ( !($this->_memcache instanceOf Memcache) ) {
            $this->_memcache = new Memcache();
            $this->_host = SugarConfig::getInstance()->get('external_cache.memcache.host', $this->_host);
            $this->_port = SugarConfig::getInstance()->get('external_cache.memcache.port', $this->_port);
            if ( !@$this->_memcache->connect($this->_host,$this->_port) ) {
                return false;
            }
        }
        
        return $this->_memcache;
    }
    
    /**
     * @see SugarCacheAbstract::_setExternal()
     *
     * Does nothing; we write to cache on destroy
     */
    protected function _setExternal(
        $key,
        $value
        )
    {
        $this->_getMemcacheObject()->set($key, $value, $this->expireTimeout);
    }
    
    /**
     * @see SugarCacheAbstract::_getExternal()
     *
     * Does nothing; we get from cache on construct
     */
    protected function _getExternal(
        $key
        )
    {
        $returnValue = $this->_getMemcacheObject()->get($key);
        if ( $returnValue === false ) {
            return null;
        }
        
        return $returnValue;
    }
    
    /**
     * @see SugarCacheAbstract::_clearExternal()
     *
     * Does nothing; we write to cache on destroy
     */
    protected function _clearExternal(
        $key
        )
    {
        $this->_getMemcacheObject()->delete($key);
    }
    
    /**
     * @see SugarCacheAbstract::_resetExternal()
     *
     * Does nothing; we write to cache on destroy
     */
    protected function _resetExternal()
    {
        $this->_getMemcacheObject()->flush();
    }
}
