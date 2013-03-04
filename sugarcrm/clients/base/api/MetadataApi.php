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
        static $mm;
        if ( !isset($mm) ) {
            $mm = new MetaDataManager(null,$this->platforms, $public);
        }
        return $mm;
    }

    public function getAllMetadata(ServiceBase $api, array $args) {
        global $current_language, $app_strings, $app_list_strings, $current_user;

        // asking for a specific language
        if (isset($args['lang']) && !empty($args['lang'])) {
            $current_language = $args['lang'];
            $app_strings = return_application_language($current_language);
            $app_list_strings = return_app_list_strings_language($current_language);

        }        
        // Default the type filter to everything
        $this->typeFilter = array('modules','full_module_list','fields', 'labels', 'module_list', 'views', 'layouts','relationships','currencies', 'jssource', 'server_info');
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


        $this->setPlatformList($api);


        $data = $this->getMetadataCache($this->platforms[0],false);

        //If we failed to load the metadata from cache, load it now the hard way.
        if (empty($data)) {
            ini_set('max_execution_time', 0);
            $data = $this->loadMetadata();
            $this->putMetadataCache($data, $this->platforms[0], false);
        }
        
        // Bug 60345 - Default currency id of -99 was failing hard on 64bit 5.2.X
        // PHP builds. This was causing metadata to store a different value in the 
        // cache than -99. The fix was to add a space arround the -99 to force it
        // to string. This trims that value prior to sending it to the client.
        $data = $this->normalizeCurrencyIds($data);

        generateETagHeader($data['_hash']);

        $baseChunks = array('fields','labels','module_list', 'views', 'layouts', 'full_module_list','relationships', 'currencies', 'jssource', 'server_info');
        $perModuleChunks = array('modules');
        
        return $this->filterResults($args, $data, $onlyHash, $baseChunks, $perModuleChunks, $moduleFilter);
    }

    // this is the function for the endpoint of the public metadata api.
    public function getPublicMetadata($api, $args) {
        $configs = array();

        // right now we are getting the config only for the portal
        // Added an isset check for platform because with no platform set it was
        // erroring out. -- rgonzalez
        $this->setPlatformList($api);

        // Default the type filter to everything available to the public, no module info at this time
        $this->typeFilter = array('fields','labels','views', 'layouts', 'config', 'jssource');

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

        $data = $this->getMetadataCache($this->platforms[0],true);
        
        if ( empty($data) ) {
            // since this is a public metadata call pass true to the meta data manager to only get public/
            $mm = $this->getMetadataManager( TRUE );
            
            
            // Start collecting data
            $data = array();
            
            $data['fields']  = $mm->getSugarFields();
            $data['views']   = $mm->getSugarViews();
            $data['layouts'] = $mm->getSugarLayouts();
            $data['labels'] = $this->getStringUrls($data,true);
            $data['modules'] = array(
                "Login" => array("fields" => array()));
            $data['config']           = $this->getConfigs();
            $data['jssource']         = $this->buildJSFileFromMD($data, $this->platforms[0]);        
            $data["_hash"] = md5(serialize($data));
            
            $this->putMetadataCache($data, $this->platforms[0], TRUE);

        }
        generateETagHeader($data['_hash']);

        $baseChunks = array('fields','labels','views', 'layouts', 'config', 'jssource');

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

            $js .= "\n\t\"modules\":{";

            $allModuleJS = '';
            foreach($data['modules'] as $module => $def)
            {
                $moduleJS = $this->buildJSForComponents($def,true);
                if(!empty($moduleJS)) {
                    $allModuleJS .= ",\n\t\t\"$module\":{{$moduleJS}}";
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

        return $this->getUrlForCacheFile($path);
    }


    protected function buildJSForComponents(&$data, $isModule = false) {
        $js = "";
        $platforms = array_reverse($this->platforms);
        
        $typeData = array();
        
        if ( $isModule ) {
            $types = array('fieldTemplates', 'views', 'layouts'); 
        } else {
            $types = array('fields', 'views', 'layouts'); 
        }

        foreach($types as $mdType) {

            if (!empty($data[$mdType])){
                $platControllers = array();

                foreach($data[$mdType] as $name => $component) {
                    if ( !is_array($component) || !isset($component['controller']) ) {
                        continue;
                    }
                    $controllers = $component['controller'];

                    if (is_array($controllers) ) {
                        foreach ($platforms as $platform) {
                            if (!isset($controllers[$platform])) {
                                continue;
                            }
                            $controller = $controllers[$platform];
                            // remove additional symbols in end of js content - it will be included in content
                            $controller = trim(trim($controller), ",;");
                            $controller = $this->insertHeaderComment($controller, $mdType, $name, $platform);
                            
                            if ( !isset($platControllers[$platform]) ) { $platControllers[$platform] = array(); }
                            $platControllers[$platform][] = "\"$name\": {\"controller\": ".$controller." }";
                                
                        }
                    }
                    unset($data[$mdType][$name]['controller']);
                }
                

                // We should have all of the controllers for this type, split up by platform
                $thisTypeStr = "\"$mdType\": {\n";

                foreach ( $platforms as $platform ) {
                    if ( isset($platControllers[$platform]) ) {
                        $thisTypeStr .= "\"$platform\": {\n".implode(",\n",$platControllers[$platform])."\n},\n";
                    }
                }

                $thisTypeStr = trim($thisTypeStr,"\n,")."}\n";
                $typeData[] = $thisTypeStr;
            }
        }

        $js = implode(",\n",$typeData)."\n";
        
        return $js;
        
    }
    
    // Helper to insert header comments for controllers
    private function insertHeaderComment($controller, $mdType, $name, $platform) {
        $singularType = substr($mdType, 0, -1);
        $needle = '({';
        $headerComment = "\n\t// " . ucfirst($name) ." ". ucfirst($singularType) . " ($platform) \n";

        // Find position "after" needle
        $pos = (strpos($controller, $needle) + strlen($needle));

        // Insert our comment and return ammended controller
        return substr($controller, 0, $pos) . $headerComment . substr($controller, $pos);
    }

    protected function loadMetadata() {
        // Start collecting data
        $data = $this->_populateModules(array());
        $mm = $this->getMetadataManager();
        // TODO:
        // Sadly, it's now unclear what our abstraction is here. It should be that this class
        // is just for API stuff and $mm is for any metadata data operations. However, since
        // we now have child classes like MetadataPortalApi overriding getModules, etc., I'm
        // tentative to push the following three calls out to $mm. I propose refactor to instead
        // inherit as MetadataPortalDataManager and put all accessors, etc., there.
        $data['currencies'] = $this->getSystemCurrencies();
        
        foreach($data['modules'] as $moduleName => $moduleDef) {
            if (!array_key_exists($moduleName, $data['full_module_list']) && array_key_exists($moduleName, $data['modules'])) {
                unset($data['modules'][$moduleName]);
            }
        }

        $data['full_module_list']['_hash'] = md5(serialize($data['full_module_list']));

        $data['fields']  = $mm->getSugarFields();
        $data['views']   = $mm->getSugarViews();
        $data['layouts'] = $mm->getSugarLayouts();
        $data['labels'] = $this->getStringUrls($data,false);
        $data['relationships'] = $mm->getRelationshipData();
        $data['jssource'] = $this->buildJSFileFromMD($data, $this->platforms[0]);
        $data['server_info'] = $mm->getServerInfo();
        $hash = md5(serialize($data));
        $data["_hash"] = $hash;

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
     * Creates the list of platforms to build the metadata from
     * the standard function does [ "yourPlatform", "base" ]
     * You can override it in your platform specific API class if you want a different order
     *
     * @param ServiceBase $api The calling API class
     */
    protected function setPlatformList(ServiceBase $api)
    {
        if ( $api->platform != 'base' ) {
            $this->platforms = array(basename($api->platform),'base');
        } else {
            $this->platforms = array('base');
        }
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
     *
     * @param array $data load metadata array
     * @return array
     */
    public function _populateModules($data) {
        $mm = $this->getMetadataManager();
        $data['full_module_list'] = $this->getModuleList();
        $data['modules'] = array();
        foreach($data['full_module_list'] as $module) {
            $bean = BeanFactory::newBean($module);
            $data['modules'][$module] = $mm->getModuleData($module);
            $this->_relateFields($data, $module, $bean);
        }
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
                    if ( isset($bean->$fieldDef['name']) && method_exists($bean->$fieldDef['name'],'getRelatedModuleName') ) {
                        $otherSide = $bean->$fieldDef['name']->getRelatedModuleName();
                        $data['full_module_list'][$otherSide] = $otherSide;
                    }
                }
            }
        }
    }

    /**
     * Returns a list of URL's pointing to json-encoded versions of the strings
     *
     * @param array $data The metadata array
     * @return array
     */
    public function getStringUrls(&$data, $isPublic = false) {
        $mm = $this->getMetadataManager();

        $languageList = array_keys(get_languages());
        sugar_mkdir(sugar_cached('api/metadata'), null, true);

        $fileList = array();
        foreach ( $languageList as $language ) {            
            $stringData = array();
            $stringData['app_list_strings'] = $mm->getAppListStrings($language);
            $stringData['app_strings'] = $mm->getAppStrings($language);
            if ( $isPublic ) {
                // Exception for the AppListStrings.
                $app_list_strings_public = array();
                $app_list_strings_public['available_language_dom'] = $stringData['app_list_strings']['available_language_dom'];
                
                // Let clients fill in any gaps that may need to be filled in
                $app_list_strings_public = $this->fillInAppListStrings($app_list_strings_public, $stringData['app_list_strings'],$language);
                $stringData['app_list_strings'] = $app_list_strings_public;
                
            } else {
                $modStrings = array();
                foreach ($data['modules'] as $modName => $moduleDef) {
                    $modData = $mm->getModuleStrings($modName, $language);
                    $modStrings[$modName] = $modData;
                }
                $stringData['mod_strings'] = $modStrings;
            }
            // cast the app list strings to objects to make integer key usage in them consistent for the clients
            foreach ($stringData['app_list_strings'] as $listIndex => $listArray) {
                if (is_array($listArray) && !array_key_exists('',$listArray)) {
                    $stringData['app_list_strings'][$listIndex] = (object) $listArray;
                }
            }
            $stringData['_hash'] = md5(serialize($stringData));
            $fileList[$language] = sugar_cached('api/metadata/lang_'.$language.'_'.$stringData['_hash'].'.json');
            sugar_file_put_contents_atomic($fileList[$language],json_encode($stringData));
        }
        
        $urlList = array();
        foreach ( $fileList as $lang => $file ) {
            $urlList[$lang] = $this->getUrlForCacheFile($file);
        }

        // We need the default language somewhere, how about here?
        $urlList['default'] = $GLOBALS['sugar_config']['default_language'];
        $urlList['_hash'] = md5(serialize($urlList));

        return $urlList;
    }

    public function getUrlForCacheFile($cacheFile) {
        // This is here so we can override it and have the cache files upload to a CDN
        // and return the CDN locations later.
        return $GLOBALS['sugar_config']['site_url'].'/'.$cacheFile;
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
                $currency['iso4217'] = $current->iso4217;
                $currency['status'] = $current->status;
                $currency['symbol'] = $current->symbol;
                $currency['conversion_rate'] = $current->conversion_rate;
                $currency['name'] = $current->name;
                $currency['date_entered'] = $current->date_entered;
                $currency['date_modified'] = $current->date_modified;
                
                // Bug 60345 - Default currency id of -99 was failing hard on 64bit 5.2.X
                // PHP builds when writing to the cache because of how PHP was
                // handling negative int array indexes. This was causing metadata 
                // to store a different value in the cache than -99. The fix was 
                // to add a space arround the -99 to force it to string.
                $id = $current->id == -99 ? '-99 ': $current->id;
                $currencies[$id] = $currency;
            }
        }
        return $currencies;
    }

    protected function putMetadataCache($data, $platform, $isPublic)
    {
        if ( $isPublic ) {
            $type = 'public';
        } else {
            $type = 'private';
        }
        $cacheFile = sugar_cached('api/metadata/metadata_'.$platform.'_'.$type.'.php');
        create_cache_directory($cacheFile);
        write_array_to_file('metadata', $data, $cacheFile);
    }

    protected function getMetadataCache($platform, $isPublic)
    {
        if ( inDeveloperMode() ) {
            return null;
        }

        if ( $isPublic ) {
            $type = 'public';
        } else {
            $type = 'private';
        }
        $cacheFile = sugar_cached('api/metadata/metadata_'.$platform.'_'.$type.'.php');
        if ( file_exists($cacheFile) ) {
            require $cacheFile;
            return $metadata;
        } else {
            return null;
        }
    }

    /**
     * Accessor to the metadata manager cache cleaner
     */
    public function clearMetadataCache()
    {
        MetaDataManager::clearAPICache();
    }

    /**
     * Bug 60345
     * 
     * Normalizes the -99 currency id to remove the space added to the index prior
     * to storing in the cache.
     * 
     * @param array $data The metadata
     * @return array
     */
    protected function normalizeCurrencyIds($data) {
        if (isset($data['currencies']['-99 '])) {
            // Change the spaced index back to normal
            $data['currencies']['-99'] = $data['currencies']['-99 '];
            
            // Ditch the spaced index
            unset($data['currencies']['-99 ']);
        }
        
        return $data;
    }
}
