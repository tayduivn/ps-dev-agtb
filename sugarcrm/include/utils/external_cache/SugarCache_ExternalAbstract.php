<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * @abstract
 */
class SugarCache_ExternalAbstract extends SugarCache_Base
{
    /**
     * Use the parent of object to attempt to retrieve cache?  i.e., use local
     * memory cache.
     *
     * @var bool
     * @access protected
     */
    var $_use_parent = true;
    
    /**
     * An internal value that can be used to adjust the length of a timeout.
     *
     * If not set prior to calling {@link init()}, this will default to the constant
     * EXTERNAL_CACHE_INTERVAL_SECONDS
     *
     * @var int
     */
    var $timeout = null;
    
    /**
     * Stores the cache name
     * @access private
     */
    var $_name = '';

    /**
     * Serves to initialize this cache
     */
    function init()
    {
        if (empty($this->_timeout)) {
            $this->timeout = EXTERNAL_CACHE_INTERVAL_SECONDS;
        }
        
        $this->_use_parent = false;
        
        $value = $this->get(EXTERNAL_CACHE_WORKING_CHECK_KEY);
        if ($value != EXTERNAL_CACHE_WORKING_CHECK_KEY) {
            $this->set(
                EXTERNAL_CACHE_WORKING_CHECK_KEY,
                EXTERNAL_CACHE_WORKING_CHECK_KEY
            );
            $value = $this->get(EXTERNAL_CACHE_WORKING_CHECK_KEY);
            
            // Clear the cache statistics after the test.  This makes the statistics work out.
            $GLOBALS['external_cache_request_external_hits'] = 0;
            $GLOBALS['external_cache_request_external_total'] = 0;
        }
        
        $this->_use_parent = true;
        $this->initialized = (EXTERNAL_CACHE_WORKING_CHECK_KEY == $value);
        
        if (empty($this->_name)) {
            $this->_name = substr(get_class($this), 11);
        }
    }

    function get($key)
    {
        if ($this->_use_parent && !is_null($value = parent::get($key))) {
            if (EXTERNAL_CACHE_DEBUG) {
                SugarCache::log("{$this->_name}:: found {$key} in local memory cache");
            }
            return $value;
        }

        if(!$GLOBALS['external_cache_enabled']) {
            if (EXTERNAL_CACHE_DEBUG) {
                SugarCache::log("{$this->_name}:: caching disabled", 'fail');
            }
            return null;
        }

        $GLOBALS['external_cache_request_external_total']++;

        if(EXTERNAL_CACHE_DEBUG) {
            SugarCache::log("{$this->_name}:: retrieving key from cache ($key)");
        }

        return null;
    }

    function _realKey($key)
    {
        return $GLOBALS['sugar_config']['unique_key'] . $key;
    }

    function _processGet($key, $value)
    {
        if (!empty($value)) {
            if (EXTERNAL_CACHE_DEBUG) {
                SugarCache::log("{$this->_name}:: Retrieved from external cache: {$key}", 'pass');
            }
            $GLOBALS['external_cache_request_external_hits']++;
            $this->_cache[$key] = $value;
            return $this->_cache[$key];
        }
        if(EXTERNAL_CACHE_DEBUG) {
            SugarCache::log("{$this->_name}:: External cache retrieve failed: $key", 'fail');
        }
        return null;
    }
}