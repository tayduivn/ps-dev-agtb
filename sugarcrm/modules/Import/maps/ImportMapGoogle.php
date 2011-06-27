<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once('modules/Import/maps/ImportMapOther.php');

class ImportMapGoogle extends ImportMapOther
{
	/**
     * String identifier for this import
     */
    public $name = 'google';
    
    /**
     * Gets the default mapping for a module
     *
     * @param  string $module
     * @return array field mappings
     */
	public function getMapping()
    {
         $return_array = array(
             'first_name' => array('sugar_key' => 'first_name', 'sugar_label' => 'LBL_FIRST_NAME', 'default_label' => ''),
             'last_name' => array('sugar_key' => 'last_name', 'sugar_label' => 'LBL_LAST_NAME', 'default_label' => ''),
             'birthday' => array('sugar_key' => 'birthdate', 'sugar_label' => 'LBL_BIRTHDATE', 'default_label' => ''),
             'title' => array('sugar_key' => 'title', 'sugar_label' => 'LBL_TITLE', 'default_label' => ''),
             'notes' => array('sugar_key' => 'description', 'sugar_label' => 'LBL_DESCRIPTION', 'default_label' => 'Description'),

             'alt_address_street' => array('sugar_key' => 'alt_address_street', 'sugar_label' => 'LBL_ALT_ADDRESS_STREET', 'default_label' => ''),
             'alt_address_postcode' => array('sugar_key' => 'alt_address_postalcode', 'sugar_label' => 'LBL_ALT_ADDRESS_POSTALCODE', 'default_label' => ''),
             'alt_address_city' => array('sugar_key' => 'alt_address_city', 'sugar_label' => 'LBL_ALT_ADDRESS_CITY', 'default_label' => ''),
             'alt_address_state' => array('sugar_key' => 'alt_address_state', 'sugar_label' => 'LBL_ALT_ADDRESS_STATE', 'default_label' => ''),
             'alt_address_country' => array('sugar_key' => 'alt_address_country', 'sugar_label' => 'LBL_ALT_ADDRESS_POSTALCODE', 'default_label' => ''),

             'primary_address_street' => array('sugar_key' => 'primary_address_street', 'sugar_label' => 'LBL_PRIMARY_ADDRESS_STREET', 'default_label' => ''),
             'primary_address_postcode' => array('sugar_key' => 'primary_address_postalcode', 'sugar_label' => 'LBL_PRIMARY_ADDRESS_POSTALCODE', 'default_label' => ''),
             'primary_address_city' => array('sugar_key' => 'primary_address_city', 'sugar_label' => 'LBL_PRIMARY_ADDRESS_CITY', 'default_label' => ''),
             'primary_address_state' => array('sugar_key' => 'primary_address_state', 'sugar_label' => 'LBL_PRIMARY_ADDRESS_STATE', 'default_label' => ''),
             'primary_address_country' => array('sugar_key' => 'primary_address_country', 'sugar_label' => 'LBL_PRIMARY_ADDRESS_COUNTRY', 'default_label' => ''),

             'phone_main' => array('sugar_key' => '', 'sugar_label' => '', 'default_label' => 'Phone'),
             'phone_mobile' => array('sugar_key' => 'phone_mobile', 'sugar_label' => 'LBL_MOBILE_PHONE', 'default_label' => ''),
             'phone_home' => array('sugar_key' => 'phone_home', 'sugar_label' => 'LBL_HOME_PHONE', 'default_label' => ''),
             'phone_work' => array('sugar_key' => 'phone_work', 'sugar_label' => 'LBL_OFFICE_PHONE', 'default_label' => ''),
             'phone_fax' => array('sugar_key' => 'phone_fax', 'sugar_label' => 'LBL_FAX_PHONE', 'default_label' => ''),
             );

         return $return_array;
    }
}


?>
