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
class MetaDataManager 
{

    /**
     * The user bean for the logged in user
     *
     * @var User
     */
    protected $user;

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
     * The constructor for the class.
     *
     * @param User  $user      A User bean
     * @param array $platforms A list of clients
     * @param bool  $public    is this a public metadata grab
     */
    public function __construct ($user, $platforms = null, $public = false)
    {
        if ($platforms == null) {
            $platforms = array('base');
        }

        $this->user = $user;
        $this->platforms = $platforms;

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

        $spd = new SubPanelDefinitions($parent_bean);
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
        $data['views'] = $this->getModuleViews($moduleName);
        $data['layouts'] = $this->getModuleLayouts($moduleName);
        $data['fieldTemplates'] = $this->getModuleFields($moduleName);
        $data['subpanels'] = $this->getSubpanelDefs($moduleName);
        $data['menu'] = $this->getModuleMenu($moduleName);
        $data['config'] = $this->getModuleConfig($moduleName);
        $data['filters'] = $this->getModuleFilters($moduleName);

        // Indicate whether Module Has duplicate checking enabled --- Rules must exist and Enabled flag must be set
        $data['dupCheckEnabled'] = isset($vardefs['duplicate_check']) && isset($vardefs['duplicate_check']['enabled']) && ($vardefs['duplicate_check']['enabled']===true);

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

        $data["_hash"] = md5(serialize($data));

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
        foreach ($data as $relKey => $relData) {
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

            return $data;
        }

        // Bug 56505 - multiselect fields default value wrapped in '^' character
        if (!empty($data['fields']) && is_array($data['fields']))
            $data['fields'] = $this->normalizeFielddefs($data['fields']);

        if (!isset($data['relationships'])) {
            $data['relationships'] = array();
        }

        // loop over the fields to find if they can be sortable
        // get the indexes on the module and the first field of each index
        $indexes = array();
        if (isset($data['indices'])) {
            foreach ($data['indices'] AS $index) {
                if (isset($index['fields'][0])) {
                    $indexes[$index['fields'][0]] = $index['fields'][0];
                }
            }
        }

        // If sortable isn't already set THEN
        //      Set it sortable to TRUE, if the field is indexed.
        //      Set sortable to FALSE, otherwise. (Bug56943, Bug57644)
        $isIndexed = !empty($indexes);
        if (!empty($data['fields']) && is_array($data['fields'])) {
            foreach ($data['fields'] AS $field_name => $info) {
                if (!isset($data['fields'][$field_name]['sortable'])) {
                    $data['fields'][$field_name]['sortable'] = false;
                    if ($isIndexed && isset($indexes[$field_name])) {
                        $data['fields'][$field_name]['sortable'] = true;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Gets the ACL's for the module, will also expand them so the client side of the ACL's don't have to do as many checks.
     *
     * @param  string      $module     The module we want to fetch the ACL for
     * @param  object      $userObject The user object for the ACL's we are retrieving.
     * @param  object|bool $bean       The SugarBean for getting specific ACL's for a module
     * @return array       Array of ACL's, first the action ACL's (access, create, edit, delete) then an array of the field level acl's
     */
    public function getAclForModule($module,$userObject,$bean=false)
    {
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
            foreach (SugarACL::$all_access AS $action => $bool) {
                $outputAcl[$action] = ($moduleAcls[$action] == true || !isset($moduleAcls[$action])) ? 'yes' : 'no';
            }

            // is the user an admin user for the module
            $outputAcl['admin'] = ($userObject->isAdminForModule($module)) ? 'yes' : 'no';
            // Bug56391 - Use the SugarACL class to determine access to different actions within the module
            foreach (SugarACL::$all_access AS $action => $bool) {
                $outputAcl[$action] = ($moduleAcls[$action] == true || !isset($moduleAcls[$action])) ? 'yes' : 'no';
            }

            // Only loop through the fields if we have a reason to, admins give full access on everything, no access gives no access to anything
            if ($outputAcl['access'] == 'yes') {
                // Currently create just uses the edit permission, but there is probably a need for a separate permission for create
                $outputAcl['create'] = $outputAcl['edit'];

                // Now time to dig through the fields
                $fieldsAcl = array();
                // we cannot use ACLField::getAvailableFields because it limits the fieldset we return.  We need all fields
                // for instance assigned_user_id is skipped in getAvailableFields, thus making the acl's look odd if Assigned User has ACL's
                // only assigned_user_name is returned which is a derived ["fake"] field.  We really need assigned_user_id to return as well.
                if($bean === false) {
                    $bean = BeanFactory::newBean($module);
                }
                if(empty($GLOBALS['dictionary'][$bean->object_name]['fields'])){
                    if(empty($bean->acl_fields)) {
                        $fieldsAcl = array();
                    } else {
                        $fieldsAcl = $bean->field_defs;
                    }
                } else{
                    $fieldsAcl = $GLOBALS['dictionary'][$bean->object_name]['fields'];
                    if(isset($GLOBALS['dictionary'][$bean->object_name]['acl_fields']) && $GLOBALS['dictionary'][$bean->object_name]=== false){
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
        // for brevity, filter out 'yes' fields since UI assumes 'yes'
        foreach ($outputAcl as $k => $v) {
            if ($v == 'yes') {
                unset($outputAcl[$k]);
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
    public function getAppStrings($lang = 'en_us' )
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


     /*
     * Factory for layouts.
     *
     * @param  string $name - Name of the layout.
     * @param  array  $args Arguments passed in to the constructor.
     * @return class  The instantiated version of the layout.
     */
    public static function getLayout($name, array $args = array())
    {
        $cstmName = 'Custom'.$name;
        $class = false;
        if (class_exists($cstmName)) {
            $class = new $cstmName($args);
        } elseif (class_exists($name)) {
            $class = new $name($args);
        }

        return $class;
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
     * Clears the API metadata cache of all cache files
     *
     * @param bool $deleteModuleClientCache Should we also delete the client file cache of the modules
     * @static
     */
    public static function clearAPICache( $deleteModuleClientCache = true )
    {
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

        // clear the platform cache from sugar_cache to avoid out of date data
        $platforms = self::getPlatformList();
        foreach ($platforms as $platform) {
            $platformKey = $platform == "base" ?  "base" : implode(",", array($platform, "base"));
            $hashKey = "metadata:$platformKey:hash";
            sugar_cache_clear($hashKey);
        }

        // When changes are made to the metadata the stored hash has to be deleted
        // so that the next request can tell the client that there are metadata
        // changes
        $user = isset($GLOBALS['current_user']) ? $GLOBALS['current_user'] : null;
        $mm = new MetaDataManager($user);
        $mm->resetSessionHash();
    }

    /**
     * Gets server information
     *
     * @return array of ServerInfo
     */
    public function getServerInfo()
    {
        global $sugar_flavor;
        global $sugar_version;

        $data['flavor'] = $sugar_flavor;
        $data['version'] = $sugar_version;

        //BEGIN SUGARCRM flav=pro ONLY
        $fts_enabled = SugarSearchEngineFactory::getFTSEngineNameFromConfig();
        if (!empty($fts_enabled) && $fts_enabled != 'SugarSearchEngine') {
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
        return $data;
    }

    /**
     * Resets the session metadata hash to empty. This forces the api to send
     * back a not authorized on the next request so that a new auth request can
     * be sent forcing a new metadata hit. The only time this should be called
     * is when metadata cache is cleared
     */
    public function resetSessionHash()
    {
        // Only empty the session value if it is set
        if (isset($_SESSION['system_metadata_hash'])) {
            $_SESSION['system_metadata_hash'] = '';
        }
    }

    /**
     * Sets the session metadata hash to a value provided the session hash is
     * not already set.
     *
     * @param string $hash The metadata hash value for use in the session
     */
    public function setSessionHash($hash)
    {
        $_SESSION['system_metadata_hash'] = $hash;
    }

    /**
     * Gets the current session metadata hash if it exists
     *
     * @return string
     */
    public function getSessionHash()
    {
        return isset($_SESSION['system_metadata_hash']) ? $_SESSION['system_metadata_hash'] : null;
    }

    /**
     * Unsets the session value entry altogether. This is called when there is a
     * mismatch in session metadata hash and cached metadata hash.
     */
    public function unsetSessionHash()
    {
        unset($_SESSION['system_metadata_hash']);
    }

    /**
     * Checks the validity of the current session metadata hash value. Since the
     * only time the session value is set is after a metadata fetch has been made
     * a non-existent session value is valid. However if there is a session value
     * then there either has to be a metadata cache of hashes to check against
     * or the session value has to be false (meaning the session value was set
     * before the metadata cache was built) in order to pass the validity check.
     *
     * @param  string  $platform The platform to check the metadata hash against
     * @return boolean
     */
    public function isSessionHashValid($platform = null)
    {
        // Get the current platform if one wasn't presented
        if (empty($platform)) {
            $platform = $this->platforms[0];
        }

        // Is there a current session var for the metadata hash
        $sessionHash  = $this->getSessionHash();
        if ($sessionHash !== null) {
            // See if there is a hash cache. If there is, see if the hash cache
            // for this platform matches what's in the session, ensuring that the
            // session value isn't false (the default value when setting from
            // cache)
            $hashCache = sugar_cached("api/metadata/hashes.php");
            if (file_exists($hashCache)) {
                include $hashCache;

                // Valid is either a platform hash that matches the session hash
                // OR no platform hash and no session hash
                $platformHash = empty($hashes['meta_hash_' . $platform]) ? null : $hashes['meta_hash_' . $platform];

                return ($platformHash && $sessionHash && $platformHash == $sessionHash) ||
                       (!$platformHash && !$sessionHash);
            } else {
                // We have a session var but no cache file. That means the either
                // A) the cache file has been nuked, or B) the cache file never
                // existed. Case B happens if the session var is false, which is
                // a valid session hash value to prevent logouts.
                return $sessionHash === false;
            }
        }

        // There is no session var so we say we're good so as not to get stuck in
        // a continual logout loop
        return true;
    }

    /**
     * Sets the session value for the metadata hash to the hash value for a given
     * platform. If there is no hash file, or if there is no hash for this platform
     * then assume there is no metadata cache and set the session hash to false.
     *
     * @param string $platform The platform to get the metadata hash for
     */
    public function setSessionHashFromCache($platform)
    {
        $hash = false;
        $hashCache = sugar_cached("api/metadata/hashes.php");
        if (file_exists($hashCache)) {
            include $hashCache;
            if (!empty($hashes['meta_hash_' . $platform])) {
                $hash = $hashes['meta_hash_' . $platform];
            }
        }

        $this->setSessionHash($hash);
    }

    /**
     * Tells the app the user preference metadata has changed.
     *
     * Because Administration and Users are BWC modules, we cannot use SESSIONS
     * to relay information between requests since a BWC request is a different
     * HTTP request from an API request. Because of that, this method and all
     * methods surrounding the user metadata change notification build a simple
     * empty file in the api/metadata/ cache directory for use between requests.
     *
     * @param Person $user The user that is changing preferences
     */
    public function setUserMetadataHasChanged($user)
    {
        sugar_touch(sugar_cached("api/metadata/user_metadata_changed_{$user->id}"));
    }

    /**
     * Checks the state of changed metadata for a user
     *
     * @param  Person $user The user that is changing preferences
     * @return bool
     */
    public function hasUserMetadataChanged($user)
    {
        return file_exists(sugar_cached("api/metadata/user_metadata_changed_{$user->id}"));
    }

    /**
     * Clears the temporary file that is used to indicate that a user has changed
     * their preferences.
     *
     * @param Person $user The user that is changing preferences
     */
    public function unsetUserMetadataHasChanged($user)
    {
        //unset($_SESSION['user_metadata_changed']);
        if ($this->hasUserMetadataChanged($user)) {
            @unlink(sugar_cached("api/metadata/user_metadata_changed_{$user->id}"));
        }
    }

    /**
     * Gets the list of fields that should trigger a user metadata change reauth
     *
     * @return array
     */
    public function getUserPrefsToCache()
    {
        return $this->userPrefsToCache;
    }

    /**
     * Accessor to allow mapping a user pref field name to a metadata property
     * name.
     *
     * @param string $prefName     The name of the user preference
     * @param string $metadataName The name of the metadata property that maps to this field
     */
    public function addUserPrefToCache($prefName, $metadataName = '')
    {
        if (empty($metadataName)) {
            $metadataName = $prefName;
        }

        $this->userPrefsToCache[$prefName] = $metadataName;
    }

    /**
     * Accessor to delete a user preference from the metadata change reauth
     * collection
     *
     * @param string $prefName The name of the user preference
     */
    public function delUserPrefToCache($prefName)
    {
        unset($this->userPrefsToCache[$prefName]);
    }
}
