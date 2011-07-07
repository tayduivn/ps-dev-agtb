<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
class SugarTestLangPackCreator
{
    public function __construct()
    {
    }
    
    public function __destruct()
    {
        $this->clearLangCache();
    }
    
    /**
     * Set a string for the app_strings array
     *
     * @param $key   string
     * @param $value string
     */
    public function setAppString(
        $key,
        $value
        )
    {
        $this->_strings['app_strings'][$key] = $value;
    }
    
    /**
     * Set a string for the app_list_strings array
     *
     * @param $key   string
     * @param $value string
     */
    public function setAppListString(
        $key,
        $value
        )
    {
        $this->_strings['app_list_strings'][$key] = $value;
    }
    
    /**
     * Set a string for the mod_strings array
     *
     * @param $key    string
     * @param $value  string
     * @param $module string
     */
    public function setModString(
        $key,
        $value,
        $module
        )
    {
        $this->_strings['mod_strings'][$module][$key] = $value;
    }
    
    /**
     * Saves the created strings
     *
     * Here, we cheat the system by storing our string overrides in the sugar_cache where
     * we normally stored the cached language strings.
     */
    public function save()
    {
        $language = $GLOBALS['current_language'];
        if ( isset($this->_strings['app_strings']) ) {
            $cache_key = 'app_strings.'.$language;
            $app_strings = sugar_cache_retrieve($cache_key);
            if ( empty($app_strings) )
                $app_strings = return_application_language($language);
            foreach ( $this->_strings['app_strings'] as $key => $value )
                $app_strings[$key] = $value;
            sugar_cache_put($cache_key, $app_strings);
            $GLOBALS['app_strings'] = $app_strings;
        }
        
        if ( isset($this->_strings['app_list_strings']) ) {
            $cache_key = 'app_list_strings.'.$language;
            $app_list_strings = sugar_cache_retrieve($cache_key);
            if ( empty($app_list_strings) )
                $app_list_strings = return_app_list_strings_language($language);
            foreach ( $this->_strings['app_list_strings'] as $key => $value )
                $app_list_strings[$key] = $value;
            sugar_cache_put($cache_key, $app_list_strings);
            $GLOBALS['app_list_strings'] = $app_list_strings;
        }
        
        if ( isset($this->_strings['mod_strings']) ) {
            foreach ( $this->_strings['mod_strings'] as $module => $strings ) {
                $cache_key = LanguageManager::getLanguageCacheKey($module, $language);
                $mod_strings = sugar_cache_retrieve($cache_key);
                if ( empty($mod_strings) )
                    $mod_strings = return_module_language($language, $module);
                foreach ( $strings as $key => $value )
                    $mod_strings[$key] = $value;
                sugar_cache_put($cache_key, $mod_strings);
                $GLOBALS['mod_strings'] = $mod_strings;
            }
        }
    }
    
    /**
     * Clear the language string cache in sugar_cache, which will get rid of our
     * language file overrides.
     */
    protected function clearLangCache()
    {
        $language = $GLOBALS['current_language'];
        
        if ( isset($this->_strings['app_strings']) ) {
            $cache_key = 'app_strings.'.$language;
            sugar_cache_clear($cache_key);
        }
        
        if ( isset($this->_strings['app_list_strings']) ) {
            $cache_key = 'app_list_strings.'.$language;
            sugar_cache_clear($cache_key);
        }
        
        if ( isset($this->_strings['mod_strings']) ) {
            foreach ( $this->_strings['mod_strings'] as $module => $strings ) {
                $cache_key = LanguageManager::getLanguageCacheKey($module, $language);
                sugar_cache_clear($cache_key);
            }
        }
    }
}
