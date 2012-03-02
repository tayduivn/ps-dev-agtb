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

    private $modules = null;
    private $mobile = false;
    private $filter = null;
    private $typeFilter = null;
    private $filterVarDefs = false;
    private $basePath = "modules";

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

        foreach ($mods as $modName) {
            $data[$modName] = $this->getDataCollection($modName);
        }

        $data['SugarFields'] = $this->getSugarFields();

        $md5 = serialize($data);
        $md5 = md5($md5);
        $data["md5"] = $md5;

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
    private function getAllViewsData($moduleName) {
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
            $cusDef = "custom/modules/{$moduleName}/metadata/{$defFile}";

            unset($$viewAccessor);

            if (file_exists($stdDef)) {
                include_once($stdDef);
            }

            if (file_exists($cusDef)) {
                include_once($cusDef);
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
            $md5 = serialize($data[$viewType]);
            $md5 = md5($md5);
            $data[$viewType]["{$viewType}_md5"] = $md5;
            unset($$viewAccessor);
        }

        return $data;
    }

    /**
     * The master collector method.  Gets metadata for all of the known types.
     *
     * @param $moduleName The name of the module to collect metadata about.
     * @return array An array of hashes containing the metadata.  Empty arrays are
     * returned in the case of no metadata.
     */
    private function getDataCollection($moduleName) {
        $data = array(
            "views" => array()
        );
        $vardefs = null;

        $data["views"] = $this->getAllViewsData($moduleName);
        $vardefs = $this->getVarDefs($moduleName);

        foreach (array_keys($vardefs) as $key => $val) {
            if (is_array($vardefs[$val])) {

                global $beanList;
                include_once("include/modules.php");

                if (in_array($val, $beanList)) {
                    $reverse_name = null;

                    foreach ($beanList as $bName => $bValue) {
                        if ($bValue == $val) {
                            $reverse_name = $bName;
                            break;
                        }
                    }

                    if ($reverse_name != null) {
                        $data['beans'][$val] = $this->getBeanInfo($reverse_name);
                        $data['beans'][$val]['vardefs'] = $vardefs[$val];
                    }
                }
            }
        }

        $keys = array_keys($vardefs);
        if (count($keys) > 0) {
            $data["beans"]["primary_bean"] = $keys[0];
        }

        $md5 = serialize($data);
        $md5 = md5($md5);
        $data["{$moduleName}_md5"] = $md5;

        return $data;
    }

    /**
     * Reads the directory passed to it, and generates a list of directories contained within
     * the parent directory.  This method filters out the ".." & "." directories.
     *
     * @param $dir The directory to read modules directory's from.
     * @return array An array of module names.
     */
    private function readModuleDir($dir) {
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
    private function getVarDefs($moduleName) {
        $data = array();
        $vdefFile = "vardefs.php";
        $isCustom = false;
        $stdVdef = "modules/{$moduleName}/{$vdefFile}";
        $cusVdef = "custom/modules/{$moduleName}/{$vdefFile}";
        $extVdef = "custom/modules/{$moduleName}/Ext/Vardefs/vardefs.ext.php";
        $keys = null;

        if ($this->filterVarDefs) {
            return $data;
        }

        // check to see if
        if (file_exists($stdVdef)) {
            include_once($stdVdef);
        }

        if (file_exists($cusVdef)) {
            include_once($cusVdef);
        }

        if (file_exists($extVdef)) {
            include_once($extVdef);
        }

        if (!isset($dictionary)) {
            return $data;
        }

        // this is a hack, but for some reason php will choke and die on $dictionary unless this is all done
        // before going and then setting up the global version.  Lame and needs debugging!
        $keys = array_keys($dictionary);

        global $dictionary;
        if (file_exists($stdVdef)) {
            include_once($stdVdef);
        }

        if (file_exists($cusVdef)) {
            include_once($cusVdef);
        }

        if (file_exists($extVdef)) {
            include_once($extVdef);
        }

        if (!isset($dictionary)) {
            return $data;
        }

        foreach ($keys as $key) {
            if (array_key_exists($key, $dictionary)) {
                $data[$key] = $dictionary[$key];
            }
        }

        $data['isCustom'] = $isCustom;
        $md5 = serialize($data);
        $md5 = md5($md5);
        $data['vardefs_md5'] = $md5;

        return $data;
    }

    /**
     * Collects information from a bean after trying to create it using the bean factory.
     *
     *
     * @param $name
     * @return array
     */
    private function getBeanInfo($name) {
        $data = array();

        global $beanList;

        require_once("data/BeanFactory.php");

        $bean = BeanFactory::newBean($name);
        if ($bean != false) {
            if (key_exists($name, $beanList)) {
                $data["bean_name"] = $beanList[$name];
            }

            if (isset($bean->module_dir)) {
                $mod_dir = $bean->module_dir;
            } else {
                $mod_dir = "";
            }

            if (isset($bean->module_name)) {
                $mod_name = $bean->module_name;
            } else {
                $mod_name = "";
            }

            $data["module_dir"] = $mod_dir;
            $data["module_name"] = $mod_name;
        }

        $md5 = json_encode($data);
        $md5 = md5($md5);
        $data["bean_md5"] = $md5;

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
                    $result[$fieldName] = array('views'=>array());
                }
                if (!isset($result[$fieldName]['views'][$action]) && strtolower($action) != strtolower($fieldName)) {
                    $result[$fieldName]['views'][$action] = array();
                }

                if (strtolower($action) != strtolower($fieldName)){
                    $result[$fieldName]['views'][$action] = array_merge($result[$fieldName]['views'][$action], array($fieldMeta=>$fcontents)) ;
                } else {
                    $result[$fieldName]['handler'] = $fcontents ;
                }

            }

            $result['md5'] = md5(serialize($result));
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
    private function getFiles($directory, $exempt = array('.', '..', '.ds_store', '.svn'), &$files = array(), $exempt_extensions = array('tpl', 'php'))
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

}
