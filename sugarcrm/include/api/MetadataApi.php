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
        global $current_language, $app_strings, $app_list_strings, $current_user;

        // asking for a specific language
        if (isset($args['lang']) && !empty($args['lang'])) {
            $current_language = $args['lang'];
            $app_strings = return_application_language($current_language);
            $app_list_strings = return_app_list_strings_language($current_language);

        }

        // Default the type filter to everything
        $this->typeFilter = array('modules','full_module_list','fields','view_templates','labels','mod_strings','app_strings','app_list_strings','acl','module_list', 'views', 'layouts','relationships');
        if ( !empty($args['type_filter']) ) {
            // Explode is fine here, we control the list of types
            $types = explode(",", $args['type_filter']);
            if ($types != false) {
                $this->typeFilter = $types;
            }
        }

        $moduleFilter = array();
        if (!empty($args['module_filter'])) {
            // Use str_getcsv here so that commas can be escaped, I pity the fool that has commas in his module names.
            $modules = str_getcsv($args['module_filter'],',','');
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

        $baseChunks = array('view_templates','fields','app_strings','app_list_strings','module_list', 'views', 'layouts', 'full_module_list','relationships');
        $perModuleChunks = array('modules','mod_strings','acl');

        return $this->filterResults($args, $data, $onlyHash, $baseChunks, $perModuleChunks, $moduleFilter);
    }

    // this is the function for the endpoint of the public metadata api.
    public function getPublicMetadata($api, $args) {
        $configs = array();

        // right now we are getting the config only for the portal
        // Added an isset check for platform because with no platform set it was
        // erroring out. -- rgonzalez
        if(isset($args['platform']) && $args['platform'] == 'portal') {

            $admin = new Administration();
            $admin->retrieveSettings();
            foreach($admin->settings AS $setting_name => $setting_value) {
                if(stristr($setting_name, 'portal_')) {
                    $key = str_replace('portal_', '', $setting_name);
                    $configs[$key] = json_decode(html_entity_decode($setting_value));
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
        $this->typeFilter = array('fields','view_templates','app_strings','views', 'layouts', 'config');

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
        if (isset($args['platform']) && $args['platform'] == 'portal') {
            $app_list_strings_public['countries_dom'] = $app_list_strings['countries_dom'];
            $app_list_strings_public['state_dom'] = $app_list_strings['state_dom'];
        }

        // Start collecting data
        $data = array();

        $data['fields']  = $mm->getSugarClientFiles('field');
        $data['views']   = $mm->getSugarClientFiles('view');
        $data['layouts'] = $mm->getSugarLayouts();
        $data['view_templates'] = $mm->getViewTemplates();
        $data['app_strings'] = $mm->getAppStrings();
        $data['app_list_strings'] = $app_list_strings_public;
        $data['config'] = $configs;
        $md5 = serialize($data);
        $md5 = md5($md5);
        $data["_hash"] = md5(serialize($data));

        $baseChunks = array('view_templates','fields','app_strings','views', 'layouts', 'config');

        return $this->filterResults($args, $data, $onlyHash, $baseChunks);
    }

    protected function loadMetadata($hashKey) {
        // Start collecting data
        $data = array();

        $mm = $this->getMetadataManager();


        $data['module_list'] = $mm->getModuleList($this->platforms[0]);
        $data['full_module_list'] = $data['module_list'];

        $data['modules'] = array();

        foreach($data['full_module_list'] as $module) {
            $bean = BeanFactory::newBean($module);
            if (!$bean || !is_a($bean,'SugarBean') ) {
                // There is no bean, we can't get data on this
                continue;
            }

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
            if (!array_key_exists($moduleName, $data['full_module_list'])) {
                unset($data['modules'][$moduleName]);
            }
        }

        $data['mod_strings'] = array();
        foreach ($data['modules'] as $modName => $moduleDef) {
            $modData = $mm->getModuleStrings($modName);
            $data['mod_strings'][$modName] = $modData;
            $data['mod_strings'][$modName]['_hash'] = md5(serialize($data['mod_strings'][$modName]));
        }

        $data['acl'] = array();

        foreach ($data['full_module_list'] as $modName) {
            $bean = BeanFactory::newBean($modName);
            if (!$bean || !is_a($bean,'SugarBean') ) {
                // There is no bean, we can't get data on this
                continue;
            }
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

        // populate available system currencies
        $data['currencies'] = array();
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
                $data['currencies'][$current->id] = $currency;
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
            foreach($data['module_list'] as $moduleKey => $moduleName){
                if (in_array($moduleName,$tabs[1])) {
                    unset($data['module_list'][$moduleKey]);
                }
            }
        }

        $data['fields']  = $mm->getSugarClientFiles('field');
        $data['views']   = $mm->getSugarClientFiles('view');
        $data['layouts'] = $mm->getSugarClientFiles('layout');
        $data['view_templates'] = $mm->getViewTemplates();
        $data['app_strings'] = $mm->getAppStrings();
        $data['app_list_strings'] = $mm->getAppListStrings();
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

}
