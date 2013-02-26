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

require_once('data/BeanFactory.php');
require_once('include/api/SugarApi.php');

class ModuleApi extends SugarApi {
    public function registerApiRest() {
        return array(
            'create' => array(
                'reqType' => 'POST',
                'path' => array('<module>'),
                'pathVars' => array('module'),
                'method' => 'createRecord',
                'shortHelp' => 'This method creates a new record of the specified type',
                'longHelp' => 'include/api/help/module_new_help.html',
            ),
            'retrieve' => array(
                'reqType' => 'GET',
                'path' => array('<module>','?'),
                'pathVars' => array('module','record'),
                'method' => 'retrieveRecord',
                'shortHelp' => 'Returns a single record',
                'longHelp' => 'include/api/help/module_retrieve_help.html',
            ),
            'update' => array(
                'reqType' => 'PUT',
                'path' => array('<module>','?'),
                'pathVars' => array('module','record'),
                'method' => 'updateRecord',
                'shortHelp' => 'This method updates a record of the specified type',
                'longHelp' => 'include/api/help/module_update_help.html',
            ),
            'delete' => array(
                'reqType' => 'DELETE',
                'path' => array('<module>','?'),
                'pathVars' => array('module','record'),
                'method' => 'deleteRecord',
                'shortHelp' => 'This method deletes a record of the specified type',
                'longHelp' => 'include/api/help/module_delete_help.html',
            ),
            'favorite' => array(
                'reqType' => 'PUT',
                'path' => array('<module>','?', 'favorite'),
                'pathVars' => array('module','record', 'favorite'),
                'method' => 'setFavorite',
                'shortHelp' => 'This method sets a record of the specified type as a favorite',
                'longHelp' => 'include/api/help/module_favorite_help.html',
            ),
            'deleteFavorite' => array(
                'reqType' => 'DELETE',
                'path' => array('<module>','?', 'favorite'),
                'pathVars' => array('module','record', 'favorite'),
                'method' => 'unsetFavorite',
                'shortHelp' => 'This method unsets a record of the specified type as a favorite',
                'longHelp' => 'include/api/help/module_unfavorite_help.html',
            ),            
            'unfavorite' => array(
                'reqType' => 'PUT',
                'path' => array('<module>','?', 'unfavorite'),
                'pathVars' => array('module','record', 'unfavorite'),
                'method' => 'unsetFavorite',
                'shortHelp' => 'This method unsets a record of the specified type as a favorite',
                'longHelp' => 'include/api/help/module_unfavorite_help.html',
            ),
            'enum' => array(
                'reqType' => 'GET',
                'path' => array('<module>','enum','?'),
                'pathVars' => array('module', 'enum', 'field'),
                'method' => 'getEnumValues',
                'shortHelp' => 'This method returns enum values for a specified field',
                'longHelp' => 'include/api/help/module_enum_help.html',
            ),
        );
    }

    /**
     * This method returns the dropdown options of a given field
     * @param array $api 
     * @param array $args 
     * @return array
     */
    public function getEnumValues($api, $args) {
        $this->requireArgs($args, array('module','field'));

        $bean = BeanFactory::newBean($args['module']);

        if(!isset($bean->field_defs[$args['field']])) {
           throw new SugarApiExceptionNotFound('field not found');
        }

        $vardef = $bean->field_defs[$args['field']];
        
        $api->setHeader('Cache-Control', 'max-age=3600, private');

        if(isset($vardef['cache_setting'])) {
            $api->setHeader('Cache-Control', "max-age={$vardef['cache_setting']}, private");
        }
    
        if(isset($vardef['function'])) {
            if ( isset($vardef['function']['returns']) && $vardef['function']['returns'] == 'html' ) {
                throw new SugarApiExceptionError('html dropdowns are not supported');
            }

            $funcName = $vardef['function'];
            $includeFile = '';
            if ( isset($vardef['function_include']) ) {
                $includeFile = $vardef['function']['include'];
            }

            if(!empty($includeFile)) {
                require_once($includeFile);
            }

            return $funcName();
        }
        else {
            if(!isset($GLOBALS['app_list_strings'][$vardef['options']])) {
                throw new SugarApiExceptionNotFound('options not found');
            }
            return $GLOBALS['app_list_strings'][$vardef['options']];
        }
    }

    public function createRecord($api, $args) {
        $this->requireArgs($args,array('module'));

        $bean = BeanFactory::newBean($args['module']);
        
        // TODO: When the create ACL goes in to effect, add it here.
        if (!$bean->ACLAccess('save')) {
            // No create access so we construct an error message and throw the exception
            $moduleName = null;
            if(isset($args['module'])){
                $failed_module_strings = return_module_language($GLOBALS['current_language'], $args['module']);
                $moduleName = $failed_module_strings['LBL_MODULE_NAME'];
            }
            $args = null;
            if(!empty($moduleName)){
                $args = array('moduleName' => $moduleName);
            }
            throw new SugarApiExceptionNotAuthorized('EXCEPTION_CREATE_MODULE_NOT_AUTHORIZED', $args);
        }

        $id = $this->updateBean($bean, $api, $args);

        $args['record'] = $id;

        $bean = $this->loadBean($api, $args, 'view');
        $api->action = 'view';
        $data = $this->formatBean($api, $args, $bean);

        return $data;
    }

    public function updateRecord($api, $args) {
        $this->requireArgs($args,array('module','record'));

        $bean = $this->loadBean($api, $args, 'save');

        $id = $this->updateBean($bean, $api, $args);

        $bean = $this->loadBean($api, $args, 'view');

        $api->action = 'view';
        $data = $this->formatBean($api, $args, $bean);

        return $data;
    }

    public function retrieveRecord($api, $args) {
        $this->requireArgs($args,array('module','record'));

        $bean = $this->loadBean($api, $args, 'view');
        
        $api->action = 'view';
        $data = $this->formatBean($api, $args, $bean);

        return $data;

    }

    public function deleteRecord($api, $args) {
        $this->requireArgs($args,array('module','record'));

        $bean = $this->loadBean($api, $args, 'delete');
        $bean->mark_deleted($args['record']);

        return array('id'=>$bean->id);
    }

    public function setFavorite($api, $args) {
        $this->requireArgs($args, array('module', 'record'));
        $bean = $this->loadBean($api, $args, 'view');
        $this->toggleFavorites($bean, true);
        $api->action = 'view';
        $data = $this->formatBean($api, $args, $bean);
        return $data;
    }

    public function unsetFavorite($api, $args) {
        $this->requireArgs($args, array('module', 'record'));
        $bean = $this->loadBean($api, $args, 'view');
        $this->toggleFavorites($bean, false);
        $api->action = 'view';
        $data = $this->formatBean($api, $args, $bean);
        return $data;
    }

}
