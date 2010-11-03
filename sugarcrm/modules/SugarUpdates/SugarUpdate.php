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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id$
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('log4php/LoggerManager.php');
require_once('include/database/PearDatabase.php');
require_once('data/SugarBean.php');
require_once('include/utils.php');

// Case is used to store customer information.
class SugarUpdate extends SugarBean {
	var $field_name_map = array();

	// Stored fields
	var $id;
	var $date_entered;
	var $date_modified;
	var $modified_user_id;
	var $assigned_user_id;
	var $created_by;
	var $created_by_name;
	var $modified_by_name;

	var $beat_id;
	var $time_stamp;
	var $application_key;
	var $ip_address;
	var $sugar_version;
	var $sugar_db_version;
	var $sugar_flavor;
	var $db_type;
	var $db_version;
	var $users;
	var $admin_users;
	var $registered_users;
	var $users_active_30_days;
	var $latest_tracker_id;
	var $license_users;
	var $license_expire_date;
	var $license_key;
	var $soap_client_ip;
	var $php_version;
	var $license_num_lic_oc;
	var $server_software;

    var $os;
    var $os_version;
    var $distro_name;
    var $timezone;
    var $timezone_u;

	var $installation_id;

	var $table_name = "sugar_updates";
	var $module_dir = "SugarUpdates";
	var $object_name = "SugarUpdate";

	// This is used to retrieve related fields from form posts.
	var $additional_column_fields = array();

	var $relationship_fields = array();

	var $new_schema = true;

	function SugarUpdate() {
		parent::SugarBean();
		global $sugar_config;

		$this->disable_row_level_security = TRUE;

		foreach ($this->field_defs as $field) {
			$this->field_name_map[$field['name']] = $field;
		}
	}


	function get_summary_text() {
		return "{$this->time_stamp} ({$this->application_key})";
	}

	function create_list_query($order_by, $where, $show_deleted = 0) {
		$query = "SELECT {$this->table_name}.* FROM {$this->table_name} ";

		$where_auto = "1=1";
		if ($show_deleted == 0) {
			$where_auto = " {$this->table_name}.deleted = 0 ";
		}
		else {
			$where_auto = " {$this->table_name}.deleted = 1 ";
		}

		if (!empty($where)) {
			$query .= " WHERE {$where} AND {$where_auto}";
		}
		else {
			$query .= " WHERE {$where_auto}";
		}


		if(!empty($order_by)) {
			$query .= " ORDER BY {$order_by}";
		}
		else {
			$query .= " ORDER BY {$this->table_name}.time_stamp";
		}

		return $query;
	}

	function create_export_query($order_by, $where, $show_deleted = 0) {
		$query = "SELECT {$this->table_name}.* FROM {$this->table_name} ";

		$where_auto = "1=1";
		if ($show_deleted == 0) {
			$where_auto = " {$this->table_name}.deleted = 0 ";
		}
		else {
			$where_auto = " {$this->table_name}.deleted = 1 ";
		}

		if (!empty($where)) {
			$query .= " WHERE {$where} AND {$where_auto}";
		}
		else {
			$query .= " WHERE {$where_auto}";
		}


		if(!empty($order_by)) {
			$query .= " ORDER BY {$order_by}";
		}
		else {
			$query .= " ORDER BY {$this->table_name}.time_stamp";
		}

		return $query;
	}

	function save_relationship_changes($is_update) {
		parent::save_relationship_changes($is_update);
	}

	function fill_in_additional_list_fields() {

	}

	function fill_in_additional_detail_fields() {
		$this->assigned_user_name = get_assigned_user_name($this->assigned_user_id);
		$this->created_by_name = get_assigned_user_name($this->created_by);
		$this->modified_by_name = get_assigned_user_name($this->modified_user_id);
	}

	function get_list_view_data() {
		global $current_language, $image_path;
		$app_list_strings = return_app_list_strings_language($current_language);
		$temp_array = $this->get_list_view_array();

		$temp_array['TIME_STAMP'] = "<a href=\"index.php?module=SugarUpdates&action=DetailView&record={$this->id}&return_module=SugarUpdates&return_action=index\" class=\"listViewTdLinkS1\">{$this->time_stamp}</a>";

		return $temp_array;
	}

	function bean_implements($interface) {
		switch($interface){
			case 'ACL':
				return false;
			default:
				return false;
		}
	}
}
?>
