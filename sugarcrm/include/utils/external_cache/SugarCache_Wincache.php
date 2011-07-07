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

class SugarCache_Wincache extends SugarCache_ExternalAbstract
{
    function init()
    {
        if (defined('SUGARCRM_IS_INSTALLING')) {
            if (EXTERNAL_CACHE_DEBUG) {
                SugarCache::log('not initializing on Windows during an installation');
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
            SugarCache::log('grabbing via wincache_ucache_get(' . $this->_realKey($key) . ')');
        }
        return $this->_processGet(
            $key,
            wincache_ucache_get(
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

        wincache_ucache_set($external_key, $value, $this->timeout);

        if (EXTERNAL_CACHE_DEBUG) {
            SugarCache::log("Step 4: Added key to Wincache cache {$external_key} with value ($value) to be stored for ".EXTERNAL_CACHE_INTERVAL_SECONDS." seconds");
        }
    }

    function __unset($key)
    {
        parent::__unset($key);
        wincache_ucache_delete($this->_realKey($key));
    }
    /**
     * Clean opcode cache
     */
    function clean_opcodes()
    {
		wincache_refresh_if_changed();
    }
}
