<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
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
/*********************************************************************************
 * $Id: field_arrays.php 53116 2009-12-10 01:24:37Z mitani $
 * Description:  Contains field arrays that are used for caching
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
$fields_array['User'] = array (
	'column_fields' => array(
		'id',
		'full_name',
		'user_name'
		,'user_hash'
		,'first_name'
		,'last_name'
		,'description'
		,'date_entered'
		,'date_modified'
		,'modified_user_id'
		, 'created_by'
		,'title'
		,'department'
		,'is_admin'
		,'phone_home'
		,'phone_mobile'
		,'phone_work'
		,'phone_other'
		,'phone_fax'
		,'address_street'
		,'address_city'
		,'address_state'
		,'address_postalcode'
		,'address_country'
		,'reports_to_id'
		,'portal_only'
		,'status'
		,'receive_notifications'
		,'employee_status'
		,'messenger_id'
		,'messenger_type'
		//BEGIN SUGARCRM flav!=sales ONLY
		,'is_group'

        //END SUGARCRM flav!=sales ONLY
		//BEGIN SUGARCRM flav=pro ONLY

		,'default_team'
		//END SUGARCRM flav=pro ONLY
	),
    'list_fields' => array(
    	'full_name',
		'id', 
		'first_name', 
		'last_name', 
		'user_name', 
		'status', 
		'department', 
		'is_admin', 
		'email1', 
		'phone_work', 
		'title', 
		'reports_to_name', 
		'reports_to_id', 
		//BEGIN SUGARCRM flav!=sales ONLY
		'is_group'

		//END SUGARCRM flav!=sales ONLY
	),
	'export_fields' => array(
		'id',
		'user_name'
		,'first_name'
		,'last_name'
		,'description'
		,'date_entered'
		,'date_modified'
		,'modified_user_id'
		,'created_by'
		,'title'
		,'department'
		,'is_admin'
		,'phone_home'
		,'phone_mobile'
		,'phone_work'
		,'phone_other'
		,'phone_fax'
		,'address_street'
		,'address_city'
		,'address_state'
		,'address_postalcode'
		,'address_country'
		,'reports_to_id'
		//BEGIN SUGARCRM flav!=sales ONLY
		,'portal_only'
		//END SUGARCRM flav!=sales ONLY
		,'status'
		,'receive_notifications'
		,'employee_status'
		,'messenger_id'
		,'messenger_type'
		//BEGIN SUGARCRM flav!=sales ONLY
		,'is_group'

		//END SUGARCRM flav!=sales ONLY
		//BEGIN SUGARCRM flav=pro ONLY

		,'default_team'
		//END SUGARCRM flav=pro ONLY
	),
    'required_fields' =>   array("last_name"=>1,'user_name'=>2,'status'=>3),
);

$fields_array['UserSignature'] = array(
	'column_fields' => array(
		'id',
		'date_entered',
		'date_modified',
		'deleted',
		'user_id',
		'name',
		'signature',
	),
	'list_fields' => array(
		'id',
		'date_entered',
		'date_modified',
		'deleted',
		'user_id',
		'name',
		'signature',
	),
);
?>