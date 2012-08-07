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
require_once('include/SugarFields/SugarFieldHandler.php');

class RegisterLeadApi extends SugarApi {
    public function registerApiRest() {
        return array(
            'create' => array(
                'reqType' => 'POST',
                'path' => array('Leads','register'),
                'pathVars' => array('module'),
                'method' => 'createLeadRecord',
                'shortHelp' => 'This method registers leads',
                'longHelp' => 'include/api/html/module_new_help.html',
                'noLoginRequired' => true,
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

        foreach ( $bean->field_defs as $fieldName => $properties ) {
            if ( !isset($args[$fieldName]) ) {
                // They aren't trying to modify this field
                continue;
            }

            $type = !empty($properties['custom_type']) ? $properties['custom_type'] : $properties['type'];
            $field = $sfh->getSugarField($type);

            if ( $field != null ) {
                $field->save($bean, $args, $fieldName, $properties);
            }
        }

        // Bug 54515: Set modified by and created by users to assigned to user. If not set default to admin.
        $bean->update_modified_by = false;
        $bean->set_created_by = false;
        $admin = new Administration();
       	$admin->retrieveSettings();
        if (isset($admin->settings['supportPortal_RegCreatedBy']) && !empty($admin->settings['supportPortal_RegCreatedBy'])) {
            $bean->created_by = $admin->settings['supportPortal_RegCreatedBy'];
            $bean->modified_user_id = $admin->settings['supportPortal_RegCreatedBy'];
        } else {
            $bean->created_by = '1';
            $bean->modified_user_id = '1';
        }

        // Bug 54516 users not getting notified on new record creation
        $bean->save(true);

        /*
         * Refresh the bean with the latest data.
         * This is necessary due to BeanFactory caching.
         * Calling retrieve causes a cache refresh to occur.
         */

        $id = $bean->id;

        $bean->retrieve($id);

        return $id;
    }

    /**
     * Creates lead records
     * @param $apiServiceBase The API class of the request, used in cases where the API changes how the fields are pulled from the args array.
     * @param $args array The arguments array passed in from the API
     * @return array properties on lead bean formatted for display
     */
    public function createLeadRecord($api, $args) {

        // Bug 54647 Lead registration can create empty leads
        if (!isset($args['last_name'])) {
            throw new SugarApiExceptionMissingParameter();
        }

        $bean = BeanFactory::newBean('Leads');
        // we force team and teamset because there is no current user to get them from
        $fields = array(
            'team_set_id' => '1',
            'team_id' => '1',
            'lead_source' => 'Support Portal User Registration',
        );

        $admin = new Administration();
       	$admin->retrieveSettings();

        if (isset($admin->settings['portal_defaultUser']) && !empty($admin->settings['portal_defaultUser'])) {
            $fields['assigned_user_id'] = json_decode(html_entity_decode($admin->settings['portal_defaultUser']));
        }

        $fieldList = array('first_name', 'last_name', 'phone_work', 'email', 'primary_address_country', 'primary_address_state', 'account_name', 'title');
        foreach ($fieldList as $fieldName) {
            if (isset($args[$fieldName])) {
                $fields[$fieldName] = $args[$fieldName];
            }
        }

        $id = $this->updateBean($bean, $api, $fields);

        $data = $this->formatBean($api, $args, $bean);;
        return $data;
    }


}
