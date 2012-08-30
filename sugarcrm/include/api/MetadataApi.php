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
        global $current_language, $app_strings, $current_user;
        // get the currrent person object of interest
        $apiPerson = $current_user;
        if (isset($_SESSION['type']) && $_SESSION['type'] == 'support_portal') {
            $apiPerson = BeanFactory::getBean('Contacts', $_SESSION['contact_id']);
        }

        // asking for a specific language
        if (isset($args['lang']) && !empty($args['lang'])) {
            $lang = $args['lang'];
            $current_language = $lang;
            $app_strings = return_application_language($lang);
        // load prefs if set
        } elseif (isset($apiPerson->preferred_language) && !empty($apiPerson->preferred_language)) {
            $app_strings = return_application_language($apiPerson->preferred_language);
            $current_language = $apiPerson->preferred_language;
        }

        // Default the type filter to everything
        $this->typeFilter = array('modules','fullModuleList','fields','viewTemplates','labels','modStrings','appStrings','appListStrings','acl','moduleList', 'views', 'layouts','relationships');
        if ( !empty($args['typeFilter']) ) {
            // Explode is fine here, we control the list of types
            $types = explode(",", $args['typeFilter']);
            if ($types != false) {
                $this->typeFilter = $types;
            }
        }

        $moduleFilter = array();
        if (!empty($args['moduleFilter'])) {
            // Use str_getcsv here so that commas can be escaped, I pity the fool that has commas in his module names.
            $modules = str_getcsv($args['moduleFilter'],',','');
            if ( $modules != false ) {
                $moduleFilter = $modules;
            }
        }

        $onlyHash = false;
        if (!empty($args['onlyHash']) && ($args['onlyHash'] == 'true' || $args['onlyHash'] == '1')) {
            $onlyHash = true;
        }


        $this->user = $GLOBALS['current_user'];

        if ( isset($args['platform']) ) {
            $this->platforms = array(basename($args['platform']),'base');
        } else {
            $this->platforms = array('base');
        }

        $data = array();

        $hashKey = "metadata:" . implode(",", $this->platforms) . ":hash";
        //First check if the hash is cached so we don't have to load the metadata manually to calculate it
        $hash = sugar_cache_retrieve($hashKey);
        //If it was, check if the client has the same version cached
        if (!empty($hash)) {
            generateETagHeader($hash);
            //If we got here without dying, the client doesn't have the metadata cached.
            //First check if we have the metadata contents cached in a file
            $cacheFile = sugar_cached("api/metadata/$hash");
            if (file_exists($cacheFile)) {
                //$data will be populated by the include
                include($cacheFile);
            }
        }

        //If we failed to load the metadat from cache, load it now the hard way.
        if (empty($data))
            $data = $this->loadMetadata($hashKey);

        //If we had to generate a new hash, create the etag with the new hash
        if (empty($hash))
            generateETagHeader($data['_hash']);

        $baseChunks = array('viewTemplates','fields','appStrings','appListStrings','moduleList', 'views', 'layouts', 'fullModuleList','relationships');
        $perModuleChunks = array('modules','modStrings','acl');

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
            $args['platform'] = 'forecasts' ? 'base' : $args['platform'];
            $prefix = "{$args['platform']}_";
            $admin = new Administration();
            $category = $args['platform'];
            $admin->retrieveSettings($category, true);
            foreach($admin->settings AS $setting_name => $setting_value) {
                if(stristr($setting_name, $prefix)) {
                    $key = str_replace($prefix, '', $setting_name);
                    $configs[$key] = json_decode(html_entity_decode($setting_value));
                }
            }
        }

        global $current_language, $app_strings, $app_list_strings;
        $lang = isset($args['lang']) ? $args['lang'] : "en_us";
        $current_language = $lang;
        $app_strings = return_application_language($lang);
        $app_list_strings = return_app_list_strings_language($lang);


        // Default the type filter to everything available to the public, no module info at this time
        $this->typeFilter = array('fields','viewTemplates','appStrings','views', 'layouts', 'config', 'modules');

        if ( !empty($args['typeFilter']) ) {
            // Explode is fine here, we control the list of types
            $types = explode(",", $args['typeFilter']);
            if ($types != false) {
                $this->typeFilter = $types;
            }
        }

        $onlyHash = false;

        if (!empty($args['onlyHash']) && ($args['onlyHash'] == 'true' || $args['onlyHash'] == '1')) {
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
        if (isset($args['platform']) && $args['platform'] == 'portal') {
            $app_list_strings_public['countries_dom'] = $app_list_strings['countries_dom'];
            $app_list_strings_public['state_dom'] = $app_list_strings['state_dom'];
        }

        // Start collecting data
        $data = array();

        $data['fields']  = $mm->getSugarClientFiles('field');
        $data['views']   = $mm->getSugarClientFiles('view');
        $data['layouts'] = $mm->getSugarLayouts();
        $data['viewTemplates'] = $mm->getViewTemplates();
        $data['appStrings'] = $mm->getAppStrings();
        $data['appListStrings'] = $app_list_strings_public;
        $data['config'] = $configs;
        $data['modules'] = array(
            "Login" => array("fields" => array()));
        $data["_hash"] = md5(serialize($data));

        $baseChunks = array('viewTemplates','fields','appStrings','views', 'layouts', 'config', 'modules');

        return $this->filterResults($args, $data, $onlyHash, $baseChunks);
    }

    protected function loadMetadata($hashKey) {
        // Start collecting data
        $data = array();

        $mm = $this->getMetadataManager();

        $this->modules = array_keys(get_user_module_list($this->user));

        $data['modules'] = array();
        foreach ($this->modules as $modName) {
            $modData = $mm->getModuleData($modName);
            $data['modules'][$modName] = $modData;
        }


        $data['moduleList'] = $mm->getModuleList($this->platforms[0]);
        $data['fullModuleList'] = $data['moduleList'];
        foreach($data['moduleList'] as $module) {
            $bean = BeanFactory::newBean($module);
            if (isset($data['modules'][$module]['fields'])) {
                $fields = $data['modules'][$module]['fields'];
                foreach($fields as $fieldName => $fieldDef) {
                    if (isset($fieldDef['type']) && ($fieldDef['type'] == 'relate')) {
                        if (isset($fieldDef['module']) && !in_array($fieldDef['module'], $data['fullModuleList'])) {
                            $data['fullModuleList'][$fieldDef['module']] = $fieldDef['module'];
                        }
                    } elseif (isset($fieldDef['type']) && ($fieldDef['type'] == 'link')) {
                        $bean->load_relationship($fieldDef['name']);
                        $otherSide = $bean->$fieldDef['name']->getRelatedModuleName();
                        $data['fullModuleList'][$otherSide] = $otherSide;
                    }
                }
            }
        }

        foreach($data['modules'] as $moduleName => $moduleDef) {
            if (!array_key_exists($moduleName, $data['fullModuleList']) && array_key_exists($moduleName, $data['modules'])) {
                unset($data['modules'][$moduleName]);
            }
        }

        $data['modStrings'] = array();
        foreach ($data['modules'] as $modName => $moduleDef) {
            $modData = $mm->getModuleStrings($modName);
            $data['modStrings'][$modName] = $modData;
            $data['modStrings'][$modName]['_hash'] = md5(serialize($data['modStrings'][$modName]));
        }

        $data['acl'] = array();
        foreach ($this->modules as $modName) {
            $data['acl'][$modName] = $mm->getAclForModule($modName,$GLOBALS['current_user']->id);
            // Modify the ACL's for portal, this is a hack until "create" becomes a real boy.
            if(isset($_SESSION['type'])&&$_SESSION['type']=='support_portal') {
                $data['acl'][$modName]['admin'] = 'no';
                $data['acl'][$modName]['developer'] = 'no';
                $data['acl'][$modName]['edit'] = 'no';
                $data['acl'][$modName]['delete'] = 'no';
                $data['acl'][$modName]['import'] = 'no';
                $data['acl'][$modName]['export'] = 'no';
                $data['acl'][$modName]['massupdate'] = 'no';
            }
        }

        if (isset($_SESSION['type']) && $_SESSION['type']=='support_portal') {
            $apiPerson = BeanFactory::getBean('Contacts', $_SESSION['contact_id']);
            // This is a change in the ACL's for users without Accounts
            $vis = new SupportPortalVisibility($apiPerson);
            $accounts = $vis->getAccountIds();
            if (count($accounts)==0) {
                // This user has no accounts, modify their ACL's so that they match up with enforcement
                $data['acl']['Accounts']['access'] = 'no';
                $data['acl']['Cases']['access'] = 'no';
            }
        
        }

        // remove the disabled modules from the module list
        require_once("modules/MySettings/TabController.php");
        $controller = new TabController();
        $tabs = $controller->get_tabs_system();

        if (isset($tabs[1])) {
            foreach($data['moduleList'] as $moduleKey => $moduleName){
                if (in_array($moduleName,$tabs[1])) {
                    unset($data['moduleList'][$moduleKey]);
                }
            }
        }

        $data['fields']  = $mm->getSugarClientFiles('field');
        $data['views']   = $mm->getSugarClientFiles('view');
        $data['layouts'] = $mm->getSugarClientFiles('layout');
        $data['viewTemplates'] = $mm->getViewTemplates();
        $data['appStrings'] = $mm->getAppStrings();
        $data['appListStrings'] = $mm->getAppListStrings();
        $data['relationships'] = $mm->getRelationshipData();
        $hash = md5(serialize($data));
        $data["_hash"] = $hash;

        //Cache the result to the filesystem
        $cacheFile = sugar_cached("api/metadata/$hash");
        create_cache_directory("api/metadata/$hash");
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

}
