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

require_once('include/SugarFields/SugarFieldHandler.php');


abstract class SugarApi {
    // This class intentionally left blank.
    // It primarialy serves as a way to flag which classes could be called from externally facing API's
    function requireArgs(&$args,$requiredFields = array()) {
        foreach ( $requiredFields as $fieldName ) {
            if ( !isset($args[$fieldName]) ) {
                throw new SugarApiExceptionMissingParameter('Missing parameter: '.$fieldName);
            }
        }
    }

    /**
     * Fetches data from the $args array and formats the bean with those parameters
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the formatted data is returned
     * @param $args array The arguments array passed in from the API, will check this for the 'fields' argument to only return the requested fields
     * @param $bean SugarBean The fully loaded bean to format
     * @return array An array version of the SugarBean with only the requested fields (also filtered by ACL)
     */
    protected function formatBean(ServiceBase $api, $args, SugarBean $bean) {

        $sfh = new SugarFieldHandler();
        //BEGIN SUGARCRM flav=pro ONLY
        $aclField = new ACLField();
        //END SUGARCRM flav=pro ONLY

        // Need to figure out the ownership for ACL's
        $isOwner = false;
        if ( isset($bean->field_defs['assigned_user_id']) && $bean->assigned_user_id == $GLOBALS['current_user']->id ) {
            $isOwner = true;
        }
        
        if ( !empty($args['fields']) ) {
            $fieldList = explode(',',$args['fields']);
            if ( ! in_array('date_modified',$fieldList ) ) {
                $fieldList[] = 'date_modified';
            }
        } else {
            $fieldList = '';
        }
        $data = array();
        foreach ( $bean->field_defs as $fieldName => $properties ) {
            //BEGIN SUGARCRM flav=pro ONLY
            if ( $aclField->hasAccess($fieldName,$bean->module_dir,$GLOBALS['current_user']->id,$isOwner) < 1 ) { 
                // No read access to this field, skip it.
                continue;
            }
            //END SUGARCRM flav=pro ONLY
            if ( !empty($fieldList) && !in_array($fieldName,$fieldList) ) {
                // They want to skip this field
                continue;
            }

            $type = !empty($properties['custom_type']) ? $properties['custom_type'] : $properties['type'];
            if ( $type == 'link' ) {
                // There is a different API to fetch linked records, don't try to encode all of the related data.
                continue;
            }
            $field = $sfh->getSugarField($type);
            
            if ( $field != null ) {
                if ( method_exists($field,'apiFormatField') ) {
                    $field->apiFormatField($data, $bean, $args, $fieldName, $properties);
                } else {
                    if ( isset($bean->$fieldName) ) {
                        $data[$fieldName] = $bean->$fieldName;
                    } else {
                        $data[$fieldName] = '';
                    }
                }
            }
        }

        if (isset($bean->field_defs['email']) &&
            (empty($fieldList) || in_array('email',$fieldList))) {
                $emailsRaw = $bean->emailAddress->getAddressesByGUID($bean->id, $bean->module_name);
                $emails = array();
                $emailProps = array(
                    'email_address',
                    'opt_out',
                    'invalid_email',
                    'primary_address'
                );
                foreach($emailsRaw as $rawEmail) {
                    $formattedEmail = array();
                    foreach ($emailProps as $property) {
                        if (isset($rawEmail[$property])) {
                            $formattedEmail[$property] = $rawEmail[$property];
                        }
                    }
                    array_push($emails, $formattedEmail);
                }
                $data['email'] = $emails;
        }

        return $data;
    }

    /**
     * Fetches data from the $args array and updates the bean with that data
     * @param $api ServiceBase The API class of the request, used in cases where the API changes how the bean is retrieved
     * @param $args array The arguments array passed in from the API
     * @param $aclToCheck string What kind of ACL to verify when loading a bean. Supports: view,edit,create,import,export
     * @return SugarBean The loaded bean
     */
    protected function loadBean(ServiceBase $api, $args, $aclToCheck = 'read') {

        $bean = BeanFactory::getBean($args['module'],$args['record']);
        
        if ( $bean == FALSE ) {
            // Couldn't load the bean
            throw new SugarApiExceptionNotFound('Could not find record: '.$args['record'].' in module: '.$args['module']);
        }

        if (!$bean->ACLAccess($aclToCheck)) {
            throw new SugarApiExceptionNotAuthorized('No access to edit records for module: '.$args['module']);
        }
        
        return $bean;
    }

}