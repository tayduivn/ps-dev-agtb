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

    protected $modules = null;
    protected $platform = 'base';
    protected $typeFilter = null;
    protected $user;
    protected $defaultPlatformDefs = array('list', 'detail', 'edit',);

    /**
     * The constructor for the class.
     *
     */
    function __construct ($user, $platforms = null) {
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
     * @param string $view The name of a view type to get defs for. If omitted
     *                     all viewdefs for this module will be returned.
     * @return array
     */
    protected function getModuleViewdefs($moduleName, $viewdefType = 'view', $view = null) {
        // Return data
        $data = array();

        // Metadata types we expect to have
        if ($view) {
            $expectedTypes = array($view);
        } else {
            $expectedTypes = MetaDataFiles::getClientDefType($this->platforms[0]);
            if (empty($expectedTypes)) {
                $expectedTypes = $this->defaultPlatformDefs;
            }
        }

        // Loop and fetch
        $locations = array(MB_BASEMETADATALOCATION, MB_CUSTOMMETADATALOCATION);
        foreach ($locations as $location) {
            foreach ($expectedTypes as $type) {
                // First try, see if the module has the metadata file we want
                $filename = MetaDataFiles::getModuleFileName($moduleName, $type, $location, $this->platforms[0], $viewdefType);
                if (!file_exists($filename)) {
                    // If we are in the custom scope no need to get SugarObject meta
                    // since it's already been gotten
                    if ($location == MB_CUSTOMMETADATALOCATION) {
                        continue;
                    }

                    // Fall back to SugarObjects if we there is one
                    $filename = MetaDataFiles::getSugarObjectFileName($moduleName, $type, $this->platforms[0], $viewdefType);
                    if (!file_exists($filename)) {
                        continue;
                    }
                }

                // Require rather than require once since we need the data as is
                require $filename;

                // Search is not fully converted to sidecar so handle it differently
                if ($type == 'search') {
                    if (isset($searchdefs['<module_name>']) || isset($searchdefs['<_module_name>']) || isset($searchdefs['<MODULE_NAME>'])) {
                        $searchdefs = MetaDataFiles::getModuleMetaDataDefsWithReplacements($moduleName, $searchdefs);
                    }

                    if (isset($searchdefs[$moduleName])) {
                        $data[$type] = $searchdefs[$moduleName];
                    }
                } else {
                    if (isset($viewdefs['<module_name>']) || isset($viewdefs['<_module_name>']) || isset($viewdefs['<MODULE_NAME>'])) {
                        $viewdefs = MetaDataFiles::getModuleMetaDataDefsWithReplacements($moduleName, $viewdefs);
                    }

                    // Data in that file should look like: $viewdefs['Cases']['portal']['layout']['detail'] = array(...);
                    if ( isset($viewdefs[$moduleName][$this->platforms[0]][$viewdefType][$type]) ) {
                        $data[$type] = $viewdefs[$moduleName][$this->platforms[0]][$viewdefType][$type];
                    }
                }
            }
        }

        return $data;
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
     * The collector method for modules.  Gets metadata for all of the module specific data
     *
     * @param $moduleName The name of the module to collect metadata about.
     * @return array An array of hashes containing the metadata.  Empty arrays are
     * returned in the case of no metadata.
     */
    public function getModuleData($moduleName) {
        $vardefs = $this->getVarDef($moduleName);

        $data['fields'] = $vardefs['fields'];
        //FIXME: Need more relationshp data (all relationship data)
        $data['relationships'] = $vardefs['relationships'];
        $data['views'] = $this->getModuleViews($moduleName);
        $data['layouts'] = $this->getModuleLayouts($moduleName);

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
     * @param $module The module we want to fetch the ACL for
     * @param $user The user id for the ACL's we are retrieving.
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
                            break;
                        case ACL_READ_ONLY:
                            // $outputAcl['fields'][$field]['read'] = 'yes';
                            $outputAcl['fields'][$field]['write'] = 'no';
                            break;
                        case ACL_OWNER_READ_WRITE:
                            $outputAcl['fields'][$field]['read'] = 'owner';
                            $outputAcl['fields'][$field]['write'] = 'owner';
                            break;
                        case ACL_ALLOW_NONE:
                        default:
                            $outputAcl['fields'][$field]['read'] = 'no';
                            $outputAcl['fields'][$field]['write'] = 'no';
                            break;
                    }
                }
                
            }
        }
        $outputAcl['_hash'] = md5(serialize($outputAcl));
        return $outputAcl;
    }
    
    /**
     * gets sugar fields
     *
     * @return array array of sugarfields with a hash
     */
    public function getSugarFields()
    {
        $result = array();

        //Each platform can have it's own set of sugar fields
        foreach ( $this->platforms as $platform ) {
            $baseFieldDirectory = "clients/{$platform}/fields/";
            $builtinSugarFields = glob($baseFieldDirectory."*",GLOB_ONLYDIR);
            if ( is_dir('custom/'.$baseFieldDirectory) ) {
                $customSugarFields = glob('custom/'.$baseFieldDirectory."*",GLOB_ONLYDIR);
            } else {
                $customSugarFields = array();
            }
            $allSugarFieldDirs = $builtinSugarFields+$customSugarFields;
            $allSugarFields = array();
            foreach ( $allSugarFieldDirs as $fieldDir ) {
                // To prevent doing the work twice, let's sort this out by basename
                $field = basename($fieldDir);
                $allSugarFields[$field] = $field;
            }
        }



        foreach ( $allSugarFields as $fieldName) {
            $fieldData = array('views' => array());
            // Check each platform in order of precendence to find the "best" controller
            foreach ( $this->platforms as $platform ) {
                $controller = "clients/{$platform}/fields/{$fieldName}/{$fieldName}.js";
                if ( file_exists('custom/'.$controller) ) {
                    $controller = 'custom/'.$controller;
                }
                if ( file_exists($controller) ) {
                    $fieldData['controller'] = file_get_contents($controller);
                    // We found a controller, let's get out of here!
                    break;
                }
            }

            // Reverse the platform order so that "better" templates override worse ones
            $backwardsPlatforms = array_reverse($this->platforms);
            $templateDirs = array();
            foreach ( $backwardsPlatforms as $platform ) {
                $templateDirs[] = "clients/{$platform}/fields/{$fieldName}/";
            }
            $fieldData['views'] = $this->fetchTemplates($templateDirs);
            
            $result[$fieldName] = $fieldData;
        }

        $result['_hash'] = md5(serialize($result));
        return $result;
    }

    /**
     * A method to collect templates and pass them back, shared between sugarfields, viewtemplates and per-module templates
     *
     * @param searchDirs array A list of directories to search, custom directories will be searched automatically, ordered by least to most important
     * @param extension string A extension to search for, defaults to ".hbt"
     * @return array An array of template file contents keyed by the template name.
     */
    protected function fetchTemplates($searchDirs,$extension='.hbt') {
        $templates = array();

        foreach ( $searchDirs as $searchDir ) {
            if ( is_dir($searchDir) ) {
                $stdTemplates = glob($searchDir."/*".$extension);
                if ( is_array($stdTemplates) ) {
                    foreach ( $stdTemplates as $templateFile ) {
                        $templateName = substr(basename($templateFile),0,-strlen($extension));
                        $templates[$templateName] = file_get_contents($templateFile);
                    }
                }                    
            }
            // Do the custom directory last so it will override anything in the core product
            if ( is_dir('custom/'.$searchDir) ) {
                $cstmTemplates = glob('custom/'.$searchDir."/*".$extension);
                if ( is_array($cstmTemplates) ) {
                    foreach ( $cstmTemplates as $templateFile ) {
                        $templateName = substr(basename($templateFile),0,-strlen($extension));
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
            $defaultPortalViewsPath = 'modules/*/metadata/portal/views/*.php';
            $defaultPortalLayoutsPath = 'modules/*/metadata/portal/layouts/*.php';
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
                $fileParts = explode('/',$file);
                if ( $fileParts[0] == 'custom' ) {
                    // 0 => custom, 1 => modules, 2 => Accounts, 3 => metadata, 4 => portal, 5 => views, 6 => edit.php
                    $module = $fileParts[2];
                } else {
                    // 0 => modules, 1 => Accounts, 2 => metadata, 3 => portal, 4 => views, 5 => edit.php
                    $module = $fileParts[1];
                }
                $portalModules[$module] = $module;
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
        }
        
        $oldModuleList = $moduleList;
        $moduleList = array();
        foreach ( $oldModuleList as $module ) {
            $moduleList[$module] = $module;
        }

        $moduleList['_hash'] = md5(serialize($moduleList));
        return $moduleList;
    }
}
