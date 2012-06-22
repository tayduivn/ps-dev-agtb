<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('include/MetaDataManager/MetaDataManager.php');

// An API to let the user in to the metadata
class MetadataApi extends SugarApi {
    public function registerApiRest() {
        return array(
            'getAllMetadata' => array(
                'reqType' => 'GET',
                'path' => array('metadata'),
                'pathVars' => array(''),
                'method' => 'getAllMetadata',
                'shortHelp' => 'This method will return all metadata for the system',
                'longHelp' => 'include/api/html/metadata_all_help.html',
            ),
            'getAllMetadataPost' => array(
                'reqType' => 'POST',
                'path' => array('metadata'),
                'pathVars' => array(''),
                'method' => 'getAllMetadata',
                'shortHelp' => 'This method will return all metadata for the system, filtered by the array of hashes sent to the server',
                'longHelp' => 'include/api/html/metadata_all_help.html',
            ),
            'getAllMetadataHashes' => array(
                'reqType' => 'GET',
                'path' => array('metadata','_hash'),
                'pathVars' => array(''),
                'method' => 'getAllMetadataHash',
                'shortHelp' => 'This method will return the hash of all metadata for the system',
                'longHelp' => 'include/api/html/metadata_all_help.html',
            ),
        );
    }
    
    protected function getMetadataManager() {
        return new MetaDataManager($this->user,$this->platforms);
    }

    public function getAllMetadata($api, $args) {
        // Default the type filter to everything
        $this->typeFilter = array('modules','fullModuleList','fields','viewTemplates','labels','modStrings','appStrings','appListStrings','acl','moduleList', 'views', 'layouts','relationships');
        if ( !empty($args['typeFilter']) ) {
            // Explode is fine here, we control the list of types
            $types = explode(",", $args['typeFilter']);
            if ($types != false) {
                $this->typeFilter = $types;
            }
        }

        $moduleFilter = array();
        if (!empty($args['moduleFilter'])) {
            // Use str_getcsv here so that commas can be escaped, I pity the fool that has commas in his module names.
            $modules = str_getcsv($args['moduleFilter'],',','');
            if ( $modules != false ) {
                $moduleFilter = $modules;
            }
        }

        $onlyHash = false;
        if (!empty($args['onlyHash']) && ($args['onlyHash'] == 'true' || $args['onlyHash'] == '1')) {
            $onlyHash = true;
        }


        $this->user = $GLOBALS['current_user'];
        
        if ( isset($args['platform']) ) {
            $this->platforms = array(basename($args['platform']),'base');
        } else {
            $this->platforms = array('base');
        }

        $mm = $this->getMetadataManager();
        
        $this->modules = array_keys(get_user_module_list($this->user));

        // Start collecting data
        $data = array();

        $data['modules'] = array();
        foreach ($this->modules as $modName) {
            $modData = $mm->getModuleData($modName);
            $data['modules'][$modName] = $modData;
        }


        $data['moduleList'] = $mm->getModuleList($this->platforms[0]);
        $data['fullModuleList'] = $data['moduleList'];
        foreach($data['moduleList'] as $module) {
            $bean = BeanFactory::newBean($module);
            if (isset($data['modules'][$module]['fields'])) {
                $fields = $data['modules'][$module]['fields'];
                foreach($fields as $fieldName => $fieldDef) {
                    if (isset($fieldDef['type']) && ($fieldDef['type'] == 'relate')) {
                        if (isset($fieldDef['module']) && !in_array($fieldDef['module'], $data['fullModuleList'])) {
                            $data['fullModuleList'][$fieldDef['module']] = $fieldDef['module'];
                        }
                    } elseif (isset($fieldDef['type']) && ($fieldDef['type'] == 'link')) {
                        $bean->load_relationship($fieldDef['name']);
                        $otherSide = $bean->$fieldDef['name']->getRelatedModuleName();
                        $data['fullModuleList'][$otherSide] = $otherSide;
                    }
                }
            }
        }

        foreach($data['modules'] as $moduleName => $moduleDef) {
            if (!array_key_exists($moduleName, $data['fullModuleList'])) {
                unset($data['modules'][$moduleName]);
            }
        }

        $data['modStrings'] = array();
        foreach ($data['modules'] as $modName => $moduleDef) {
            $modData = $mm->getModuleStrings($modName);
            $data['modStrings'][$modName] = $modData;
            $data['modStrings'][$modName]['_hash'] = md5(serialize($data['modStrings'][$modName]));
        }

        $data['acl'] = array();
        foreach ($this->modules as $modName) {
            $data['acl'][$modName] = $mm->getAclForModule($modName,$GLOBALS['current_user']->id);
            // Modify the ACL's for portal, this is a hack until "create" becomes a real boy.
            if(isset($_SESSION['type'])&&$_SESSION['type']=='support_portal') {
                $data['acl'][$modName]['admin'] = 'no';
                $data['acl'][$modName]['developer'] = 'no';
                $data['acl'][$modName]['edit'] = 'no';
                $data['acl'][$modName]['delete'] = 'no';
                $data['acl'][$modName]['import'] = 'no';
                $data['acl'][$modName]['export'] = 'no';
                $data['acl'][$modName]['massupdate'] = 'no';
            }
        }
        // remove the disabled modules from the module list
        require_once("modules/MySettings/TabController.php");
        $controller = new TabController();
        $tabs = $controller->get_tabs_system();

        if (isset($tabs[1])) {
            foreach($data['moduleList'] as $moduleKey => $moduleName){
                if (in_array($moduleName,$tabs[1])) {
                    unset($data['moduleList'][$moduleKey]);
                }
            }
        }

        $data['fields']  = $mm->getSugarClientFiles('field');
        $data['views']   = $mm->getSugarClientFiles('view');
        $data['layouts'] = $mm->getSugarClientFiles('layout');
        $data['viewTemplates'] = $mm->getViewTemplates();
        $data['appStrings'] = $mm->getAppStrings();
        $data['appListStrings'] = $mm->getAppListStrings();
        $data['relationships'] = $mm->getRelationshipData();
        $md5 = serialize($data);
        $md5 = md5($md5);
        $data["_hash"] = md5(serialize($data));
        
        $baseChunks = array('viewTemplates','fields','appStrings','appListStrings','moduleList', 'views', 'layouts', 'fullModuleList','relationships');
        $perModuleChunks = array('modules','modStrings','acl');



        if ( $onlyHash ) {
            // The client only wants hashes
            $hashesOnly = array();
            $hashesOnly['_hash'] = $data['_hash'];
            foreach ( $baseChunks as $chunk ) {
                if (in_array($chunk,$this->typeFilter) ) {
                    $hashesOnly[$chunk]['_hash'] = $data['_hash'];
                }
            }
            
            foreach ( $perModuleChunks as $chunk ) {
                if (in_array($chunk, $this->typeFilter)) {
                    // We want modules, let's filter by the requested modules and by which hashes match.
                    foreach($data[$chunk] as $modName => &$modData) {
                        if (empty($moduleFilter) || in_array($modName,$moduleFilter)) {
                            $hashesOnly[$chunk][$modName]['_hash'] = $data[$chunk][$modName]['_hash'];
                        }
                    }
                }
            }

            $data = $hashesOnly;
            
        } else {
            // The client is being bossy and wants some data as well.
            foreach ( $baseChunks as $chunk ) {
                if (!in_array($chunk,$this->typeFilter)
                    || (isset($args[$chunk]) && $args[$chunk] == $data[$chunk]['_hash'])) {
                    unset($data[$chunk]);
                }
            }
            
            // Relationships are special, they are a baseChunk but also need to pay attention to modules
            if (!empty($moduleFilter) && isset($data['relationships']) ) {
                // We only want some modules, but we want the relationships
                foreach ($data['relationships'] as $relName => $relData ) {
                    if ( $relName == '_hash' ) {
                        continue;
                    }
                    if (!in_array($relData['rhs_module'],$moduleFilter)
                        && !in_array($relData['lhs_module'],$moduleFilter)) {
                        unset($data['relationships'][$relName]);
                    }
                }
            }

            foreach ( $perModuleChunks as $chunk ) {
                if (!in_array($chunk, $this->typeFilter)) {
                    unset($data[$chunk]);
                } else {
                    // We want modules, let's filter by the requested modules and by which hashes match.
                    foreach($data[$chunk] as $modName => &$modData) {
                        if ((!empty($moduleFilter) && !in_array($modName,$moduleFilter))
                            || (isset($args[$chunk][$modName]) && $args[$chunk][$modName] == $modData['_hash'])) {
                            unset($data[$chunk][$modName]);
                        }
                    }
                }
            }
        }
        
        return $data;
        
        
    }
}
