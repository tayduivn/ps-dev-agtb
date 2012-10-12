<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once 'tests/rest/RestTestBase.php';
require_once 'include/MetaDataManager/MetaDataManager.php';

class RestMetadataModuleListTest extends RestTestBase {
    /**
     * @group rest
     */
    public function testMetadataGetModuleListBase() {
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata?type_filter=module_list');

        $this->assertTrue(isset($restReply['reply']['module_list']['_hash']),'There is no base module list');
        $restModules = $restReply['reply']['module_list'];
        unset($restModules['_hash']);
        
        // Get the expected
        $modules = $this->_getModuleListsLikeTheAPIDoes();
        $modules = $modules['module_list'];
        
        // Diff
        $extras = array_diff($restModules, $modules);
        
        // Assert
        $this->assertEmpty($extras, "There are extra modules in the REST list");
    }

    /**
     * @group rest
     */
    public function testMetadataGetFullModuleListBase() {
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('metadata?type_filter=full_module_list');
        $this->assertArrayHasKey('full_module_list', $restReply['reply'], "Full Module List is missing from the reply");
        $fullRestModules = $restReply['reply']['full_module_list'];
        $this->assertArrayHasKey('_hash', $fullRestModules, 'There is no _hash key in the response');
        unset($fullRestModules['_hash']);
        
        // Now get what we expect
        $fullModuleList = $this->_getFullModuleListLikeTheAPIDoes();
        
        // Check for differences
        $extras = array_diff($fullRestModules, $fullModuleList);
        
        // Assert
        $this->assertEmpty($extras, "There are extra modules in the rest reply");
    }

    /**
     * Helper function that gets a full module list like the API would do
     * 
     * @return array
     */
    protected function _getFullModuleListLikeTheAPIDoes() {
        $data = $this->_getModuleListsLikeTheAPIDoes();
        return $data['full_module_list'];
    }

    /**
     * Helper method to get all the module lists that the API would get. Returns
     * an array of modules, module_list and full_module_list
     * 
     * @return array
     */
    protected function _getModuleListsLikeTheAPIDoes() {
        // Get the metadata manager
        $mm = new MetaDataManager($this->_user);
        
        // Get the api
        require_once 'clients/base/api/MetadataApi.php';
        $api = new MetadataApi();
        
        $data['module_list'] = $api->getModuleList();
        $data['full_module_list'] = $data['module_list'];
        
        $data['modules'] = array();
        
        foreach($data['full_module_list'] as $module) {
            $bean = BeanFactory::newBean($module);
            if (!$bean || !is_a($bean,'SugarBean') ) {
                // There is no bean, we can't get data on this
                continue;
            }

            $modData = $mm->getModuleData($module);
            $data['modules'][$module] = $modData;

            if (isset($data['modules'][$module]['fields'])) {
                $fields = $data['modules'][$module]['fields'];
                foreach($fields as $fieldName => $fieldDef) {
                    if (isset($fieldDef['type']) && ($fieldDef['type'] == 'relate')) {
                        if (isset($fieldDef['module']) && !in_array($fieldDef['module'], $data['full_module_list'])) {
                            $data['full_module_list'][$fieldDef['module']] = $fieldDef['module'];
                        }
                    } elseif (isset($fieldDef['type']) && ($fieldDef['type'] == 'link')) {
                        $bean->load_relationship($fieldDef['name']);
                        $otherSide = $bean->$fieldDef['name']->getRelatedModuleName();
                        $data['full_module_list'][$otherSide] = $otherSide;
                    }
                }
            }
        }

        foreach($data['modules'] as $moduleName => $moduleDef) {
            if (!array_key_exists($moduleName, $data['full_module_list'])) {
                unset($data['modules'][$moduleName]);
            }
        }
        
        return $data;
    }
}
