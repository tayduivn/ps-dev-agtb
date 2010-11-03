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
require_once('modules/Accounts/Account.php');
require_once('include/utils.php');
require_once('modules/Tasks/Task.php');
require_once('modules/Notes/Note.php');
require_once('modules/Users/User.php');

// Case is used to store customer information.
class Subscription extends SugarBean {
	var $field_name_map = array();

	// Stored fields
	var $id;
	var $name;

	var $subscription_id;
	var $expiration_date;
	var $audited;
	var $enforce_user_limit;
	var $debug;
	var $status;
	var $perpetual;
	var $portal_users;
	var $enforce_portal_users;

// BEGIN jostrow MoofCart customization
// See ITRequest #9329

	var $ignore_expiration_date;

// END jostrow MoofCart customization

	var $date_entered;
	var $date_modified;
	var $modified_user_id;
	var $assigned_user_id;
	var $created_by;
	var $team_id;
	var $team_name;
	var $deleted;

	var $account_id;
	var $account_name;
	var $account_name1;

	var $task_id;
        var $note_id;
        var $meeting_id;
	var $user_id;

	var $table_name = "subscriptions";
	var $module_dir = "Subscriptions";
	var $object_name = "Subscription";

	// This is used to retrieve related fields from form posts. //DEE
        var $additional_column_fields = array('account_id', 'task_id', 'note_id', 'meeting_id', 'user_id');

        var $relationship_fields = array('task_id'=>'tasks', 'note_id'=>'notes', 'meeting_id'=>'meetings',
                            'user_id' => 'users', 'account_id' => 'accounts');

	var $new_schema = true;

	function Subscription() {
		parent::SugarBean();
		global $sugar_config;

		$this->disable_row_level_security = false;
		
	
		foreach ($this->field_defs as $field) {
			$this->field_name_map[$field['name']] = $field;
		}
	}


	function get_summary_text() {
		return "Sub: ".$this->subscription_id;
	}


	function create_list_query($order_by, $where, $show_deleted = 0) {
		$query = "SELECT ".
			 "   {$this->table_name}.*, \n".
			 "   accounts.name as account_name1 \n".
			 "FROM {$this->table_name} \n";
		$this->add_team_security_where_clause($query);
		$query .="   LEFT JOIN accounts ON {$this->table_name}.account_id = accounts.id \n";

		$where_auto = "1=1";
		if ($show_deleted == 0) {
			$where_auto = " {$this->table_name}.deleted = 0 AND accounts.deleted = 0 ";
		}
		else {
			$where_auto = " {$this->table_name}.deleted = 1 AND accounts.deleted = 1 ";
		}

		if (!empty($where)) {
			$query .= "WHERE {$where} AND {$where_auto} \n";
		}
		else {
			$query .= "WHERE {$where_auto} \n";
		}


		if(!empty($order_by)) {
			$query .= "ORDER BY {$order_by}";
		}
		else {
			$query .= "ORDER BY {$this->table_name}.subscription_id";
		}
		
		return $query;
	}

	function create_export_query($order_by, $where, $show_deleted = 0) {
		$query = "SELECT ".
			 "   {$this->table_name}.*, \n".
			 "   accounts.name as account_name1 \n".
			 "FROM {$this->table_name} \n";
		$this->add_team_security_where_clause($query);
		$query .="   LEFT JOIN accounts ON {$this->table_name}.account_id = accounts.id \n";

		$where_auto = "1=1";
		if ($show_deleted == 0) {
			$where_auto = " {$this->table_name}.deleted = 0 AND accounts.deleted = 0 ";
		}
		else {
			$where_auto = " {$this->table_name}.deleted = 1 AND accounts.deleted = 1 ";
		}

		if (!empty($where)) {
			$query .= "WHERE {$where} AND {$where_auto} \n";
		}
		else {
			$query .= "WHERE {$where_auto} \n";
		}


		if(!empty($order_by)) {
			$query .= "ORDER BY {$order_by}";
		}
		else {
			$query .= "ORDER BY {$this->table_name}.subscription_id";
		}

		return $query;
	}

	function fill_in_additional_detail_fields() {
		$query = "SELECT accounts.id, accounts.name FROM accounts, {$this->table_name} ";
		$query .= "WHERE accounts.id = {$this->table_name}.account_id AND {$this->table_name}.id = '{$this->id}' AND {$this->table_name}.deleted=0 AND accounts.deleted = 0";
		$result = $this->db->query($query,true," Error filling in additional detail fields: ");

		$row = $this->db->fetchByAssoc($result);

		if($row != null) {
			$this->account_name = stripslashes($row['name']);
			$this->account_id 	= $row['id'];
		}
		else {
			$this->account_name = '';
			$this->account_id 	= '';
		}

		$this->team_name = get_assigned_team_name($this->team_id);
		$this->assigned_user_name = get_assigned_user_name($this->assigned_user_id);
        
		//DEE CUSTOMIZATION ITREQUEST 4666		
		if(isset($this->subscription_id) && !empty($this->subscription_id))
                        $this->name = $this->subscription_id;
		//END DEE CUSTOMIZATION
	}

	function fill_in_additional_list_fields() {
		$this->status = $this->set_status_color($this->status);

        if (!empty($this->account_id)) {
            require_once("modules/Accounts/Account.php");

            $acc = new Account();
            $acc->retrieve($this->account_id);

            $this->account_name = $acc->name;
        }
	}

	function set_status_color($status){
		if(!empty($status)){
			$color = 'black';
			switch($status){
				case "enabled":
					$color = "green";
					break;
				case "disabled":
					$color = "red";
					break;
				default:
					break;
			}
			$status = "<font color='$color'>{$status}</font>";
		}
		return $status;
	}

	function save_relationship_changes($is_update) {
		parent::save_relationship_changes($is_update);
	}

	function get_list_view_data() {
		global $current_language, $image_path;
		$app_list_strings = return_app_list_strings_language($current_language);
		$temp_array = $this->get_list_view_array();

		return $temp_array;
	}

	function bean_implements($interface) {
		switch($interface){
			case 'ACL':return true;
		}
		return false;
	}

	function save($check_notify = FALSE){
		return parent::save($check_notify);
	}

}
?>
