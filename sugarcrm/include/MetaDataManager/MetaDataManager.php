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
    private $entryPoint = null;
    private $mobile = false;
    private $filter = null;

    /**
     * The constructor for the class.
     *
     * @param $entryPoint Sets the base directory for the sugarcrm app.
     * @param null $filter A list of modules to return, if null all modules are returned.
     * @param bool $isMobile Will cause mobile metadata to be returned where it exists.
     */
    function __construct ($entryPoint, $filter = null, $isMobile = false) {
        $this->entryPoint = $entryPoint;
        $this->mobile = $isMobile;
        $this->filter = $filter;
        $this->modules = $this->readModuleDir("{$this->entryPoint}/modules");
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

        $md5 = serialize($data);
        $md5 = md5($md5);
        $data["md5"] = $md5;

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

        $tmp = $this->getDetailDefs($moduleName);
        if (!array_key_exists("DetailView", $tmp)) {
            $tmp["DetailView"] = array();
        }

        $data["views"]["detaildefs"] = $tmp['DetailView'];

        $tmp = $this->getEditDefs($moduleName);
        if (!array_key_exists("EditView", $tmp)) {
            $tmp["EditView"] = array();
        }

        $data["views"]["editdefs"] = $tmp['EditView'];
        $data["views"]["listviewdefs"] = $this->getListDefs($moduleName);
        $data["views"]["searchdefs"] = $this->getSearchDefs($moduleName);
        $vardefs = $this->getVarDefs($moduleName);

        foreach (array_keys($vardefs) as $key => $val) {
            if (is_array($vardefs[$val])) {

                global $beanList;
                include_once("{$this->entryPoint}/include/modules.php");

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

        while ( ($file = readdir($dir)) != false) {
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
        $stdVdef = "{$this->entryPoint}/modules/{$moduleName}/{$vdefFile}";
        $cusVdef = "{$this->entryPoint}/custom/modules/{$moduleName}/{$vdefFile}";
        $extVdef = "{$this->entryPoint}/custom/modules/{$moduleName}/Ext/Vardefs/vardefs.ext.php";

        unset($dictionary);

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

        $data = $dictionary;
        unset($dictionary);

        $data['isCustom'] = $isCustom;
        $md5 = serialize($data);
        $md5 = md5($md5);
        $data['vardefs_md5'] = $md5;

        return $data;
    }

    /**
     * Gets detailviewdef info for a given module.
     *
     * @param $moduleName The name of the module to get detailviewdefs for.
     * @return array An array of the detailviewdefs info.
     */
    private function getDetailDefs($moduleName) {
        $stdDel = "";
        $cusDel = "";
        $defFile = "detailviewdefs.php";
        $useFile = null;
        $data = array();
        $isCustom = false;

        if ($this->mobile) {
            $defFile = "wireless.{$defFile}";
        }

        $stdDel = "{$this->entryPoint}/modules/{$moduleName}/metadata/{$defFile}";
        $cusDel = "{$this->entryPoint}/custom/modules/{$moduleName}/metadata/{$defFile}";

        if (file_exists($cusDel)) {
            $useFile = $cusDel;
            $isCustom = true;
        } else {
            $useFile = $stdDel;
        }

        if (file_exists($useFile)) {
            unset($viewdefs);
            include_once($useFile);
            $keys = array_keys($viewdefs);
            $data = $viewdefs[$keys[0]];
            unset($viewdefs);
        } else {
            return $data;
        }

        $data['isCustom'] = $isCustom;
        $data['isMobile'] = $this->mobile;
        $md5 = serialize($data);
        $md5 = md5($md5);
        $data['detailview'] = $md5;

        return $data;
    }

    /**
     * Gets editviewdef info for a given module.
     *
     * @param $moduleName The name of the module to collect editviewdefs info about.
     * @return array An array of editviewdefs info.
     */
    private function getEditDefs($moduleName) {
        $stdDel = "";
        $cusDel = "";
        $defFile = "editviewdefs.php";
        $useFile = null;
        $data = array();
        $isCustom = false;

        if ($this->mobile) {
            $defFile = "wireless.{$defFile}";
        }

        $stdDel = "{$this->entryPoint}/modules/{$moduleName}/metadata/{$defFile}";
        $cusDel = "{$this->entryPoint}/custom/modules/{$moduleName}/metadata/{$defFile}";

        if (file_exists($cusDel)) {
            $useFile = $cusDel;
            $isCustom = true;
        } else {
            $useFile = $stdDel;
        }

        if (file_exists($useFile)) {
            unset($viewdefs);
            include_once($useFile);
            $keys = array_keys($viewdefs);
            $data = $viewdefs[$keys[0]];
            unset($viewdefs);
        } else {
            return $data;
        }

        $data['isCustom'] = $isCustom;
        $data['isMobile'] = $this->mobile;
        $md5 = serialize($data);
        $md5 = md5($md5);
        $data['editdefs_md5'] = $md5;

        return $data;
    }

    /**
     * Gets searchviewdefs for a given module.
     *
     * @param $moduleName The module to collect searchdefs info about.
     * @return array An array of searchdefs info.
     */
    private function getSearchDefs($moduleName) {
        $stdDel = "";
        $cusDel = "";
        $defFile = "searchdefs.php";
        $useFile = null;
        $data = array();
        $isCustom = false;

        if ($this->mobile) {
            $defFile = "wireless.{$defFile}";
        }

        $stdDel = "{$this->entryPoint}/modules/{$moduleName}/metadata/{$defFile}";
        $cusDel = "{$this->entryPoint}/custom/modules/{$moduleName}/metadata/{$defFile}";

        if (file_exists($cusDel)) {
            $useFile = $cusDel;
            $isCustom = true;
        } else {
            $useFile = $stdDel;
        }

        if (file_exists($useFile)) {
            unset($searchdefs);
            include_once($useFile);
            $keys = array_keys($searchdefs);
            $data = $searchdefs[$keys[0]];
            unset($searchdefs);
        } else {
            return $data;
        }

        $data['isCustom'] = $isCustom;
        $data['isMobile'] = $this->mobile;
        $md5 = serialize($data);
        $md5 = md5($md5);
        $data['searchdefs_md5'] = $md5;

        return $data;
    }

    /**
     * Gets listviewdefs for a given module.
     *
     * @param $moduleName The module to collect listviewdefs info on.
     * @return array An array of listviewdefs info.
     */
    private function getListDefs($moduleName) {
        $stdDel = "";
        $cusDel = "";
        $defFile = "listviewdefs.php";
        $useFile = null;
        $data = array();
        $isCustom = false;

        if ($this->mobile) {
            $defFile = "wireless.{$defFile}";
        }

        $stdDel = "{$this->entryPoint}/modules/{$moduleName}/metadata/{$defFile}";
        $cusDel = "{$this->entryPoint}/custom/modules/{$moduleName}/metadata/{$defFile}";

        if (file_exists($cusDel)) {
            $useFile = $cusDel;
            $isCustom = true;
        } else {
            $useFile = $stdDel;
        }

        if (file_exists($useFile)) {
            unset($listViewDefs);
            include_once($useFile);
            $keys = array_keys($listViewDefs);
            $data = $listViewDefs[$keys[0]];
            unset($listViewDefs);
        } else {
            return $data;
        }

        $data['isCustom'] = $isCustom;
        $data['isMobile'] = $this->mobile;
        $md5 = serialize($data);
        $md5 = md5($md5);
        $data['listview_md5'] = $md5;

        return $data;
    }

    /**
     * @param $name
     * @return array
     */
    private function getBeanInfo($name) {
        $data = array();

        global $beanList;

        require_once("{$this->entryPoint}/data/BeanFactory.php");

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
}
