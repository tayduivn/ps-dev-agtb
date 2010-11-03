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

 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/




require_once('data/SugarBean.php');
require_once('modules/Tasks/Task.php');
require_once('modules/Notes/Note.php');
require_once('modules/Users/User.php');
require_once('modules/Bugs/Bug.php');
require_once('modules/Users/User.php');
require_once('include/utils.php');

// ITRequest is used to store customer information.
class ITRequest extends SugarBean {
        var $field_name_map = array();
	// Stored fields
	var $id;
	var $date_resolved;
	var $date_entered;
	var $date_modified;
	var $modified_user_id;
	var $assigned_user_id;

	var $team_id;

	var $itrequest_number;
	var $resolution;
	var $description;
	var $name;
	var $category;
	var $subcategory;
	var $status;
	var $priority;
	var $target_date;
	var $development_time;

	var $created_by;
	var $created_by_name;
	var $modified_by_name;

	// These are related
	var $bug_id;
	var $account_id;
	var $case_id;
	var $task_id;
	var $note_id;
	var $meeting_id;
	var $user_id;
	var $itrequest_id;
	var $assigned_user_name;
	var $created_user_name;

	var $team_name;
	var $system_id;

	var $table_name = "itrequests";
	var $module_dir = 'ITRequests';
	var $object_name = "ITRequest";

	// This is used to retrieve related fields from form posts.
	var $additional_column_fields = Array('bug_id', 'assigned_user_name', 'assigned_user_id', 'created_by', 'created_user_name', 'task_id', 'note_id', 'meeting_id', 'user_id', 'case_id', 'account_id', 'itrequest_id');

	var $relationship_fields = Array('bug_id' => 'bugs', 'user_id' => 'users',
									'task_id'=>'tasks', 'note_id'=>'notes',
									'meeting_id'=>'meetings', 									
									'case_id' => 'cases',
									'account_id' => 'accounts',
									'itrequest_id' => 'related_itrequests',
									);

	function ITRequest() {
		parent::SugarBean();
		global $sugar_config;
		
		 $this->setupCustomFields('ITRequests');
		foreach ($this->field_defs as $field)
                {
                        $this->field_name_map[$field['name']] = $field;
                }

        // ITR: 14724 : jwhitcraft - Not needed as the new MVC pulls in the users default teams
		//$this->team_id = 1; // make the item globally accessible


	}

	var $new_schema = true;

	

	

	function get_summary_text()
	{
		return "$this->name";
	}
	
	function listviewACLHelper(){
		$array_assign = parent::listviewACLHelper();
		$is_owner = false;

		return $array_assign;
	}

	function create_list_query($order_by, $where, $show_deleted = 0)
	{

				$custom_join = $this->custom_fields->getJOIN();
                $query = "SELECT ";
            

		$query .= "
                                itrequests.*,
                                users.user_name as assigned_user_name,
                                created_user.user_name as created_user_name";

                                $query .= ", teams.name AS team_name";

                            	if($custom_join){
   									$query .= $custom_join['select'];
 								}
                                $query .= " FROM itrequests ";


		// We need to confirm that the user is a member of the team of the item.
		$this->add_team_security_where_clause($query);

		$query .= "				LEFT JOIN users
                                ON itrequests.assigned_user_id=users.id ";
		$query .= "				LEFT JOIN users created_user
                                ON itrequests.created_by=created_user.id ";

		$query .= " LEFT JOIN teams ON itrequests.team_id=teams.id ";

		if($custom_join){
  			$query .= $custom_join['join'];
		}
		$where_auto = '1=1';
		if($show_deleted == 0){
            		$where_auto = " $this->table_name.deleted=0 ";
		}else if($show_deleted == 1){
			$where_auto = " $this->table_name.deleted=1 ";	
		}
                



		if($where != "")
			$query .= "where $where AND ".$where_auto;
		else
			$query .= "where ".$where_auto;

		if($order_by != "")
			$query .= " ORDER BY $order_by";
		else
			$query .= " ORDER BY itrequests.name";
		
		return $query;
	}

        function create_export_query($order_by, $where)
        {
				$custom_join = $this->custom_fields->getJOIN();
                $query = "SELECT
                                itrequests.*,
                                users.user_name as assigned_user_name,
                                created_user.user_name as created_user_name";
             					if($custom_join){
   									$query .= $custom_join['select'];
 								}
                                $query .= " FROM itrequests ";

		// We need to confirm that the user is a member of the team of the item.
		$this->add_team_security_where_clause($query);

		$query .= "				LEFT JOIN users
                                ON itrequests.assigned_user_id=users.id";
		$query .= "				LEFT JOIN created_user
                                ON itrequests.created_by=users.id ";
                                
                 			if($custom_join){
  								$query .= $custom_join['join'];
							}	
                $where_auto = " AND itrequests.deleted=0
                ";

                if($where != "")
                        $query .= " where $where AND ".$where_auto;
                else
                        $query .= " where ".$where_auto;

                if($order_by != "")
                        $query .= " ORDER BY $order_by";
                else
                        $query .= " ORDER BY itrequests.name";
                return $query;
        }

	function save_relationship_changes($is_update)
	{
		parent::save_relationship_changes($is_update);
	}

	function fill_in_additional_list_fields()
	{
		// Fill in the assigned_user_name
		//$this->assigned_user_name = get_assigned_user_name($this->assigned_user_id);
		
		$this->assigned_name = get_assigned_team_name($this->team_id);

	}

	function fill_in_additional_detail_fields()
	{
		// Fill in the assigned_user_name
		$this->assigned_user_name = get_assigned_user_name($this->assigned_user_id);
		$this->created_user_name = get_assigned_user_name($this->created_by);

		$this->assigned_name = get_assigned_team_name($this->team_id);
        $this->team_name=$this->assigned_name;        


		$this->created_by_name = get_assigned_user_name($this->created_by);
		$this->modified_by_name = get_assigned_user_name($this->modified_user_id);
	}


	function get_list_view_data(){
		global $current_language, $image_path;
		$app_list_strings = return_app_list_strings_language($current_language);
		
		$temp_array = $this->get_list_view_array();
		$temp_array['NAME'] = (($this->name == "") ? "<em>blank</em>" : $this->name);
		$temp_array['PRIORITY'] = empty($this->priority)? "" : $app_list_strings['itrequest_priority_dom'][$this->priority];
		$temp_array['STATUS'] = empty($this->status)? "" : $app_list_strings['itrequest_status_dom'][$this->status];
		$temp_array['ENCODED_NAME'] = $this->name;
		$temp_array['ITREQUEST_NUMBER'] = $this->itrequest_number;
		$temp_array['SET_COMPLETE'] =  "<a href='index.php?return_module=Home&return_action=index&action=EditView&module=ITRequests&record=$this->id&status=Closed'>".get_image($image_path."close_inline","alt='Close' border='0'")."</a>";

		$temp_array['ITREQUEST_NUMBER'] = format_number_display($this->itrequest_number,$this->system_id);

		return $temp_array;
	}

	/**
		builds a generic search based on the query string using or
		do not include any $this-> because this is called on without having the class instantiated
	*/
	function build_generic_where_clause ($the_query_string) {
	$where_clauses = Array();
	$the_query_string = PearDatabase::quote(from_html($the_query_string));
	array_push($where_clauses, "itrequests.name like '$the_query_string%'");

	if (is_numeric($the_query_string)) array_push($where_clauses, "itrequests.itrequest_number like '$the_query_string%'");

	$the_where = "";

	foreach($where_clauses as $clause)
	{
		if($the_where != "") $the_where .= " or ";
		$the_where .= $clause;
	}
	
	if($the_where != ""){
		$the_where = "(".$the_where.")";	
	}
	
	return $the_where;
	}

	function set_notification_body($xtpl, $itrequest)
	{
		global $app_list_strings;		
		
		$xtpl->assign("ITREQUEST_SUBJECT", $itrequest->name);
		$xtpl->assign("ITREQUEST_PRIORITY", (isset($itrequest->priority) ? $app_list_strings['itrequest_priority_dom'][$itrequest->priority]:""));
		$xtpl->assign("ITREQUEST_STATUS", (isset($itrequest->status) ? $app_list_strings['itrequest_status_dom'][$itrequest->status]:""));
		$xtpl->assign("ITREQUEST_DESCRIPTION", $itrequest->description);

		return $xtpl;
	}
	
		function bean_implements($interface){
		switch($interface){
			case 'ACL':return true;
		}
		return false;
	}
	
	function save($check_notify = FALSE){

		if(!isset($this->system_id) || empty($this->system_id))
		{
			require_once("modules/Administration/Administration.php");
			$admin = new Administration();
			$admin->retrieveSettings();
			$system_id = $admin->settings['system_system_id'];
			if(!isset($system_id)){
				$system_id = 1;
			}
			$this->system_id = $system_id;
		}

		return parent::save($check_notify);
	}
	
}
?>
