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
 * Notes:
 *  All data that is retuned for each data type such as vardefs, listviewdefs, etc... will
 * all have hash keys called "isMobile", "isCustom", "{moduleName}_md5".
 *
 *  "isMobile": is a bool value which lets you know if the data is for a mobile view or not.
 *  "isCustom": is a bool value which lets you know if the data is from the custom sugarcrm
 *      directory or not.
 *  "{moduleName}_md5": Is this an md5sum of the metadata for a given module.
 *
 */
class MetaDataManager {

    protected $modules = null;
    protected $mobile = false;
    protected $filter = null;
    protected $typeFilter = null;
    protected $filterVarDefs = false;
    protected $basePath = "modules";

    /**
     * The constructor for the class.
     *
     * @param null $filter A list of modules to return, if null all modules are returned.
     * @param bool $isMobile Will cause mobile metadata to be returned where it exists.
     */
    function __construct ($filter = null, $type = null, $isMobile = false) {
        $this->mobile = $isMobile;
        $this->filter = $filter;
        $this->typeFilter = $type;
        $this->modules = $this->readModuleDir($this->basePath);

        if ($this->typeFilter != null && in_array("vardefs", $this->typeFilter)) {
            $this->filterVarDefs = true;
        }

    }

    /**
     * This function goes and collects the metadata for you.
     *
     * @return array Retuns an array of module names and all of the metata for each module
     * in hashes contained in the array.
     */
    public function getData() {
        $mods = null;
        $data = array();

        // check to see if there is a list of mods to return other then all of them.
        if ($this->filter != null) {
            if (count($this->filter) < 1) {
                $mods = $this->modules;
            } else {
                $mods = $this->filter;
            }
        } else {
            $mods = $this->modules;
        }
        
        $data['modules'] = array();
        foreach ($mods as $modName) {
            $modData = $this->getModuleData($modName);
            $data['modules'][$modName] = $modData;
        }

        $data['sugarFields'] = $this->getSugarFields();
        
        $data['viewTemplates'] = $this->getViewTemplates();
        
        $data['appStrings'] = $this->getAppStrings();
        
        $data['appListStrings'] = $this->getAppListStrings();

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

        $types = array(
            "detailviewdefs" => "viewdefs",
            "editviewdefs" => "viewdefs",
            "searchdefs" => "searchdefs",
            "listviewdefs" => "listViewDefs"
        );

        /*
         * filter out the unwanted views.
         */
        if ($this->typeFilter != null) {
            $tmptypes = array();
            foreach ($this->typeFilter as $userFilter) {
                $userFilter = strtolower($userFilter);

                if ($userFilter != "vardefs") {
                    $tmptypes[$userFilter] = "{$types[$userFilter]}";
                }
            }

            $types = $tmptypes;
            $tmptypes = null;
        }

        foreach ($types as $viewType => $viewAccessor) {
            $data[$viewType] = array();
            $stdDel = "";
            $cusDel = "";
            $useFile = null;
            $isCustom = false;
            $defFile = "{$viewType}.php";

            if ($this->mobile) {
                $defFile = "wireless.{$defFile}";
            }

            $stdDef = "modules/{$moduleName}/metadata/{$defFile}";
            $cusDef = "custom/{$stdDef}";

            unset($$viewAccessor);

            if (file_exists($stdDef)) {
                require($stdDef);
            }

            if (file_exists($cusDef)) {
                require($cusDef);
                $isCustom = true;
            }

            if (!isset($$viewAccessor)) {
                $data[$viewType] = array();
                continue;
            }

            $tmp = $$viewAccessor;
            $keys = array_keys($tmp);
            $data[$viewType] = $tmp[$keys[0]];
            $data[$viewType]['isCustom'] = $isCustom;
            $data[$viewType]['isMobile'] = $this->mobile;
            unset($$viewAccessor);
        }

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
        $data['views'] = $this->getModuleViews($moduleName);
        $vardefs = $this->getVarDef($moduleName);

        $data['fields'] = $vardefs['fields'];
        //FIXME: Need more relationshp data (all relationship data)
        $data['relationships'] = $vardefs['relationships'];
        $data['labels'] = return_module_language($GLOBALS['current_language'],$moduleName);

        $md5 = serialize($data);
        $md5 = md5($md5);
        $data["_hash"] = $md5;

        return $data;
    }

    /**
     * Reads the directory passed to it, and generates a list of directories contained within
     * the parent directory.  This method filters out the ".." & "." directories.
     *
     * @param $dir The directory to read modules directory's from.
     * @return array An array of module names.
     */
    protected function readModuleDir($dir) {
        $dir = opendir($dir);
        $modules = array();

        if ($dir == FALSE) {
            return $modules;
        }

        while (($file = readdir($dir)) != false) {
            if ( $file === "." || $file  === "..") {
                continue;
            }

            array_push($modules, $file);
        }

        return $modules;
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
     * @return array array of sugarfields with
     */
    public function getSugarFields()
    {
        $fieldFileTypes2meta = array('hbt'=>'template','js'=>'js');
        $result = array();
        $fieldsDirectory = "include/SugarFields/PortalFields/";
        // get list of portal fields
        $portalFiles = $this->getFiles($fieldsDirectory);

        foreach ($portalFiles as $finfo) {
            $build = false;
            $fieldMeta = '';

            // get file info
            $fieldName = array_pop(explode('/', $finfo['dirname']));
            $fileExtension = $finfo['extension'];
            $action=$finfo["filename"];

            // check if we want this file
            if (in_array($fileExtension, array_keys($fieldFileTypes2meta))) {
                $build = true;
                $fieldMeta = $fieldFileTypes2meta[$fileExtension];
            }

            // add it to result if we want it
            if ($build) {
                $fcontents = file_get_contents($finfo["dirname"]."/".$finfo["basename"]);
                if (!isset($result[$fieldName])) {
                    $result[$fieldName] = array('templates'=>array());
                }
                if (!isset($result[$fieldName]['templates'][$action]) && strtolower($action) != strtolower($fieldName)) {
                    $result[$fieldName]['templates'][$action] = array();
                }

                if (strtolower($action) != strtolower($fieldName)){
                    $result[$fieldName]['templates'][$action] = array_merge($result[$fieldName]['templates'][$action], array($fieldMeta=>$fcontents)) ;
                } else {
                    $result[$fieldName]['controller'] = $fcontents ;
                }

            }

            $result['_hash'] = md5(serialize($result));
        }
        return $result;
    }


    /**
     * return files from a directory recursively
     *
     * @param $directory
     * @param array $exempt full file names to ignore
     * @param array $files
     * @param array $exempt_extensions file extensions to ignore
     * @return array
     */
    protected function getFiles($directory, $exempt = array('.', '..', '.ds_store', '.svn'), &$files = array(), $exempt_extensions = array('tpl', 'php'))
    {
        $handle = opendir($directory);
        while (false !== ($resource = readdir($handle))) {
            if (!in_array(strtolower($resource), $exempt)) {
                if (is_dir($directory . $resource . '/')) {
                    array_merge($files,
                        $this->getFiles($directory . $resource . '/', $exempt, $files));
                }
                else {
                    $resourceParts = explode('.', $resource);
                    $extension = end($resourceParts);
                    if ($extension && !in_array($extension, $exempt_extensions)) {
                        $files[] = pathinfo($directory . $resource);
                    }
                }
            }
        }
        closedir($handle);
        return $files;
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
