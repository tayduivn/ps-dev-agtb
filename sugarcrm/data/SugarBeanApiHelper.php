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

/**
 * This class is here to provide functions to easily call in to the individual module api helpers
 */
class SugarBeanApiHelper
{
    /**
     * This is used when formatting records to do things like provide URI's for objects.
     */
    protected $api;

    function __construct(ServiceBase $api)
    {
        $this->api = $api;
    }

    /**
     * Formats the bean so it is ready to be handed back to the API's client. Certian fields will get extra processing
     * to make them easier to work with from the client end.
     *
     * @param $bean SugarBean The bean you want formatted
     * @param $fieldList array Which fields do you want formatted and returned (leave blank for all fields)
     * @param $options array Currently no options are supported
     * @return array The bean in array format, ready for passing out the API to clients.
     */
    public function formatForApi(SugarBean $bean, array $fieldList = array(), array $options = array() )
    {
        $sfh = new SugarFieldHandler();

        $data = array();
        foreach ( $bean->field_defs as $fieldName => $properties ) {
            //BEGIN SUGARCRM flav=pro ONLY
            if ( !$bean->ACLFieldAccess($fieldName,'read') ) { 
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
            
            if ( $field != null && isset($bean->$fieldName) ) {
                if ( method_exists($field,'apiFormatField') ) {
                    $field->apiFormatField($data, $bean, $options, $fieldName, $properties);
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
     * This function 
     *
     * @param $bean SugarBean The bean you want populated from the $submittedData array, this function will modify this
     *                        record
     * @param $submittedData array The data that was passed in from the client to update/create this record
     * @param $options array Options to pass in to the populateFromApi function, look at SugarBeanApiHelper:populateFromApi
     *                       for more information
     * @return array An array of validation errors, or true if the submitted data appeared to be correct
     */
    public function populateFromApi(SugarBean $bean, array $submittedData, array $options = array() )
    {
        $sfh = new SugarFieldHandler();

        foreach ( $bean->field_defs as $fieldName => $properties ) {
            if ( !isset($submittedData[$fieldName]) ) {
                // They aren't trying to modify this field
                continue;
            }

            //BEGIN SUGARCRM flav=pro ONLY
            if ( !$bean->ACLFieldAccess($fieldName,'save') ) { 
                // No write access to this field, but they tried to edit it
                throw new SugarApiExceptionNotAuthorized('Not allowed to edit field '.$fieldName.' in module: '.$submittedData['module']);
            }
            //END SUGARCRM flav=pro ONLY
            
            $type = !empty($properties['custom_type']) ? $properties['custom_type'] : $properties['type'];
            $field = $sfh->getSugarField($type);
            
            if ( $field != null ) {
                $field->save($bean, $submittedData, $fieldName, $properties);
            }
        }

        return true;
    } 
}
