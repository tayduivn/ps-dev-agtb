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
 * The Base adapter only stores values in-memory.
 *
 * Cache adapters can extend this class in order to acheive local, in-memory
 * caching to reduce the number of round trips to outside caching mechanisms
 * during a single request.
 *
 */
class SugarCache_Base
{
    /**
     * The status of this object
     *  - null if init() has not been called
     *  - false if init() failed
     *  - true if init() succeeded
     *
     * @var null|bool
     * @access public
     */
    var $initialized = null;

    /**
     * Contains an in-memory cache of values in the cache
     *
     * @var array
     * @access protected
     */
    var $_cache = array();

    var $_my_class_name = '';

    /**
     * Handle any initialization.
     *
     * @internal As shown here, at a minimum init() is responsible for flagging
     *           the {@link $initialized} property to true on success.
     */
    function init()
    {
        $this->initialized = true;
        $this->_my_class_name = strtolower(get_class($this));
    }

    /**
     * Set a given key/value pair within the cache
     *
     * @param string $key
     * @param mixed $value
     */
    function set($key, $value)
    {
        if(EXTERNAL_CACHE_DEBUG) {
            SugarCache::log("Step 1: Adding key to {$GLOBALS['external_cache_type']} cache $key with value ($value)");
        }

        if(empty($value)) {
            $value = EXTERNAL_CACHE_NULL_VALUE;
        }

        if(EXTERNAL_CACHE_DEBUG) {
            SugarCache::log("Step 2: Adding key to {$GLOBALS['external_cache_type']} cache $key with value ($value)");
        }

        $this->_cache[$key] = $value;
    }

    /**
     * Retrieve the value of a given key
     *
     * @param string $key
     * @return mixed
     */
    function get($key)
    {
        $GLOBALS['external_cache_request_local_total']++;
        if (isset($this->_cache[$key])) {
            if (EXTERNAL_CACHE_DEBUG) {
                SugarCache::log("BASE: found {$key}", 'lightpass');
            }
            $GLOBALS['external_cache_request_local_hits']++;
            return $this->_cache[$key];
        } else {
            if (EXTERNAL_CACHE_DEBUG) {
                $type = $this->_my_class_name == 'sugarcache_base' ? 'fail' : 'lightfail';
                SugarCache::log("BASE: unable to locate {$key}", $type);
            }
        }
    }

    /**
     * Unset a given value
     *
     * @internal The term "unset" is a reserved word within PHP.  This
     *           opts for using the magic __unset() within PHP5 to enable
     *           direct unset($cache->foo) calls.  Due to BC considerations
     *           with PHP 4, however, this method should be invoked
     *           directly via $cache->__unset('foo');
     *
     * @param string $key
     */
    function __unset($key)
    {
        unset($this->_cache[$key]);
    }

    /**
     * Clean opcode cache
     */
    function clean_opcodes()
    {
    	/* nothing by default */
    }
}