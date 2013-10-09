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

require_once 'soap/SoapHelperFunctions.php';
require_once 'modules/ModuleBuilder/parsers/MetaDataFiles.php';
require_once 'include/SugarFields/SugarFieldHandler.php';
require_once 'include/SugarObjects/LanguageManager.php';
require_once 'modules/ActivityStream/Activities/ActivityQueueManager.php';

SugarAutoLoader::requireWithCustom('include/MetaDataManager/MetaDataHacks.php');
/**
 * This class is for access to metadata for all sugarcrm modules in a read only
 * state.  This means that you can not modify any of the metadata using this
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
class MetaDataManager
{
    /**
     * Constants that define the sections of the metadata
     */
    const MM_MODULES        = 'modules';
    const MM_FULLMODULELIST = 'full_module_list';
    const MM_FIELDS         = 'fields';
    const MM_LABELS         = 'labels';
    const MM_VIEWS          = 'views';
    const MM_LAYOUTS        = 'layouts';
    const MM_RELATIONSHIPS  = 'relationships';
    const MM_CURRENCIES     = 'currencies';
    const MM_JSSOURCE       = 'jssource';
    const MM_SERVERINFO     = 'server_info';
    const MM_CONFIG         = 'config';
    const MM_LANGUAGES      = 'languages';
    const MM_HIDDENSUBPANELS = 'hidden_subpanels';
    const MM_MODULETABMAP   = 'module_tab_map';
    const MM_LOGOURL        = 'logo_url';
    const MM_OVERRIDEVALUES = '_override_values';

    /**
     * Collection of fields in the user metadata that can trigger a reauth when
     * changed.
     *
     * Mapping is 'prefname' => 'metadataname'
     * @var array
     */
    protected $userPrefsToCache = array(
        'datef' => 'datepref',
        'timef' => 'timepref',
        'timezone' => 'timezone',
    );

    /**
     * The metadata hacks class
     * 
     * @var MetaDataHacks
     */
    protected $metaDataHacks;

    /**
     * Stack of flag that tells this class to clear the metadata cache on shutdown
     * of the request. The stack is keyed on whether a delete module client cache
     * was requested or not, so a cache clear will happen no more than twice (and 
     * more than likely will only happen once). 
     * 
     * @var array
     */
    protected static $clearCacheOnShutdown = array();

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
        self::MM_LABELS         => 'getStringUrls',
        self::MM_VIEWS          => 'getSugarViews', 
        self::MM_LAYOUTS        => 'getSugarLayouts',
        self::MM_RELATIONSHIPS  => 'getRelationshipData',
        self::MM_CURRENCIES     => 'getSystemCurrencies', 
        self::MM_JSSOURCE       => false, 
        self::MM_SERVERINFO     => 'getServerInfo', 
        self::MM_CONFIG         => 'getConfigs',
        self::MM_LANGUAGES      => 'getAllLanguages',
        self::MM_HIDDENSUBPANELS => 'getHiddenSubpanels',
        self::MM_MODULETABMAP   => 'getModuleTabMap',
        self::MM_LOGOURL        => 'getLogoUrl',
    );

    /**
     * Flag that tells the manager whether the cache refresher is queued. This
     * is off by default and can be toggled using enable/disableCacheRefresherQueue().
     * 
     * @var bool
     */
    protected static $isQueued = false;

    /**
     * The actual cache refresher queue. When the cache refresher queue runner is
     * called, this array will drive what is done. First by section then by module
     * unless 'full' is set to true.
     * 
     * @var array
     */
    protected static $queue  = array();

    /**
     * Set by the cache refresh queue runner, if true, the refresh*Cache functions
     * will not run. This prevents MetaDataManager from calling a support method
     * that clears a cache elsewhere that in turn triggers another section or 
     * module cache clear in the metadata manager.
     * 
     * @var bool
     */
    protected static $inProcess  = false;

    /**
     * Current process stack. Used internally by the refresh* methods to prevent 
     * infinite self-referencing method calls.
     * 
     * @var array
     */
    protected static $currentProcess = array();

    /**
     * @var array List of metadata keys for values that should be overriden rather than
     * merged client side with existing metadata .
     */
    protected static $defaultOverrides = array(
        'fields',
        'module_list',
        'relationships',
        'currencies',
        'server_info',
        'module_tab_map',
        'hidden_subpanels',
        'config',
    );

    /**
     * List of the various parts of metadata that are cached mapped to the method
     * name that handles the refreshing of that part. These are not to be
     * confused with sections (the individual elements of metadata). These are,
     * instead, the areas of metadata that contain their own caches and are thus
     * handled a little differently. Order here is important so do not change
     * this without good reason.
     * @var array
     */
    protected static $cacheParts = array(
        'modules' => 'refreshModulesCache',
        'languages' => 'refreshLanguagesCache',
        'section' => 'refreshSectionCache',
    );

    /**
     * List of deleted languages when clearing the cache. Used in {@see rebuildCache}
     * when deleting current caches.
     * 
     * @var array
     */
    protected $deletedLanguageCaches = array();

    /**
     * The constructor for the class. Sets the visibility flag, the visibility 
     * string indicator and loads the appropriate metadata section list.
     *
     * @param array $platforms A list of clients
     * @param bool $public is this a public metadata grab
     */
    public function __construct ($platforms = null, $public = false) 
    {
        // To support previous iterations of MetaDataManager prior to 7.1, in
        // which the first required argument was a CurrentUser
        if ($platforms instanceof SugarBean) {
            // The first arg would have been User, the second arg platforms, which
            // could have been null but will be handled after this block is run
            $platforms = $public;
            
            // The public flag is a little trickier, since it wasn't required. If
            // it was passed, we need to grab it, otherwise just default it
            $public = false;
            if (func_num_args() === 3) {
                $public = func_get_arg(2);
            }

            // Let consumers know this isn't the correct use of this constuctor
            $GLOBALS['log']->deprecated("MetaDataManager no longer accepts a User object as an arguments");
        }

        if ($platforms == null) {
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

        // Load the hacks object
        $className = SugarAutoLoader::customClass('MetaDataHacks');
        $this->metaDataHacks = new $className();
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
        require_once 'include/SubPanel/SubPanelDefinitions.php';
        $parent_bean = BeanFactory::getBean($moduleName);
        //Hack to allow the SubPanelDefinitions class to check the correct module dir
        if (!$parent_bean) {
            $parent_bean = (object) array('module_dir' => $moduleName);
        }

        $spd = new SubPanelDefinitions($parent_bean, '', '', $this->platforms[0]);
        $layout_defs = $spd->layout_defs;

        if (is_array($layout_defs) && isset($layout_defs['subpanel_setup'])) {
            foreach ($layout_defs['subpanel_setup'] AS $name => $subpanel_info) {
                $aSubPanel = $spd->load_subpanel($name, '', $parent_bean);

                if (!$aSubPanel) {
                    continue;
                }

                if ($aSubPanel->isCollection()) {
                    $collection = array();
                    foreach ($aSubPanel->sub_subpanels AS $key => $subpanel) {
                        $collection[$key] = $subpanel->panel_definition;
                    }
                    $layout_defs['subpanel_setup'][$name]['panel_definition'] = $collection;
                } else {
                    $layout_defs['subpanel_setup'][$name]['panel_definition'] = $aSubPanel->panel_definition;
                }

            }
        }

        return $layout_defs;
    }

    /**
     * This method collects all view data for a modul
     *
     * @param $moduleName The name of the sugar module to collect info about.
     *
     * @return Array A hash of all of the view data.
     */
    public function getModuleViews($moduleName)
    {
        return $this->getModuleClientData('view',$moduleName);
    }

    /**
     * This method collects all view data for a modul
     *
     * @param $moduleName The name of the sugar module to collect info about.
     *
     * @return Array A hash of all of the view data.
     */
    public function getModuleMenu($moduleName)
    {
        return $this->getModuleClientData('menu',$moduleName);
    }

    /**
     * This method collects all view data for a module
     *
     * @param $moduleName The name of the sugar module to collect info about.
     *
     * @return Array A hash of all of the view data.
     */
    public function getModuleLayouts($moduleName)
    {
        return $this->getModuleClientData('layout', $moduleName);
    }

    /**
     * This method collects all field data for a module
     *
     * @param string $moduleName The name of the sugar module to collect info about.
     *
     * @return Array A hash of all of the view data.
     */
    public function getModuleFields($moduleName)
    {
        return $this->getModuleClientData('field', $moduleName);
    }

    /**
     * This method collects all filter data for a module
     *
     * @param string $moduleName The name of the sugar module to collect info about.
     *
     * @return Array A hash of all of the filter data.
     */
    public function getModuleFilters($moduleName)
    {
        return $this->getModuleClientData('filter', $moduleName);
    }

    /**
     * The collector method for modules.  Gets metadata for all of the module specific data
     *
     * @param $moduleName The name of the module to collect metadata about.
     * @return array An array of hashes containing the metadata.  Empty arrays are
     * returned in the case of no metadata.
     */
    public function getModuleData($moduleName)
    {
        //BEGIN SUGARCRM flav=pro ONLY
        require_once 'include/SugarSearchEngine/SugarSearchEngineMetadataHelper.php';
        //END SUGARCRM flav=pro ONLY
        $vardefs = $this->getVarDef($moduleName);
        if (!empty($vardefs['fields']) && is_array($vardefs['fields'])) {
            require_once 'include/MassUpdate.php';
            $vardefs['fields'] = MassUpdate::setMassUpdateFielddefs($vardefs['fields'], $moduleName);
        }

        $data['fields'] = isset($vardefs['fields']) ? $vardefs['fields'] : array();
        // Add the _hash for the fields array
        $data['fields']['_hash'] = md5(serialize($data['fields']));
        $data['views'] = $this->getModuleViews($moduleName);
        $data['layouts'] = $this->getModuleLayouts($moduleName);
        $data['fieldTemplates'] = $this->getModuleFields($moduleName);
        $data['subpanels'] = $this->getSubpanelDefs($moduleName);
        $data['menu'] = $this->getModuleMenu($moduleName);
        $data['config'] = $this->getModuleConfig($moduleName);
        $data['filters'] = $this->getModuleFilters($moduleName);

        // Indicate whether Module Has duplicate checking enabled --- Rules must exist and Enabled flag must be set
        $data['dupCheckEnabled'] = isset($vardefs['duplicate_check']) && isset($vardefs['duplicate_check']['enabled']) && ($vardefs['duplicate_check']['enabled']===true);

        // Indicate whether a Module has activity stream enabled
        $data['activityStreamEnabled'] = ActivityQueueManager::isEnabledForModule($moduleName);
        //BEGIN SUGARCRM flav=pro ONLY
        $data['ftsEnabled'] = SugarSearchEngineMetadataHelper::isModuleFtsEnabled($moduleName);
        //END SUGARCRM flav=pro ONLY

        // TODO we need to have this kind of information on the module itself not hacked around on globals
        $data['isBwcEnabled'] = in_array($moduleName, $GLOBALS['bwcModules']);

        $seed = BeanFactory::newBean($moduleName);
        $data['globalSearchEnabled'] = $this->getGlobalSearchEnabled($seed, $vardefs, $this->platforms[0]);

        //BEGIN SUGARCRM flav=pro ONLY
        if (!empty($seed)) {
            $favoritesEnabled = ($seed->isFavoritesEnabled() !== false) ? true : false;
            $data['favoritesEnabled'] = $favoritesEnabled;
        }
        //END SUGARCRM flav=pro ONLY

        $data["_hash"] = $this->hashChunk($data);

        return $data;
    }

    /**
     * Helper to determine if vardef for module has global search enabled or not.
     * @param  array   $seed     the new bean created from module name passed to BeanFactory::newBean
     * @param  array   $vardefs  The vardefs
     * @param  string  $platform The platform
     * @return boolean indicating whether or not global search is enabled
     */
    public function getGlobalSearchEnabled($seed, $vardefs, $platform = null)
    {
        if (empty($platform)) {
            $platform = $this->platforms[0];
        }
        // Is the argument set for this module
        if (isset($vardefs['globalSearchEnabled'])) {
            // Is it an array of platforms or a simple boolean
            if (is_array($vardefs['globalSearchEnabled'])) {
                // if the platform is set use that value; otherwise check if set in 'base'; lastly, fallback to true
                if (isset($vardefs['globalSearchEnabled'][$platform])) {
                    return $vardefs['globalSearchEnabled'][$platform];
                } else {
                    // Check if global search enabled set on the base platform. If so, and not set for platform at all, we've decided that we should fall back to base's value
                    return isset($vardefs['globalSearchEnabled']['base']) ? $vardefs['globalSearchEnabled']['base'] : true;
                }
            } else {
                // If a simple boolean we return that as it defines whether search enabled globally across all platforms
                return $vardefs['globalSearchEnabled'];
            }
        }
        // If globalSearchEnabled property not set, we check if valid bean (all "real" beans are, by default, global search enabled)
        return !empty($seed);
    }

    /**
     * Get the config for a specific module from the Administration Layer
     *
     * @param  string $moduleName The Module we want the data back for.
     * @return array
     */
    public function getModuleConfig($moduleName)
    {
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');

        return $admin->getConfigForModule($moduleName, $this->platforms[0]);
    }

    /**
     * The collector method for relationships.
     *
     * @return array An array of relationships, indexed by the relationship name
     */
    public function getRelationshipData()
    {
        $relFactory = SugarRelationshipFactory::getInstance();

        $data = $relFactory->getRelationshipDefs();

        // Sanity check the rel defs, just in case they came back empty
        if (is_array($data)) {
            foreach ($data as $relKey => $relData) {
                unset($data[$relKey]['table']);
                unset($data[$relKey]['fields']);
                unset($data[$relKey]['indices']);
                unset($data[$relKey]['relationships']);
            }
        }

        $data["_hash"] = $this->hashChunk($data);

        return $data;
    }

    /**
     * Gets vardef info for a given module.
     *
     * @param $moduleName The name of the module to collect vardef information about.
     * @return array The vardef's $dictonary array.
     */
    public function getVarDef($moduleName)
    {
        require_once 'data/BeanFactory.php';
        $obj = BeanFactory::getObjectName($moduleName);

        if ($obj) {
            require_once 'include/SugarObjects/VardefManager.php';
            global $dictionary;
            VardefManager::loadVardef($moduleName, $obj);
            if (isset($dictionary[$obj])) {
                $data = $dictionary[$obj];
            }

            // vardefs are missing something, for consistency let's populate some arrays
            if (!isset($data['fields'])) {
                $data['fields'] = array();
            }
            if (!isset($data['relationships'])) {
                $data['relationships'] = array();
            }
            if(!isset($data['fields'])) {
                $data['fields'] = array();
            }
        }

        // Bug 56505 - multiselect fields default value wrapped in '^' character
        if (!empty($data['fields'])) {
            $data['fields'] = $this->metaDataHacks->normalizeFieldDefs($data['fields']);
        }

        if (!isset($data['relationships'])) {
            $data['relationships'] = array();
        }

        return $data;
    }

    /**
     * Gets the ACL's for the module, will also expand them so the client side of the ACL's don't have to do as many checks.
     *
     * @param  string $module     The module we want to fetch the ACL for
     * @param  object $userObject The user object for the ACL's we are retrieving.
     * @param  object|bool $bean       The SugarBean for getting specific ACL's for a module
     * @param bool $showYes Do not unset Yes Results
     * @return array       Array of ACL's, first the action ACL's (access, create, edit, delete) then an array of the field level acl's
     */
    public function getAclForModule($module, $userObject, $bean = false, $showYes = false)
    {
        $outputAcl = array('fields' => array());
        $outputAcl['admin'] = ($userObject->isAdminForModule($module)) ? 'yes' : 'no';
        $outputAcl['developer'] = ($userObject->isDeveloperForModule($module)) ? 'yes' : 'no';

        if (!SugarACL::moduleSupportsACL($module)) {
            foreach (array('access', 'view', 'list', 'edit', 'delete', 'import', 'export', 'massupdate') as $action) {
                $outputAcl[$action] = 'yes';
            }
        } else {
            $context = array(
                'user' => $userObject,
            );
            if ($bean instanceof SugarBean) {
                $context['bean'] = $bean;
            }

            // if the bean is not set, or a new bean.. set the owner override
            // this will allow fields marked Owner to pass through ok.
            if ($bean == false || empty($bean->id) || (isset($bean->new_with_id) && $bean->new_with_id == true)) {
                $context['owner_override'] = true;
            }

            $moduleAcls = SugarACL::getUserAccess($module, array(), $context);

            // Bug56391 - Use the SugarACL class to determine access to different actions within the module
            foreach (SugarACL::$all_access as $action => $bool) {
                $outputAcl[$action] = ($moduleAcls[$action] == true || !isset($moduleAcls[$action])) ? 'yes' : 'no';
            }

            // Only loop through the fields if we have a reason to, admins give full access on everything, no access gives no access to anything
            if ($outputAcl['access'] == 'yes') {
                // Currently create just uses the edit permission, but there is probably a need for a separate permission for create
                $outputAcl['create'] = $outputAcl['edit'];

                if ($bean === false) {
                    $bean = BeanFactory::newBean($module);
                }

                // we cannot use ACLField::getAvailableFields because it limits the fieldset we return.  We need all fields
                // for instance assigned_user_id is skipped in getAvailableFields, thus making the acl's look odd if Assigned User has ACL's
                // only assigned_user_name is returned which is a derived ["fake"] field.  We really need assigned_user_id to return as well.
                if (empty($GLOBALS['dictionary'][$bean->object_name]['fields'])) {
                    if (empty($bean->acl_fields)) {
                        $fieldsAcl = array();
                    } else {
                        $fieldsAcl = $bean->field_defs;
                    }
                } else {
                    $fieldsAcl = $GLOBALS['dictionary'][$bean->object_name]['fields'];
                    if (isset($GLOBALS['dictionary'][$bean->object_name]['acl_fields']) && $GLOBALS['dictionary'][$bean->object_name] === false) {
                        $fieldsAcl = array();
                    }
                }
                // get the field names

                SugarACL::listFilter($module, $fieldsAcl, $context, array('add_acl' => true));
                $fieldsAcl = $this->metaDataHacks->fixAcls($fieldsAcl);
                foreach ($fieldsAcl as $field => $fieldAcl) {
                    switch ($fieldAcl['acl']) {
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
        // there are times when we need the yes results, for instance comparing access for a record
        if ($showYes === false) {
            // for brevity, filter out 'yes' fields since UI assumes 'yes'
            foreach ($outputAcl as $k => $v) {
                if ($v == 'yes') {
                    unset($outputAcl[$k]);
                }
            }
        }
        $outputAcl['_hash'] = $this->hashChunk($outputAcl);

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
     * @param  string $type   The type of files to get
     * @param  string $module Module name (leave blank to get the system wide files)
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
     * @param  string $moduleName The name of the module
     * @param  string $language   The language for the translations
     * @return array  The module strings for the requested language
     */
    public function getModuleStrings( $moduleName, $language = 'en_us' )
    {
        // Bug 58174 - Escaped labels are sent to the client escaped
        // TODO: SC-751, fix the way languages merge
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
     * @param  string $lang The language you wish to fetch the app strings for
     * @return array  The app strings for the requested language
     */
    public function getAppStrings($lang = 'en_us')
    {
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
     * @param  string $lang The language you wish to fetch the app list strings for
     * @return array  The app list strings for the requested language
     */
    public function getAppListStrings($lang = 'en_us')
    {
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
        foreach (SugarAutoLoader::getFilesCustom("clients", true) as $dir) {
            $dir = basename($dir);
            if ($dir[0] == '_') {
                continue;
            }
            $platforms[$dir] = true;
        }

        return array_keys($platforms);
    }

    /**
     * Gets a list of platforms that currently have cached metadata. This is used
     * in methods that refresh parts of the cache and prevents the unnecessary
     * building of caches for platforms that don't need to be primed.
     * 
     * @param boolean $addBase Flag to determine whether to add the base platform by default
     * @return array
     */
    public static function getPlatformsWithCaches($addBase = true)
    {
        $platforms = array();

        // Add base to the list by default
        if ($addBase) {
            $platforms['base'] = 'base';
        }

        // Get the listing of files in the cache directory
        $caches = glob(sugar_cached('api/metadata/') . '*.php');
        foreach ($caches as $cache) {
            $file = basename($cache, '.php');
            // If the filename fits the pattern of a metadata cache file get the
            // platform for the file so long as it isn't base
            preg_match('/^metadata_(.*)_(private|public)$/', $file, $m);
            if (isset($m[1])) {
                $platforms[$m[1]] = $m[1];
            }
        }
        
        return $platforms;
    }

    /**
     * Recursive decoder that handles decoding of HTML entities in metadata strings
     * before returning them to a client
     *
     * @param  mixed        $source
     * @return array|string
     */
    protected function decodeStrings($source)
    {
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
     * Registers the API metadata cache to be cleared at shutdown
     *
     * @param bool $deleteModuleClientCache Should we also delete the client file cache of the modules
     * @static
     */
    public static function clearAPICache( $deleteModuleClientCache = true )
    {
        // True/false stack for handling both client cache cases
        $key = $deleteModuleClientCache ? 1 : 0;

        // If we are in unit tests we need to fire this off right away
        if (defined('SUGAR_PHPUNIT_RUNNER') && SUGAR_PHPUNIT_RUNNER === true) {
            self::clearAPICacheOnShutdown($deleteModuleClientCache);
        } elseif (($key === 0 && empty(self::$clearCacheOnShutdown)) || !isset(self::$clearCacheOnShutdown[$key])) {
            // Will only clear cache if 
            //  - A) delete module cache is false and there is no stack of clears, OR
            //  - B) delete module cache is true and it hasn't already been called with true
            // 
            // This prevents calling this once each for true and false when a true
            // would handle what a false would anyway
            register_shutdown_function(array('MetaDataManager', 'clearAPICacheOnShutdown'), $deleteModuleClientCache, getcwd());
            self::$clearCacheOnShutdown[$key] = true;
        }
    }
    
    /**
     * Clears the API metadata cache of all cache files
     *
     * @param bool $deleteModuleClientCache Should we also delete the client file cache of the modules
     * @param string $workingDirectory directory to chdir into before starting the clears
     * @static
     */
    public static function clearAPICacheOnShutdown($deleteModuleClientCache = true, $workingDirectory = "")
    {
        //shutdown functions are not always called from the same working directory as the script that registered it
        //Need to chdir to ensure we can find the correct files
        if (!empty($workingDirectory)) {
            chdir($workingDirectory);
        }


        if ($deleteModuleClientCache) {
            // Delete this first so there is no race condition between deleting a metadata cache
            // and the module client cache being stale.
            MetaDataFiles::clearModuleClientCache();
        }

        // Wipe out any files from the metadata cache directory
        $metadataFiles = glob(sugar_cached('api/metadata/').'*');
        if ( is_array($metadataFiles) ) {
            foreach ($metadataFiles as $metadataFile) {
                // This removes the file and the reference from the map. This does
                // NOT save the file map since that would be expensive in a loop
                // of many deletes.
                unlink($metadataFile);
            }
        }

        // clear the platform cache from sugar_cache to avoid out of date data as well as platform component files
        $platforms = self::getPlatformList();
        foreach ($platforms as $platform) {
            $platformKey = $platform == "base" ?  "base" : implode(",", array($platform, "base"));
            $hashKey = "metadata:$platformKey:hash";
            sugar_cache_clear($hashKey);
            $jsFiles = glob(sugar_cached("javascript/{$platform}/").'*');
            if (is_array($jsFiles) ) {
                foreach ($jsFiles as $jsFile) {
                    unlink($jsFile);
                }
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
        return $this->setupModuleLists($data);
    }

    /**
     * Rebuilds the JS Source File section of the metadata. Called by refreshSectionCache
     * 
     * @param array $data Existing metadata
     * @return mixed
     */
    protected function rebuildJssourceSection($data)
    {
        
        $data['jssource'] = $this->buildJavascriptComponentFile($data, !$this->public);
        return $data;
    }
    
    /**
     * Rebuilds the language file caches
     * 
     * @param string|array $languages Languages to rebuild caches for
     */
    public function rebuildLanguagesCache($languages)
    {
        // If there is no languages passed then do nothing
        if (!empty($languages)) {
            // We will always need the metadata for this process, but only if there
            // is existing metadata to work (why build a section of an empty set)
            $data = $this->getMetadata(array(), false);
            
            if (!empty($data)) {
                $this->clearLanguagesCache($languages);
                $data = $this->loadSectionMetadata(self::MM_LABELS, $data);
                $data['_hash'] = $this->hashChunk($data);
                $this->putMetadataCache($data);
            }
        }
    }

    /**
     * Rebuilds the metadata for a module or modules provided the metadata cache
     * exists already.
     * 
     * @param string|array $modules A single module or array of modules
     * @param array $data Existing metadata
     */
    public function rebuildModulesCache($modules, $data = array()) 
    {
        // Only write if were actually asked for
        $write = false;
        
        // Only process if there are modules to work on
        if (!empty($modules) && $this->isValidSection('modules')) {
            // If there was no metadata payload given, get the metadata from the 
            // source. Same as with section caching, we only want to rebuild the
            // modules metadata if there are modules metadata already. 
            if (empty($data)) {
                $data = $this->getMetadata(array(), false);
            }
            
            if (!empty($data)) {
                // Now clear the module client cache for these modules so that 
                // getModuleData is fresh
                MetaDataFiles::clearModuleClientCache($modules);
                
                // Handle the module(s)
                foreach ((array) $modules as $module) {
                    // Only work on modules that was have already grabbed
                    if (isset($data['modules'][$module])) {
                        $index = 'module:' . $module;
                        if (isset(self::$currentProcess[$index])) {
                            continue;
                        }
                        
                        self::$currentProcess[$index] = true;
                        
                        $bean = BeanFactory::newBean($module);
                        if ($bean) {
                            $data['modules'][$module] = $this->getModuleData($module);
                            $this->relateFields($data, $module, $bean);
                            unset($bean);
        
                            if (!$write) {
                                $write = true;
                            }
                        }
                        
                        unset(self::$currentProcess[$index]);
                    }
                }
            }
            
            // Now cache the new data if there is a need
            if ($write) {
                $data['_hash'] = $this->hashChunk($data);
                $this->putMetadataCache($data);
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
            $data = $this->getMetadata(array(), false);
            
            if (!empty($data)) {
                // Handle the section(s)
                foreach ((array) $section as $index) {
                    if (isset($this->sectionMap[$index]) && $this->isValidSection($index)) {
                        if (isset(self::$currentProcess[$index])) {
                            continue;
                        }
                        
                        self::$currentProcess[$index] = true;
                        $data = $this->loadSectionMetadata($index, $data);
                        unset(self::$currentProcess[$index]);
                        
                        if (!$write) {
                            $write = true;
                        }
                    }
                }
            }
            
            // Now cache the new data if there is a need
            if ($write) {
                $data['_hash'] = $this->hashChunk($data);
                $this->putMetadataCache($data);
            }
        }
    }

    /**
     * Rebuilds the cache for this platform and visibility
     * 
     * @param bool $force Indicator that tells this method whether to force a build
     */
    public function rebuildCache($force = false) 
    {
        // Delete our current supply of caches if there are any
        $deleted = $this->deletePlatformVisibilityCaches();
        
        // Rebuild the cache if there was a deleted cache or if we are forced to
        if ($force || $deleted) {
            // Clear the module client cache first
            MetaDataFiles::clearModuleClientCache(array(), '', array($this->platforms[0]));

            $data = $this->loadMetadata();
            $this->putMetadataCache($data);

            // Handle deleted languages as necessary. This will be set in the
            // call to deletePlatformVisibilityCaches().
            if (!empty($this->deletedLanguageCaches)) {
                foreach ($this->deletedLanguageCaches as $lang) {
                    $this->getLanguage($lang);
                }

                // Reset the deleted languages stack
                $this->deletedLanguageCaches = array();
            }
        }
    }

    /**
     * Rewrites caches for all metadata manager platforms and visibility
     * 
     * @param array $platforms
     * @param bool $force Indicator that tells this method whether to force a build
     */
    public static function refreshCache($platforms = array(), $force = false)
    {
        // If we are in queue state (like in RepairAndRebuild), hold on to this
        // request until we are told to run it
        if (self::$isQueued) {
            self::$queue['full'] = $platforms;
            return;
        }
        
        // Set our inProcess flag;
        self::$inProcess = true;
        
        // The basics are, for each platform, rewrite the cache for public and private
        if (empty($platforms)) {
            $platforms = self::getPlatformList();
        }
        
        foreach ((array) $platforms as $platform) {
            foreach (array(true, false) as $public) {
                $mm = self::getManager($platform, $public, true);
                $mm->rebuildCache($force);
            }
        }
        
        // Reset the in process flag
        self::$inProcess = false;
    }

    /**
     * Refreshes the cache for a section or collection of sections
     * 
     * @param string $section
     * @param array $platforms
     */
    public static function refreshSectionCache($section = '', $platforms = array())
    {
        self::refreshCachePart('section', $section, $platforms);
    }

    /**
     * Refreshes the cache for a module or collection of modules.
     * 
     * @param array $modules
     * @param array $platforms
     */
    public static function refreshModulesCache($modules = array(), $platforms = array())
    {
        self::refreshCachePart('modules', $modules, $platforms);
    }

    /**
     * Refreshes the language cache files for the metadata for a collection of
     * languages. This will primarily be used by studio to change lang strings.
     * 
     * @param array $languages Array of languages to refresh the caches of
     * @param array $platforms List of platforms for this request
     */
    public static function refreshLanguagesCache($languages = array(), $platforms = array())
    {
        self::refreshCachePart('languages', $languages, $platforms);
    }

    /**
     * Refreshes a single part of the cache provided there is a compatible rebuild*
     * method to do so. A part of the cache can be modules, sections or languages
     * since these all have their own caches that need to be dealt with.
     * 
     * @param string $part which part of the cache to build
     * @param array $args List of items to be passed to the rebuild method
     * @param array $platforms List of platforms to carry out the refresh for
     * @return null
     */
    protected static function refreshCachePart($part, $args = array(), $platforms = array())
    {
        // No args, no worries
        if (empty($args)) {
            return;
        }
        
        // If we are in the middle of a refresh do nothing
        if (self::$inProcess) {
            return;
        }
        
        // If we are in queue state (like in RepairAndRebuild), hold on to this
        // request until we are told to run it
        if (self::$isQueued) {
            self::buildCacheRefreshQueueSection($part, $args, $platforms);
            return;
        }
        
        if (empty($platforms)) {
            // Only get platforms with existing caches so we don't build everything
            // if we don't need to
            $platforms = self::getPlatformsWithCaches();
        }
        
        // This only needs to be done for private visibility since modules are not
        // in public metadata
        $method = 'rebuild' . ucfirst(strtolower($part)) . 'Cache';
        foreach ((array) $platforms as $platform) {
            $mm = self::getManager($platform, false, true);
            if (method_exists($mm, $method)) {
                $mm->$method($args);
            }
        }
    }

    /**
     * Builds up a section of the refreshCacheQueue based on name.
     * 
     * @param string $name Name of the queue section
     * @param array  $data The list of modules or sections
     * @param array  $platforms The list of platforms
     */
    protected static function buildCacheRefreshQueueSection($name, $data, $platforms)
    {
        if (is_array($data)) {
            foreach ($data as $item) {
                self::$queue[$name][$item] = $item;
            }
        } else {
            self::$queue[$name][$data] = $data;
        }
        
        // Keep track of platforms... use the fullest list presented
        if (!isset(self::$queue[$name]['platforms'])) {
            self::$queue[$name]['platforms'] = array();
        }
        
        self::$queue[$name]['platforms'] = array_merge(self::$queue[$name]['platforms'], (array) $platforms);
    }

    /**
     * Runs all of the cache refreshers in the queue. If $disable is false, will
     * leave the queue state as is. By default, will turn the queue off when it
     * completes.
     * 
     * @param bool $disable
     */
    public static function runCacheRefreshQueue($disable = true)
    {
        // Hold on to the queue state until later when we need it
        $queueState = self::$isQueued;
        
        // Temporarily turn off queueing to allow this to happen
        self::$isQueued = false;
        
        // If full is set, run all cache clears and be done
        if (isset(self::$queue['full'])) {
            // Handle the refreshing of the cache and emptying of the queue
            self::refreshCache(self::$queue['full']);
            self::$queue = array();
        }
        
        // Run modules first
        foreach (self::$cacheParts as $part => $method) {
            if (isset(self::$queue[$part])) {
                if (isset(self::$queue[$part]['platforms'])) {
                    $platforms = self::$queue[$part]['platforms'];
                    unset(self::$queue[$part]['platforms']);
                } else {
                    $platforms = array();
                }
                
                self::$method(self::$queue[$part], $platforms);
                unset(self::$queue[$part]);
            }
        }

        // Handle queue state
        if ($disable) {
            self::$isQueued = false;
        } else {
            self::$isQueued = $queueState;
        }
    }

    /**
     * Turns on the cache refresh queue
     */
    public static function enableCacheRefreshQueue()
    {
        self::$isQueued = true;
    }

    /**
     * Turns off the cache refresh queue and runs any of the rebuild processes
     * currently in the queue
     */
    public static function disableCacheRefreshQueue() 
    {
        self::$isQueued = false;
        self::runCacheRefreshQueue();
    }

    /**
     * Simply runs the queue and resets the queue to empty leaving queue state in
     * tact
     */
    public static function flushCacheRefreshQueue()
    {
        self::runCacheRefreshQueue(false);
    }

    /**
     * Gets server information
     *
     * @return array of ServerInfo
     */
    public function getServerInfo()
    {
        global $system_config;
        $data['flavor'] = $GLOBALS['sugar_flavor'];
        $data['version'] = $GLOBALS['sugar_version'];
        $data['build'] = $GLOBALS['sugar_build'];
        //BEGIN SUGARCRM flav=pro ONLY
        // Product Name for Professional edition.
        $data['product_name'] = "SugarCRM Professional";
        //END SUGARCRM flav=pro ONLY
        //BEGIN SUGARCRM flav=ent ONLY
        // Product Name for Enterprise edition.
        $data['product_name'] = "SugarCRM Enterprise";
        //END SUGARCRM flav=ent ONLY
        //BEGIN SUGARCRM flav=corp ONLY
        // Product Name for Corp edition.
        $data['product_name'] = "SugarCRM Corporate";
        //END SUGARCRM flav=corp ONLY
        //BEGIN SUGARCRM flav=ult ONLY
        // Product Name for Ultimate edition.
        $data['product_name'] = "SugarCRM Ultimate";
        //END SUGARCRM flav=ult ONLY
        if (file_exists('custom/version.php')) {
            include 'custom/version.php';
            $data['custom_version'] = $custom_version;
        }

        if(isset($system_config->settings['system_skypeout_on']) && $system_config->settings['system_skypeout_on'] == 1){
            $data['system_skypeout_on'] = true;
        }
        //BEGIN SUGARCRM flav=pro ONLY
        $fts_enabled = SugarSearchEngineFactory::getFTSEngineNameFromConfig();
        if (!empty($fts_enabled) && $fts_enabled != 'SugarSearchEngine') {
            $data['fts'] = array(
                'enabled' => true,
                'type' => $fts_enabled,
            );
        } else {
            $data['fts'] = array(
                'enabled' => false,
            );
        }
        //END SUGARCRM flav=pro ONLY

        //BEGIN SUGARCRM flav=ent ONLY
        //Adds the portal status to the server info collection.
        $admin = Administration::getSettings();
        //Property 'on' of category 'portal' must be a boolean.
        $data['portal_active'] = !empty($admin->settings['portal_on']);
        //END SUGARCRM flav=ent ONLY

        return $data;
    }

    /**
     * Gets configs
     *
     * @return array
     */
    protected function getConfigs()
    {
        global $sugar_config;
        $administration = new Administration();
        $administration->retrieveSettings();
        // These configs are controlled via System Settings in Administration module
        $configs = array(
            'maxQueryResult' => $sugar_config['list_max_entries_per_page'],
            'maxSubpanelResult' => $sugar_config['list_max_entries_per_subpanel'],
            'maxRecordFetchSize' => $sugar_config['max_record_fetch_size'],
            'massUpdateChunkSize' => $sugar_config['mass_update_chunk_size'],
            'massDeleteChunkSize' => $sugar_config['mass_delete_chunk_size'],
            'mergeRelateFetchConcurrency' => $sugar_config['merge_relate_fetch_concurrency'],
            'mergeRelateFetchTimeout' => $sugar_config['merge_relate_fetch_timeout'],
            'mergeRelateFetchLimit' => $sugar_config['merge_relate_fetch_limit'],
            'mergeRelateUpdateConcurrency' => $sugar_config['merge_relate_update_concurrency'],
            'mergeRelateUpdateTimeout' => $sugar_config['merge_relate_update_timeout'],
            'mergeRelateMaxAttempt' => $sugar_config['merge_relate_max_attempt'],
        );

        if (isset($administration->settings['honeypot_on'])) {
            $configs['honeypot_on'] = true;
        }
        if (isset($GLOBALS['sugar_config']['passwordsetting']['forgotpasswordON'])) {
            if ($GLOBALS['sugar_config']['passwordsetting']['forgotpasswordON'] === '1' || $GLOBALS['sugar_config']['passwordsetting']['forgotpasswordON'] === true) {
                $configs['forgotpasswordON'] = true;
            } else {
                $configs['forgotpasswordON'] = false;
            }
        }
        $auth = AuthenticationController::getInstance();
        if($auth->isExternal()) {
            $configs['externalLogin'] = true;
        }

        return $configs;
    }

    /**
     * Checks the validity of the current session metadata hash value. Since the
     * only time the session value is set is after a metadata fetch has been made
     * a non-existent session value is valid. However if there is a session value
     * then there either has to be a metadata cache of hashes to check against
     * or the session value has to be false (meaning the session value was set
     * before the metadata cache was built) in order to pass the validity check.
     *
     * @param string   $hash Metadata hash to validate against the cache.
     *
     * @return boolean
     */
    public function isMetadataHashValid($hash)
    {
        // Is there a current metadata hash sent in the request (empty string is not a valid hash)
        if (!empty($hash)) {
            // See if there is a hash cache. If there is, see if the hash cache
            // for this platform matches what's in the session, ensuring that the
            // session value isn't false (the default value when setting from
            // cache)
            $platformHash = $this->getMetadataHash();
            
            if ($platformHash === false) {
                //If the cache file doesn't exist, we have no way to know if the current hash is correct
                //and most likely the cache file was nuked due to a metadata change so the client
                //needs to hit the metadata api anyhow.
                return false;
            } else {
                return $platformHash == $hash;
            }
        }

        // There is no session var so we say we're good so as not to get stuck in
        // a continual logout loop
        return true;
    }

    /**
     * Tells the app the user preference metadata has changed.
     * 
     * For now this will be done by simply changing the date_modified on the User
     * record and using that as the metadata hash value. This could change in the
     * future.
     *
     * @param Person $user The user that is changing preferences
     */
    public function setUserMetadataHasChanged($user)
    {
        $user->update_date_modified = true;
        $user->save();
    }

    /**
     * Checks the state of changed metadata for a user
     *
     * @param Person $user The user that is changing preferences
     * @param string $hash The user preference data hash to compare
     *
     * @return bool
     */
    public function hasUserMetadataChanged($user, $hash)
    {
       return $user->getUserMDHash() != $hash;
    }

    /**
     * Gets all metadata for the current platform and visibility
     * 
     * NOTE ON $buildCache - In most cases this will be true. But in edge cases, 
     * like installation when there isn't a database yet, this has to be false 
     * since we can't try get module information without the ability to get to 
     * the database.
     * 
     * @param array $args Arguments passed into the request for metadata
     * @param bool $buildCache Flag that tells the getters whether to build the
     *                         cache. 
     * @return mixed
     */
    public function getMetadata($args = array(), $buildCache = true)
    {
        // Get our metadata
        $data = $this->getMetadataCache();

        //If we failed to load the metadata from cache, load it now the hard way.
        if (empty($data) && $buildCache) {
            // Allow more time for private metadata builds since it is much heavier
            if (!$this->public) {
                ini_set('max_execution_time', 0);
            }
            $data = $this->loadMetadata($args);
            $this->putMetadataCache($data);
        }

        // Only finish working the metadata if there was data to start with. There
        // is a chance that $data will be empty at this point if there is no cache
        // and $buildCache is false.
        if (!empty($data)) {
            // Bug 60345 - Default currency id of -99 was failing hard on 64bit 5.2.X
            // PHP builds. This was causing metadata to store a different value in the 
            // cache than -99. The fix was to add a space arround the -99 to force it
            // to string. This trims that value prior to sending it to the client.
            // 
            // @TODO: Is this still relevant?
            $data = $this->normalizeCurrencyIds($data);

            // We need to see if we need to send any warnings down to the user
            $systemStatus = apiCheckSystemStatus();
            if ($systemStatus !== true) {
                // Something is up with the system status
                // We need to tack it on and refresh the hash
                $data['config']['system_status'] = $systemStatus;
                $data['_hash'] = md5($data['_hash'].serialize($systemStatus));
            }
        }

        return $data;
    }

    /**
     * Gets the metadata cache for a given platform and visibility
     * 
     * @return array The metadata cache is it exists, null otherwise
     */
    protected function getMetadataCache()
    {
        if (inDeveloperMode()) {
            return null;
        }

        $cacheFile = $this->getMetadataCacheFileName($this->platforms[0], $this->public);
        if (file_exists($cacheFile)) {
            require $cacheFile;
            return $metadata;
        }

        // No metadata file found
        return null;
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
     * Builds the current platform and visibility metadata and returns it
     * 
     * @param array $args Arguments passed into the request for metadata
     * @return array
     */
    protected function loadMetadata($args = array())
    {
        // Start collecting data
        $data = array();
        
        foreach ($this->sections as $section) {
            // Overrides are handled at the end because they are "special"
            // full_module_list is handled by the modules section handler and is
            // only found in private metadata
            if ($section == self::MM_OVERRIDEVALUES || $section == self::MM_FULLMODULELIST) {
                continue;
            }

            $data = $this->loadSectionMetadata($section, $data);
        }

        // Handle overrides
        $data['_override_values'] = $this->getOverrides($data, $args);

        // Handle hashing
        $data["_hash"] = $this->hashChunk($data);

        // Send it back
        return $data;
    }

    /**
     * Utility method shared between the metadata loader and section rebuilder
     * 
     * @param string $section The section to build
     * @param array $data The metadata payload that is appended to
     * @return array Appended metadata
     */
    protected function loadSectionMetadata($section, $data)
    {
        // Adopt the same logic as the section rebuilder
        if (isset($this->sectionMap[$section])) {
            if ($this->sectionMap[$section] === false) {
                $method = 'rebuild' . ucfirst($section) . 'Section';
                $data = $this->$method($data);
            } else {
                $method = $this->sectionMap[$section];
                $data[$section] = $this->$method();
            }
        }
        
        return $data;
    }

    /**
     * Gets the system logo url
     * @return string
     */
    public function getLogoUrl()
    {
        return SugarThemeRegistry::current()->getImageURL('company_logo.png');
    }

    /**
     * Gets the list of hidden subpanels
     * 
     * @return array
     */
    public function getHiddenSubpanels()
    {
        // BR-29 Handle hidden subpanels - SubPanelDefinitons needs a bean at
        // construct time, so hand it an admin bean. This returns a list of
        // hidden subpanels in lowercase module name form:
        // array('accounts', 'bugs', 'contacts');
        $spd = new SubPanelDefinitions(BeanFactory::getBean('Administration'));
        return array_values($spd->get_hidden_subpanels());
    }

    /**
     * Builds the javascript file used by the clients
     * 
     * @param array $data The metadata to build from
     * @param boolean $onlyReturnModuleComponents Indicator to return only module
     *                                            components
     * @return string A url to the file that was just built
     */
    protected function buildJavascriptComponentFile(&$data, $onlyReturnModuleComponents = false)
    {
        $platform = $this->platforms[0];
        
        $js = "(function(app) {\n SUGAR.jssource = {";


        $compJS = $this->buildJavascriptComponentSection($data);
        if (!$onlyReturnModuleComponents) {
            $js .= $compJS;
        }

        if (!empty($data['modules'])) {
            if (!empty($compJS) && !$onlyReturnModuleComponents)
                $js .= ",";

            $js .= "\n\t\"modules\":{";

            $allModuleJS = '';
            foreach ($data['modules'] as $module => $def) {
                $moduleJS = $this->buildJavascriptComponentSection($def,true);
                if (!empty($moduleJS)) {
                    $allModuleJS .= ",\n\t\t\"$module\":{{$moduleJS}}";
                }
            }
            //Chop off the first comma in $allModuleJS
            $js .= substr($allModuleJS, 1);
            $js .= "\n\t}";
        }

        $js .= "}})(SUGAR.App);";
        $hash = md5($js);
        //If we are going to be using uglify to minify our JS, we should minify the entire file rather than each component separately.
        if (!inDeveloperMode() && SugarMin::isMinifyFast()) {
            $js = SugarMin::minify($js);
        }
        $path = "cache/javascript/$platform/components_$hash.js";
        if (!file_exists($path)) {
            mkdir_recursive(dirname($path));
            sugar_file_put_contents_atomic($path, $js);
        }

        return $this->getUrlForCacheFile($path);
    }

    /**
     * Builds component javascript
     * 
     * @param array $data The metadata to build from
     * @param boolean $isModule Module specific indicator
     * @return string A javascript string
     */
    protected function buildJavascriptComponentSection(&$data, $isModule = false)
    {
        $js = "";
        $platforms = array_reverse($this->platforms);

        $typeData = array();

        if ($isModule) {
            $types = array('fieldTemplates', 'views', 'layouts');
        } else {
            $types = array('fields', 'views', 'layouts');
        }

        foreach ($types as $mdType) {

            if (!empty($data[$mdType])) {
                $platControllers = array();

                foreach ($data[$mdType] as $name => $component) {
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

                            if ( !isset($platControllers[$platform]) ) {
                                $platControllers[$platform] = array();
                            }
                            $platControllers[$platform][] = "\"$name\": {\"controller\": ".$controller." }";

                        }
                    }
                    unset($data[$mdType][$name]['controller']);
                }

                // We should have all of the controllers for this type, split up by platform
                $thisTypeStr = "\"$mdType\": {\n";

                foreach ($platforms as $platform) {
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
     * @param string $controller The controller this is being done for
     * @param string $mdType The type of metadata
     * @param string $name The name
     * @param string $platform The platform for this string
     * @return string
     */
    protected function insertHeaderComment($controller, $mdType, $name, $platform)
    {
        $singularType = substr($mdType, 0, -1);
        $needle = '({';
        $headerComment = "\n\t// " . ucfirst($name) ." ". ucfirst($singularType) . " ($platform) \n";

        // Find position "after" needle
        $pos = (strpos($controller, $needle) + strlen($needle));

        // Insert our comment and return ammended controller
        return substr($controller, 0, $pos) . $headerComment . substr($controller, $pos);
    }

    /**
     * Gets all enabled and disabled languages. Wraps the util function to allow
     * for manipulation of the return in the future.
     * 
     * @return array Array of enabled and disabled languages
     */
    public function getAllLanguages()
    {
        $languages = LanguageManager::getEnabledAndDisabledLanguages();

        return array(
            'enabled' => $this->getLanguageKeys($languages['enabled']), 
            'disabled' => $this->getLanguageKeys($languages['disabled']),
        );
    }

    /**
     * Gets language keys only. Used by the API in conjunction with language indexes
     * from app_list_strings.
     * 
     * @param array $language An enabled or disabled language array
     * @return array
     */
    protected function getLanguageKeys($language)
    {
        $return = array();
        foreach ($language as $lang) {
            $return[] = $lang['module'];
        }
        return $return;
    }

    /**
     * Returns a language JSON contents
     * 
     * @param string $lang
     */
    public function getLanguage($lang)
    {
        return $this->getLanguageFileData($lang);
    }
    
    /**
     * Get the data element of the language file properties for a language
     *
     * @param  string  $lang   The language to get data for
     * @return string  A JSON string of langauge data
     */
    protected function getLanguageFileData($lang)
    {
        $resp = $this->getLanguageFileProperties($lang);
        return $resp['data'];
    }

    /**
     * Gets full module list and data for each module and uses that data to 
     * populate the modules/full_module_list section of the metadata
     *
     * @param array $data Existing metadata
     * @return array
     */
    public function populateModules($data)
    {
        $data['full_module_list'] = $this->getModuleList();
        $data['modules'] = array();
        foreach($data['full_module_list'] as $key => $module) {
            if ($key == '_hash') {
                continue;
            }
            
            $bean = BeanFactory::newBean($module);
            $data['modules'][$module] = $this->getModuleData($module);
            $this->relateFields($data, $module, $bean);
        }
        return $data;
    }

    /**
     * Gets the cleaned up list of modules for this client
     * @return array
     */
    public function getModuleList()
    {
        $moduleList = $this->getModules();
        $oldModuleList = $moduleList;
        $moduleList = array();
        foreach ( $oldModuleList as $module ) {
            $moduleList[$module] = $module;
        }

        $moduleList['_hash'] = $this->hashChunk($moduleList);
        return $moduleList;
    }

    /**
     * Gets the list of modules for this client
     *
     * @return array
     */
    protected function getModules()
    {
        // Loading a standard module list. If the module list isn't set into the
        // globals, load them up. This happens on installation.
        if (empty($GLOBALS['app_list_strings']['moduleList'])) {
            $als = return_app_list_strings_language($GLOBALS['current_language']);
            $list = $als['moduleList'];
        } else {
            $list = $GLOBALS['app_list_strings']['moduleList'];
        }

        // TODO - need to make this more extensible through configuration
        $list['Audit'] = true;
        return array_keys($list);
    }

    /**
     * Loads relationships for relate and link type fields
     * @param array $data load metadata array
     * @return array
     */
    protected function relateFields(&$data, $module, $bean)
    {
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
     * Gets currencies
     * @return array
     */
    public function getSystemCurrencies()
    {
        $currencies = array();
        require_once 'modules/Currencies/ListCurrency.php';
        $lcurrency = new ListCurrency();
        $lcurrency->lookupCurrencies(true);
        if (!empty($lcurrency->list)) {
            foreach ($lcurrency->list as $current) {
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
                // to add a space around the -99 to force it to string.
                $id = $current->id == -99 ? '-99 ': $current->id;
                $currencies[$id] = $currency;
            }
        }

        return $currencies;
    }

    /**
     * Gets the moduleTabMap array to allow clients to decide which menu element
     * a module should live in for non-module modules
     *
     * @return array
     */
    public function getModuleTabMap()
    {
        return $GLOBALS['moduleTabMap'];
    }

    /**
     * Returns a list of URL's pointing to json-encoded versions of the strings
     *
     * @param  array $data The metadata array
     * @return array
     */
    public function getStringUrls()
    {
        $languageList = array_keys(get_languages());
        sugar_mkdir(sugar_cached('api/metadata'), null, true);

        $fileList = array();
        foreach ($languageList as $language) {
            $fileList[$language] = $this->getLangUrl($language);
        }
        $urlList = array();
        foreach ($fileList as $lang => $file) {
            // Get the hash for this lang file so we can append it to the URL.
            // This fixes issues where lang strings or list strings change but
            // don't force a metadata refresh
            $hash = $this->getLanguageFileModified($lang);
            $urlList[$lang] = $this->getUrlForCacheFile($file) . '?v=' . $hash;
        }
        $urlList['default'] = $GLOBALS['sugar_config']['default_language'];

        return $urlList;
    }

    /**
     * Public read only accessor for getting a language file hash if there is one
     * 
     * @param string $lang The language to get a hash for
     * @return string The hash if there is one, false otherwise
     */
    public function getLanguageHash($lang)
    {
        return $this->getCachedLanguageHash($lang);
    }

    /**
     * Get the hash element of the language file properties for a language
     *
     * @param  string  $lang   The language to get data for
     * @return string  The date modifed of the language file
     */
    protected function getLanguageFileModified($lang)
    {
        $ret = "";
        $custAppPaths = array(
            "custom/application/Ext/Language/$lang.lang.ext.php",
            "custom/include/language/$lang.lang.php"
        );
        foreach($custAppPaths as $custFilePath) {
            if (SugarAutoLoader::fileExists($custFilePath)){
                $ret = max(filemtime($custFilePath), $ret);
            }
        }
        foreach($this->getModules() as $module) {
            $modPaths = array(
                'custom/modules/' . $module . '/Ext/Language/' . $lang . '.lang.ext.php',
                'custom/modules/' . $module . '/language/' . $lang . '.lang.php',
            );
            foreach($modPaths as $custFilePath) {
                if (SugarAutoLoader::fileExists($custFilePath)){
                    $ret = max(filemtime($custFilePath), $ret);
                }
            }
        }
        return $ret;
    }

    /**
     * Gets a url for a language file
     * 
     * @param string $language The language to get the file for
     * @return string
     */
    protected function getLangUrl($language)
    {
        $public_key = $this->public ? "_public" : "";
        $platform = $this->platforms[0];
        return  sugar_cached("api/metadata/lang_{$language}_{$platform}{$public_key}.json");
    }

    /**
     * Get the hash element of the language file properties for a language
     *
     * @param  string  $lang   The language to get data for
     * @return string  The hash of the contents of the language file
     */
    protected function getLanguageFileHash($lang)
    {
        $resp = $this->getLanguageFileProperties($lang);
        return $resp['hash'];
    }

    /**
     * Gets the file properties for a language
     *
     * @param  string  $lang   The language to get data for
     * @return array   Array containing the hash and data for a language file
     */
    protected function getLanguageFileProperties($lang)
    {
        $hash = $this->getCachedLanguageHash($lang);
        $resp = $this->buildLanguageFile($lang, $this->getModuleList());
        if (empty($hash) || $hash != $resp['hash']) {
            $this->putCachedLanguageHash($lang, $resp['hash']);
        }

        return $resp;
    }

    /**
     * Gets a hash for a cached language
     * 
     * @param string $lang The lang to get the hash for
     * @return string
     */
    protected function getCachedLanguageHash($lang)
    {
        $key = $this->getLangUrl($lang);

        return $this->getFromHashCache($key);
    }

    /**
     * Builds the language javascript file if needed, else returns what is known
     *
     * @param string $language The language for this file
     * @param array $modules The module list
     * @return array Array containing the language file contents and the hash for the data
     */
    protected function buildLanguageFile($language, $modules)
    {
        sugar_mkdir(sugar_cached('api/metadata'), null, true);
        $filePath = $this->getLangUrl($language);
        if (SugarAutoLoader::fileExists($filePath)) {
            // Get the contents of the file so that we can get the hash
            $data = file_get_contents($filePath);

            // Decode the json and get the hash. The hash should be there but
            // check for it just in case something went wrong somewhere.
            $array = json_decode($data, true);
            $hash = isset($array['_hash']) ? $array['_hash'] : '';

            // Cleanup
            unset($array);

            // Return the same thing as would be returned if we had to build the
            // file for the first time
            return array('hash' => $hash, 'data' => $data);
        }

        $stringData = array();
        $stringData['app_list_strings'] = $this->getAppListStrings($language);
        $stringData['app_strings'] = $this->getAppStrings($language);
        if ($this->public) {
            // Exception for the AppListStrings.
            $app_list_strings_public = array();
            $app_list_strings_public['available_language_dom'] = $stringData['app_list_strings']['available_language_dom'];

            // Let clients fill in any gaps that may need to be filled in
            $app_list_strings_public = $this->fillInAppListStrings($app_list_strings_public, $stringData['app_list_strings'],$language);
            $stringData['app_list_strings'] = $app_list_strings_public;

        } else {
            $modStrings = array();
            foreach ($modules as $modName => $moduleDef) {
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
        $stringData['_hash'] = $this->hashChunk($stringData);
        $data = json_encode($stringData);
        sugar_file_put_contents_atomic($filePath,$data);

        return array("hash" => $stringData['_hash'], "data" => $data);
    }

    /**
     * Fills in additional app list strings data as needed by the client
     *
     * @param  array $public Public app list strings
     * @param  array $main Core app list strings
     * @return array
     */
    protected function fillInAppListStrings(Array $public, Array $main)
    {
        return $public;
    }

    /**
     * Gets a hash from a hash key in the hash cache
     * 
     * @param string $key The hash cache key
     * @return string A metadata hash if found, false otherwise
     */
    protected function getFromHashCache($key)
    {
        $hashes = array();
        $path = sugar_cached("api/metadata/hashes.php");
        @include($path);

        return !empty($hashes[$key]) ? $hashes[$key] : false;
    }

    /**
     * Gets a URL for a cache file. This is most useful in overrides where a base
     * path can be prepended to the $cacheFile for use in CDN setups and such.
     * 
     * @param string $cacheFile The cache file to build a URL to
     * @return string The url for the cached file
     */
    public function getUrlForCacheFile($cacheFile)
    {
        // This is here so we can override it and have the cache files upload to a CDN
        // and return the CDN locations later.
        return $cacheFile;
    }

    /**
     * Wrapper to add a language file hash to the hash cache
     * 
     * @param string $lang The language string for the language file
     * @param string $hash The hash for the language file
     */
    protected function putCachedLanguageHash($lang, $hash)
    {
        $key = $this->getLangUrl($lang);
        $this->addToHashCache($key, $hash);
    }

    /**
     * Adds a language file hash to the hash cache
     * 
     * @param string $key The key for this cache hash
     * @param string $hash The hash to match to this key
     */
    protected function addToHashCache($key, $hash)
    {
        $hashes = array();
        $path = sugar_cached("api/metadata/hashes.php");
        @include($path);
        $hashes[$key] = $hash;
        write_array_to_file("hashes", $hashes, $path);
        SugarAutoLoader::addToMap($path);
    }

    /**
     * Bug 60345
     *
     * Normalizes the -99 currency id to remove the space added to the index prior
     * to storing in the cache.
     *
     * @param  array $data The metadata
     * @return array
     */
    protected function normalizeCurrencyIds($data)
    {
        if (isset($data['currencies']['-99 '])) {
            // Change the spaced index back to normal
            $data['currencies']['-99'] = $data['currencies']['-99 '];

            // Ditch the spaced index
            unset($data['currencies']['-99 ']);
        }

        return $data;
    }

    /**
     * Gets override values from an argument list
     * 
     * @param Array $data data to be returned to the client
     * @param Array $args args passed to the API
     */
    protected function getOverrides($data, $args = array()) {
        if (isset($args['override_values']) && is_array($args['override_values'])) {
            return $args['override_values'];
        }

        return array_intersect(array_keys($data), self::$defaultOverrides);
    }

    /**
     * Adds a metadata file hash to the hash cache
     * 
     * @param string $hash The hash for the metadata file
     * @return null
     */
    protected function cacheMetadataHash($hash)
    {
        $key = $this->getCachedMetadataHashKey();
        return $this->addToHashCache($key, $hash);
    }

    /**
     * Gets the hash for a metadata file cache
     * 
     * @return string A metadata cache file hash or false if not found
     */
    protected function getCachedMetadataHash()
    {
        $key = $this->getCachedMetadataHashKey();
        return $this->getFromHashCache($key);
    }

    /**
     * Saves the metadata into a cache file
     * 
     * @param array $data The metadata to cache
     */
    protected function putMetadataCache($data)
    {
        // Create the cache cirectory if need be
        // The is a fix for the cache/cache/api/metadata problem
        $cacheDir  = 'api/metadata';
        create_cache_directory($cacheDir);

        // Handle the cache file
        $cacheFile = $this->getMetadataCacheFileName();
        $write =   "<?php\n" .
                   '// created: ' . date('Y-m-d H:i:s') . "\n" .
                   '$metadata = ' .
                    var_export_helper($data) . ';';

        // Write with atomic writing to prevent issues with simultaneous requests
        // for this file
        sugar_file_put_contents_atomic($cacheFile, $write);

        // Cache the hash as well
        if (isset($data['_hash'])) {
            $this->cacheMetadataHash($data['_hash']);
        }
    }

    /**
     * Deletes caches for this metadata manager visibility and platform
     */
    protected function deletePlatformVisibilityCaches()
    {
        //Sets a flag that tells callers whether this action actually did something
        $deleted = false;

        // Get the hashes array
        $hashes = array();
        $path = sugar_cached("api/metadata/hashes.php");
        @include($path);

        // Delete language caches and remove from the hash cache
        $pattern = $this->getLangUrl('(.*)');
        foreach ($hashes as $k => $v) {
            if (preg_match("#^{$pattern}$#", $k, $m)) {
                // Add the deleted language to the stack
                $this->deletedLanguageCaches[] = $m[1];

                // Remove from the cache
                unset($hashes[$k]);
                
                // Delete the file
                unlink($k);
                
                $deleted = true;
            }
        }

        // Then delete the metadata cache and delete from the hash cache if there
        // is a cache to handle
        $cacheFile = $this->getMetadataCacheFileName();
        $cacheKey  = $this->getCachedMetadataHashKey();
        if (file_exists($cacheFile)) {
            unset($hashes[$cacheKey]);
            unlink($cacheFile);
            $deleted = true;
        }

        // Save file I/O by only writing if there are changes to write
        if ($deleted) {
            write_array_to_file("hashes", $hashes, $path);
        }

        // Return the flag
        return $deleted;
    }

    /**
     * Calculates a metadata hash. Removes any existing first level _hash index
     * prior to calculation.
     * 
     * @param array $data
     * @return string
     */
    protected function hashChunk($data) 
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
            self::MM_MODULES,
            self::MM_FIELDS,
            self::MM_VIEWS, 
            self::MM_LAYOUTS, 
            self::MM_LABELS,
            self::MM_CONFIG,
            self::MM_JSSOURCE,
            self::MM_LOGOURL,
            self::MM_OVERRIDEVALUES,
        );
    }

    /**
     * Loads the standard private metadata sections. This can be overridden.
     */
    protected function getPrivateSections() {
        return array(
            self::MM_MODULES,
            self::MM_FULLMODULELIST,
            self::MM_HIDDENSUBPANELS,
            self::MM_CURRENCIES, 
            self::MM_MODULETABMAP,
            self::MM_FIELDS, 
            self::MM_VIEWS,
            self::MM_LAYOUTS,
            self::MM_LABELS, 
            self::MM_RELATIONSHIPS,
            self::MM_JSSOURCE, 
            self::MM_SERVERINFO,
            self::MM_LOGOURL,
            self::MM_LANGUAGES,
            self::MM_OVERRIDEVALUES,
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
        //If `Home` is not the first item of the list
        if ($moduleList[0] !== 'Home') {
            //Remove it if it is at a random position
            if (($key = array_search('Home', $moduleList)) !== false) {
                unset($moduleList[$key]);
            }
            //Add it to the first position
            array_unshift($moduleList, 'Home');
        }
        return $moduleList;
    }

    /**
     * Deletes a language cache file for this platform and visibility
     * 
     * @param string|array $langs The language(s) to clear the cache for
     * @return bool True if the file no longer exists or never existed
     */
    protected function clearLanguagesCache($langs) {
        foreach ((array) $langs as $lang) {
            $cache = $this->getLangUrl($lang);
            if (file_exists($cache)) {
                @unlink($cache);
            }
        }
        return true;
    }

    /**
     * Gets the key used for the metadata hash cache store
     * 
     * @return string The key for this platform and visibility version of metadata
     */
    public function getCachedMetadataHashKey()
    {
        $public = $this->public ? "public_" : "";
        $key = "meta_hash_$public" . implode( "_", $this->platforms);
        return $key;
    }

    /**
     * Public accessor that gets the hash for a metadata file cache. This is a 
     * wrapper to {@see getCachedMetadataHash}
     * 
     * @return string A metadata cache file hash or false if not found
     */
    public function getMetadataHash()
    {
        // Start with the known has if there is one
        $hash = $this->getCachedMetadataHash();
        
        if ($hash) {
            // We need to see if we need to send any warnings down to the user
            $systemStatus = apiCheckSystemStatus();
            if ($systemStatus !== true) {
                // Something is up with the system status, let the client know
                // by mangling the hash
                $hash = md5($hash.serialize($systemStatus));
            }
        }

        return $hash;
    }
    
    /**
     * Sets up the modules and full_module_list portion of metadata
     * 
     * @param array $data Array of arguments or existing data to be written
     * @return array Array of data containing full_module_list and modules
     */
    protected function setupModuleLists($data) 
    {
        $method = 'setup' . ucfirst($this->visibility) . 'ModuleLists';
        return $this->$method($data);
    }

    /**
     * Sets up the private module lists consisting of modules and full_module_list
     * 
     * @param array $data Array of arguments or existing data to be written
     * @return array Array of data containing full_module_list and modules
     */
    protected function setupPrivateModuleLists($data)
    {
        $data = $this->populateModules($data);
        foreach ($data['modules'] as $moduleName => $moduleDef) {
            if (!array_key_exists($moduleName, $data['full_module_list']) && array_key_exists($moduleName, $data['modules'])) {
                unset($data['modules'][$moduleName]);
            }
        }
        $data['full_module_list']['_hash'] = $this->hashChunk($data['full_module_list']);
        return $data;
    }

    /**
     * Setups the public module lists, which includes modules only
     * 
     * @param array $data Array of arguments or existing data to be written
     * @return array Array of data containing modules
     */
    protected function setupPublicModuleLists($data)
    {
        $data['modules'] = array("Login" => array("fields" => array()));
        return $data;
    }

    /**
     * Sets up metadata caches for various platforms and languages.
     * 
     * NOTE: This can get expensive for many platforms and/or many languages.
     * 
     * @param array $platforms Array of platforms for setup metadata for
     * @param array $languages Array of language metadata caches to build
     * @return void
     */
    public static function setupMetadata($platforms = array(), $languages = array())
    {
        // Set up the platforms array
        if (empty($platforms)) {
            $platforms = array('base');
        }

        if (!is_array($platforms)) {
            $platforms = (array) $platforms;
        }

        if (empty($languages)) {
            $languages = array('en_us');
        }

        if (!is_array($languages)) {
            $languages = (array) $languages;
        }

        // Loop over the metadata managers for each platform and visibility, then
        // over each of the languages for each manager
        foreach ($platforms as $platform) {
            foreach (array(true, false) as $public) {
                $mm = MetaDataManager::getManager($platform, $public);
                $mm->getMetadata();
                foreach ($languages as $language) {
                    $mm->getLanguage($language);
                }
            }
        }
    }
}
