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

class ModuleApi extends SugarApi {
    public function registerApiRest() {
        return array(
            'create' => array(
                'reqType' => 'POST',
                'path' => array('<module>'),
                'pathVars' => array('module'),
                'method' => 'createRecord',
                'shortHelp' => 'This method creates a new record of the specified type',
                'longHelp' => 'include/api/html/module_new_help.html',
            ),
            'retrieve' => array(
                'reqType' => 'GET',
                'path' => array('<module>','?'),
                'pathVars' => array('module','record'),
                'method' => 'retrieveRecord',
                'shortHelp' => 'Returns a single record',
                'longHelp' => 'include/api/html/module_retrieve_help.html',
            ),
            'update' => array(
                'reqType' => 'PUT',
                'path' => array('<module>','?'),
                'pathVars' => array('module','record'),
                'method' => 'updateRecord',
                'shortHelp' => 'This method updates a record of the specified type',
                'longHelp' => 'include/api/html/module_update_help.html',
            ),
            'delete' => array(
                'reqType' => 'DELETE',
                'path' => array('<module>','?'),
                'pathVars' => array('module','record'),
                'method' => 'deleteRecord',
                'shortHelp' => 'This method deletes a record of the specified type',
                'longHelp' => 'include/api/html/module_delete_help.html',
            ),
        );
    }

    /**
     * Fetches data from the $args array and updates the bean with that data
     * @param $bean SugarBean The bean to be updated
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return id Bean id
     */
    protected function updateBean(SugarBean $bean,ServiceBase $api, $args) {
        $sfh = new SugarFieldHandler();

        // Need to figure out the ownership for ACL's
        $isOwner = false;
        if ( !isset($bean->id) || $bean->new_with_id ) {
            // It's a new record
            $isOwner = true;
            
        } else {
            // It's an existing record
            if ( isset($bean->field_defs['assigned_user_id']) ) {
                if ( $api->user->id == $bean->assigned_user_id ) {
                    $isOwner = true;
                }
            }
        }

        foreach ( $bean->field_defs as $fieldName => $properties ) {
            if ( !isset($args[$fieldName]) ) {
                // They aren't trying to modify this field
                continue;
            }

            //BEGIN SUGARCRM flav=pro ONLY
            if ( !$bean->ACLFieldAccess($fieldName,'save') ) { 
                // No write access to this field, but they tried to edit it
                throw new SugarApiExceptionNotAuthorized('Not allowed to edit field '.$fieldName.' in module: '.$args['module']);
            }
            //END SUGARCRM flav=pro ONLY
            
            $type = !empty($properties['custom_type']) ? $properties['custom_type'] : $properties['type'];
            $field = $sfh->getSugarField($type);
            
            if ( $field != null ) {
                $field->save($bean, $args, $fieldName, $properties);
            }
        }

        $bean->save();

        /*
         * Refresh the bean with the latest data.
         * This is necessary due to BeanFactory caching.
         * Calling retrieve causes a cache refresh to occur.
         */

        $id = $bean->id;

        $bean->retrieve($id);

        /*
         * Even though the bean is refreshed above, return only the id
         * This allows loadBean to be run to handle formatting and ACL
         */
        return $id;
    }

    public function createRecord($api, $args) {
        $this->requireArgs($args,array('module'));

        $bean = BeanFactory::newBean($args['module']);
        
        // TODO: When the create ACL goes in to effect, add it here.
        if (!$bean->ACLAccess('save')) {
            throw new SugarApiExceptionNotAuthorized('No access to create new records for module: '.$args['module']);
        }

        $id = $this->updateBean($bean, $api, $args);

        $args['record'] = $id;

        $bean = $this->loadBean($api, $args, 'view');

        $data = $this->formatBean($api, $args, $bean);

        return $data;
    }

    public function updateRecord($api, $args) {
        $this->requireArgs($args,array('module','record'));

        $bean = $this->loadBean($api, $args, 'save');

        $id = $this->updateBean($bean, $api, $args);

        $bean = $this->loadBean($api, $args, 'view');

        $data = $this->formatBean($api, $args, $bean);

        return $data;
    }

    public function retrieveRecord($api, $args) {
        $this->requireArgs($args,array('module','record'));

        $bean = $this->loadBean($api, $args, 'view');

        $data = $this->formatBean($api, $args, $bean);

        return $data;

    }

    public function deleteRecord($api, $args) {
        $this->requireArgs($args,array('module','record'));

        $bean = $this->loadBean($api, $args, 'delete');
        $bean->mark_deleted($args['record']);

        return array('id'=>$bean->id);
    }

}
