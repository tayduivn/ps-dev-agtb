<?php
if(!defined('sugarEntry'))define('sugarEntry', true);
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'soap/SoapHelperFunctions.php';
require_once 'modules/ModuleBuilder/parsers/MetaDataFiles.php';
require_once 'include/SugarFields/SugarFieldHandler.php';
require_once 'include/SugarObjects/LanguageManager.php';
require_once 'modules/ActivityStream/Activities/ActivityQueueManager.php';
require_once 'include/SubPanel/SubPanelDefinitions.php';
require_once 'modules/MySettings/TabController.php';

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
    const MM_MODULESINFO    = 'modules_info';
    const MM_FIELDS         = 'fields';
    const MM_LABELS         = 'labels';
    const MM_ORDEREDLABELS  = 'ordered_labels';
    const MM_VIEWS          = 'views';
    const MM_LAYOUTS        = 'layouts';
    const MM_RELATIONSHIPS  = 'relationships';
    const MM_DATA           = 'datas';
    const MM_CURRENCIES     = 'currencies';
    const MM_JSSOURCE       = 'jssource';
    const MM_SERVERINFO     = 'server_info';
    const MM_CONFIG         = 'config';
    const MM_LANGUAGES      = 'languages';
    const MM_HIDDENSUBPANELS = 'hidden_subpanels';
    const MM_MODULETABMAP   = 'module_tab_map';
    const MM_LOGOURL        = 'logo_url';
    const MM_OVERRIDEVALUES = '_override_values';
    const MM_FILTERS        = 'filters';

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
        self::MM_MODULESINFO    => 'getModulesInfo',
        self::MM_FIELDS         => 'getSugarFields',
        self::MM_LABELS         => 'getStringUrls',
        self::MM_ORDEREDLABELS  => 'getOrderedStringUrls',
        self::MM_VIEWS          => 'getSugarViews',
        self::MM_LAYOUTS        => 'getSugarLayouts',
        self::MM_DATA           => 'getSugarData',
        self::MM_RELATIONSHIPS  => 'getRelationshipData',
        self::MM_CURRENCIES     => 'getSystemCurrencies',
        self::MM_JSSOURCE       => false,
        self::MM_SERVERINFO     => 'getServerInfo',
        self::MM_CONFIG         => 'getConfigs',
        self::MM_LANGUAGES      => 'getAllLanguages',
        self::MM_HIDDENSUBPANELS => 'getHiddenSubpanels',
        self::MM_MODULETABMAP   => 'getModuleTabMap',
        self::MM_LOGOURL        => 'getLogoUrl',
        self::MM_FILTERS        => 'getSugarFilters',
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
     * Indicates the state of the cleared metadata so that subsequent calls to
     * clear the cache in the same request are ignored
     *
     * @var boolean
     */
    protected static $cacheHasBeenCleared = false;

    /**
     * White listed properties which shall be copied from server side
     * configurations to client side configurations.
     *
     * @var array
     * @see getConfigProperties
     * @see parseConfigProperties
     */
    protected static $configProperties = array(
        'list_max_entries_per_page' => true,
        'list_max_entries_per_subpanel' => true,
        'max_record_fetch_size' => true,
        'max_record_link_fetch_size' => true,
        'mass_actions' => array(
            'mass_update_chunk_size' => true,
            'mass_delete_chunk_size' => true,
            'mass_link_chunk_size' => true,
        ),
        'merge_duplicates' => array(
            'merge_relate_fetch_concurrency' => true,
            'merge_relate_fetch_timeout' => true,
            'merge_relate_fetch_limit' => true,
            'merge_relate_update_concurrency' => true,
            'merge_relate_update_timeout' => true,
            'merge_relate_max_attempt' => true,
        ),
        'default_decimal_seperator' => true,
        'default_number_grouping_seperator' => true,
        'default_currency_significant_digits' => true,
        'enable_legacy_dashboards' => true,
        'logger' => array(
            'level' => true,
            'write_to_server' => true,
        ),
    );

    /**
     * Map of configuration properties that should assume a different name than
     * the one provided by parse mechanism.
     *
     * <code>
     *     array(
     *       'parsedKey1' => 'newKey1',
     *       'parsedKey2' => 'newKey2',
     *    )
     * </code>
     *
     * @deprecated This should only be used to handle legacy code, thus should
     * removed when that code gets cleaned up.
     *
     * @var array
     * @see handleConfigPropertiesExceptions
     */
    protected static $configPropertiesExceptions = array(
        'listMaxEntriesPerPage' => 'maxQueryResult',
        'listMaxEntriesPerSubpanel' => 'maxSubpanelResult',
        'defaultDecimalSeperator' => 'defaultDecimalSeparator',
        'defaultNumberGroupingSeperator' => 'defaultNumberGroupingSeparator'
    );

    /**
     * Stores the loaded metadata
     *
     * @var array
     */
    protected $data = array();

    /**
     * These sections are skipped as part of a full metadata fetch either because
     * they are handled in a combination method like the modules section builder
     * or because they are handled separately, like override values
     *
     * @var array
     */
    protected $sectionsToSkip = array(
        self::MM_OVERRIDEVALUES => true,
        self::MM_FULLMODULELIST => true,
        self::MM_MODULESINFO => true,
    );

    /**
     * Explicit flag that tells the queue to run when the run method is called.
     * In most cases this will never be changed, but in the case of module builder
     * this will usually be turned off so that the postExecute method can force
     * it to run explicitly.
     * 
     * @var boolean
     */
    protected static $runQueueOnCall = true;

    /**
     * Name of the cache table used to store metadata cache data
     * @var string
     */
    protected static $cacheTable = "metadata_cache";

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

        $this->db = DBManagerFactory::getInstance();
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
        $parent_bean = BeanFactory::getBean($moduleName);
        //Hack to allow the SubPanelDefinitions class to check the correct module dir
        if (!$parent_bean) {
            $parent_bean = (object) array('module_dir' => $moduleName);
        }

        $spd = new SubPanelDefinitions($parent_bean, '', '', $this->platforms[0]);
        $layout_defs = $spd->layout_defs;

        if (is_array($layout_defs) && isset($layout_defs['subpanel_setup'])) {
            foreach ($layout_defs['subpanel_setup'] AS $name => $subpanel_info) {
                $aSubPanel = $spd->load_subpanel($name, false, false, true);

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
        $data = $this->getModuleClientData('view', $moduleName);
        $data = $this->removeDisabledFields($data);
        return $data;
    }

    /**
     * Removes disabled fields from view definition
     *
     * @param array $data
     * @return array
     */
    protected function removeDisabledFields(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if ($key === 'fields') {
                    $value = array_filter($value, function ($field) {
                        return !is_array($field) || !isset($field['enabled']) || $field['enabled'];
                    });

                    // make sure the resulting array has no gaps in keys
                    $value = array_values($value);
                } else {
                    $value = $this->removeDisabledFields($value);
                }
                $data[$key] = $value;
            }
        }

        return $data;
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
     * This method collects all the collection controllers for a module
     *
     * @param string $moduleName The name of the sugar module to collect info about.
     *
     * @return Array A hash of all collections and models controllers
     */
    public function getModuleDatas($moduleName)
    {
        return $this->getModuleClientData('data', $moduleName);
    }

    /**
     * Gets metadata for all modules
     *
     * @return array An array of hashes containing the modules and their
     * metadata.
     */
    public function getModulesData()
    {
        if (!isset($this->data['full_module_list'])) {
            $this->data['full_module_list'] = $this->getModuleList();
        }
        $moduleList = $this->data['full_module_list'];
        $modules = array();
        foreach ($moduleList as $key => $module) {
            if ($key == '_hash') {
                continue;
            }

            $bean = BeanFactory::newBean($module);
            $modules[$module] = $this->getModuleData($module);
            $this->relateFields($data, $module, $bean);
        }
        return $modules;
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
        $data['nameFormat'] = isset($vardefs['name_format_map'])?$vardefs['name_format_map']:null;
        $data['views'] = $this->getModuleViews($moduleName);
        $data['datas'] = $this->getModuleDatas($moduleName);
        $data['layouts'] = $this->getModuleLayouts($moduleName);
        $data['fieldTemplates'] = $this->getModuleFields($moduleName);
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
        // Currently no way to disable following
        // But this flag is here in case we add that feature in the future
        $data['followingEnabled'] = true;

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

        // Request fresh relationship metadata always
        $data = $relFactory->getRelationshipDefs(true);

        // Sanity check the rel defs, just in case they came back empty
        if (is_array($data)) {
            // Certain elements of the relationship defs need to be pruned
            $unsets = array('table', 'fields', 'indices', 'relationships');
            foreach ($data as $relKey => $relData) {
                // Prune the relationship defs as needed
                foreach ($unsets as $unset) {
                    unset($relData[$unset]);
                }

                // Sort each def array for consistency to ensure sameness between
                // metadata cache refreshes
                ksort($relData);

                // Reset the defs for this key
                $data[$relKey] = $relData;
            }
        }

        // To maintain hashes between requests, make sure this array is always
        // in the same order. Otherwise, the serialized value of this data will
        // potentially be different from one request to another.
        ksort($data);

        $data["_hash"] = $this->hashChunk($data);

        return $data;
    }

    /**
     * Gets vardef info for a given module.
     *
     * @param string $moduleName The name of the module to collect vardef information about.
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
            $data['fields'] = $this->getMetaDataHacks()->normalizeFieldDefs($data);
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
                $fieldsAcl = $this->getMetaDataHacks()->fixAcls($fieldsAcl);
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
     * Filters accessor, gets sugar filter operators
     *
     * @return array array of filters with a hash
     */
    public function getSugarFilters()
    {
        return $this->getSystemClientData('filter');
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
     * Gets client models and collection controllers that maybe a platform would like
     * to override.
     *
     * @return array
     */
    public function getSugarData()
    {
        return $this->getSystemClientData('data');
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
    public function getAppListStrings($lang = 'en_us', $useTuples = false)
    {
        $strings = return_app_list_strings_language($lang);
        if (is_array($strings)) {
            foreach ($strings as $k => $v) {
                $list = $this->decodeStrings($v);
                if ($useTuples) {
                    $list = $this->convertToTuples($list);
                }
                $strings[$k] = $list;
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
        foreach (SugarAutoLoader::existingCustom('clients/platforms.php') as $file) {
            require $file;
        }

        return $platforms;
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
     * Converts an associative array of strings to a flat array of tuples to preserve ordering
     * @param Array $list
     *
     */
    protected function convertToTuples($list) {
        if (!is_array($list)) {
            return $list;
        }
        $ret = array();
        foreach($list as $key => $val) {
            $ret[] = array($key, $val);
        }
        return $ret;
    }

    /**
     * Registers the API metadata cache to be cleared at shutdown
     *
     * @param bool $deleteModuleClientCache Should we also delete the client file
     *             cache of the modules
     * @param bool $clearNow Tells this method to clear the cache now instead of
     *             at shutdown
     * @static
     */
    public static function clearAPICache($deleteModuleClientCache = true, $clearNow = false)
    {
        // True/false stack for handling both client cache cases
        $key = $deleteModuleClientCache ? 1 : 0;

        // If we are in unit tests we need to fire this off right away
        if ($clearNow || (defined('SUGAR_PHPUNIT_RUNNER') && SUGAR_PHPUNIT_RUNNER === true)) {
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
        if (!self::getCacheHasBeenCleared()) {
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
            static::clearCacheTable();

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
        // If we are in a rebuild process for the modules section, clear the module
        // client cache so that module metadata is fresh
        if (isset(self::$currentProcess[self::MM_MODULES])) {
            MetaDataFiles::clearModuleClientCache();
        }
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
        //If this is private meta, we will still need to build the public javascript to verify that it hasn't changed.
        //If it has changed, the client will need to refresh to load it.
        if (!$this->public) {
            $this->public = true;
            $cache = $this->getMetadataCache(true);
            if (empty($cache['jssource'])) {
                $publicMM = MetaDataManager::getManager($this->platforms, true);
                $cache = $publicMM->getMetadata($this->args);
            }
            if ($cache && !empty($cache['jssource'])) {
                $data['jssource_public'] =  $cache['jssource'];
            }
            $this->public = false;
        }
        return $data;
    }

    /**
     * Rebuilds the label section of metadata and clears language caches
     */
    public function rebuildLanguagesCache()
    {
        // We will always need the metadata for this process, but only if there
        // is existing metadata to work (why build a section of an empty set)
        $data = $this->getMetadataCache(true);

        // NOTE: Do not try to rebuild language cache files as this could be
        // problematic on installations with many installed languages, like OD
        if (!empty($data)) {
            $this->clearLanguagesCache();
            $data = $this->loadSectionMetadata(self::MM_LABELS, $data);
            $data = $this->loadSectionMetadata(self::MM_ORDEREDLABELS, $data);
            $data = $this->normalizeMetadata($data);
            $data['_hash'] = $this->hashChunk($data);
            $this->putMetadataCache($data);
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
                $data = $this->getMetadataCache(true);
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
                $data = $this->normalizeMetadata($data);
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
            $data = $this->getMetadataCache(true);

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
                $data = $this->normalizeMetadata($data);
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

        // Make sure the LanguageManager created modules cache is clear
        LanguageManager::resetCreatedModules();

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
    public static function refreshSectionCache($section, $platforms = array())
    {
        self::refreshCachePart('section', $section, $platforms);
    }

    /**
     * Refreshes the cache for a module or collection of modules.
     *
     * @param array $modules
     * @param array $platforms
     */
    public static function refreshModulesCache($modules, $platforms = array())
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
    public static function refreshLanguagesCache($languages, $platforms = array())
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

        // Make sure the LanguageManager created modules cache is clear
        LanguageManager::resetCreatedModules();

        // Handle refreshing based on the cache part
        $method = 'rebuild' . ucfirst(strtolower($part)) . 'Cache';
        foreach ((array) $platforms as $platform) {
            foreach (array(true, false) as $public) {
                $mm = MetaDataManager::getManager($platform, $public, true);
                if (method_exists($mm, $method)) {
                    $mm->$method($args);
                }
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
        // Only run the runner if the explicit flag allowing it is true
        if (self::$runQueueOnCall) {
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
    }

    /**
     * Turns off the queue runner. Setting this to false will prevent the queue
     * from running even if the run method is called.
     */
    public static function setRunQueueOnCallOff()
    {
        self::setRunQueueOnCall(false);
    }

    /**
     * Turns on the queue runner. This is the default state of the runner.
     */
    public static function setRunQueueOnCallOn()
    {
        self::setRunQueueOnCall(true);
    }

    /**
     * Sets the queue runner flag to boolean true or false
     * 
     * @param boolean $value Flag that tells the queue runner to run or not
     */
    public static function setRunQueueOnCall($value)
    {
        self::$runQueueOnCall = (bool) $value;
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
        $system_config = Administration::getSettings(false, true);
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

        if(isset($system_config->settings['system_tweettocase_on']) && $system_config->settings['system_tweettocase_on'] == 1){
            $data['system_tweettocase_on'] = true;
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
        $sugarConfig = $this->getSugarConfig();
        $administration = new Administration();
        $administration->retrieveSettings();

        $properties = $this->getConfigProperties();
        $properties = $this->parseConfigProperties($sugarConfig, $properties);
        $configs = $this->handleConfigPropertiesExceptions($properties);

        // FIXME: Clean up properties bellow in order to fit standards
        // regarding property names
        if (isset($administration->settings['honeypot_on'])) {
            $configs['honeypot_on'] = true;
        }
        if (isset($sugarConfig['passwordsetting']['forgotpasswordON'])) {
            if ($sugarConfig['passwordsetting']['forgotpasswordON'] === '1' || $sugarConfig['passwordsetting']['forgotpasswordON'] === true) {
                $configs['forgotpasswordON'] = true;
            } else {
                $configs['forgotpasswordON'] = false;
            }
        }

        if (!empty($sugarConfig['authenticationClass'])) {
            $auth = new AuthenticationController($sugarConfig['authenticationClass']);

            if($auth->isExternal()) {
                $configs['externalLogin'] = true;
            }
        }

        if (isset($sugarConfig['analytics'])) {
            $configs['analytics'] = $sugarConfig['analytics'];
        } else {
            $configs['analytics'] = array('enabled' => false);
        }

        $caseBean = BeanFactory::getBean('Cases');
        if(!empty($caseBean)) {
            $configs['inboundEmailCaseSubjectMacro'] = $caseBean->getEmailSubjectMacro();
        }

        // System name setting for sidecar modules
        if (isset($administration->settings['system_name'])) {
            $configs['appId'] = $administration->settings['system_name'];
        }

        return $configs;
    }

    /**
     * Retrieve server side configurations.
     *
     * @return array Server side configurations.
     */
    protected function getSugarConfig()
    {
        global $sugar_config;
        return $sugar_config;
    }

    /**
     * Retrieve white listed properties which shall be copied from server side
     * configurations to client side configurations.
     *
     * @return array Configuration properties.
     */
    protected function getConfigProperties()
    {
        return static::$configProperties;
    }

    /**
     * Retrieve map of configuration properties that should assume a different
     * name than the one provided by parse mechanism.
     *
     * @deprecated
     *
     * @return array Configuration properties.
     */
    protected function getConfigPropertiesExceptions()
    {
        return static::$configPropertiesExceptions;
    }

    /**
     * Parse supplied configurations.
     *
     * All $configProperties are translated to 'camelCase' and included on
     * client side configurations if exist on $config.
     *
     * @param array $config Server side configurations.
     * @param array $configProperties White listed properties which shall be
     *   copied from server side.
     *
     * @return array Array of client side configuration properties.
     */
    protected function parseConfigProperties(array $config, array $configProperties)
    {
        $configs = array();
        foreach($configProperties as $key => $value) {
            if (!isset($config[$key])) {
                continue;
            }

            $translatedKey = $this->translateConfigProperty($key);

            if (is_array($value)) {
                $configs[$translatedKey] = $this->parseConfigProperties(
                    $config[$key],
                    $value
                );
            } else if ($value === true) {
                $configs[$translatedKey] = $config[$key];
            }
        }
        return $configs;
    }

    /**
     * Translate supplied $property from an 'underscore' version to a
     * 'camelCase' version.
     *
     * @param string $property Configuration property name.
     *
     * @return string Translated property name.
     */
    protected function translateConfigProperty($property)
    {
        return lcfirst(
            preg_replace(
                '/(^|_)([a-z])/e', 'strtoupper("\\2")',
                $property
            )
        );
    }

    /**
     * Handle configuration properties that should assume a different name than
     * the one provided by parse mechanism.
     *
     * @deprecated This should only be used to handle legacy code, thus should
     * removed when that code gets cleaned up.
     *
     * @param array $configs Client side configuration properties.
     *
     * @return array Array of client side configuration properties
     */
    protected function handleConfigPropertiesExceptions(array $configs)
    {
        $exceptions = $this->getConfigPropertiesExceptions();
        foreach($exceptions as $key => $value) {
            if (!isset($configs[$key])) {
                continue;
            }

            $configs[$value] = $configs[$key];
            unset($configs[$key]);
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
     * @return mixed
     */
    public function getMetadata($args = array())
    {
        // Get our metadata
        $data = $this->getMetadataCache();

        //If we failed to load the metadata from cache, load it now the hard way.
        if (empty($data) || !$this->verifyJSSource($data)) {
            // Allow more time for private metadata builds since it is much heavier
            if (!$this->public) {
                ini_set('max_execution_time', 0);
            }
            $data = $this->loadMetadata($args);
        }

        // Cache the data so long as the current cache is different from the data
        // hash
        if ($data['_hash'] != $this->getMetadataHash()) {
            $this->putMetadataCache($data);
        }

        // We need to see if we need to send any warnings down to the user
        $systemStatus = apiCheckSystemStatus();
        if ($systemStatus !== true) {
            // Something is up with the system status
            // We need to tack it on and refresh the hash
            $data['config']['system_status'] = $systemStatus;
            $data['_hash'] = md5($data['_hash'].serialize($systemStatus));
        }

        return $data;
    }

    /**
     * Gets the metadata cache for a given platform and visibility
     *
     * @param boolean $ignoreDevMode If true, ignore developer mode and return cached metadata
     * @return array The metadata cache is it exists, null otherwise
     */
    protected function getMetadataCache($ignoreDevMode = false)
    {
        if (inDeveloperMode() && !$ignoreDevMode) {
            return null;
        }

        return $this->getFromCacheTable($this->getCachedMetadataHashKey());
    }

    /**
     * @param (array) $data
     *
     * @return bool true if the js-component file for this metadata call exists, false otherwise
     */
    protected function verifyJSSource($data) {
        if (!empty($data['jssource']) && !SugarAutoLoader::fileExists($data['jssource'])) {
            //The jssource file is invalid, we need to invalidate the hash as well.
            return false;
        }
    }

    /**
     * Builds the current platform and visibility metadata and returns it
     *
     * @param array $args Arguments passed into the request for metadata
     * @return array
     */
    protected function loadMetadata($args = array())
    {
        $this->args = $args;
        // Start collecting data
        $this->data = array();

        foreach ($this->sections as $section) {
            // Overrides are handled at the end because they are "special"
            // full_module_list and module_info are handled by the modules section
            // handler and is only found in private metadata
            if ($this->sectionIsSkipped($section)) {
                continue;
            }

            $this->data = $this->loadSectionMetadata($section, $this->data);
        }

        // Handle overrides
        $this->data['_override_values'] = $this->getOverrides($this->data, $args);

        // Handle client specific normalizations
        $this->data = $this->normalizeMetadata($this->data);

        // Handle hashing
        $this->data["_hash"] = $this->hashChunk($this->data);

        // Send it back
        return $this->data;
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
        return SugarThemeRegistry::current()->getImageURL('company_logo.png', true, true);
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
            //Grab the keys this way rather than through $key => $value to preserve pass by reference for $data
            $modules = array_keys($data['modules']);
            foreach ($modules as $module) {
                $moduleJS = $this->buildJavascriptComponentSection($data['modules'][$module], true);
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
            $types = array('fieldTemplates', 'views', 'layouts', 'datas');
        } else {
            $types = array('fields', 'views', 'layouts', 'datas');
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
                    //Remove any entries that were only a controller
                    if (empty($data[$mdType][$name])) {
                        unset($data[$mdType][$name]);
                    }
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
     * Sets the flag that lets the metadata manager know NOT to clear the cache
     * again. Used in cases where the cache was nuked for some reason and the
     * metadata endpoint was hit, rebuilding certain caches which destroy the
     * metadata again.
     */
    public static function setCacheHasBeenCleared()
    {
        self::$cacheHasBeenCleared = true;
    }

    /**
     * Gets the flag that indicates whether the metadata manager has cleared the
     * cache on this request.
     *
     * @return bool
     */
    public static function getCacheHasBeenCleared()
    {
        return self::$cacheHasBeenCleared;
    }

    /**
     * Returns a language JSON contents
     *
     * @param array $args
     */
    public function getLanguage($args)
    {
        if (is_string($args)) {
            $lang = $args;
            $ordered = false;
        } else {
            $lang = $args['lang'];
            $ordered = empty($args['ordered']) ? false : (bool) $args['ordered'];
        }
        return $this->getLanguageFileData($lang, $ordered);
    }

    /**
     * Get the data element of the language file properties for a language
     *
     * @param  string  $lang   The language to get data for
     * @param boolean $ordered is a flag that determines $app_list_strings should be key => value pairs or tuples
     * @return string  A JSON string of langauge data
     */
    protected function getLanguageFileData($lang, $ordered = false)
    {
        $resp = $this->getLanguageFileProperties($lang, $ordered);
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
        $this->data['full_module_list'] = $this->getModuleList();
        $data['full_module_list'] = $this->data['full_module_list'];
        $data['modules'] = $this->getModulesData();
        $data['modules_info'] = $this->getModulesInfo();
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
     * Gets every single module of the application and the properties for every
     * of these modules
     *
     * @return array An array with all the modules and their properties
     */
    public function getModulesInfo()
    {
        global $moduleList;

        $fullModuleList = $this->getFullModuleList();

        $modulesInfo = array();

        $visibleList = array_flip($moduleList);
        $tabs = array_flip($this->getTabList());
        $subpanels = array_flip($this->getSubpanelList());
        $quickcreate = array_flip($this->getQuickCreateList());

        foreach ($fullModuleList as $module) {
            $modulesInfo[$module] = array();
            $modulesInfo[$module]['enabled'] = true;
            $modulesInfo[$module]['visible'] = isset($visibleList[$module]);
            $modulesInfo[$module]['display_tab'] = isset($tabs[$module]);
            $modulesInfo[$module]['show_subpanels'] = isset($subpanels[strtolower($module)]);
            $modulesInfo[$module]['quick_create'] = isset($quickcreate[$module]);
        }
        return $modulesInfo;
    }

    /**
     * Gets the full module list of this application. This list contains every
     * single module, not the restricted list returned by `getModuleList`.
     *
     * @return array An array of module names
     */
    public function getFullModuleList()
    {
        global $moduleList, $modInvisList;

        $fullModuleList = array_merge($moduleList, $modInvisList);
        return $fullModuleList;
    }

    /**
     * Get tabs for the navigation bar of this application
     *
     * @return array An array of module names
     */
    public function getTabList()
    {
        $controller = new TabController();
        return array_keys($controller->get_system_tabs());
    }

    /**
     * Gets the list of modules displayable as subpanels
     *
     * @return array An array of module names
     */
    public function getSubpanelList()
    {
        return SubPanelDefinitions::get_all_subpanels();
    }

    /**
     * Gets the list of modules enabled in the quickcreate dropdown.
     *
     * @return array An array of module names
     */
    public function getQuickcreateList()
    {
        if (!isset($this->data['modules'])) {
            $this->data['modules'] = $this->getModulesData();
        }
        $modulesData = $this->data['modules'];

        $quickcreateModules = array();

        foreach ($modulesData as $key => $module) {
            if ($key == '_hash') {
                continue;
            }
            if (isset($modulesData[$key]) &&
                isset($modulesData[$key]['menu']) &&
                isset($modulesData[$key]['menu']['quickcreate']) &&
                isset($modulesData[$key]['menu']['quickcreate']['meta']) &&
                !empty($modulesData[$key]['menu']['quickcreate']['meta']['visible'])
            ) {
                $quickcreateModules[] = $key;
            }
        }
        return $quickcreateModules;
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
                $currencies[$current->id] = $currency;
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
     * @param boolean $ordered is a flag that determines $app_list_strings should be key => value pairs or tuples
     * @return array
     */
    public function getStringUrls($ordered = false)
    {
        $languageList = array_keys(get_languages());
        sugar_mkdir(sugar_cached('api/metadata'), null, true);

        $fileList = array();
        foreach ($languageList as $language) {
            $fileList[$language] = $this->getLangUrl($language, $ordered);
        }
        $urlList = array();
        foreach ($fileList as $lang => $file) {
            // Get the hash for this lang file so we can append it to the URL.
            // This fixes issues where lang strings or list strings change but
            // don't force a metadata refresh
            $urlList[$lang] = getVersionedPath(
                $this->getUrlForCacheFile($file),
                $GLOBALS['sugar_config']['js_lang_version'],
                true
            );
        }
        $urlList['default'] = $GLOBALS['sugar_config']['default_language'];

        return $urlList;
    }

    public function getOrderedStringUrls() {
        return $this->getStringUrls(true);
    }

    /**
     * Public read only accessor for getting a language file hash if there is one
     *
     * @param string $lang The language to get a hash for
     * @param boolean $ordered is a flag that determines $app_list_strings should be key => value pairs or tuples
     * @return string The hash if there is one, false otherwise
     */
    public function getLanguageHash($lang, $ordered = false)
    {
        return $this->getCachedLanguageHash($lang, $ordered);
    }

    /**
     * Gets a url for a language file
     *
     * @param string $language The language to get the file for
     * @param boolean $ordered is a flag that determines $app_list_strings should be key => value pairs or tuples
     * @return string
     */
    protected function getLangUrl($language, $ordered = false)
    {
        $order_key = $ordered ? "_ordered" : "";
        $public_key = $this->public ? "_public" : "";
        $platform = $this->platforms[0];
        return  sugar_cached("api/metadata/lang_{$language}_{$platform}{$public_key}{$order_key}.json");
    }

    /**
     * Get the hash element of the language file properties for a language
     *
     * @param  string  $lang   The language to get data for
     * @param boolean $ordered is a flag that determines $app_list_strings should be key => value pairs or tuples
     * @return string  The hash of the contents of the language file
     */
    protected function getLanguageFileHash($lang, $ordered = false)
    {
        $resp = $this->getLanguageFileProperties($lang, $ordered);
        return $resp['hash'];
    }

    /**
     * Gets the file properties for a language
     *
     * @param  string  $lang   The language to get data for
     * @param boolean $ordered is a flag that determines $app_list_strings should be key => value pairs or tuples
     * @return array   Array containing the hash and data for a language file
     */
    protected function getLanguageFileProperties($lang, $ordered = false)
    {
        $hash = $this->getCachedLanguageHash($lang, $ordered);
        $resp = $this->buildLanguageFile($lang, $this->getModuleList(), $ordered);
        if (empty($hash) || $hash != $resp['hash']) {
            $this->putCachedLanguageHash($lang, $resp['hash'], $ordered);
        }

        return $resp;
    }

    /**
     * Gets a hash for a cached language
     *
     * @param string $lang The lang to get the hash for
     * @param boolean $ordered is a flag that determines $app_list_strings should be key => value pairs or tuples
     * @return string
     */
    protected function getCachedLanguageHash($lang, $ordered = false)
    {
        $key = $this->getLangUrl($lang, $ordered);

        return $this->getFromHashCache($key);
    }

    /**
     * Builds the language javascript file if needed, else returns what is known
     *
     * @param string $language The language for this file
     * @param array $modules The module list
     * @param boolean $ordered is a flag that determines $app_list_strings should be key => value pairs or tuples
     * @return array Array containing the language file contents and the hash for the data
     */
    protected function buildLanguageFile($language, $modules, $ordered = false)
    {
        sugar_mkdir(sugar_cached('api/metadata'), null, true);
        $filePath = $this->getLangUrl($language, $ordered);
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
        $stringData['app_list_strings'] = $this->getAppListStrings($language, $ordered);
        $stringData['app_strings'] = $this->getAppStrings($language);
        if ($this->public) {
            // Exception for the AppListStrings.
            $app_list_strings_public = array();
            $app_list_strings_public['available_language_dom'] = $stringData['app_list_strings']['available_language_dom'];

            // Let clients fill in any gaps that may need to be filled in
            $app_list_strings_public = $this->fillInAppListStrings($app_list_strings_public, $stringData['app_list_strings'], $language);
            $stringData['app_list_strings'] = $app_list_strings_public;

        } else {
            $modStrings = array();
            foreach ($modules as $modName => $moduleDef) {
                $modData = $this->getModuleStrings($modName, $language);
                $modStrings[$modName] = $modData;
            }
            $stringData['mod_strings'] = $modStrings;
        }
        $stringData['_hash'] = $this->hashChunk($stringData);
        $data = json_encode($stringData);
        sugar_file_put_contents_atomic($filePath, $data);

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
        $hashes = $this->getFromCacheTable('hashes');
        return !empty($hashes[$key]) ? $hashes[$key] : false;
    }

    /**
     * Used to cache metadata responses in the database
     * @param String $key key for data stored in the cache table
     *
     * @return mixed|null Value pulled from cache table blob if found.
     */
    protected function getFromCacheTable($key) {
        $result = null;
        //During install/setup, this function might get called before the DB is setup.
        if (!empty($this->db)) {
            $cacheResult =  $this->db->getOne("SELECT data FROM " . static::$cacheTable . " WHERE type=" . $this->db->quoted($key));
            if (!empty($cacheResult)) {
                try {
                    $result = unserialize(gzinflate(base64_decode($cacheResult)));
                } catch (Exception $e) {
                    $GLOBALS['log']->error("Exception when decompressing metadata hash for $key:" . $e->getMessage());

                }
            }
        }

        return $result;
    }

    /**
     * Stores data in the cache table compressed and serialized. Any PHP data is valid.
     *
     * @param String $key key to store data with
     * @param mixed $data Data to store in the cache table blob
     *
     * @return bool
     */
    protected function storeToCacheTable($key, $data) {
        //During install/setup, this function might get called before the DB is setup.
        if (!empty($this->db)) {
            try {
                $encoded = base64_encode(gzdeflate(serialize($data)));
            } catch (Exception $e) {
                $GLOBALS['log']->fatal("Exception when compressing metadata for $key:" . $e->getMessage());
                return false;
            }

            $values = array(
                'id' => $this->db->getOne("SELECT id FROM " . static::$cacheTable . " WHERE type=" . $this->db->quoted($key)),
                'type' => $key,
                'data' => $encoded,
                'date_modified' => TimeDate::getInstance()->nowDb(),
                'deleted' => 0,
            );

            $fields = array();
            foreach ($this->getFields() as $field) {
                $fields[$field['name']] = $field;
            }
            if (empty($values['id'])) {
                $values['id'] = create_guid();
                return $this->db->insertParams(
                    static::$cacheTable,
                    $fields,
                    $values,
                    null,
                    true,
                    $this->db->supports('prepared_statements')
                );
            } else {
                return $this->db->updateParams(
                    static::$cacheTable,
                    $fields,
                    $values,
                    array('id' => $values['id']),
                    null,
                    true,
                    $this->db->supports('prepared_statements')
                );
            }
        }
        return false;
    }

    /**
     * Returns array of fields of static::$cacheTable
     * 
     * @return array
     */
    protected function getFields()
    {
        $fields = array();
        if (!empty($GLOBALS['dictionary'][static::$cacheTable]['fields'])) {
            $fields = $GLOBALS['dictionary'][static::$cacheTable]['fields'];
        }
        return $fields;
    }

    /**
     * Remove an entry in the cache table.
     * @param String $key
     *
     * @return mixed
     */
    protected function removeFromCacheTable($key)
    {
        return $this->db->query("DELETE FROM " . static::$cacheTable . " WHERE type=" . $this->db->quoted($key));
    }

    /**
     * Clears all entries in the cache table.
     */
    protected static function clearCacheTable()
    {
        $db = DBManagerFactory::getInstance();
        $db->commit();
        $db->query($db->truncateTableSQL(static::$cacheTable));
        $db->commit();
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
     * @param boolean $ordered is a flag that determines $app_list_strings should be key => value pairs or tuples
     */
    protected function putCachedLanguageHash($lang, $hash, $ordered = false)
    {
        $key = $this->getLangUrl($lang, $ordered);
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
        $hashes = $this->getFromCacheTable('hashes');
        if (empty($hashes)) {
            $hashes = array();
        }
        $hashes[$key] = $hash;
        $this->storeToCacheTable('hashes', $hashes);
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
    public function getCachedMetadataHash()
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
        $this->storeToCacheTable($this->getCachedMetadataHashKey(), $data);

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
        $hashes = $this->getFromCacheTable('hashes');
        if (empty($hashes)) {
            $hashes = array();
        }

        // Delete language caches and remove from the hash cache
        foreach (array(true, false) as $ordered) {
            $pattern = $this->getLangUrl('(.*)', $ordered);
            foreach ($hashes as $k => $v) {
                if (preg_match("#^{$pattern}$#", $k, $m)) {
                    // Add the deleted language to the stack
                    $this->deletedLanguageCaches[] = $m[1];

                    // Remove from the cache
                    unset($hashes[$k]);

                    // Delete the file
                    if (is_file($k)) {
                        unlink($k);
                    }

                    $deleted = true;
                }
            }
        }

        // Then delete the metadata cache and delete from the hash cache if there
        // is a cache to handle
        $cacheKey = $this->getCachedMetadataHashKey();
        if (!empty($hashes[$cacheKey])) {
            $this->removeFromCacheTable($cacheKey);
            unset($hashes[$cacheKey]);
            $deleted = true;
        }

        // Save file I/O by only writing if there are changes to write
        if ($deleted) {
            $this->storeToCacheTable('hashes', $hashes);
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
            self::MM_ORDEREDLABELS,
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
            self::MM_MODULESINFO,
            self::MM_HIDDENSUBPANELS,
            self::MM_CURRENCIES,
            self::MM_MODULETABMAP,
            self::MM_FIELDS,
            self::MM_FILTERS,
            self::MM_VIEWS,
            self::MM_LAYOUTS,
            self::MM_DATA,
            self::MM_LABELS,
            self::MM_ORDEREDLABELS,
            self::MM_CONFIG,
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
     *
     * @return array The list of module names
     */
    public function getUserModuleList() {
        // Loading a standard module list
        require_once("modules/MySettings/TabController.php");
        $controller = new TabController();
        $tabs = $controller->get_tabs($this->getCurrentUser());
        $moduleList = array_keys($tabs[0]);
        $moduleList = $this->addHomeToModuleList($moduleList);
        return $moduleList;
    }

    /**
     * Deletes all language cache files and references in the hash cache
     *
     * @return bool True if the file no longer exists or never existed
     */
    protected function clearLanguagesCache() {
        // Get the hashes array handled first
        $hashes = array();
        $path = sugar_cached("api/metadata/hashes.php");
        @include($path);

        // Track which indexes were deleted
        $deleted = array();
        foreach ($hashes as $key => $hash) {
            // If the index is a .json file path, unset it and delete it
            if (strpos($key, '.json')) {
                unset($hashes[$key]);
                @unlink($key);
                $deleted[$key] = $key;
            }
        }

        // Now handle files on the file system. This should yield an empty array
        // but its better to be safe than sorry
        $files = glob(sugar_cached("api/metadata/*.json"));
        foreach ($files as $file) {
            @unlink($file);
            $deleted[$file] = $file;
        }

        if ($deleted) {
            write_array_to_file("hashes", $hashes, $path);
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
     * @param bool $verifyDataExists if true, the javascript component files
     * will be verified as a part of the hash check
     *
     * @return string A metadata cache file hash or false if not found
     */
    public function getMetadataHash($verifyDataExists = false)
    {
        // Start with the known has if there is one
        $hash = $this->getCachedMetadataHash();

        if ($verifyDataExists) {
            $data = $this->getMetadataCache(true);
            if (!$this->verifyJSSource($data)) {
                //The jssource file is invalid, we need to invalidate the hash as well.
                return false;
            }
        }

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
        $data['modules_info']['_hash'] = $this->hashChunk($data['modules_info']);
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

    /**
     * Adds Home as the first module of users modules lists
     *
     * @param array $moduleList Array of modules
     * @return array
     */
    protected function addHomeToModuleList($moduleList)
    {
        //If Home is not the first item of the list
        if (!empty($moduleList) && $moduleList[0] !== 'Home') {
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
     * Checks to see if a particular sections is supposed to be skipped in the
     * full metadata load
     *
     * @param string $section Name of the section to check
     * @return boolean
     */
    protected function sectionIsSkipped($section)
    {
        return !empty($this->sectionsToSkip[$section]);
    }

    /**
     * Normalizes the metadata response for the platform.
     *
     * This is here for platforms that need to manipulate the metadata collection
     * prior to sending it back to the client. This should be overridden as needed
     * in the platform specific metadata managers.
     *
     * @param array $data The metadata collection
     * @return array The normalize metadata collection for this platform
     */
    public function normalizeMetadata($data)
    {
        return $data;
    }

    /**
     *
     * This method collects view data for given module and view
     *
     * @param string $moduleName The name of the module
     * @param string $view       The view name
     * @return array
     */
    public function getModuleView($moduleName, $view)
    {
        $views = $this->getModuleViews($moduleName);
        if (isset($views[$view])) {
            return $views[$view];
        }
        return array();
    }

    /**
     *
     * Return flat list of fields defined for a given module and view
     *
     * @param string $moduleName The name of the module
     * @param string $view       The view name
     * @param array $displayParams Associative array of field names and their display params on the given view
     * @return array
     */
    public function getModuleViewFields($moduleName, $view, &$displayParams = array())
    {
        $displayParams = array();
        $viewData = $this->getModuleView($moduleName, $view);
        if (!isset($viewData['meta']) || !isset($viewData['meta']['panels'])) {
            return array();
        }

        $fields = array();
        $varDefs = $this->getVarDef($moduleName);
        $fieldDefs = $varDefs['fields'];

        // flatten fields
        foreach ($viewData['meta']['panels'] as $panel) {
            if (isset($panel['fields']) && is_array($panel['fields'])) {
                $fields = array_merge($fields, $this->getFieldNames($panel['fields'], $fieldDefs, $displayParams));
            }
        }

        return $fields;
    }

    /**
     * Return list of fields from view def field set and populate $displayParams with display parameters
     * of link and collection fields
     *
     * @param array $fieldSet The field set
     * @param array $fieldDefs Bean field definitions
     * @param array $displayParams Associative array of field names and their display params
     * @return array
     *
     * @access protected Should be used only by SugarFieldBase and subclasses
     */
    public function getFieldNames(array $fieldSet, array $fieldDefs, &$displayParams)
    {
        $fields = array();
        foreach ($fieldSet as $field) {
            if (is_string($field)) {
                // direct field name
                $field = array('name' => $field);
            }
            if (is_array($field)) {
                $type = 'base';
                if (isset($field['name'])) {
                    $fields[] = $field['name'];
                    if (isset($fieldDefs[$field['name']]['type'])) {
                        $type = $fieldDefs[$field['name']]['type'];
                    }
                }

                $sf = SugarFieldHandler::getSugarField($type);
                $sf->processLayoutField($this, $field, $fieldDefs, $fields, $displayParams);
            }
        }
        return $fields;
    }

    /**
     * Lazily loads metadata hacks instance
     *
     * @return MetaDataHacks
     */
    protected function getMetaDataHacks()
    {
        if (!$this->metaDataHacks) {
            $className = SugarAutoLoader::customClass('MetaDataHacks');
            $this->metaDataHacks = new $className();
        }

        return $this->metaDataHacks;
    }
}
