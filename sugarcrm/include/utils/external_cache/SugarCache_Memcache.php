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

class SugarCache_Memcache extends SugarCache_ExternalAbstract
{
    var $_memcache = null;
    var $_config = array(
        'host' => 'localhost',
        'port' => 11211,
    );

    function SugarCache_Memcache()
    {
        $config = SugarConfig::getInstance();
        $this->_config['host'] = $config->get('external_cache.memcache.host', 'localhost');
        $this->_config['port'] = $config->get('external_cache.memcache.port', 11211);
    }

    function init()
    {
        if (EXTERNAL_CACHE_DEBUG) {
            SugarCache::log('initializing memcache');
        }
        $this->_memcache = new Memcache();
        $status = @$this->_memcache->connect(
            $this->_config['host'],
            $this->_config['port']
        );
        if (!$status) {
            if (EXTERNAL_CACHE_DEBUG) {
                SugarCache::log('initialization of memcache failed', 'fail');
            }
            $this->initialized = false;
            return;
        } 
        parent::init();
    }

    function get($key)
    {
        $value = parent::get($key);
        if (!is_null($value)) {
            return $value;
        }
        if (EXTERNAL_CACHE_DEBUG) {
            SugarCache::log('grabbing via Memcache::get(' . $this->_realKey($key) . ')');
        }
        return $this->_processGet(
            $key,
            $this->_memcache->get(
                $this->_realKey($key)
            )
        );
    }

    function set($key, $value)
    {
        parent::set($key, $value);

        // caching is turned off
        if(!$GLOBALS['external_cache_enabled']) {
            return;
        }

        $external_key = $this->_realKey($key);
		if (EXTERNAL_CACHE_DEBUG) {
            SugarCache::log("Step 3: Converting key ($key) to external key ($external_key)");
        }

        $this->_memcache->set($external_key, $value, $this->timeout);

        if (EXTERNAL_CACHE_DEBUG) {
            SugarCache::log("Step 4: Added key to memcache cache {$external_key} with value ($value) to be stored for ".EXTERNAL_CACHE_INTERVAL_SECONDS." seconds");
        }
    }

    function __unset($key)
    {
        parent::__unset($key);
        $this->_memcache->delete($this->_realKey($key));
    }
}

