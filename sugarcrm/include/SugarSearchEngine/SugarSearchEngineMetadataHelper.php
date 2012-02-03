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


class SugarSearchEngineMetadataHelper
{
    /**
     * Cache key for enabled modules
     */
    const ENABLE_MODULE_CACHE_KEY = 'ftsEnabledModules';

    /**
     * Cache key for disabled modules
     */
    const DISABLED_MODULE_CACHE_KEY = 'ftsDisabledModules';


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
            $GLOBALS['log']->fatal("Retrieving enabled fts modules from cache");
            return $cachedResults;
        }
        $results = array();
        foreach( $GLOBALS['moduleList'] as $moduleName )
        {
            $fields = self::retrieveFtsEnabledFieldsPerModule($moduleName);
            if( !empty($fields) && self::isModuleFtsEnabled($moduleName) )
                $results[$moduleName] = $fields;
        }

        //write_array_to_file('cacheFtsModulesFile', $results, $this->cacheFtsModulesFile);
        sugar_cache_put(self::ENABLE_MODULE_CACHE_KEY, $results);
        return $results;
    }

    /**
     * Return all of the modules disabled for FTS by the administrator
     *
     * @return mixed|The
     */
    public static function getSystemDisabledFTSModules()
    {
        $cachedResults = sugar_cache_retrieve(self::DISABLED_MODULE_CACHE_KEY);
        if($cachedResults != null && !empty($cachedResults) )
        {
            $GLOBALS['log']->fatal("Retrieving disabled fts modules from cache");
            return $cachedResults;
        }
        $GLOBALS['log']->fatal("Could not resolve fts module cache, loading from file....");
        $cacheFtsModulesFile = sugar_cached('modules/ftsModulesCache.php');
        if( file_exists($cacheFtsModulesFile) )
        {
            include($cacheFtsModulesFile);
            return $ftsDisabledModules;
        }
        return array();
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

        $cacheKey = "fts_fields_{$obj->table_name}";
        $cacheResults = sugar_cache_retrieve($cacheKey);
        if(!empty($cacheResults))
            return $cacheResults;

        foreach($obj->field_defs as $field => $def)
        {
            if( isset($def['full_text_search']) && is_array($def['full_text_search']) && !empty($def['full_text_search']['boost']) )
                $results[$field] = $def;
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
        $GLOBALS['log']->fatal("Checking if module is fts enabled");
        $disabledModules = self::getSystemDisabledFTSModules();
        if( empty($disabledModules) )
            return TRUE;

        return !in_array($module, $disabledModules);

    }


}