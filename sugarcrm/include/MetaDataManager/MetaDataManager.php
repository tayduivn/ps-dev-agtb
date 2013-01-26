<?php
if(!defined('sugarEntry'))define('sugarEntry', true);
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('soap/SoapHelperFunctions.php');
require_once 'modules/ModuleBuilder/parsers/MetaDataFiles.php';
require_once 'include/SugarFields/SugarFieldHandler.php';

/**
 * This class is for access metadata for all sugarcrm modules in a read only
 * state.  This means that you can not modifiy any of the metadata using this
 * class currently.
 *
 *
 * @method Array getData getData() gets all meta data.
 *
 *
 *  "platform": is a bool value which lets you know if the data is for a mobile view, portal or not.
 *
 */
class MetaDataManager {
    /**
     * SugarFieldHandler, to assist with cleansing default sugar field values
     *
     * @var SugarFieldHandler
     */
    protected $sfh;

    /**
     * A collection of metadata managers for use as a simple cache in the factory.
     * 
     * @var array
     */
    protected static $managers = array();

    /**
     * Whether this is a public or private request
     * 
     * @var bool
     */
    protected $public = false;

    /**
     * Visibility type indicator
     * @var string
     */
    protected $visibility = 'private';

    /**
     * Sections of the metadata based on visibility
     * 
     * @var array
     */
    protected static $sections = array(
        'private' => array('modules','full_module_list','fields', 'labels', 'module_list', 'views', 'layouts','relationships','currencies', 'jssource', 'server_info'),
        'public'  => array('fields','labels','views', 'layouts', 'config', 'jssource'),
    );

    /**
     * Mapping of metadata sections to the methods that get the data for that
     * section. If the value of the section index is false, the method will be
     * build{section}() and will require the current metadata array as an argument.
     * 
     * @var array
     */
    protected $sectionMap = array(
        'modules' => false,
        'full_module_list' => 'getModuleList',
        'fields' => 'getSugarFields',
        'labels' => false,
        'views' =>'getSugarViews', 
        'layouts' => 'getSugarLayouts',
        'relationships' => 'getRelationshipData',
        'currencies' => 'getSystemCurrencies', 
        'jssource' => false, 
        'server_info' => 'getServerInfo', 
        'config' => 'getConfigs',
    );

    /**
     * The constructor for the class.
     *
     * @param array $platforms A list of clients
     * @param bool $public is this a public metadata grab
     */
    function __construct ($platforms = null, $public = false) {
        if ( $platforms == null ) {
            $platforms = array('base');
        }
        
        $this->platforms = $platforms;
        $this->public = $public;
        
        if ($public) {
            $this->visibility = 'public';
        }
    }

    /**
     * Simple factory for getting a metadata manager
     * 
     * @param string $platform The platform for the metadata
     * @param bool $public Public or private
     * @param bool $fresh Whether to skip the cache and get a new manager
     * @return MetaDataManager
     */
    public static function getManager($platform = null, $public = false, $fresh = false) {
        if ( $platform == null ) {
            $platform = array('base');
        }
        $platform = (array) $platform;
        
        // Get the platform metadata class name
        $class = 'MetaDataManager';
        $path  = 'include/MetaDataManager/';
        $found = false;
        foreach ($platform as $type) {
            $mmClass = $class . ucfirst(strtolower($type));
            $file = $path . $mmClass . '.php';
            if (SugarAutoLoader::requireWithCustom($file)) {
                $class = SugarAutoLoader::customClass($mmClass);
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            SugarAutoLoader::requireWithCustom($path . $class . '.php');
            $class = SugarAutoLoader::customClass($class);
        }
        
        // Build a simple key
        $key = implode(':', $platform) . ':' . intval($public);
        
        if ($fresh || empty(self::$managers[$key])) {
            // TODO: employ logic here to make platform specific managers
            $manager = new $class($platform, $public);
            
            // If this is a fresh manager request, send it back without caching
            if ($fresh) {
                return $manager;
            }
            
            // Cache it and move on
            self::$managers[$key] = $manager;
        }
        
        return self::$managers[$key];
    }

    /**
     * Gets a fresh metadata manager, bypassing the cache if there is one.
     * 
     * @param string $platform The platform for the metadata
     * @param bool $public Public or private
     * @return MetaDataManager
     */
    public static function getManagerNew($platform = null, $public = false) {
        return self::getManager($platform, $public, true);
    }

    /**
     * Gets a list of metadata sections based on visibility
     * 
     * @param bool $public Public flag
     * @return array
     */
    public static function getSections($public = false) {
        $type = $public ? 'public' : 'private';
        return self::$sections[$type];
    }
    
    /**
     * For a specific module get any existing Subpanel Definitions it may have
     * @param string $moduleName
     * @return array
     */
    public function getSubpanelDefs($moduleName)
    {
        require_once('include/SubPanel/SubPanelDefinitions.php');
        $parent_bean = BeanFactory::getBean($moduleName);
        //Hack to allow the SubPanelDefinitions class to check the correct module dir
        if (!$parent_bean){
            $parent_bean = (object) array('module_dir' => $moduleName);
        }

        $spd = new SubPanelDefinitions($parent_bean);
        $layout_defs = $spd->layout_defs;

        if(is_array($layout_defs) && isset($layout_defs['subpanel_setup']))
        {
            foreach($layout_defs['subpanel_setup'] AS $name => $subpanel_info)
            {
                $aSubPanel = $spd->load_subpanel($name, '', $parent_bean);

                if(!$aSubPanel)
                {
                    continue;
                }

                if($aSubPanel->isCollection())
                {
                    $collection = array();
                    foreach($aSubPanel->sub_subpanels AS $key => $subpanel)
                    {
                        $collection[$key] = $subpanel->panel_definition;
                    }
                    $layout_defs['subpanel_setup'][$name]['panel_definition'] = $collection;
                }
                else
                {
                    $layout_defs['subpanel_setup'][$name]['panel_definition'] = $aSubPanel->panel_definition;
                }

            }
        }

        return $layout_defs;
    }

    /**
     * This method collects all view data for a module
     *
     * @param $moduleName The name of the sugar module to collect info about.
     *
     * @return Array A hash of all of the view data.
     */
    public function getModuleViews($moduleName) {
        return $this->getModuleClientData('view',$moduleName);
    }

    /**
     * This method collects all view data for a module
     *
     * @param $moduleName The name of the sugar module to collect info about.
     *
     * @return Array A hash of all of the view data.
     */
    public function getModuleLayouts($moduleName) {
        return $this->getModuleClientData('layout', $moduleName);
    }

    /**
     * This method collects all field data for a module
     *
     * @param string $moduleName    The name of the sugar module to collect info about.
     *
     * @return Array A hash of all of the view data.
     */
    public function getModuleFields($moduleName) {
        return $this->getModuleClientData('field', $moduleName);
    }

    /**
     * The collector method for modules.  Gets metadata for all of the module specific data
     *
     * @param $moduleName The name of the module to collect metadata about.
     * @return array An array of hashes containing the metadata.  Empty arrays are
     * returned in the case of no metadata.
     */
    public function getModuleData($moduleName) {
        //BEGIN SUGARCRM flav=pro ONLY
        require_once('include/SugarSearchEngine/SugarSearchEngineMetadataHelper.php');
        //END SUGARCRM flav=pro ONLY
        $vardefs = $this->getVarDef($moduleName);

        $data['fields'] = isset($vardefs['fields']) ? $vardefs['fields'] : array();
        $data['views'] = $this->getModuleViews($moduleName);
        $data['layouts'] = $this->getModuleLayouts($moduleName);
        $data['fieldTemplates'] = $this->getModuleFields($moduleName);
        $data['subpanels'] = $this->getSubpanelDefs($moduleName);
        $data['config'] = $this->getModuleConfig($moduleName);

        //BEGIN SUGARCRM flav=pro ONLY
        $data['ftsEnabled'] = SugarSearchEngineMetadataHelper::isModuleFtsEnabled($moduleName);
        //END SUGARCRM flav=pro ONLY

        $seed = BeanFactory::newBean($moduleName);

        //BEGIN SUGARCRM flav=pro ONLY
        if ($seed !== false) {
            $favoritesEnabled = ($seed->isFavoritesEnabled() !== false) ? true : false;
            $data['favoritesEnabled'] = $favoritesEnabled;
        }
        //END SUGARCRM flav=pro ONLY

        $data["_hash"] = md5(serialize($data));

        return $data;
    }

    /**
     * Get the config for a specific module from the Administration Layer
     *
     * @param string $moduleName        The Module we want the data back for.
     * @return array
     */
    public function getModuleConfig($moduleName) {
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        return $admin->getConfigForModule($moduleName, $this->platforms[0]);
    }

    /**
     * The collector method for relationships.
     *
     * @return array An array of relationships, indexed by the relationship name
     */
    public function getRelationshipData() {
        require_once('data/Relationships/RelationshipFactory.php');
        $relFactory = SugarRelationshipFactory::getInstance();

        $data = $relFactory->getRelationshipDefs();
        foreach ( $data as $relKey => $relData ) {
            unset($data[$relKey]['table']);
            unset($data[$relKey]['fields']);
            unset($data[$relKey]['indices']);
            unset($data[$relKey]['relationships']);
        }

        $data["_hash"] = md5(serialize($data));

        return $data;
    }

    /**
     * Gets vardef info for a given module.
     *
     * @param $moduleName The name of the module to collect vardef information about.
     * @return array The vardef's $dictonary array.
     */
    public function getVarDef($moduleName) {

        require_once("data/BeanFactory.php");
        $obj = BeanFactory::getObjectName($moduleName);

        require_once("include/SugarObjects/VardefManager.php");
        global $dictionary;
        VardefManager::loadVardef($moduleName, $obj);
        if ( isset($dictionary[$obj]) ) {
            $data = $dictionary[$obj];
        }

        // vardefs are missing something, for consistancy let's populate some arrays
        if (!isset($data['fields']) ) {
            $data['fields'] = array();
        }

        // Bug 56505 - multiselect fields default value wrapped in '^' character
        $data['fields'] = $this->normalizeFielddefs($data['fields']);

        if (!isset($data['relationships'])) {
            $data['relationships'] = array();
        }

        // loop over the fields to find if they can be sortable
        // get the indexes on the module and the first field of each index
        $indexes = array();
        if(isset($data['indices'])) {
            foreach($data['indices'] AS $index) {
                if(isset($index['fields'][0]))
                {
                    $indexes[$index['fields'][0]] = $index['fields'][0];
                }
            }
        }

        // If sortable isn't already set THEN
        //      Set it sortable to TRUE, if the field is indexed.
        //      Set sortable to FALSE, otherwise. (Bug56943, Bug57644)
        $isIndexed = !empty($indexes);
        foreach($data['fields'] AS $field_name => $info) {
            if(!isset($data['fields'][$field_name]['sortable'])){
                $data['fields'][$field_name]['sortable'] = false;
                if($isIndexed && isset($indexes[$field_name])) {
                    $data['fields'][$field_name]['sortable'] = true;
                }
            }
        }

        return $data;
    }

    /**
     * Gets the ACL's for the module, will also expand them so the client side of the ACL's don't have to do as many checks.
     *
     * @param string $module The module we want to fetch the ACL for
     * @param object $userObject The user object for the ACL's we are retrieving.
     * @param object|bool $bean The SugarBean for getting specific ACL's for a module
     * @return array Array of ACL's, first the action ACL's (access, create, edit, delete) then an array of the field level acl's
     */
    public function getAclForModule($module,$userObject,$bean=false) {
        $obj = BeanFactory::getObjectName($module);

        $outputAcl = array('fields'=>array());

        if (!SugarACL::moduleSupportsACL($module)) {
            foreach ( array('admin', 'access','create', 'view','list','edit','delete','import','export','massupdate') as $action ) {
                $outputAcl[$action] = 'yes';
            }
        } else {
            $context = array(
                    'user' => $userObject,
                );
            
            // if the bean is not set, or a new bean.. set the owner override
            // this will allow fields marked Owner to pass through ok.
            if($bean == false || empty($bean->id) || (!empty($bean->new_with_id))) {
                $context['owner_override'] = true;
            }

            if($bean instanceof SugarBean) {
                $context['bean'] = $bean;
            }

            $moduleAcls = SugarACL::getUserAccess($module, array(), $context);

            // Bug56391 - Use the SugarACL class to determine access to different actions within the module
            foreach(SugarACL::$all_access AS $action => $bool) {
                $outputAcl[$action] = ($moduleAcls[$action] == true || !isset($moduleAcls[$action])) ? 'yes' : 'no';
            }

            // is the user an admin user for the module
            $outputAcl['admin'] = ($userObject->isAdminForModule($module)) ? 'yes' : 'no';

            // Only loop through the fields if we have a reason to, admins give full access on everything, no access gives no access to anything
            if ( $outputAcl['access'] == 'yes') {

                // Now time to dig through the fields
                $fieldsAcl = array();

                // we cannot use ACLField::getAvailableFields because it limits the fieldset we return.  We need all fields
                // for instance assigned_user_id is skipped in getAvailableFields, thus making the acl's look odd if Assigned User has ACL's
                // only assigned_user_name is returned which is a derived ["fake"] field.  We really need assigned_user_id to return as well.
                if(empty($GLOBALS['dictionary'][$module]['fields'])){
                    if($bean === false) {
                        $bean = BeanFactory::newBean($module);
                    }
                    if(empty($bean->acl_fields)) {
                        $fieldsAcl = array();
                    } else {
                        $fieldsAcl = $bean->field_defs;
                    }
                } else{
                    $fieldsAcl = $GLOBALS['dictionary'][$module]['fields'];
                    if(isset($GLOBALS['dictionary'][$module]['acl_fields']) && $GLOBALS['dictionary'][$module]=== false){
                        $fieldsAcl = array();
                    }   
                }          
                
                SugarACL::listFilter($module, $fieldsAcl, $context, array('add_acl' => true));
                        
                foreach ( $fieldsAcl as $field => $fieldAcl ) {
                    switch ( $fieldAcl['acl'] ) {
                        case SugarACL::ACL_READ_WRITE:
                            // Default, don't need to send anything down
                            break;
                        case SugarACL::ACL_READ_ONLY:
                            $outputAcl['fields'][$field]['write'] = 'no';
                            $outputAcl['fields'][$field]['create'] = 'no';
                            break;
                        case SugarACL::ACL_CREATE_ONLY:
                            $outputAcl['fields'][$field]['write'] = 'no';
                            $outputAcl['fields'][$field]['read'] = 'no';
                            break;
                        case SugarACL::ACL_NO_ACCESS:
                        default:
                            $outputAcl['fields'][$field]['read'] = 'no';
                            $outputAcl['fields'][$field]['write'] = 'no';
                            $outputAcl['fields'][$field]['create'] = 'no';
                            break;
                    }
                }
            }
        }
        $outputAcl['_hash'] = md5(serialize($outputAcl));
        return $outputAcl;
    }

    /**
     * Fields accessor, gets sugar fields
     *
     * @return array array of sugarfields with a hash
     */
    public function getSugarFields()
    {
        return $this->getSystemClientData('field');
    }

    /**
     * Views accessor Gets client views
     *
     * @return array
     */
    public function getSugarViews()
    {
        return $this->getSystemClientData('view');
    }

    /**
     * Gets client layouts, similar to module specific layouts except used on a
     * global level by the clients consuming this data
     *
     * @return array
     */
    public function getSugarLayouts()
    {
        return $this->getSystemClientData('layout');
    }

    /**
     * Gets client files of type $type (view, layout, field) for a module or for the system
     *
     * @param string $type The type of files to get
     * @param string $module Module name (leave blank to get the system wide files)
     * @return array
     */
    public function getSystemClientData($type)
    {
        // This is a semi-complicated multi-step process, so we're going to try and make this as easy as possible.
        // This should get us a list of the client files for the system
        $fileList = MetaDataFiles::getClientFiles($this->platforms, $type);

        // And this should get us the contents of those files, properly sorted and everything.
        $results = MetaDataFiles::getClientFileContents($fileList, $type);

        return $results;
    }

    public function getModuleClientData($type, $module)
    {
        return MetaDataFiles::getModuleClientCache($this->platforms, $type, $module);
    }

    /**
     * The collector method for the module strings
     *
     * @param string $moduleName The name of the module
     * @param string $language The language for the translations
     * @return array The module strings for the requested language
     */
    public function getModuleStrings( $moduleName, $language = 'en_us' ) {
        // Bug 58174 - Escaped labels are sent to the client escaped
        $strings = return_module_language($language,$moduleName);
        if (is_array($strings)) {
            foreach ($strings as $k => $v) {
                $strings[$k] = $this->decodeStrings($v);
            }
        }

        return $strings;
    }

    /**
     * The collector method for the app strings
     * 
     * @param string $lang The language you wish to fetch the app strings for
     * @return array The app strings for the requested language
     */
    public function getAppStrings($lang = 'en_us' ) {
        $strings = return_application_language($lang);
        if (is_array($strings)) {
            foreach ($strings as $k => $v) {
                $strings[$k] = $this->decodeStrings($v);
            }
        }
        return $strings;        
    }

    /**
     * The collector method for the app strings
     *
     * @param string $lang The language you wish to fetch the app list strings for
     * @return array The app list strings for the requested language
     */
    public function getAppListStrings($lang = 'en_us') {
        $strings = return_app_list_strings_language($lang);
        if (is_array($strings)) {
            foreach ($strings as $k => $v) {
                $strings[$k] = $this->decodeStrings($v);
            }
        }
        return $strings;        
    }

    /**
     * Gets a list of platforms found in the application.
     * 
     * @return array
     */
    public static function getPlatformList()
    {
        $platforms = array();
        // remove ones with _
        foreach(SugarAutoLoader::getFilesCustom("clients", true) as $dir) {
            $dir = basename($dir);
            if($dir[0] == '_') {
                continue;
            }
            $platforms[$dir] = true;
        }

        return array_keys($platforms);
    }

    /**
     * Cleans field def default values before returning them as a member of the
     * metadata response payload
     *
     * Bug 56505
     * Cleans default value of fields to strip out metacharacters used by the app.
     * Used initially for cleaning default multienum values.
     *
     * @param array $fielddefs
     * @return array
     */
    protected function normalizeFielddefs(Array $fielddefs) {
        $this->getSugarFieldHandler();

        foreach ($fielddefs as $name => $def) {
            if (isset($def['type'])) {
                $type = !empty($def['custom_type']) ? $def['custom_type'] : $def['type'];

                $field = $this->sfh->getSugarField($type);

                $fielddefs[$name] = $field->getNormalizedDefs($def);
            }
        }

        return $fielddefs;
    }

    /**
     * Gets the SugarFieldHandler object
     *
     * @return SugarFieldHandler The SugarFieldHandler
     */
    protected function getSugarFieldHandler() {
        if (!$this->sfh instanceof SugarFieldHandler) {
            $this->sfh = new SugarFieldHandler;
        }

        return $this->sfh;
    }

    /**
     * Recursive decoder that handles decoding of HTML entities in metadata strings
     * before returning them to a client
     *
     * @param mixed $source
     * @return array|string
     */
    protected function decodeStrings($source) {
        if (is_string($source)) {
            return html_entity_decode($source, ENT_QUOTES, 'UTF-8');
        } else {
            if (is_array($source)) {
                foreach ($source as $k => $v) {
                    $source[$k] = $this->decodeStrings($v);
                }
            }

            return $source;
        }
    }

    /**
     * Clears the API metadata cache of all cache files
     *
     * @param bool $deleteModuleClientCache Should we also delete the client file cache of the modules
     * @static
     */
    public static function clearAPICache( $deleteModuleClientCache = true ){
        if ( $deleteModuleClientCache ) {
            // Delete this first so there is no race condition between deleting a metadata cache
            // and the module client cache being stale.
            MetaDataFiles::clearModuleClientCache();
        }

        // Wipe out any files from the metadata cache directory
        $metadataFiles = glob(sugar_cached('api/metadata/').'*');
        if ( is_array($metadataFiles) ) {
            foreach ( $metadataFiles as $metadataFile ) {
                // This removes the file and the reference from the map. This does
                // NOT save the file map since that would be expensive in a loop
                // of many deletes.
                unlink($metadataFile);
            }
        }
        
        // clear the platform cache from sugar_cache to avoid out of date data
//        $platforms = self::getPlatformList();
//        foreach($platforms as $platform) {
//            $platformKey = $platform == "base" ?  "base" : implode(",", array($platform, "base"));
//            $hashKey = "metadata:$platformKey:hash";
//            sugar_cache_clear($hashKey);
//        }
    }
    
    public static function buildMetadataSectionCache($section = '', $modules = array(), $platforms = array())
    {
        
    }
    
    public function rebuildCache() 
    {
        $method = 'load' . ($this->public ? 'Public' : '') . 'Metadata';
        $data = $this->$method();
        $this->putMetadataCache($data, $this->platforms[0], $this->public);
    }

    /**
     * Rewrites caches for all metadata manager platforms and visibility
     * 
     * @param array $platforms
     */
    public static function refreshCache($platforms = array())
    {
        // The basics are, for each platform, rewrite the cache for public and private
        if (empty($platforms)) {
            $platforms = self::getPlatformList();
        }
        
        foreach ($platforms as $platform) {
            foreach (array(true, false) as $public) {
                $mm = self::getManagerNew($platform, $public);
                $mm->rebuildCache();
            }
        }
    }
    
    /**
     * Gets server information
     * 
     * @return array of ServerInfo
     */
    public function getServerInfo() {
        global $sugar_flavor;
        global $sugar_version;
        global $timedate;

        $data['flavor'] = $sugar_flavor;
        $data['version'] = $sugar_version;
        
        //BEGIN SUGARCRM flav=pro ONLY
        $fts_enabled = SugarSearchEngineFactory::getFTSEngineNameFromConfig();
        if(!empty($fts_enabled) && $fts_enabled != 'SugarSearchEngine') {
            $data['fts'] = array(
                'enabled' =>  true,
                'type'    =>  $fts_enabled,
            );
        } else {
            $data['fts'] = array(
                'enabled' =>  false,
            );
        }
        //END SUGARCRM flav=pro ONLY

        //Always return dates in ISO-8601
        $date = new SugarDateTime();
        $data['server_time'] = $timedate->asIso($date, $GLOBALS['current_user']);
        $data['gmt_time'] = gmdate('Y-m-d\TH:i:s') . '+0000';

        return $data;
    }
    
    public function getMetadata() {
        $method = 'get' . ($this->public ? 'Public' : 'All') . 'Metadata';
        return $this->$method();
    }
    
    protected function getAllMetadata() {
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
        
        return $data;
    }
    
    protected function getPublicMetadata() {
        $data = $this->getMetadataCache($this->platforms[0],true);
        
        if ( empty($data) ) {
            // Load up the public metadata
            $data = $this->loadPublicMetadata();            
            $this->putMetadataCache($data, $this->platforms[0], TRUE);

        }
        
        return $data;
    }
    
    protected function loadPublicMetadata()
    {
        // Start collecting data
        $data = array();
        
        $data['fields']  = $this->getSugarFields();
        $data['views']   = $this->getSugarViews();
        $data['layouts'] = $this->getSugarLayouts();
        $data['labels'] = $this->getStringUrls($data,true);
        $data['modules'] = array(
            "Login" => array("fields" => array()));
        $data['config']           = $this->getConfigs();
        $data['jssource']         = $this->buildJSFileFromMD($data, $this->platforms[0]);        
        $data["_hash"] = md5(serialize($data));
        
        return $data;
    }
    // CARRYOVERS FROM METADATAAPI

    protected function loadMetadata() {
        // Start collecting data
        $data = $this->_populateModules(array());
        $data['currencies'] = $this->getSystemCurrencies();
        
        foreach($data['modules'] as $moduleName => $moduleDef) {
            if (!array_key_exists($moduleName, $data['full_module_list']) && array_key_exists($moduleName, $data['modules'])) {
                unset($data['modules'][$moduleName]);
            }
        }

        $data['full_module_list']['_hash'] = md5(serialize($data['full_module_list']));

        $data['fields']  = $this->getSugarFields();
        $data['views']   = $this->getSugarViews();
        $data['layouts'] = $this->getSugarLayouts();
        $data['labels'] = $this->getStringUrls($data,false);
        $data['relationships'] = $this->getRelationshipData();
        $data['jssource'] = $this->buildJSFileFromMD($data, $this->platforms[0]);
        $data['server_info'] = $this->getServerInfo();
        $hash = md5(serialize($data));
        $data["_hash"] = $hash;

        return $data;
    }

    /**
     * Gets configs
     *
     * @return array
     */
    protected function getConfigs() {
        // As of now configs are only for portal, so return the default
        return array();
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
        //$mm = $this->getMetadataManager();
        $data['full_module_list'] = $this->getModuleList();
        $data['modules'] = array();
        foreach($data['full_module_list'] as $module) {
            $bean = BeanFactory::newBean($module);
            $data['modules'][$module] = $this->getModuleData($module);
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
    
    
/*****************/    

    /**
     * Returns a list of URL's pointing to json-encoded versions of the strings
     *
     * @param array $data The metadata array
     * @return array
     */
    public function getStringUrls(&$data, $isPublic = false) {
        //$mm = $this->getMetadataManager();

        $languageList = array_keys(get_languages());
        sugar_mkdir(sugar_cached('api/metadata'), null, true);

        $fileList = array();
        foreach ( $languageList as $language ) {            
            $stringData = array();
            $stringData['app_list_strings'] = $this->getAppListStrings($language);
            $stringData['app_strings'] = $this->getAppStrings($language);
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
                    $modData = $this->getModuleStrings($modName, $language);
                    $modStrings[$modName] = $modData;
                }
                $stringData['mod_strings'] = $modStrings;
            }
            // cast the app list strings to objects to make integer key usage in them consistent for the clients
            foreach ($stringData['app_list_strings'] as $listIndex => $listArray) {
                $stringData['app_list_strings'][$listIndex] = (object) $listArray;
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

    public static function getUrlForCacheFile($cacheFile) {
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
                // TODO Remove this when we no longer support PHP 5.2
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
