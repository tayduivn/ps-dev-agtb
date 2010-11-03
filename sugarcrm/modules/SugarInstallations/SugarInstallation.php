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
class SugarInstallation extends SugarBean {
	var $field_name_map = array();

	// Stored fields
	var $id;
	var $date_created;
	var $date_modified;
	var $modified_user_id;
	var $assigned_user_id;
	var $created_by;
	var $created_by_name;
	var $modified_by_name;

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
	var $update_count;
	var $first_active;
	var $last_active;
	var $server_software;
	var $auth_level;

	var $os;
	var $os_version;
	var $distro_name;
	var $timezone;
	var $timezone_u;
	
	var $last_touch;

	var $installation_age;
	var $first_update;
	var $last_update;
	var $status;

	var $account_id;
	var $account_name;
	var $account_name1;

	var $retrieved_additional_details = FALSE;

	var $table_name = "sugar_installations";
	var $module_dir = "SugarInstallations";
	var $object_name = "SugarInstallation";

	// This is used to retrieve related fields from form posts.
	var $additional_column_fields = Array();

	var $relationship_fields = array();

	var $new_schema = true;

	function SugarInstallation() {
		parent::SugarBean();
		global $sugar_config;

		$this->disable_row_level_security = TRUE;

		foreach ($this->field_defs as $field) {
			$this->field_name_map[$field['name']] = $field;
		}
	}


	function get_summary_text() {
		return "{$this->soap_client_ip} {$this->date_created} ({$this->account_name})";
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
			$query .= " ORDER BY {$this->table_name}.date_created";
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
			$query .= " ORDER BY {$this->table_name}.date_created";
		}

		return $query;
	}

	function save_relationship_changes($is_update) {
		parent::save_relationship_changes($is_update);
	}

	function fill_in_additional_list_fields() {
		$this->fill_in_additional_detail_fields();
	}

	function fill_in_additional_detail_fields() {
		if (!$this->retrieved_additional_details && $this->id) {
			$this->first_update = $this->date_created;

			$result = $this->db->query("SELECT * FROM sugar_updates WHERE installation_id = {$this->id} ORDER BY time_stamp DESC LIMIT 1");
			$row = $this->db->fetchByAssoc($result);

			$unix_timestamp_diff = time() - strtotime($row['time_stamp']);
			$this->installation_age = number_format(round($unix_timestamp_diff / (60 * 24)));
			
			$this->last_update = $row['time_stamp'];
			$this->date_created = $row['time_stamp'];
			$this->ip_address = $row['ip_address'];
			$this->sugar_db_version = $row['sugar_db_version'];
			$this->db_type = $row['db_type'];
			$this->db_version = $row['db_version'];
			//$this->users = $row['users'];
			$this->admin_users = $row['admin_users'];
			$this->registered_users = $row['registered_users'];
			$this->latest_tracker_id = number_format($row['latest_tracker_id']);
			$this->license_users = $row['license_users'];
			$this->users_active_30_days = $row['users_active_30_days'];
			$this->license_expire_date = $row['license_expire_date'];
			$this->license_key = $row['license_key'];
			$this->php_version = $row['php_version'];
			$this->license_num_lic_oc = $row['license_num_lic_oc'];
			$this->server_software = $row['server_software'];

			if (!empty($this->account_id)) {
				require_once("modules/Accounts/Account.php");

				$acc = new Account();
				$acc->retrieve($this->account_id);

				$this->account_name = $acc->name;
			}

			$this->retrieved_additional_details = TRUE;
		}
	}

	function get_list_view_data() {
		global $current_language, $image_path;

		$app_list_strings = return_app_list_strings_language($current_language);
		$temp_array = $this->get_list_view_array();

		//$temp_array['DATE_CREATED'] = "<a href=\"index.php?module=SugarInstallations&action=DetailView&record={$this->id}&return_module=SugarInstallations&return_action=index\" class=\"listViewTdLinkS1\">{$this->date_created}</a>";
		$this->fill_in_additional_detail_fields();
		$temp_array['LATEST_TRACKER_ID'] = $this->latest_tracker_id;

		return $temp_array;
	}

	function bean_implements($interface) {
                switch($interface){
                        case 'ACL':return true;
                }
		return false;
	}
}
?>
