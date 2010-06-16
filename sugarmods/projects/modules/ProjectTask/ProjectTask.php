<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * Data access layer for the project_task table
 *
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: ProjectTask.php 17224 2006-10-20 18:01:50 +0000 (Fri, 20 Oct 2006) awu $

require_once('data/SugarBean.php');

require_once('include/utils.php');


require_once('modules/Calls/Call.php');
require_once('modules/Notes/Note.php');
require_once('modules/Emails/Email.php');

class ProjectTask extends SugarBean {
	// database table columns
	var $id;
	var $date_entered;
	var $date_modified;
	//var $assigned_user_id;
	//var $modified_user_id;
	//var $created_by;
	//BEGIN SUGARCRM flav=pro ONLY 
	var $team_id;
	//END SUGARCRM flav=pro ONLY 
	var $name;
    var $description;
    var $project_id;
    var $project_task_id;
    var $date_start;
    var $date_finish;
    var $duration;
    var $duration_unit;
    var $percent_complete;
    var $parent_task_id;
    var $predecessors;
    var $resource_id;
    var $priority;

/*
	var $status;
	var $date_due;
	var $time_due;
	var $time_start;
	var $parent_id;
	var $priority;
	var $order_number;
	var $task_number;
	var $depends_on_id;
	var $milestone_flag;
	var $estimated_effort;
	var $actual_effort;
	var $utilization;
	var $deleted;
*/
	// related information
	var $assigned_user_name;
	var $parent_name;
	var $depends_on_name;
	var $email_id;
	//BEGIN SUGARCRM flav=pro ONLY 
	var $team_name;
	//END SUGARCRM flav=pro ONLY 

	var $table_name = 'project_task';
	var $object_name = 'ProjectTask';
	var $module_dir = 'ProjectTask';


	var $field_name_map;
	var $new_schema = true;

	var $relationship_fields = array(
		'email_id' => 'emails',
	);

	//////////////////////////////////////////////////////////////////
	// METHODS
	//////////////////////////////////////////////////////////////////

	/*
	 *
	 */
	function ProjectTask()
	{
		parent::SugarBean();

		// default value for a clean instantiation
		$this->utilization = 100;

		global $current_user;
		if(empty($current_user))
		{
			$this->assigned_user_id = 1;
			$this->assigned_user_name = 'admin';
		}
		else
		{
			$this->assigned_user_id = $current_user->id;
			$this->assigned_user_name = $current_user->user_name;
		}

		//BEGIN SUGARCRM flav=pro ONLY 
		global $current_user;
		if (!empty($current_user))
		{
			$this->team_id = $current_user->default_team;
		}
		else
		{
			$this->team_id = 1;  // global team
		}
		//END SUGARCRM flav=pro ONLY 
	}
	
	function save($check_notify = FALSE){
		$id = parent::save($check_notify);
        return $id;
	}
	
	/**
	 * overriding the base class function to do a join with users table
	 */
	function create_list_query($order_by, $where, $show_deleted = 0)
	{
		$custom_join = $this->custom_fields->getJOIN();

		$query = "SELECT users.user_name assigned_user_name, project.name parent_name, project.assigned_user_id parent_name_owner, project_task.*";

		if($custom_join)
		{
			$query .=  $custom_join['select'];
		}

		//BEGIN SUGARCRM flav=pro ONLY 
		$query .= ", teams.name team_name";
		//END SUGARCRM flav=pro ONLY 
		$query .= " FROM project_task ";

		//BEGIN SUGARCRM flav=pro ONLY 
		// We need to confirm that the user is a member of the team of the item.
		$this->add_team_security_where_clause($query);
		//END SUGARCRM flav=pro ONLY 
		
		$query .= "LEFT JOIN users ON project_task.assigned_user_id=users.id ";
		$query .= "LEFT JOIN project ON project_task.project_id=project.id ";
		
		//BEGIN SUGARCRM flav=pro ONLY 
		$query .= "LEFT JOIN teams ON project_task.team_id=teams.id ";
		//END SUGARCRM flav=pro ONLY 
		
		if($custom_join)
		{
			$query .=  $custom_join['join'];
		}

			$where_auto = '1=1';
				if($show_deleted == 0){
                	$where_auto = "$this->table_name.deleted=0 AND project.deleted=0";
				}else if($show_deleted == 1){
                	$where_auto = "$this->table_name.deleted=1";
				}

				if($where != '')
						  $query .= "where ($where) AND ".$where_auto;
				else
						  $query .= "where ".$where_auto;

				if(!empty($order_by))
						  $query .= " ORDER BY $order_by";
		//die($query);
		return $query;
	}

	/*
	 *
	 */
   function fill_in_additional_detail_fields()
   {
      $this->assigned_user_name = get_assigned_user_name($this->assigned_user_id);
		//BEGIN SUGARCRM flav=pro ONLY 
		$this->team_name = get_assigned_team_name($this->team_id);
		//END SUGARCRM flav=pro ONLY 
      $this->project_name = $this->_get_project_name($this->project_id);
		/*
        $this->depends_on_name = $this->_get_depends_on_name($this->depends_on_id);
		if(empty($this->depends_on_name))
		{
			$this->depends_on_id = '';
		}
		$this->parent_name = $this->_get_parent_name($this->parent_id);
		if(empty($this->parent_name))
		{
			$this->parent_id = '';
		}
        */
   }

	/*
	 *
	 */
   function fill_in_additional_list_fields()
   {
      $this->assigned_user_name = get_assigned_user_name($this->assigned_user_id);
      //$this->parent_name = $this->_get_parent_name($this->parent_id);
      $this->project_name = $this->_get_project_name($this->project_id);
   }

	/*
	 *
	 */
	function get_summary_text()
	{
		return $this->name;
	}

	/*
	 *
	 */
	function _get_depends_on_name($depends_on_id)
	{
		$return_value = '';

		$query  = "SELECT name, assigned_user_id FROM {$this->table_name} WHERE id='{$depends_on_id}'";
		$result = $this->db->query($query,true," Error filling in additional detail fields: ");
		$row = $this->db->fetchByAssoc($result);
		if($row != null)
		{
			$this->depends_on_name_owner = $row['assigned_user_id'];
			$this->depends_on_name_mod = 'ProjectTask';
			$return_value = $row['name'];
		}

		return $return_value;
	}

    function _get_project_name($project_id)
    {
        $return_value = '';

        $query  = "SELECT name, assigned_user_id FROM project WHERE id='{$project_id}'";
        $result = $this->db->query($query,true," Error filling in additional detail fields: ");
        $row = $this->db->fetchByAssoc($result);
        if($row != null)
        {
            //$this->parent_name_owner = $row['assigned_user_id'];
            //$this->parent_name_mod = 'Project';
            $return_value = $row['name'];
        }

        return $return_value;
    }
	/*
	 *
	 */
	function _get_parent_name($parent_id)
	{
		$return_value = '';

		$query  = "SELECT name, assigned_user_id FROM project WHERE id='{$parent_id}'";
		$result = $this->db->query($query,true," Error filling in additional detail fields: ");
		$row = $this->db->fetchByAssoc($result);
		if($row != null)
		{
			$this->parent_name_owner = $row['assigned_user_id'];
			$this->parent_name_mod = 'Project';
			$return_value = $row['name'];
		}

		return $return_value;
	}

	/*
	 *
	 */
	function build_generic_where_clause ($the_query_string)
	{
		$where_clauses = array();
		$the_query_string = PearDatabase::quote(from_html($the_query_string));
		array_push($where_clauses, "project_task.name like '$the_query_string%'");

		$the_where = "";
		foreach($where_clauses as $clause)
		{
			if($the_where != "") $the_where .= " or ";
			$the_where .= $clause;
		}

		return $the_where;
	}

	function get_list_view_data(){
		global $action, $currentModule, $focus, $current_module_strings, $app_list_strings, $image_path, $timedate;
		$today = $timedate->handle_offset(date("Y-m-d H:i:s", time()), $timedate->dbDayFormat, true);
		$task_fields =$this->get_list_view_array();
		//$date_due = $timedate->to_db_date($task_fields['DATE_DUE'],false);
        if (isset($this->parent_type)) 
			$task_fields['PARENT_MODULE'] = $this->parent_type;
		/*
        if ($this->status != "Completed" && $this->status != "Deferred" ) {
			$task_fields['SET_COMPLETE'] = "<a href='index.php?return_module=$currentModule&return_action=$action&return_id=" . ((!empty($focus->id)) ? $focus->id : "") . "&module=ProjectTask&action=EditView&record={$this->id}&status=Completed'>".get_image($image_path."close_inline","alt='Close' border='0'")."</a>";
		}
        
		if( $date_due	< $today){
			$task_fields['DATE_DUE']= "<font class='overdueTask'>".$task_fields['DATE_DUE']."</font>";
		}else if( $date_due	== $today ){
			$task_fields['DATE_DUE'] = "<font class='todaysTask'>".$task_fields['DATE_DUE']."</font>";
		}else{
			$task_fields['DATE_DUE'] = "<font class='futureTask'>".$task_fields['DATE_DUE']."</font>";
		}
        */

		$task_fields['CONTACT_NAME']= return_name($task_fields,"FIRST_NAME","LAST_NAME");
		$task_fields['TITLE'] = '';
		if (!empty($task_fields['CONTACT_NAME'])) {
			$task_fields['TITLE'] .= $current_module_strings['LBL_LIST_CONTACT'].": ".$task_fields['CONTACT_NAME'];
		}

		return $task_fields;
	}
	
	function bean_implements($interface){
		switch($interface){
			case 'ACL':return true;
		}
		return false;
	}
	function listviewACLHelper(){
		$array_assign = parent::listviewACLHelper();
		$is_owner = false;
		if(!empty($this->parent_name)){
			
			if(!empty($this->parent_name_owner)){
				global $current_user;
				$is_owner = $current_user->id == $this->parent_name_owner;
			}
		}
			if(ACLController::checkAccess('Project', 'view', $is_owner)){
				$array_assign['PARENT'] = 'a';
			}else{
				$array_assign['PARENT'] = 'span';
			}
		$is_owner = false;
		if(!empty($this->depends_on_name)){
			
			if(!empty($this->depends_on_name_owner)){
				global $current_user;
				$is_owner = $current_user->id == $this->depends_on_name_owner;
			}
		}
			if( ACLController::checkAccess('ProjectTask', 'view', $is_owner)){
				$array_assign['PARENT_TASK'] = 'a';
			}else{
				$array_assign['PARENT_TASK'] = 'span';
			}
		
		return $array_assign;
	}
	
	function create_export_query(&$order_by, &$where)
    {
      	$custom_join = $this->custom_fields->getJOIN();
		$query = "SELECT
				project_task.*,
                users.user_name as assigned_user_name ";
        //BEGIN SUGARCRM flav=pro ONLY 
        $query .= ", teams.name AS team_name ";
        //END SUGARCRM flav=pro ONLY 
        if($custom_join){
			$query .=  $custom_join['select'];
		}
        $query .= "FROM project_task ";
        
		//BEGIN SUGARCRM flav=pro ONLY 
		// We need to confirm that the user is a member of the team of the item.
		$this->add_team_security_where_clause($query);
		//END SUGARCRM flav=pro ONLY 
		if($custom_join){
			$query .=  $custom_join['join'];
		}
        $query .= " LEFT JOIN users
                   	ON project_task.assigned_user_id=users.id ";
        //BEGIN SUGARCRM flav=pro ONLY 
        $query .=		"LEFT JOIN teams ON project_task.team_id=teams.id ";
        //END SUGARCRM flav=pro ONLY 

        $where_auto = " project_task.deleted=0 ";

        if($where != "")
        	$query .= "where ($where) AND ".$where_auto;
        else
            $query .= "where ".$where_auto;

        if(!empty($order_by)){
           	//check to see if order by variable already has table name by looking for dot "."
           	$table_defined_already = strpos($order_by, ".");

	        if($table_defined_already === false){
	        	//table not defined yet, define accounts to avoid "ambigous column" SQL error 
	        	$query .= " ORDER BY $order_by";
	        }else{
	        	//table already defined, just add it to end of query
	            $query .= " ORDER BY $order_by";	
	        }           
        }
        return $query;
    }	
    
    function getResourceName(){
    	
    	$query = "SELECT DISTINCT resource_type FROM project_resources WHERE resource_id = '" . $this->resource_id . "'";
    	
    	$result = $this->db->query($query, true, "Unable to retrieve project resource type");
		$row = $this->db->fetchByAssoc($result);
		
    	$resource_table = strtolower($row['resource_type']);
    	
    	if (empty($resource_table)){
    		return '&nbsp;';
    	}
    	
    	if ($this->db->dbType=='mssql'){
			$resource = db_convert($resource_table.".first_name,''","IFNULL")." + ' ' + ".db_convert($resource_table.".last_name,''","IFNULL");

		}
		else if ($this->db->dbType=='mysql'){
			$resource = "CONCAT(".db_convert($resource_table.".first_name,''","IFNULL").", ' ',".db_convert($resource_table.".last_name,''","IFNULL").")";
		}				
		else if ($this->db->dbType=='oci8'){
			$resource = "CONCAT(CONCAT(".db_convert($resource_table.".first_name,''","IFNULL").",' '), ".db_convert($resource_table.".last_name,''","IFNULL").")";
		}		
		
		$resource_name_qry = "SELECT " . $resource . " as resource_name " .
							 "FROM " . $resource_table . " ".
							 "WHERE id = '" . $this->resource_id ."'";

		$result = $this->db->query($resource_name_qry, true, "Unable to retrieve project resource name");
		$row = $this->db->fetchByAssoc($result);
		
		return $row['resource_name'];
    }
	
}
?>