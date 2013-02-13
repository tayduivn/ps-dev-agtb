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
 * For cache handling, the naming paradigm has the following meaning:
 *  - refresh* methods are public static methods that generally call rebuild* 
 *    methods after getting a proper platform specific manager based on visibility.
 *  - rebuild* methods are instance methods and can be either public or protected
 *    and are the methods that do the actual work of recollecting the metadata 
 *    for a given section or module and rewriting that data to the cache. In some
 *    cases there are rebuild* methods that are consumed by other rebuild* methods.
 */
class MetaDataManager {
    const MM_MODULES        = 'modules';
    const MM_FULLMODULELIST = 'full_module_list';
    const MM_FIELDS         = 'fields';
    const MM_LABELS         = 'labels';
    const MM_MODULELIST     = 'module_list';
    const MM_VIEWS          = 'views';
    const MM_LAYOUTS        = 'layouts';
    const MM_RELATIONSHIPS  = 'relationships';
    const MM_CURRENCIES     = 'currencies';
    const MM_JSSOURCE       = 'jssource';
    const MM_SERVERINFO     = 'server_info';
    const MM_CONFIG         = 'config';
    
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
     * The requested platform, or collection of platforms
     * 
     * @var array
     */
    protected $platforms;

    /**
     * Flag that determines whether this is a public or private request
     * 
     * @var bool
     */
    protected $public = false;

    /**
     * String visibility type indicator used in various methods that depend on
     * visibility. This will be set based on the value of $this->public.
     * 
     * @var string
     */
    protected $visibility = 'private';

    /**
     * Sections of the metadata based on visibility
     * 
     * @var array
     */
    protected $sections = array();

    /**
     * Mapping of metadata sections to the methods that get the data for that
     * section. If the value of the section index is false, the method will be
     * rebuild{section}Section() and will require the current metadata array as an argument.
     * 
     * @var array
     */
    protected $sectionMap = array(
        self::MM_MODULES        => false,
        self::MM_FULLMODULELIST => 'getModuleList',
        self::MM_FIELDS         => 'getSugarFields',
        self::MM_LABELS         => false,
        self::MM_VIEWS          =>'getSugarViews', 
        self::MM_LAYOUTS        => 'getSugarLayouts',
        self::MM_RELATIONSHIPS  => 'getRelationshipData',
        self::MM_CURRENCIES     => 'getSystemCurrencies', 
        self::MM_JSSOURCE       => false, 
        self::MM_SERVERINFO     => 'getServerInfo', 
        self::MM_CONFIG         => 'getConfigs',
    );

    /**
     * The constructor for the class. Sets the visibility flag, the visibility 
     * string indicator and loads the appropriate metadata section list.
     *
     * @param array $platforms A list of clients
     * @param bool $public is this a public metadata grab
     */
    function __construct ($platforms = null, $public = false) {
        if ( $platforms == null ) {
            $platforms = array('base');
        }
        
        // We should have an array of platforms
        if (!is_array($platforms)) {
            $platforms = (array) $platforms;
        }
        
        // Base needs to be in place if it isn't
        if (!in_array('base', $platforms)) {
            $platforms[] = 'base';
        }
        
        $this->platforms = $platforms;
        $this->public = $public;
        
        if ($public) {
            $this->visibility = 'public';
        }
        
        // Load up the metadata sections
        $this->loadSections($public);
    }
    
    /**
     * Gets a class name for a metadata manager
     * 
     * @param  string $platform The platform of the metadata manager class
     * @return string 
     */
    public static function getManagerClassName($platform) {
        return 'MetaDataManager' . ucfirst(strtolower($platform));
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
        $class = self::getManagerClassName(''); // MetaDataManager
        $path  = 'include/MetaDataManager/';
        $found = false;
        foreach ($platform as $type) {
            $mmClass = self::getManagerClassName($type);
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
            $manager = new $class($platform, $public);
            
            // Cache it and move on
            self::$managers[$key] = $manager;
        }
        
        return self::$managers[$key];
    }

    /**
     * Gets a list of metadata sections based on visibility
     * 
     * @return array
     */
    public function getSections() {
        return $this->sections;
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

        $data["_hash"] = $this->getMetadataHash($data);

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
        
        // Sanity check the rel defs, just in case they came back empty
        if (is_array($data)) {
            foreach ( $data as $relKey => $relData ) {
                unset($data[$relKey]['table']);
                unset($data[$relKey]['fields']);
                unset($data[$relKey]['indices']);
                unset($data[$relKey]['relationships']);
            }
        }

        $data["_hash"] = $this->getMetadataHash($data);

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
            foreach ( array('admin', 'access','view','list','edit','delete','import','export','massupdate') as $action ) {
                $outputAcl[$action] = 'yes';
            }
        } else {
            $context = array(
                    'user' => $userObject,
                );
            if($bean instanceof SugarBean) {
                $context['bean'] = $bean;
            }

            // if the bean is not set, or a new bean.. set the owner override
            // this will allow fields marked Owner to pass through ok.
            if($bean == false || empty($bean->id) || (isset($bean->new_with_id) && $bean->new_with_id == true)) {
                $context['owner_override'] = true;
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

                // Currently create just uses the edit permission, but there is probably a need for a separate permission for create
                $outputAcl['create'] = $outputAcl['edit'];

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
                // get the field names

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
                        case 2:
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
        $outputAcl['_hash'] = $this->getMetadataHash($outputAcl);
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

    /**
     * Gets the client cache for a given module
     * 
     * @param string $type View, Layout, etc
     * @param string $module 
     * @return array
     */
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
    }

    /**
     * Determines if a section is valid for this visibility
     * 
     * @param string $section
     * @return bool
     */
    protected function isValidSection($section) 
    {
        return in_array($section, $this->sections);
    }

    /**
     * Rebuilds the modules section of the metadata. This will cover all modules
     * metadata. To refresh a single module or collection of modules, use
     * refreshModulesCache().
     * 
     * @param array $data Existing metadata
     * @return array
     */
    protected function rebuildModulesSection($data) 
    {
        $data = $this->populateModules($data);
        foreach($data['modules'] as $moduleName => $moduleDef) {
            if (!array_key_exists($moduleName, $data['full_module_list'])) {
                unset($data['modules'][$moduleName]);
            }
        }

        $data['full_module_list']['_hash'] = $this->getMetadataHash($data['full_module_list']);
        
        return $data;
    }

    /**
     * Rebuilds the labels section of the cache. Called by refreshSectionCache
     * 
     * @param array $data Existing metadata
     * @return mixed
     */
    protected function rebuildLabelsSection($data) 
    {
        $data['labels'] = $this->getStringUrls($data, $this->public);
        return $data;
    }

    /**
     * Rebuilds the JS Source File section of the metadata. Called by refreshSectionCache
     * 
     * @param array $data Existing metadata
     * @return mixed
     */
    protected function rebuildJssourceSection($data)
    {
        $data['jssource'] = $this->buildJSSourceFile($data);
        return $data;
    }

    /**
     * Rebuilds the metadata for a module or modules provided the metadata cache
     * exists already.
     * 
     * @param string|array $modules A single module or array of modules
     * @param array $data Existing metadata
     */
    public function rebuildModulesCache($modules, $data = array()) {
        // Only write if were actually asked for
        $write = false;
        
        // Only process if there are modules to work on
        if (!empty($modules) && $this->isValidSection('modules')) {
            // If there was no metadata payload given, get the metadata from the 
            // source. Same as with section caching, we only want to rebuild the
            // modules metadata if there are modules metadata already. 
            if (empty($data)) {
                $data = $this->getMetadata(false);
            }
            
            if (!empty($data)) {
                // Handle the module(s)
                foreach ((array) $modules as $module) {
                    // Only work on modules that was have already grabbed
                    if (isset($data['modules'][$module])) {
                        $bean = BeanFactory::newBean($module);
                        if ($bean) {
                            $data['modules'][$module] = $this->getModuleData($module);
                            $this->_relateFields($data, $module, $bean);
        
                            if (!$write) {
                                $write = true;
                            }
                        }
                    }
                }
            }
            
            // Now cache the new data if there is a need
            if ($write) {
                $data['_hash'] = $this->getMetadataHash($data);
                $this->putMetadataCache($data, $this->platforms[0], $this->public);
            }
        }
    }

    /**
     * Rebuilds a section or sections of the metadata cache provided the cache
     * already exists.
     *   
     * @param string|array $section
     */
    public function rebuildSectionCache($section = '')
    {
        // Only write if the section or module(s) were actually found and gettable
        $write = false;
        
        // If there is no section passed then do nothing
        if (!empty($section)) {
            // We will always need the metadata for this process, but only if there
            // is existing metadata to work (why build a section of an empty set)
            $data = $this->getMetadata(false);
            
            if (!empty($data)) {
                // Handle the section(s)
                foreach ((array) $section as $index) {
                    if (isset($this->sectionMap[$index]) && $this->isValidSection($index)) {
                        if ($this->sectionMap[$index] === false) {
                            $method = 'rebuild' . ucfirst($index) . 'Section';
                            $data = $this->$method($data);
                        } else {
                            $method = $this->sectionMap[$index];
                            $data[$index] = $this->$method();
                        }
                        
                        if (!$write) {
                            $write = true;
                        }
                    }
                }
            }
            
            // Now cache the new data if there is a need
            if ($write) {
                $data['_hash'] = $this->getMetadataHash($data);
                $this->putMetadataCache($data, $this->platforms[0], $this->public);
            }
        }
    }

    /**
     * Rebuilds the cache for this platform and visibility
     */
    public function rebuildCache() 
    {
        // Clear the module client cache first
        MetaDataFiles::clearModuleClientCache();
        
        // Clear our cache file
        $file = $this->getMetadataCacheFileName();
        if (file_exists($file)) {
            unlink($file);
        }
        
        // Rebuild it
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
        
        foreach ((array) $platforms as $platform) {
            foreach (array(true, false) as $public) {
                $mm = self::getManager($platform, $public, true);
                $mm->rebuildCache();
            }
        }
    }

    /**
     * Refreshes the cache for a section or collection of sections
     * 
     * @param string $section
     * @param array $platforms
     */
    public static function refreshSectionCache($section = '', $platforms = array())
    {
        if (empty($platforms)) {
            $platforms = self::getPlatformList();
        }
        
        foreach ((array) $platforms as $platform) {
            foreach (array(true, false) as $public) {
                $mm = self::getManager($platform, $public, true);
                $mm->rebuildSectionCache($section);
            }
        }
    }

    /**
     * Refreshes the cache for a module or collection of modules.
     * 
     * @param array $modules
     * @param array $platforms
     */
    public static function refreshModulesCache($modules = array(), $platforms = array())
    {
        if (empty($platforms)) {
            $platforms = self::getPlatformList();
        }
        
        // This only needs to be done for private visibility since modules are not
        // in public metadata
        foreach ((array) $platforms as $platform) {
            $mm = self::getManager($platform, false, true);
            $mm->rebuildModulesCache($modules);
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
        $data['server_time'] = $timedate->asIso($date, $this->getCurrentUser());
        $data['gmt_time'] = gmdate('Y-m-d\TH:i:s') . '+0000';

        return $data;
    }

    /**
     * Gets all metadata for the current platform and visibility
     * 
     * NOTE ON $buildCache - In most cases this will be true. But in edge cases, 
     * like installation when there isn't a database yet, this has to be false 
     * since we can't try get module information without the ability to get to 
     * the database.
     * 
     * @param bool $buildCache Flag that tells the getters whether to build the
     *                         cache. 
     * @return mixed
     */
    public function getMetadata($buildCache = true) {
        $method = 'get' . ($this->public ? 'Public' : 'All') . 'Metadata';
        return $this->$method($buildCache);
    }

    /**
     * Private metadata getter, called by getMetadata()
     * 
     * @param bool $buildCache
     * @return array
     */
    protected function getAllMetadata($buildCache = true) {
        $data = $this->getMetadataCache($this->platforms[0],false);
        
        //If we failed to load the metadata from cache, load it now the hard way.
        if (empty($data) && $buildCache) {
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

    /**
     * Public metadata getter, called by getMetadata()
     * 
     * @param bool $buildCache
     * @return array
     */
    protected function getPublicMetadata($buildCache = true) {
        $data = $this->getMetadataCache($this->platforms[0],true);
        
        if ( empty($data)  && $buildCache ) {
            // Load up the public metadata
            $data = $this->loadPublicMetadata();            
            $this->putMetadataCache($data, $this->platforms[0], TRUE);

        }
        
        return $data;
    }

    /**
     * Builds the current platform public metadata and returns it
     * 
     * @return array
     */
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
        $data['jssource']         = $this->buildJSSourceFile($data);        
        $data["_hash"] = $this->getMetadataHash($data);
        
        return $data;
    }

    /**
     * Builds the current platform private metadata and returns it
     * 
     * @return array
     */
    protected function loadMetadata() {
        // Start collecting data with the modules
        $data = $this->populateModules(array());

        foreach($data['modules'] as $moduleName => $moduleDef) {
            if (!array_key_exists($moduleName, $data['full_module_list']) && array_key_exists($moduleName, $data['modules'])) {
                unset($data['modules'][$moduleName]);
            }
        }

        $data['full_module_list']['_hash'] = $this->getMetadataHash($data['full_module_list']);

        $data['currencies'] = $this->getSystemCurrencies();
        $data['fields']  = $this->getSugarFields();
        $data['views']   = $this->getSugarViews();
        $data['layouts'] = $this->getSugarLayouts();
        $data['labels'] = $this->getStringUrls($data,false);
        $data['relationships'] = $this->getRelationshipData();
        $data['jssource'] = $this->buildJSSourceFile($data);
        $data['server_info'] = $this->getServerInfo();
        $hash = $this->getMetadataHash($data);
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
        // Loading a standard module list. If the module list isn't set into the
        // globals, load them up. This happens on installation.
        if (empty($GLOBALS['app_list_strings']['moduleList'])) {
            $als = return_app_list_strings_language($GLOBALS['current_language']);
            $list = $als['moduleList'];
        } else {
            $list = $GLOBALS['app_list_strings']['moduleList'];
        }
        
        return array_keys($list);
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

        $moduleList['_hash'] = $this->getMetadataHash($moduleList);
        return $moduleList;
    }

    /**
     * Gets full module list and data for each module and uses that data to 
     * populate the modules/full_module_list section of the metadata
     *
     * @param array $data Existing metadata
     * @return array
     */
    public function populateModules($data) {
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
    protected function _relateFields(&$data, $module, $bean) {
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
     * Builds the javascript source file that is referenced in the metadata
     * 
     * @param array $data The metadata 
     * @return string
     */
    protected function buildJSSourceFile(&$data) {
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
        $path = "cache/javascript/{$this->platforms[0]}/components_$hash.js";
        if (!file_exists($path)){
            mkdir_recursive(dirname($path));
            file_put_contents($path, $js);
        }

        return $this->getUrlForCacheFile($path);
    }


    /**
     * Builds component JS as strings
     * 
     * @param $data
     * @param bool $isModule
     * @return string
     */
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

    /**
     * Helper to insert header comments for controllers
     * 
     * @param $controller
     * @param $mdType
     * @param $name
     * @param $platform
     * @return string
     */
    protected function insertHeaderComment($controller, $mdType, $name, $platform) {
        $singularType = substr($mdType, 0, -1);
        $needle = '({';
        $headerComment = "\n\t// " . ucfirst($name) ." ". ucfirst($singularType) . " ($platform) \n";

        // Find position "after" needle
        $pos = (strpos($controller, $needle) + strlen($needle));

        // Insert our comment and return ammended controller
        return substr($controller, 0, $pos) . $headerComment . substr($controller, $pos);
    }
    
    
    /**
     * Returns a list of URL's pointing to json-encoded versions of the strings
     *
     * @param array $data The metadata array
     * @return array
     */
    public function getStringUrls(&$data, $isPublic = false) {
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
                if (is_array($listArray) && !array_key_exists('',$listArray)) {
                    $stringData['app_list_strings'][$listIndex] = (object) $listArray;
                }
            }
            $stringData['_hash'] = $this->getMetadataHash($stringData);
            $fileList[$language] = sugar_cached('api/metadata/lang_'.$language.'_'.$stringData['_hash'].'.json');
            sugar_file_put_contents_atomic($fileList[$language],json_encode($stringData));
        }
        
        $urlList = array();
        foreach ( $fileList as $lang => $file ) {
            $urlList[$lang] = $this->getUrlForCacheFile($file);
        }

        // We need the default language somewhere, how about here?
        $urlList['default'] = $GLOBALS['sugar_config']['default_language'];
        $urlList['_hash'] = $this->getMetadataHash($urlList);

        return $urlList;
    }

    /**
     * Gets a URL for a cache file
     * 
     * @param string $cacheFile
     * @return string
     */
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

    /**
     * Saves the metadata cache for a given platform and visibility
     * 
     * @param array $data The metadata
     * @param string $platform The platform for this metadata
     * @param bool $isPublic
     */
    protected function putMetadataCache($data, $platform, $isPublic)
    {
        $cacheFile = $this->getMetadataCacheFileName($platform, $isPublic);
        create_cache_directory($cacheFile);
        write_array_to_file('metadata', $data, $cacheFile);
    }

    /**
     * Gets the metadata cache for a given platform and visibility
     * 
     * @param string $platform
     * @param bool $isPublic
     * @return array The metadata cache is it exists, null otherwise
     */
    protected function getMetadataCache($platform, $isPublic)
    {
        if ( inDeveloperMode() ) {
            return null;
        }

        $cacheFile = $this->getMetadataCacheFileName($platform, $isPublic);
        if ( file_exists($cacheFile) ) {
            require $cacheFile;
            return $metadata;
        } else {
            return null;
        }
    }

    /**
     * Gets the name of the cache file for this manager
     * 
     * @param string $platform
     * @param boolean $public
     * @return string
     */
    public function getMetadataCacheFileName($platform = null, $public = null)
    {
        if (empty($platform)) {
            $platform = $this->platforms[0];
        }
        
        if ($public === null) {
            $public = $this->public;
        }
        
        $type = $public ? 'public' : 'private';
        return sugar_cached('api/metadata/metadata_'.$platform.'_'.$type.'.php');
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

    /**
     * Calculates a metadata hash. Removes any existing first level _hash index
     * prior to calculation.
     * 
     * @param array $data
     * @return string
     */
    protected function getMetadataHash($data) 
    {
        unset($data['_hash']);
        return md5(serialize($data));
    }

    /**
     * Loads up the metadata sections for this manager
     * 
     * @param bool $public
     */
    protected function loadSections($public = false) {
        $method = 'get' . ($public ? 'Public' : 'Private') . 'Sections';
        $this->sections = $this->$method();
    }

    /**
     * Loads the standard public metadata sections. This can be overridden.
     */
    protected function getPublicSections() {
        return array(
            self::MM_FIELDS,
            self::MM_LABELS,
            self::MM_VIEWS, 
            self::MM_LAYOUTS, 
            self::MM_CONFIG,
            self::MM_JSSOURCE,
        );
    }

    /**
     * Loads the standard private metadata sections. This can be overridden.
     */
    protected function getPrivateSections() {
        return array(
            self::MM_MODULES,
            self::MM_FULLMODULELIST,
            self::MM_FIELDS, 
            self::MM_LABELS, 
            self::MM_MODULELIST,
            self::MM_VIEWS,
            self::MM_LAYOUTS,
            self::MM_RELATIONSHIPS,
            self::MM_CURRENCIES, 
            self::MM_JSSOURCE, 
            self::MM_SERVERINFO,
        );
    }

    /**
     * Gets the user bean for this request
     * 
     * @return User
     */
    protected function getCurrentUser() {
        global $current_user;
        return $current_user;
    }

    /**
     * Gets display module list per user defined tabs
     * @return array
     */
    public function getUserModuleList() {
        // Loading a standard module list
        require_once("modules/MySettings/TabController.php");
        $controller = new TabController();
        $moduleList = array_keys($controller->get_user_tabs($this->getCurrentUser()));
        // always add back in employees see Bug58563
        if (!in_array('Employees',$moduleList)) {
            $moduleList[] = 'Employees';
        }
        return $moduleList;
    }
}
