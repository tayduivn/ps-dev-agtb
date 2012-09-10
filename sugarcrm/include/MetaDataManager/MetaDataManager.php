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
     * The user bean for the logged in user
     *
     * @var User
     */
    protected $user;

    /**
     * The constructor for the class.
     *
     * @param User $user A User bean
     * @param array $platforms A list of clients
     * @param bool $public is this a public metadata grab
     */
    function __construct ($user, $platforms = null, $public = false) {
        if ( $platforms == null ) {
            $platforms = array('base');
        }

        $this->user = $user;
        $this->platforms = $platforms;

    }

    /**
     * Gets the view defs for a module for a given view|layout
     *
     * @param string $moduleName The name of the module
     * @param string $viewdefType The type of def (layout or view)
     * @return array
     */
    protected function getModuleViewdefs($moduleName, $viewdefType = 'view') {
        // Return data
        $data = array();

        // The metadata filenames that we will be getting
        $data = array();

        // Module metadata directories for fetching controllers and templates
        $moduledirs = array();

        // Start with SugarObjects :: basic
        $basic = 'include/SugarObjects/templates/basic/clients/' . $this->platforms[0] . '/' . $viewdefType . 's/';
        if (is_dir($basic)) {
            $result = $this->getSugarClientFiles($viewdefType, 'include/SugarObjects/templates/basic/', "<module_name>");
            foreach ($result as $name => $fileArray) {
                $data[$name] = $fileArray;
            }
        }

        // Now get the SugarObjects :: $moduleType files
        $moduleType = MetaDataFiles::getSugarObjectFileDir($moduleName, $this->platforms[0], $viewdefType);
        if (is_dir($moduleType)) {
            $result = $this->getSugarClientFiles($viewdefType, $moduleType, "<module_name>");
            foreach ($result as $name => $fileArray) {
                $data[$name] = $fileArray;
            }
        }

        // Now handle the module locations
        $result = $this->getSugarClientFiles($viewdefType, "modules/{$moduleName}/", $moduleName);
        foreach ($result as $name => $fileArray) {
            $data[$name] = $fileArray;
        }

        return $data;
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
        return $this->getModuleViewdefs($moduleName, 'view');
    }

    /**
     * This method collects all view data for a module
     *
     * @param $moduleName The name of the sugar module to collect info about.
     *
     * @return Array A hash of all of the view data.
     */
    public function getModuleLayouts($moduleName) {
        return $this->getModuleViewdefs($moduleName, 'layout');
    }

    /**
     * This method collects all field data for a module
     *
     * @param string $moduleName    The name of the sugar module to collect info about.
     *
     * @return Array A hash of all of the view data.
     */
    public function getModuleFields($moduleName) {
        return $this->getModuleViewdefs($moduleName, 'field');
    }

    /**
     * The collector method for modules.  Gets metadata for all of the module specific data
     *
     * @param $moduleName The name of the module to collect metadata about.
     * @return array An array of hashes containing the metadata.  Empty arrays are
     * returned in the case of no metadata.
     */
    public function getModuleData($moduleName) {
        require_once('include/SugarSearchEngine/SugarSearchEngineMetadataHelper.php');
        $vardefs = $this->getVarDef($moduleName);

        $data['fields'] = $vardefs['fields'];
        $data['views'] = $this->getModuleViews($moduleName);
        $data['layouts'] = $this->getModuleLayouts($moduleName);
        $data['fieldTemplates'] = $this->getModuleFields($moduleName);
        $data['subpanels'] = $this->getSubpanelDefs($moduleName);
        $data['ftsEnabled'] = SugarSearchEngineMetadataHelper::isModuleFtsEnabled($moduleName);
        $md5 = serialize($data);
        $md5 = md5($md5);
        $data["_hash"] = $md5;

        return $data;
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

        $md5 = serialize($data);
        $md5 = md5($md5);
        $data["_hash"] = $md5;

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
        if (!isset($data['relationships'])) {
            $data['relationships'] = array();
        }

        return $data;
    }

    /**
     * Gets the ACL's for the module, will also expand them so the client side of the ACL's don't have to do as many checks.
     *
     * @param string $module The module we want to fetch the ACL for
     * @param string $userId The user id for the ACL's we are retrieving.
     * @return array Array of ACL's, first the action ACL's (access, create, edit, delete) then an array of the field level acl's
     */
    public function getAclForModule($module,$userId) {
        $aclAction = new ACLAction();
        $aclField = new ACLField();
        $acls = $aclAction->getUserActions($userId);
        $obj = BeanFactory::getObjectName($module);

        $outputAcl = array('fields'=>array());
        if ( isset($acls[$module]['module']) ) {
            $moduleAcl = $acls[$module]['module'];

            if ( ($moduleAcl['admin']['aclaccess'] == ACL_ALLOW_ADMIN) || ($moduleAcl['admin']['aclaccess'] == ACL_ALLOW_ADMIN_DEV) ) {
                $outputAcl['admin'] = 'yes';
                $isAdmin = true;
            } else {
                $outputAcl['admin'] = 'no';
                $isAdmin = false;
            }

            if ( ($moduleAcl['admin']['aclaccess'] == ACL_ALLOW_DEV) || ($moduleAcl['admin']['aclaccess'] == ACL_ALLOW_ADMIN_DEV) ) {
                $outputAcl['developer'] = 'yes';
            } else {
                $outputAcl['developer'] = 'no';
            }

            if ( ($moduleAcl['access']['aclaccess'] == ACL_ALLOW_ENABLED) || $isAdmin ) {
                $outputAcl['access'] = 'yes';
            } else {
                $outputAcl['access'] = 'no';
            }

            // Only loop through the fields if we have a reason to, admins give full access on everything, no access gives no access to anything
            if ( $outputAcl['access'] == 'yes' && $outputAcl['developer'] == 'no' ) {

                foreach ( array('view','list','edit','delete','import','export','massupdate') as $action ) {
                    if ( $moduleAcl[$action]['aclaccess'] == ACL_ALLOW_ALL ) {
                        $outputAcl[$action] = 'yes';
                    } else if ( $moduleAcl[$action]['aclaccess'] == ACL_ALLOW_OWNER ) {
                        $outputAcl[$action] = 'owner';
                    } else {
                        $outputAcl[$action] = 'no';
                    }
                }

                // Currently create just uses the edit permission, but there is probably a need for a separate permission for create
                $outputAcl['create'] = $outputAcl['edit'];

                // Now time to dig through the fields
                $fieldsAcl = $aclField->loadUserFields($module,$obj,$userId,true);

                foreach ( $fieldsAcl as $field => $fieldAcl ) {
                    switch ( $fieldAcl ) {
                        case ACL_READ_WRITE:
                            // Default, don't need to send anything down
                            break;
                        case ACL_READ_OWNER_WRITE:
                            // $outputAcl['fields'][$field]['read'] = 'yes';
                            $outputAcl['fields'][$field]['write'] = 'owner';
                            $outputAcl['fields'][$field]['create'] = 'owner';
                            break;
                        case ACL_READ_ONLY:
                            // $outputAcl['fields'][$field]['read'] = 'yes';
                            $outputAcl['fields'][$field]['write'] = 'no';
                            $outputAcl['fields'][$field]['create'] = 'no';
                            break;
                        case ACL_OWNER_READ_WRITE:
                            $outputAcl['fields'][$field]['read'] = 'owner';
                            $outputAcl['fields'][$field]['write'] = 'owner';
                            $outputAcl['fields'][$field]['create'] = 'owner';
                            break;
                        case ACL_ALLOW_NONE:
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
        return $this->getSugarClientFiles('field');
    }

    /**
     * Views accessor Gets client views
     *
     * @return array
     */
    public function getSugarViews()
    {
        return $this->getSugarClientFiles('view');
    }

    /**
     * Gets client layouts, similar to module specific layouts except used on a
     * global level by the clients consuming this data
     *
     * @return array
     */
    public function getSugarLayouts()
    {
        return $this->getSugarClientFiles('layout');
    }

    /**
     * Gets the directories for a given path along the known platform stack
     *
     * @param string $path The directory within a platform
     * @param bool $full Whether to return full paths or dirnames only
     * @return array
     */
    public function getSugarClientFileDirs($path, $full = false, $modulePath = "") {
        $dirs = array();
        foreach ( $this->platforms as $platform ) {
            $basedir  = "{$modulePath}clients/{$platform}/{$path}/";
            $custdir  = "custom/$basedir";
            $basedirs = glob($basedir."*", GLOB_ONLYDIR);
            $custdirs = is_dir($custdir) ? glob($custdir . "*", GLOB_ONLYDIR) : array();
            $alldirs  = array_merge($basedirs, $custdirs);

            foreach ($alldirs as $dir) {
                // To prevent doing the work twice, let's sort this out by basename
                $dirname = basename($dir);
                $dirs[$dirname] = $full ? $dir . '/' : $dirname;
            }
        }

        return $dirs;
    }

    /**
     * Gets client files of type $type (view, layout, field)
     *
     * @param string $type The type of files to get
     * @return array
     */
    public function getSugarClientFiles($type, $modulePath = "", $module="")
    {
        $result = array();

        $typePath = $type . 's';

        $allSugarFiles = $this->getSugarClientFileDirs($typePath, false, $modulePath);

        foreach ( $allSugarFiles as $dirname) {
            // reset $fileData
            $fileData = array();
            // Check each platform in order of precendence to find the "best" controller
            // Add in meta checking here as well
            $meta = array();
            foreach ( $this->platforms as $platform ) {
                $dir = "{$modulePath}clients/$platform/$typePath/$dirname/";
                $controller = $dir . "$dirname.js";
                if (empty($meta)) {
                    $meta = $this->fetchMetadataFromDirs(array($dir), $module);
                }
                if ( file_exists('custom/'.$controller) ) {
                    $controller = 'custom/'.$controller;
                }
                if ( file_exists($controller) ) {
                    $fileData['controller'] = file_get_contents($controller);
                    // We found a controller, let's get out of here!
                    break;
                }
            }

            // Reverse the platform order so that "better" templates override worse ones
            $backwardsPlatforms = array_reverse($this->platforms);
            $templateDirs = array();
            foreach ( $backwardsPlatforms as $platform ) {
                $templateDirs[] = "{$modulePath}clients/$platform/$typePath/$dirname/";
            }
            $templates = $this->fetchTemplates($templateDirs);
            if (!empty($module) && $type != "field"){
                //custom views/layouts can only have one templates
                $fileData['template'] = reset($templates);
            }
            else
                $fileData['templates'] = $templates;

            if ($meta) {
               $fileData['meta'] = array_shift($meta); // Get the first member
            }
            //$fileData['meta'] = $this->fetchMetadataFromDirs($templateDirs);

            // Remove empty fileData members
            foreach ($fileData as $k => $v) {
                if (empty($v)) {
                    unset($fileData[$k]);
                }
            }

            $result[$dirname] = $fileData;
        }

        $result['_hash'] = md5(serialize($result));
        return $result;
    }

    /**
     * Fetches all metadata from a set of directories
     *
     * @param array $dirs The directories to read
     * @return array
     */
    protected function fetchMetadataFromDirs($dirs, $module="") {
        $return = array();
        foreach ($dirs as $dir) {
            $dir = rtrim($dir, '/') . '/';
            $cust = "custom/$dir";
            $return = array_merge($return, $this->fetchMetadataFromDir($dir, $module), $this->fetchMetadataFromDir($cust, $module));
        }
        return $return;
    }

    /**
     * Fetches metadata from a single directory
     *
     * @param string $dir
     * @return array
     */
    protected function fetchMetadataFromDir($dir, $module = "") {
        $meta = array();
        if (is_dir($dir)) {
            // Get the client, type amd name for this particular directory
            preg_match("#clients/([^/]*)/([^/]*)/([^/]*)/#", $dir, $m);
            $platform = $m[1];
            $type = substr_replace($m[2], '', -1); // Pluck the 's' from the type
            $filename = $m[3];
            $file = rtrim($dir, '/') . '/' . $filename . '.php';
            if (file_exists($file)) {
                // Changed from require_once to require so we can actually get 
                // these on multiple requests for them
                require $file;

                // Only get the viewdefs if they exist for this platform and file
                // Also handle search, since those defs are different
                if (!empty($module)) {
                    if (isset($searchdefs[$module])) {
                        $meta[$filename] = $searchdefs[$module];
                    } 
                    else if (isset($viewdefs[$module][$platform][$type][$filename]))
                    {
                        $meta[$filename] = $viewdefs[$module][$platform][$type][$filename];
                    }
                }
                else if (isset($viewdefs[$platform][$type][$filename])) 
                {
                    $meta[$filename] = $viewdefs[$platform][$type][$filename];
                }
            }
        }

        return $meta;
    }

    /**
     * A method to collect templates and pass them back, shared between sugarfields, viewtemplates and per-module templates
     *
     * @param $searchDirs array A list of directories to search, custom directories will be searched automatically, ordered by least to most important
     * @param $extension string A extension to search for, defaults to ".hbt"
     * @return array An array of template file contents keyed by the template name.
     */
    protected function fetchTemplates($searchDirs,$extension='.hbt') {
        $templates = array();

        foreach ( $searchDirs as $searchDir ) {
            $searchDir = rtrim($searchDir, '/') . '/'; // Clean up ending path separators
            if ( is_dir($searchDir) ) {
                $stdTemplates = glob($searchDir."*".$extension);
                if ( is_array($stdTemplates) ) {
                    foreach ( $stdTemplates as $templateFile ) {
                        $templateName = basename($templateFile, $extension);
                        $templates[$templateName] = file_get_contents($templateFile);
                    }
                }
            }
            // Do the custom directory last so it will override anything in the core product
            if ( is_dir('custom/'.$searchDir) ) {
                $cstmTemplates = glob('custom/'.$searchDir."*".$extension);
                if ( is_array($cstmTemplates) ) {
                    foreach ( $cstmTemplates as $templateFile ) {
                        $templateName = basename($templateFile, $extension);
                        $templates[$templateName] = file_get_contents($templateFile);
                    }
                }
            }
        }
        return $templates;
    }

    /**
     * The collector method for view templates
     *
     * @return array A hash of the template name and the template contents
     */
    public function getViewTemplates() {
        $backwardsPlatforms = array_reverse($this->platforms);
        $templateDirs = array();
        foreach ( $backwardsPlatforms as $platform ) {
            $moreTemplates = glob("clients/${platform}/views/*",GLOB_ONLYDIR);
            $templateDirs = array_merge($templateDirs,$moreTemplates);
        }
        $templates = $this->fetchTemplates($templateDirs);
        $templates['_hash'] = md5(serialize($templates));
        return $templates;
    }

    /**
     * The collector method for the module strings
     *
     * @return array The module strings for the current language
     */
    public function getModuleStrings( $moduleName ) {
        return return_module_language($GLOBALS['current_language'],$moduleName);
    }

    /**
     * The collector method for the app strings
     *
     * @return array The app strings for the current language, and a hash of the app strings
     */
    public function getAppStrings() {
        $appStrings = $GLOBALS['app_strings'];
        $appStrings['_hash'] = md5(serialize($appStrings));
        return $appStrings;
    }

    /**
     * The collector method for the app strings
     *
     * @return array The app strings for the current language, and a hash of the app strings
     */
    public function getAppListStrings() {
        $appStrings = $GLOBALS['app_list_strings'];
        $appStrings['_hash'] = md5(serialize($appStrings));
        return $appStrings;
    }

    /**
     * The method for getting the module list, can collect for base, portal and mobile
     *
     * @return array The list of modules that are supported by this platform
     */
    public function getModuleList($platform = 'base') {
        if ( $platform == 'portal' ) {
            // Apparently this list is not stored anywhere, the module builder just uses a very
            // complicated setup to do this glob
            $defaultPortalViewsPath = 'modules/*/clients/portal/views/*/*.php';
            $defaultPortalLayoutsPath = 'modules/*/clients/portal/layouts/*/*.php';
            $customPortalViewsPath = MetaDataFiles::PATHCUSTOM . $defaultPortalViewsPath;
            $customPortalLayoutsPath = MetaDataFiles::PATHCUSTOM . $defaultPortalLayoutsPath;

            $portalFiles = glob($defaultPortalViewsPath);
            $portalLayouts = glob($defaultPortalLayoutsPath);
            if (is_array($portalLayouts)) {
                $portalFiles = array_merge($portalFiles, $portalLayouts);
            }

            $customPortalViews = glob($customPortalViewsPath);
            if (is_array($customPortalViews)) {
                $portalFiles = array_merge($portalFiles, $customPortalViews);
            }

            $customPortalLayouts = glob($customPortalLayoutsPath);
            if (is_array($customPortalLayouts)) {
                $portalFiles = array_merge($portalFiles, $customPortalLayouts);
            }

            $portalModules = array();
            foreach ( $portalFiles as $file ) {
                // Grab the module name from the file path
                preg_match('#modules/(.+)/clients#', $file, $fileParts);
                
                // And set it only if we haven't already
                if (!empty($fileParts[1]) && empty($portalModules[$fileParts[1]])) {
                    $portalModules[$fileParts[1]] = $fileParts[1];
                }
            }
            $moduleList = array_keys($portalModules);
        } else if ( $platform == 'mobile' ) {
            // replicate the essential part of the behavior of the private loadMapping() method in SugarController
            foreach ( array ( '','custom/') as $prefix) {
                if(file_exists($prefix.'include/MVC/Controller/wireless_module_registry.php')){
                    require($prefix.'include/MVC/Controller/wireless_module_registry.php');
                }
            }

            // $wireless_module_registry is defined in the file loaded above
            $moduleList = array_keys($wireless_module_registry);
        } else {
            // Loading a standard module list
            require_once("modules/MySettings/TabController.php");
            $controller = new TabController();
            $moduleList = array_keys($controller->get_user_tabs($this->user));
            $moduleList[] = 'ActivityStream';
        }

        $oldModuleList = $moduleList;
        $moduleList = array();
        foreach ( $oldModuleList as $module ) {
            $moduleList[$module] = $module;
        }

        $moduleList['_hash'] = md5(serialize($moduleList));
        return $moduleList;
    }

    public static function getPlatformList() {
        $platforms = array();
        foreach(array("clients", "custom/clients") as $path)
        {
            if (is_dir($path)) {
                $dirs = scandir($path);
                foreach($dirs as $dir) {
                    if (!empty($dir) && $dir[0] != "." && $dir[0] != "_" && is_dir("$path/$dir"))
                    {
                        $platforms[$dir] = true;
                    }
                }
            }
        }

        return array_keys($platforms);
    }
}
