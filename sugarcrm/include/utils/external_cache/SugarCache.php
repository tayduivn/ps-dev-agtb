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

class SugarCache
{
    /**
     * Discover and return available cache adapter of false if nothing is available
     *
     * @return SugarCache_Abstract|false
     */
    function discover()
    {
        // If the cache is manually disabled, turn it off.
        if(!empty($GLOBALS['sugar_config']['external_cache_disabled']) && true == $GLOBALS['sugar_config']['external_cache_disabled'])
        {
            if (EXTERNAL_CACHE_DEBUG) {
                SugarCache::log("SugarCache::discover() -- caching explicitly disabled", 'fail');
            }
            $GLOBALS['external_cache_enabled'] = false;
            return SugarCache::factory('Base');
        }

        // check for Zend caching
        if(function_exists("output_cache_get") && empty($GLOBALS['sugar_config']['external_cache_disabled_zend']))
        {
            $GLOBALS['external_cache_enabled'] = true;
            $GLOBALS['external_cache_type'] = "zend";
            $cache = SugarCache::factory('Zend');
            if (EXTERNAL_CACHE_DEBUG) {
                SugarCache::log('Found Zend - attempting to use', 'pass');
            }
        }
        elseif (function_exists("zend_shm_cache_store") && empty($GLOBALS['sugar_config']['external_cache_disabled_zendserver'])) {
            $GLOBALS['external_cache_enabled'] = true;
            $GLOBALS['external_cache_type'] = 'zendserver';
            $cache = SugarCache::factory('ZendServer');
            if (EXTERNAL_CACHE_DEBUG) {
                SugarCache::log('Found Zend Server - attempting to use', 'pass');
            }
        }
        elseif (extension_loaded('memcache') && empty($GLOBALS['sugar_config']['external_cache_disabled_memcache'])) {
            $GLOBALS['external_cache_enabled'] = true;
            $GLOBALS['external_cache_type'] = 'memcache';
            $cache = SugarCache::factory('Memcache');
            if (EXTERNAL_CACHE_DEBUG) {
                SugarCache::log('Found memcache - attempting to use', 'pass');
            }
        }
        elseif(function_exists("apc_store") && empty($GLOBALS['sugar_config']['external_cache_disabled_apc']))
        {
            $GLOBALS['external_cache_enabled'] = true;
            $GLOBALS['external_cache_type'] = "apc";
            $cache = SugarCache::factory('APC');
            if (EXTERNAL_CACHE_DEBUG) {
                SugarCache::log('Found APC - attempting to use', 'pass');
            }
        }
        elseif(function_exists("wincache_ucache_set") && empty($GLOBALS['sugar_config']['external_cache_disabled_wincache']))
        {
            $GLOBALS['external_cache_enabled'] = true;
            $GLOBALS['external_cache_type'] = "wincache";
            $cache = SugarCache::factory('Wincache');
            if (EXTERNAL_CACHE_DEBUG) {
                SugarCache::log('Found Wincache - attempting to use', 'pass');
            }
        }
        elseif(function_exists("zget") && empty($GLOBALS['sugar_config']['external_cache_disabled_smash']))
        {
            $GLOBALS['external_cache_enabled'] = true;
            $GLOBALS['external_cache_type'] = "smash";
            $cache = SugarCache::factory('sMash');
            if (EXTERNAL_CACHE_DEBUG) {
                SugarCache::log('Found sMash - attempting to use', 'pass');
            }
        }
        // @todo memcache
        // @todo file cache as fallback
        else
        {
            // no cache available....return
    		$GLOBALS['external_cache_enabled'] = true;
            $GLOBALS['external_cache_type'] = 'base-in-memory';
            $cache = SugarCache::factory('Base');
            if (EXTERNAL_CACHE_DEBUG) {
                SugarCache::log('Found no caching solution - using base');
            }
        }

        // Check the cache.
    	if(!$cache->initialized)
    	{
        	// Validation failed.  Turn off the external cache and return SugarCache_Base
            $GLOBALS['external_cache_enabled'] = false;
    		if(EXTERNAL_CACHE_DEBUG) {
                SugarCache::log("external cache validation check failed...tried cache {$GLOBALS['external_cache_type']}", 'fail');
                SugarCache::log('returning Base');
            }
            return SugarCache::factory('Base');
    	}

        // If the cache is being reset, turn it off for this round trip
        $value = '';
        if(isset($GLOBALS['sugar_config']) && isset($GLOBALS['sugar_config']['unique_key']))
        {
            $value = $cache->get($GLOBALS['sugar_config']['unique_key'].'EXTERNAL_CACHE_RESET');
        }
        if(!empty($value))
        {
            // We are in a cache reset, do not use the cache.
            $GLOBALS['external_cache_enabled'] = false;
        }
        else
        {
            // Add one to the external cache hits.  This will keep the end user statistics simple.
            // All real checks suceeding will result in 100%.  Otherwise people will be looking for
            // the one check that did not pass.
    		$GLOBALS['external_cache_request_external_hits']++;
        }
        return $cache;
    }

    function factory($type)
    {
        $class = 'SugarCache_' . $type;
        $cache = new $class();
        $cache->init();
        return $cache;
    }

    /**
     * Performs basic logging for messages generated by the external caching mechanism
     *
     * Currently this only outputs directly to the screen as it's only used internally.
     *
     * There are five supported $type values:
     *  neutral     :: just a log message with information value
     *  pass        :: a pass that attention should be brought to
     *  lightpass   :: a pass without much consequence
     *  fail        :: a fail that attention should be brought to
     *  lightfail   :: a failure without much consequence, or one that might succeed later in
     *                 the execution chain
     *
     * @param string $msg Message to output.  Note it will be filtered through htmlspecialchars()
     * @param string $type Type of message to output
     */
    function log($msg, $type = 'neutral') {
        static $messages = array();
        static $valid_types = array(
            'neutral' => '',
            'pass' => '',
            'lightpass' => '',
            'fail' => '',
            'lightfail' => '',
        );

        if (!isset($valid_types[$type])) {
            SugarCache::log("Invalid type provided: {$type}", 'fail');
            $type = 'neutral';
        }
        $session_id = session_id();
        if (empty($session_id)) {
            // add to stack of messages to output after the session starts so we don't kill the headers
            $messages[] = array(
                'message' => htmlspecialchars($msg),
                'type' => $type,
            );
        } else {
            if ($messages !== false) {
                // output base styles on first round trip - this doesn't worry that its
                // not in the proper place as its for debugging purposes only.
                echo "<style type='text/css'>"
                    . "hr +span { padding:3px 5px; display:block; } "
                    . "hr +.pass { background-color:green; color: white; } "
                    . "hr +.lightpass { background-color: #CFC; color:black; }"
                    . "hr +.fail { background-color:red; color:white; } "
                    . "hr +.lightfail { background-color:#F99; color: black; }"
                    . "hr +.neutral { background-color:#FFFFE0; color:black; } "
                    . "</style>";
            }
            if ($messages !== false && count($messages) > 0) {
                // clear stack of messages;
                echo '<hr />Messages logged prior to session starting...<hr />', "\n";
                foreach ($messages as $id => $old_msg) {
                    echo "<hr /><span class='{$old_msg['type']}'>{$id} -- {$old_msg['message']}</span><hr />\n";
                }
                echo "<hr />End of messages prior to session starting...<hr />\n";
            }
            $messages = false;
            $msg = htmlspecialchars($msg);
            echo "<hr /><span class='{$type}'>{$msg}</span><hr />\n";
        }

    }
}
