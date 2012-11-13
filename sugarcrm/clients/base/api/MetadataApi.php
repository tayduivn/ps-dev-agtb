<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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

require_once('include/MetaDataManager/MetaDataManager.php');
require_once('include/api/SugarApi.php');

// An API to let the user in to the metadata
class MetadataApi extends SugarApi {
    /**
     * Added to allow use of the $user property without throwing errors in public
     * cased where there is no user defined.
     *
     * @var User
     */
    protected $user = null;

    public function registerApiRest() {
        return array(
            'getAllMetadata' => array(
                'reqType' => 'GET',
                'path' => array('metadata'),
                'pathVars' => array(''),
                'method' => 'getAllMetadata',
                'shortHelp' => 'This method will return all metadata for the system',
                'longHelp' => 'include/api/html/metadata_all_help.html',
            ),
            'getAllMetadataPost' => array(
                'reqType' => 'POST',
                'path' => array('metadata'),
                'pathVars' => array(''),
                'method' => 'getAllMetadata',
                'shortHelp' => 'This method will return all metadata for the system, filtered by the array of hashes sent to the server',
                'longHelp' => 'include/api/html/metadata_all_help.html',
            ),
            'getAllMetadataHashes' => array(
                'reqType' => 'GET',
                'path' => array('metadata','_hash'),
                'pathVars' => array(''),
                'method' => 'getAllMetadataHash',
                'shortHelp' => 'This method will return the hash of all metadata for the system',
                'longHelp' => 'include/api/html/metadata_all_help.html',
            ),
            'getPublicMetadata' =>  array(
                'reqType' => 'GET',
                'path' => array('metadata','public'),
                'pathVars'=> array(''),
                'method' => 'getPublicMetadata',
                'shortHelp' => 'This method will return the metadata needed when not logged in',
                'longHelp' => 'include/api/html/metadata_all_help.html',
                'noLoginRequired' => true,
            ),
        );
    }

    protected function getMetadataManager( $public = false ) {
        return new MetaDataManager($this->user,$this->platforms, $public);
    }

    public function getAllMetadata($api, $args) {
        global $current_language, $app_strings, $app_list_strings, $current_user;

        // asking for a specific language
        if (isset($args['lang']) && !empty($args['lang'])) {
            $current_language = $args['lang'];
            $app_strings = return_application_language($current_language);
            $app_list_strings = return_app_list_strings_language($current_language);

        }

        // Default the type filter to everything
        $this->typeFilter = array('modules','full_module_list','fields','view_templates','labels','mod_strings','app_strings','app_list_strings','module_list', 'views', 'layouts','relationships','currencies', 'jssource');
        if ( !empty($args['type_filter']) ) {
            // Explode is fine here, we control the list of types
            $types = explode(",", $args['type_filter']);
            if ($types != false) {
                $this->typeFilter = $types;
            }
        }

        $moduleFilter = array();
        if (!empty($args['module_filter'])) {
            if ( function_exists('str_getcsv') ) {
                // Use str_getcsv here so that commas can be escaped, I pity the fool that has commas in his module names.
                $modules = str_getcsv($args['module_filter'],',','');
            } else {
                $modules = explode(",", $args['module_filter']);
            }
            if ( $modules != false ) {
                $moduleFilter = $modules;
            }
        }

        $onlyHash = false;
        if (!empty($args['only_hash']) && ($args['only_hash'] == 'true' || $args['only_hash'] == '1')) {
            $onlyHash = true;
        }


        $this->user = $GLOBALS['current_user'];

        if ( isset($args['platform']) ) {
            $this->platforms = array(basename($args['platform']),'base');
        } else {
            $this->platforms = array('base');
        }


        $data = array();

        $userHashKey = '_PUBLIC_';
        if ( isset($GLOBALS['current_user']->id) ) {
            $userHashKey = $GLOBALS['current_user']->id;
        }
        $hashKey = "metadata:" . implode(",", $this->platforms) . $userHashKey . ":hash";
        //First check if the hash is cached so we don't have to load the metadata manually to calculate it
        $hash = sugar_cache_retrieve($hashKey);
        //If it was, check if the client has the same version cached
        if (!empty($hash)) {
            generateETagHeader($hash);
            //If we got here without dying, the client doesn't have the metadata cached.
            //First check if we have the metadata contents cached in a file
            $cacheFile = sugar_cached("api/metadata/{$hash}.php");
            if (file_exists($cacheFile)) {
                //$data will be populated by the include
                include($cacheFile);
            }
        }

        $this->jssourceFilter = $this->getJSSourcePlatforms($args);

        //If we failed to load the metadata from cache, load it now the hard way.
        if (empty($data)) {
            $data = $this->loadMetadata($hashKey);
        }
        $data['jssource'] = $this->buildJSFileFromMD($data, $this->platforms[0]);

        //If we had to generate a new hash, create the etag with the new hash
        if (empty($hash)) {
            generateETagHeader($data['_hash']);
        }

        $baseChunks = array('view_templates','fields','app_strings','app_list_strings','module_list', 'views', 'layouts', 'full_module_list','relationships', 'currencies', 'jssource');
        $perModuleChunks = array('modules','mod_strings');

        return $this->filterResults($args, $data, $onlyHash, $baseChunks, $perModuleChunks, $moduleFilter);
    }

    // this is the function for the endpoint of the public metadata api.
    public function getPublicMetadata($api, $args) {
        $configs = array();

        // right now we are getting the config only for the portal
        // Added an isset check for platform because with no platform set it was
        // erroring out. -- rgonzalez

        if(isset($args['platform'])) {
            //temporary replace 'forecasts' w/ 'base'
            //as forecast settings store in db w/ prefix 'base_'
            $category = $args['platform'] == 'forecasts' ? 'base' : $args['platform'];
            $prefix = "{$category}_";
            $admin = new Administration();
            $admin->retrieveSettings($category, true);
            foreach($admin->settings AS $setting_name => $setting_value) {
                if(stristr($setting_name, $prefix)) {
                    $key = str_replace($prefix, '', $setting_name);

                    // Empty array was getting decoded as '[]' as tertiary was falsy .. this fixes
                    $decoded = json_decode(html_entity_decode($setting_value));
                    $configs[$key] = $setting_value; // set to fallback in case
                    if (strcasecmp($setting_name, "null") == 0 || $decoded !== NULL) {
                        $configs[$key] = $decoded;
                    }
                }
            }
        }

        global $current_language, $app_strings, $app_list_strings;
        if ( isset($args['lang']) ) {
            $current_language = $args['lang'];
            $app_strings = return_application_language($current_language);
            $app_list_strings = return_app_list_strings_language($current_language);
        }

        // Default the type filter to everything available to the public, no module info at this time
        $this->typeFilter = array('fields','view_templates','app_strings','views', 'layouts', 'config', 'jssource');

        if ( !empty($args['type_filter']) ) {
            // Explode is fine here, we control the list of types
            $types = explode(",", $args['type_filter']);
            if ($types != false) {
                $this->typeFilter = $types;
            }
        }

        $onlyHash = false;

        if (!empty($args['only_hash']) && ($args['only_hash'] == 'true' || $args['only_hash'] == '1')) {
            $onlyHash = true;
        }

        if ( isset($args['platform']) ) {
            $this->platforms = array(basename($args['platform']),'base');
        } else {
            $this->platforms = array('base');
        }
        // since this is a public metadata call pass true to the meta data manager to only get public/
        $mm = $this->getMetadataManager( TRUE );

        // Exception for the AppListStrings.
        $app_list_strings = $mm->getAppListStrings();
        $app_list_strings_public = array();
        $app_list_strings_public['available_language_dom'] = $app_list_strings['available_language_dom'];

        // Let clients fill in any gaps that may need to be filled in
        $app_list_strings_public = $this->fillInAppListStrings($app_list_strings_public, $app_list_strings);

        // Start collecting data
        $data = array();

        $this->jssourceFilter = $this->getJSSourcePlatforms($args);

        $data['fields']  = $mm->getSugarClientFilesForPlatforms('field', $this->jssourceFilter);
        $data['views']   = $mm->getSugarClientFilesForPlatforms('view', $this->jssourceFilter);
        $data['layouts'] = $mm->getSugarClientFilesForPlatforms('layout', $this->jssourceFilter);
        $data['view_templates']   = $mm->getViewTemplates();
        $data['app_strings']      = $mm->getAppStrings();
        $data['app_list_strings'] = $app_list_strings_public;
        $data['modules'] = array(
            "Login" => array("fields" => array()));
        $data['config']           = $this->getConfigs();
        $data['jssource']         = $this->buildJSFileFromMD($data, $this->platforms[0]);        
        $data["_hash"] = md5(serialize($data));
        $baseChunks = array('view_templates','fields','app_strings','views', 'layouts', 'config', 'jssource');

        return $this->filterResults($args, $data, $onlyHash, $baseChunks);
    }

    protected function buildJSFileFromMD(&$data, $platform) {
        $js = "(function(app) {\n SUGAR.jssource = {";
        $compJS = $this->buildJSForComponents($data);
        $js .= $compJS;

        if (!empty($data['modules']))
        {
            if (!empty($compJS))
                $js .= ",";

            $js .= "\n\tmodules:{";

            $allModuleJS = '';
            foreach($data['modules'] as $module => $def)
            {
                $moduleJS = $this->buildJSForComponents($data['modules'][$module]);
                if(!empty($moduleJS)) {
                    $allModuleJS .= ",\n\t\t$module:{{$moduleJS}}";
                }
            }
            //Chop off the first comma in $allModuleJS
            $js .= substr($allModuleJS, 1);
            $js .= "\n\t}";
        }

        $js .= "}})(SUGAR.App);";
        $hash = md5($js);
        $path = "cache/javascript/$platform/components_$hash.js";
        if (!file_exists($path)){
            mkdir_recursive(dirname($path));
            file_put_contents($path, $js);
        }
        global $sugar_config;
        $sugar_config['site_url'];
        return $sugar_config['site_url'] . "/{$path}";
    }

    protected function buildJSForComponents(&$data) {
        $js = "";
        $platforms = $this->jssourceFilter;

        foreach(array('fields', 'views', 'layouts') as $mdType) {

            if (!empty($data[$mdType])){
                $js  .= ",\n\t$mdType:{";
                $plat = '';

                foreach ($platforms as $platform) {
                    if (isset($data[$mdType][$platform])) {
                        $plat .= ",\n\t\t\"$platform\":{";
                        $comp = '';

                        foreach($data[$mdType][$platform] as $name => $component) {
                            if (is_array($component) && !empty($component['controller'])) {
                                $controller = $component['controller'];
                                // remove additional symbols in end of js content - it will be included in content
                                $controller = trim(trim($controller), ",;");
                                $comp .= ",\n\t\t\"$name\":{controller:$controller}";
                                unset($data[$mdType][$name]['controller']);
                            }
                        }
                        //Chop of the first comma in $comp
                        $plat .= substr($comp,1);
                        $plat .= "\n\t}";
                    }
                }
                //Chop of the first comma in $plat
                $js .= substr($plat, 1);
                $js .= "\n\t}";
            }
        }
        //Chop of the first comma in $js
        return substr($js,1);
    }

    protected function loadMetadata($hashKey) {
        // Start collecting data
        $data = $this->_populateModules(array());
        $mm = $this->getMetadataManager();
        // TODO:
        // Sadly, it's now unclear what our abstraction is here. It should be that this class
        // is just for API stuff and $mm is for any metadata data operations. However, since
        // we now have child classes like MetadataPortalApi overriding getModules, etc., I'm
        // tentative to push the following three calls out to $mm. I propose refactor to instead
        // inherit as MetadataPortalDataManager and put all accessors, etc., there.
        $data['mod_strings'] = $this->getModStrings($data);
        $data['currencies'] = $this->getSystemCurrencies();

        $data['modules'] = array();

        foreach($data['full_module_list'] as $module) {
            $bean = BeanFactory::newBean($module);

            $modData = $mm->getModuleData($module);
            $data['modules'][$module] = $modData;

            if (isset($data['modules'][$module]['fields'])) {
                $fields = $data['modules'][$module]['fields'];
                foreach($fields as $fieldName => $fieldDef) {
                    if (isset($fieldDef['type']) && ($fieldDef['type'] == 'relate')) {
                        if (isset($fieldDef['module']) && !in_array($fieldDef['module'], $data['full_module_list'])) {
                            $data['full_module_list'][$fieldDef['module']] = $fieldDef['module'];
                        }
                    } elseif (isset($fieldDef['type']) && ($fieldDef['type'] == 'link')) {
                        $bean->load_relationship($fieldDef['name']);
                        $otherSide = $bean->$fieldDef['name']->getRelatedModuleName();
                        $data['full_module_list'][$otherSide] = $otherSide;
                    }
                }
            }
        }

        foreach($data['modules'] as $moduleName => $moduleDef) {
            if (!array_key_exists($moduleName, $data['full_module_list']) && array_key_exists($moduleName, $data['modules'])) {
                unset($data['modules'][$moduleName]);
            }
        }

        // remove the disabled modules from the module list
        require_once("modules/MySettings/TabController.php");
        $controller = new TabController();
        $tabs = $controller->get_tabs_system();

        if (isset($tabs[1])) {
            foreach($data['module_list'] as $moduleKey => $moduleName){
                if (in_array($moduleName,$tabs[1])) {
                    unset($data['module_list'][$moduleKey]);
                }
            }
        }
        $data['fields']  = $mm->getSugarClientFilesForPlatforms('field', $this->jssourceFilter);
        $data['views']   = $mm->getSugarClientFilesForPlatforms('view', $this->jssourceFilter);
        $data['layouts'] = $mm->getSugarClientFilesForPlatforms('layout', $this->jssourceFilter);
        $data['view_templates'] = $mm->getViewTemplates();
        $data['app_strings'] = $mm->getAppStrings();
        $data['app_list_strings'] = $mm->getAppListStrings();
        $data['relationships'] = $mm->getRelationshipData();
        $hash = md5(serialize($data));
        $data["_hash"] = $hash;

        //Cache the result to the filesystem
        $cacheFile = sugar_cached("api/metadata/{$hash}.php");
        create_cache_directory("api/metadata/{$hash}.php");
        write_array_to_file("data", $data, $cacheFile);

        //Cache the hash in sugar_cache so we don't have to hit the filesystem for etag comparisons
        sugar_cache_put($hashKey, $hash);

        return $data;
    }

    /*
     * Filters the results for Public and Private Metadata
     * @param array $args the Arguments from the Rest Request
     * @param array $data the data to be filtered
     * @param bool $onlyHash check to return only hashes
     * @param array $baseChunks the chunks we want filtered
     * @param array $perModuleChunks the module chunks we want filtered
     * @param array $moduleFilter the specific modules we want
     */

    protected function filterResults($args, $data, $onlyHash = false, $baseChunks = array(), $perModuleChunks = array(), $moduleFilter = array()) {

        if ( $onlyHash ) {
            // The client only wants hashes
            $hashesOnly = array();
            $hashesOnly['_hash'] = $data['_hash'];
            foreach ( $baseChunks as $chunk ) {
                if (in_array($chunk,$this->typeFilter) ) {
                    $hashesOnly[$chunk]['_hash'] = $data['_hash'];
                }
            }

            foreach ( $perModuleChunks as $chunk ) {
                if (in_array($chunk, $this->typeFilter)) {
                    // We want modules, let's filter by the requested modules and by which hashes match.
                    foreach($data[$chunk] as $modName => &$modData) {
                        if (empty($moduleFilter) || in_array($modName,$moduleFilter)) {
                            $hashesOnly[$chunk][$modName]['_hash'] = $data[$chunk][$modName]['_hash'];
                        }
                    }
                }
            }

            $data = $hashesOnly;

        } else {
            // The client is being bossy and wants some data as well.
            foreach ( $baseChunks as $chunk ) {
                if (!in_array($chunk,$this->typeFilter)
                    || (isset($args[$chunk]) && $args[$chunk] == $data[$chunk]['_hash'])) {
                    unset($data[$chunk]);
                }
            }

            // Relationships are special, they are a baseChunk but also need to pay attention to modules
            if (!empty($moduleFilter) && isset($data['relationships']) ) {
                // We only want some modules, but we want the relationships
                foreach ($data['relationships'] as $relName => $relData ) {
                    if ( $relName == '_hash' ) {
                        continue;
                    }
                    if (!in_array($relData['rhs_module'],$moduleFilter)
                        && !in_array($relData['lhs_module'],$moduleFilter)) {
                        unset($data['relationships'][$relName]);
                    }
                    else { $data['relationships'][$relName]['checked'] = 1; }
                }
            }

            foreach ( $perModuleChunks as $chunk ) {
                if (!in_array($chunk, $this->typeFilter)) {
                    unset($data[$chunk]);
                } else {
                    // We want modules, let's filter by the requested modules and by which hashes match.
                    foreach($data[$chunk] as $modName => &$modData) {
                        if ((!empty($moduleFilter) && !in_array($modName,$moduleFilter))
                            || (isset($args[$chunk][$modName]) && $args[$chunk][$modName] == $modData['_hash'])) {
                            unset($data[$chunk][$modName]);
                            continue;
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Gets configs
     *
     * @return array
     */
    protected function getConfigs() {
        $configs = array();

        // As of now configs are only for portal
        return $configs;
    }


    /**
     * Fills in additional app list strings data as needed by the client
     *
     * @param array $public Public app list strings
     * @param array $main Core app list strings
     * @return array
     */
    protected function fillInAppListStrings(Array $public, Array $main) {
        return $public;
    }

    /**
     * Gets the list of modules for this client
     *
     * @return array
     */
    protected function getModules() {
        // Loading a standard module list
        return array_keys($GLOBALS['app_list_strings']['moduleList']);
    }

    /**
     * Gets the cleaned up list of modules for this client
     * @return array
     */
    public function getModuleList() {
        $moduleList = $this->getModules();
        $oldModuleList = $moduleList;
        $moduleList = array();
        foreach ( $oldModuleList as $module ) {
            $moduleList[$module] = $module;
        }

        $moduleList['_hash'] = md5(serialize($moduleList));
        return $moduleList;
    }


    /**
     * Gets full module list and data for each module.
     * Precondition: $data['module_list'] must already be populated.
     *
     * @param array $data load metadata array
     * @return array
     */
    private function _populateModules($data) {
        $mm = $this->getMetadataManager();
        $data['full_module_list'] = $this->getModuleList();
        $data['modules'] = array();
        foreach($data['full_module_list'] as $module) {
            $bean = BeanFactory::newBean($module);
            $data['modules'][$module] = $mm->getModuleData($module);
            $this->_relateFields($data, $module, $bean);
        }
        $data['module_list'] = $this->getDisplayModules($data['full_module_list']);

        return $data;
    }

    /**
     * Loads relationships for relate and link type fields
     * @param array $data load metadata array
     * @return array
     */
    private function _relateFields($data, $module, $bean) {
        if (isset($data['modules'][$module]['fields'])) {
            $fields = $data['modules'][$module]['fields'];

            foreach($fields as $fieldName => $fieldDef) {

                // Load and assign any relate or link type fields
                if (isset($fieldDef['type']) && ($fieldDef['type'] == 'relate')) {
                    if (isset($fieldDef['module']) && !in_array($fieldDef['module'], $data['full_module_list'])) {
                        $data['full_module_list'][$fieldDef['module']] = $fieldDef['module'];
                    }
                } elseif (isset($fieldDef['type']) && ($fieldDef['type'] == 'link')) {
                    $bean->load_relationship($fieldDef['name']);
                    $otherSide = $bean->$fieldDef['name']->getRelatedModuleName();
                    $data['full_module_list'][$otherSide] = $otherSide;
                }
            }
        }
    }

    /**
     * Gets mod strings
     *
     * @param array $data The metadata array
     * @return array
     */
    public function getModStrings($data) {
        $mm = $this->getMetadataManager();
        $modStrings = array();
        foreach ($data['modules'] as $modName => $moduleDef) {
            $modData = $mm->getModuleStrings($modName);
            $modStrings[$modName] = $modData;
            $modStrings[$modName]['_hash'] = md5(serialize($modStrings[$modName]));
        }
        return $modStrings;
    }

    /**
     * Gets currencies
     * @return array
     */
    public function getSystemCurrencies() {
        $currencies = array();
        require_once('modules/Currencies/ListCurrency.php');
        $lcurrency = new ListCurrency();
        $lcurrency->lookupCurrencies();
        if(!empty($lcurrency->list))
        {
            foreach($lcurrency->list as $current)
            {
                $currency = array();
                $currency['name'] = $current->name;
                $currency['iso'] = $current->iso4217;
                $currency['status'] = $current->status;
                $currency['symbol'] = $current->symbol;
                $currency['rate'] = $current->conversion_rate;
                $currency['name'] = $current->name;
                $currency['date_entered'] = $current->date_entered;
                $currency['date_modified'] = $current->date_modified;
                $currencies[$current->id] = $currency;
            }
        }
        return $currencies;
    }
    /**
     * Cleans up the module list for any modules that should not be on it
     *
     * @param array $module_list The module list array
     * @return array
     */
    protected function cleanUpModuleList($module_list) {
        // remove the disabled modules from the module list
        require_once("modules/MySettings/TabController.php");
        $controller = new TabController();
        $tabs = $controller->get_tabs_system();

        if (isset($tabs[1])) {
            foreach($module_list as $moduleKey => $moduleName){
                if (in_array($moduleName,$tabs[1])) {
                    unset($module_list[$moduleKey]);
                }
            }
        }

        return $module_list;
    }

    /**
     * Reconciles which platforms jssource components will be built for. 
     * @param array $args request args
     * @return array
     */
    private function getJSSourcePlatforms($args) {
        $arrJSSourcePlatforms = array();
        // If no jssource_filter supplied, jssourceFilter starts with all platforms loaded (jssource
        // components will be built for both their application's platform (e.g. portal) AND base.
        // Clients should only set this filter if their widgets do NOT extendFrom base widgets.
        if (!empty($args['jssource_filter']) ) {
            $platforms = explode(",", $args['jssource_filter']);
            if ($platforms != false) {
                $arrJSSourcePlatforms = $platforms;
            }
        } else {
            $arrJSSourcePlatforms = $this->platforms;
        }
        return $arrJSSourcePlatforms;
    }

    //TODO: This function needs to be in /me as it is user defined
    protected function getDisplayModules($moduleList)
    {
        global $app_list_strings;
        $ret = $moduleList;
        if (!empty($this->user))
        {
            // Loading a standard module list
            require_once("modules/MySettings/TabController.php");
            $controller = new TabController();
            $ret = array_intersect_key($controller->get_user_tabs($this->user), $moduleList);
            foreach($ret as $mod => $lbl)
            {
                if (!empty($app_list_strings['moduleList'][$mod])){
                    $ret[$mod] = $app_list_strings['moduleList'][$mod];
                }
            }
        }

        return $ret;

    }

}
