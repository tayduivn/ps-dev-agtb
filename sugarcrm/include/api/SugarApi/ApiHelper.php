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

/**
 * This class is here to provide functions to easily call in to the individual module api helpers
 */
class ApiHelper
{
    static $moduleHelpers = array();

    /**
     * This method looks up the helper class for the bean and will provide the default helper
     * if there is not one defined for that particular bean
     *
     * @param $bean SugarBean Grab the helper module for this bean
     * @returns SugarBeanApiHelper A API helper class for beans
     */
    protected static function findHelperForBean(SugarBean $bean) {
        $module = $bean->module_dir;
        if ( isset(self::$moduleHelpers[$module]) ) {
            return self::$moduleHelpers[$module];
        }
        
        require_once('data/SugarBeanApiHelper.php');
        $moduleHelperClass = 'SugarBeanApiHelper';
        
        if ( file_exists('custom/modules/'.$module.'/'.$module.'ApiHelperCstm.php') ) {
            require_once('custom/modules/'.$module.'/'.$module.'ApiHelperCstm.php');
            
            $moduleHelperClass = $module.'ApiHelperCstm';
        } else if ( file_exists('modules/'.$module.'/'.$module.'ApiHelper.php') ) {
            require_once('modules/'.$module.'/'.$module.'ApiHelper.php');

            $moduleHelperClass = $module.'ApiHelper';
        }

        self::$moduleHelpers[$module] = new $moduleHelperClass();
        
        return self::$moduleHelpers[$module];
    }

    /**
     * Passes through the call to the appropriate getFormattedBean call in the SugarBeanApiHelper class
     * for this module
     *
     * @param $bean SugarBean The bean you want formatted
     * @param $fieldList array Which fields do you want formatted and returned (leave blank for all fields)
     * @param $options array Options to pass in to the getFormattedBean function, look at SugarBeanApiHelper:getFormattedBean
     *                       for more information
     * @return array The bean in array format, ready for passing out the API to clients.
     */
    public static function getFormattedBean(SugarBean $bean, array $fieldList = array(), array $options = array() ) {
        $helper = self::findHelperForBean($bean);
        
        return $helper->getFormattedBean($bean, $fieldList, $options);
    } 

    /**
     * Passes through the call to the appropriate populateFromApi call in the SugarBeanApiHelper class
     * for this module
     *
     * @param $bean SugarBean The bean you want populated from the $submittedData array, this function will modify this
     *                        record
     * @param $submittedData array The data that was passed in from the client to update/create this record
     * @param $options array Options to pass in to the populateFromApi function, look at SugarBeanApiHelper:populateFromApi
     *                       for more information
     * @return array An array of validation errors, or true if the submitted data appeared to be correct
     */
    public static function populateFromApi(SugarBean &$bean, array $submittedData, array $options = array() ) {
        $helper = self::findHelperForBean($bean);
        
        return $helper->populateFromApi($bean, $submittedData, $options);
    } 
}
