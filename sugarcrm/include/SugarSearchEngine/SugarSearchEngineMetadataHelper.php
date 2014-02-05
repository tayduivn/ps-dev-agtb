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
 *Portions created by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights
 *Reserved.
 ********************************************************************************/

require_once('modules/Home/UnifiedSearchAdvanced.php');

/**
 * Class dealing with searchable modules
 * @api
 */
class SugarSearchEngineMetadataHelper
{
    /**
     * Cache key for enabled modules
     */
    const ENABLE_MODULE_CACHE_KEY = 'ftsEnabledModules';

    /**
     *
     * Cache key prefix for FTS enabled fields per module
     * @var string
     */
    const FTS_FIELDS_CACHE_KEY_PREFIX = 'fts_fields_';

    /**
     * Retrieve all FTS fields for all FTS enabled modules.
     *
     * @return array
     */
    public static function retrieveFtsEnabledFieldsForAllModules()
    {
        $cachedResults = sugar_cache_retrieve(self::ENABLE_MODULE_CACHE_KEY);
        if($cachedResults != null && !empty($cachedResults) )
        {
            $GLOBALS['log']->debug("Retrieving enabled fts modules from cache");
            return $cachedResults;
        }

        $results = array();

        $usa = new UnifiedSearchAdvanced();
        $modules = $usa->retrieveEnabledAndDisabledModules();

        foreach($modules['enabled'] as $module)
        {
            $fields = self::retrieveFtsEnabledFieldsPerModule($module['module']);
            $results[$module['module']] = $fields;
        }

        sugar_cache_put(self::ENABLE_MODULE_CACHE_KEY, $results, 0);
        return $results;

    }

    /**
     * Return all of the modules disabled for FTS by the administrator
     *
     * @return mixed|The
     */
    public static function getSystemEnabledFTSModules()
    {
        $usa = new UnifiedSearchAdvanced();
        $modules = $usa->retrieveEnabledAndDisabledModules();
        $enabledModules = array();
        foreach($modules['enabled'] as $module)
        {
            $enabledModules[ $module['module'] ] = $module['module'];
        }

        return $enabledModules;
    }

    /**
     * For a given module, return all of the full text search enabled fields.
     *
     * @param $module
     *
     */
    public static function retrieveFtsEnabledFieldsPerModule($module)
    {
        $results = array();
        if( is_string($module))
        {
            $obj = BeanFactory::getBean($module, null);
            if($obj == null)
               return FALSE;
        }
        else if( is_a($module, 'SugarBean') )
        {
            $obj = $module;
        }
        else
        {
            return $results;
        }

        if (empty($obj->module_name)) {
            return $results;
        }

        $cacheKey = self::FTS_FIELDS_CACHE_KEY_PREFIX . $obj->module_name;
        $cacheResults = sugar_cache_retrieve($cacheKey);
        if(!empty($cacheResults))
            return $cacheResults;

        foreach($obj->field_defs as $field => $def)
        {
            if (isset($def['full_text_search']) && is_array($def['full_text_search']) && !empty($def['full_text_search']['enabled'])) {
                $results[$field] = $def;
            }
        }

        sugar_cache_put($cacheKey, $results);
        return $results;

    }

    /**
     * Return all of the FTS enabled modules for a specific user
     *
     * @static
     * @param null|User $user
     * @return array
     */
    public static function getUserEnabledFTSModules(User $user = null)
    {
        if($user == null)
            $user = $GLOBALS['current_user'];

        $userDisabled = $user->getPreference('fts_disabled_modules');
        $userDisabled = explode(",", $userDisabled);

        $enabledModules = self::retrieveFtsEnabledFieldsForAllModules();
        $enabledModules = array_keys($enabledModules);

        $filteredEnabled = array();
        foreach($enabledModules as $m)
        {
            if( ! in_array($m, $userDisabled) )
            {
                $filteredEnabled[] = $m;
            }
        }

        return $filteredEnabled;
    }

    /**
     * Determine if a module is FTS enabled.
     *
     * @param $module
     * @return bool
     */
    public static function isModuleFtsEnabled($module)
    {
        $GLOBALS['log']->debug("Checking if module is fts enabled");
        $enabledModules = self::getSystemEnabledFTSModules();

        return in_array($module, $enabledModules);
    }

    /**
     *
     * Clear FTS metadata cache
     */
    public static function clearCache()
    {
        // clear possible cache entries per module
        $usa = new UnifiedSearchAdvanced();
        $list = $usa->retrieveEnabledAndDisabledModules();
        foreach ($list as $modules) {
            foreach ($modules as $module) {
                $cacheKey = self::FTS_FIELDS_CACHE_KEY_PREFIX . $module['module'];
                sugar_cache_clear($cacheKey);
            }
        }

        // clear master list of enabled modules
        sugar_cache_clear(self::ENABLE_MODULE_CACHE_KEY);
    }
}