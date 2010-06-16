<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: WorkFlowSchedule.php 56268 2010-05-05 16:49:49Z clee $
 * Description:
 ********************************************************************************/






require_once('include/workflow/workflow_utils.php');

// WorkFlowSchedule is used to process workflow time cron objects
class WorkFlowSchedule extends SugarBean {
	var $field_name_map;
	// Stored fields
	var $id;
	var $deleted;
	var $date_entered;
	var $date_modified;
	var $modified_user_id;
	var $created_by;
	var $created_by_name;
	var $modified_by_name;

	var $date_expired;
	var $module;
	var $workflow_id;
	var $bean_id;
	
	var $parameters;

	var $table_name = "workflow_schedules";
	var $module_dir = "WorkFlow";
	var $object_name = "WorkFlowSchedule";
	
	var $rel_triggershells_table = 	"workflow_triggershells";
	var $rel_triggers_table = 		"workflow_triggers";
	var $rel_alertshells_table = 	"workflow_alertshells";
	var $rel_alerts_table = 		"workflow_alerts";
	var $rel_actionshells_table = 	"workflow_actionshells";
	var $rel_actions_table = 		"workflow_actions";
	var $rel_workflow_table = 		"workflow";
	
	
	var $new_schema = true;

	var $column_fields = Array("id"
		,"module"
		,"date_entered"
		,"date_modified"
		,"modified_user_id"
		,"created_by"
		,"date_expired"
		,"workflow_id"
		,"bean_id"
		,"parameters"
		);


	// This is used to retrieve related fields from form posts.
	var $additional_column_fields = Array();

	// This is the list of fields that are in the lists.
	var $list_fields = array();

	var $relationship_fields = Array();
	
	
	// This is the list of fields that are required
	var $required_fields =  array("module"=>1, 'bean_id'=>1, 'workflow_id'=>1);

	function WorkFlowSchedule() {
		global $dictionary;
    	if(isset($this->module_dir) && isset($this->object_name) && !isset($dictionary[$this->object_name])){
    	    if(file_exists('custom/metadata/workflow_schedulesMetaData.php')) {
    		  require('custom/metadata/workflow_schedulesMetaData.php');
    		} else {
    		  require('metadata/workflow_schedulesMetaData.php');	
    		}
    	}
		
		
		parent::SugarBean();

		$this->disable_row_level_security =true;

	}

	

	function get_summary_text()
	{
		return "$this->module";
	}




	/** Returns a list of the associated product_templates
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved.
	 * Contributor(s): ______________________________________..
	*/
    
        function create_export_query(&$order_by, &$where)
        {

        }



	function save_relationship_changes($is_update)
    {
    }


	function mark_relationships_deleted($id)
	{
	}

	function fill_in_additional_list_fields()
	{

	}

	function fill_in_additional_detail_fields()
	{


	}


	function get_list_view_data(){
		global $app_strings, $mod_strings;
		global $app_list_strings;
		global $current_user;
		return $temp_array;
	}
	/**
		builds a generic search based on the query string using or
		do not include any $this-> because this is called on without having the class instantiated
	*/
	function build_generic_where_clause ($the_query_string) {
	$where_clauses = Array();
	$the_query_string = addslashes($the_query_string);
	array_push($where_clauses, "module like '$the_query_string%'");

	$the_where = "";
	foreach($where_clauses as $clause)
	{
		if($the_where != "") $the_where .= " or ";
		$the_where .= $clause;
	}


	return $the_where;
}



////////////////Time Cron Scheduling Components///////////////////////

	function check_existing_trigger($bean_id, $workflow_id){	
		
		$query = "	SELECT id
					FROM $this->table_name
					WHERE $this->table_name.bean_id = '".$bean_id."'
					AND $this->table_name.workflow_id = '".$workflow_id."'
					AND $this->table_name.deleted=0";
		$result = $this->db->query($query,true," Error checking for existing scheduled trigger: ");

		// Get the id and the name.
		$row = $this->db->fetchByAssoc($result);

		if($row != null)
		{
			$this->retrieve($row['id']);
			return true;
		}
		else
		{

			return false;
		}
	//end function check_existing_trigger
	}
	
	
	function set_time_interval(& $bean_object, $time_array, $update = false){
		
		if($update == false && $time_array['time_int_type']=="normal"){
		
		//take current date and add the time interval

		$this->date_expired = $this->get_expiry_date($bean_object, $time_array['time_int'], false, $time_array['target_field']);
			
		//end if update is false, then create a new time expiry		
		}	
		
		
		if($update == true || $time_array['time_int_type']=="datetime"){
		
		//convert date_expired into time stamp and add time_interval
		$this->date_expired = $this->get_expiry_date($bean_object, $time_array['time_int'], true, $time_array['target_field']);	
			
		//end if update is true, then just update existing expiry	
		}	
		
		
		
	//end function set_time_interval
	}	

	
	function get_expiry_date(& $bean_object, $time_interval, $is_update = false, $target_field="none"){
		global $timedate;
        global $disable_date_format;
		
		if($is_update == false){
			$target_stamp = gmdate($GLOBALS['timedate']->get_db_date_time_format());
			//$target_stamp = $timedate->to_display_date_time($target_stamp, true);	
		} else {
			
			if($target_field=="none"){

				$target_stamp = gmdate($GLOBALS['timedate']->get_db_date_time_format());
				//$target_stamp = $timedate->to_display_date_time($target_stamp, true);			
			} else {	
				$target_stamp = $bean_object->$target_field;
				//$target_stamp = $timedate->to_display_date_time($bean_object->$target_field, true);	
			}
			
		}		
	
		//convert stamp and add interval
		$current_unix_stamp = strtotime($target_stamp);	
		$new_unix_stamp = $time_interval + $current_unix_stamp;	
		
		//if($is_update == true){
		//	$new_unix_stamp = $new_unix_stamp + 3600;
		//}	
		
		$newtimestamp = date($GLOBALS['timedate']->get_db_date_time_format(), $new_unix_stamp);

		//test using expected close date via this.

        if(empty($disable_date_format)) {
            $final_stamp = $timedate->to_display_date_time($newtimestamp, true);
        }
        else {
            $final_stamp = $newtimestamp;
        }
		
		return $final_stamp;

	//end function get_expiry_date
	}	
	
function process_scheduled(){
	global $app_strings;
	global $app_list_strings;
	global $mod_strings;
	global $current_user;
	global $beanList;
	
	if(empty($beanList)) {
		require('include/modules.php');
	}
	
	$current_stamp = gmdate($GLOBALS['timedate']->get_db_date_time_format());

	$query = "	SELECT *
				FROM $this->table_name
				WHERE $this->table_name.date_expired < '".$current_stamp."'
				AND $this->table_name.deleted=0";
	$result = $this->db->query($query,true," Error checking scheduled triggers to process: ");

	// Print out the calculation column info
	while($row = $this->db->fetchByAssoc($result)){
		$temp_module = get_module_info($row['target_module']);
		$_SESSION['workflow_cron'] = "Yes";
		$_SESSION['workflow_id_cron'] = $row['workflow_id'];
		
		//Set the extension variables in case we need them
		$_SESSION['workflow_parameters'] = $row['parameters'];
		//////////////////////////////////////////////////

		$temp_module->retrieve($row['bean_id']);
		
		if($temp_module->fetched_row['deleted'] == '0'){
			$temp_module->save();
		}
	
		unset($_SESSION['workflow_cron']);
		unset($_SESSION['workflow_id_cron']);

		//remove this expired process
		$this->remove_expired($row['id']);	
		
	//end while processing
	}
////Remove any expired schedules/////
//end function process_scheduled
}	
	
function remove_expired($id){
	
		$remove_query = "	DELETE FROM $this->table_name
							WHERE $this->table_name.id = '".$id."'";
		
		$remove_results = $this->db->query($remove_query,true," Error remove expired process: ");

		
//end function remove_expired
}


	
//end class	
}

?>
