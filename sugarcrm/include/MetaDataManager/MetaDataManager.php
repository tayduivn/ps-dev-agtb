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

    /**
     * The constructor for the class.
     *
     */
    function __construct () {
    }

    /**
     * This function goes and collects the metadata for you.
     *
     * @param array $modules A list of modules to return *REQUIRED*
     * @param array $typeFilter A list of data types to return, if null all modules are returned.
     * @param string $platform What platform to load metadata for: "base", "mobile", "portal" are likely options, defaults to "base"
     * @return array Retuns an array of module names and all of the metata for each module in hashes contained in the array.
     */
    public function getData($modules, $typeFilter = array(), $platform = 'base') {
        // Default the type filter to everything
        if ( empty($typeFilter) ) {
            $typeFilter = array('modules','sugarFields','viewTemplates','labels','appStrings','appListStrings');
        }

        $this->modules = $modules;
        $this->typeFilter = $typeFilter;
        if ( $platform == 'mobile' ) {
            $this->platforms = array('mobile','portal','base');
        } else if ( $platform == 'portal' ) {
            $this->platforms = array('portal','base');
        } else {
            $this->platforms = array('base');
        }
        
        if (in_array('modules',$this->typeFilter)) {
            $data['modules'] = array();
            foreach ($modules as $modName) {
                $modData = $this->getModuleData($modName);
                $data['modules'][$modName] = $modData;
            }
        }
        
        if (in_array('sugarFields',$this->typeFilter)) {
            $data['sugarFields'] = $this->getSugarFields();
        }
        
        if (in_array('viewTemplates',$this->typeFilter)) {
            $data['viewTemplates'] = $this->getViewTemplates();
        }
        
        if (in_array('appStrings', $this->typeFilter)) {
            $data['appStrings'] = $this->getAppStrings();
        }
        
        if (in_array('appListStrings', $this->typeFilter)) {
            $data['appListStrings'] = $this->getAppListStrings();
        }

        // TODO: When we actually start caching the results of the metadata, we will have to calculate everything and only send the requested parts so the md5 is consistent.
        $md5 = serialize($data);
        $md5 = md5($md5);
        $data["_hash"] = md5(serialize($data));

        return $data;
    }

    /**
     * This method collects all view data for the different types of views supported by
     * the SugarCRM app.
     *
     * @param $moduleName The name of the sugar modulde to collect info about.
     *
     * @return Array A hash of all of the view data.
     */
    protected function getModuleViews($moduleName) {
        $data = array();

        return $data;
    }

    /**
     * The collector method for modules.  Gets metadata for all of the module specific data
     *
     * @param $moduleName The name of the module to collect metadata about.
     * @return array An array of hashes containing the metadata.  Empty arrays are
     * returned in the case of no metadata.
     */
    protected function getModuleData($moduleName) {
        $vardefs = $this->getVarDef($moduleName);

        $data['fields'] = $vardefs['fields'];
        //FIXME: Need more relationshp data (all relationship data)
        $data['relationships'] = $vardefs['relationships'];
        if(in_array('labels',$this->typeFilter)) {
            $data['labels'] = return_module_language($GLOBALS['current_language'],$moduleName);
        }
        $data['views'] = $this->getModuleViews($moduleName);

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
    protected function getVarDef($moduleName) {

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
     * gets sugar fields
     *
     * @return array array of sugarfields with a hash
     */
    public function getSugarFields()
    {
        $result = array();

        $baseFieldDirectory = "include/SugarFields/Fields/";        
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

        foreach ( $allSugarFields as $fieldName ) {
            $fieldData = array();
            // Check each platform in order of precendence to find the "best" controller
            foreach ( $this->platforms as $platform ) {
                $controller = $baseFieldDirectory.$fieldName."/${platform}/${fieldName}.js";
                if ( file_exists('custom/'.$controller) ) {
                    $controller = 'custom/'.$controller;
                }
                if ( file_exists($controller) ) {
                    $fieldData['controller'] = file_get_contents($controller);
                    // We found a controller, let's get out of here!
                    break;
                }
            }

            $fieldData['templates'] = array();
            // Reverse the platform order so that "better" templates override worse ones
            $backwardsPlatforms = array_reverse($this->platforms);
            foreach ( $backwardsPlatforms as $platform ) {
                $templateDir = $baseFieldDirectory.$fieldName."/${platform}/";
                $templates = array();
                
                if ( is_dir($templateDir) ) {
                    $templates = glob($templateDir."*.hbt");
                }
                // Do the custom directory last so it will override anything in the core product
                if ( is_dir('custom/'.$templateDir) ) {
                    $cstmTemplates = glob('custom/'.$templateDir."*.hbt");
                    if ( !empty($cstmTemplates) ) {
                        $templates += $cstmTemplates;
                    }
                }
                foreach ( $templates as $templateFile ) {
                    $templateName = substr(basename($templateFile),0,-4);
                    $fieldData['templates'][$templateName] = file_get_contents($templateFile);
                }
            }
            
            $result[$fieldName] = $fieldData;
        }

        $result['_hash'] = md5(serialize($result));
        return $result;
    }

    /**
     * The collector method for view templates
     *
     * @return array A hash of the template name and the template contents
     */
    protected function getViewTemplates() {
        $templates = array();
        $templates['_hash'] = md5(serialize($templates));
        return $templates;
    }

    /**
     * The collector method for the app strings
     *
     * @return array The app strings for the current language, and a hash of the app strings
     */
    protected function getAppStrings() {
        $appStrings = $GLOBALS['app_strings'];
        $appStrings['_hash'] = md5(serialize($appStrings));
        return $appStrings;
    }

    /**
     * The collector method for the app strings
     *
     * @return array The app strings for the current language, and a hash of the app strings
     */
    protected function getAppListStrings() {
        $appStrings = $GLOBALS['app_list_strings'];
        $appStrings['_hash'] = md5(serialize($appStrings));
        return $appStrings;
    }
}
