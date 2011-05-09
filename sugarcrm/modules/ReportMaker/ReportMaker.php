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
 * $Id: ReportMaker.php 45763 2009-04-01 19:16:18Z majed $
 * Description:
 ********************************************************************************/








// ReportMaker is used to build advanced reports from data formats.
class ReportMaker extends SugarBean {
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

	var $name;
	var $description;
	var $title;
	var $team_id;

	//UI parameters
	var $report_align;
	
	//variables for joining the report schedules table
	var $schedule_id;
	var $next_run;
	var $active;
	var $time_interval;
	


	//for the name of the parent if an interlocked data set
	var $parent_name;
	
	//for related fields
	var $query_name;

	var $table_name = "report_maker";
	var $module_dir = 'ReportMaker';
	var $object_name = "ReportMaker";
	var $rel_dataset = "data_sets";
	var $schedules_table = "report_schedules";
	
	var $new_schema = true;


	// This is used to retrieve related fields from form posts.
	var $additional_column_fields = Array();

	function ReportMaker() {
		parent::SugarBean();

		//make sure only people in the same team can see the reports
		$this->disable_row_level_security =false;

	}

	

	function get_summary_text()
	{
		return "$this->name";
	}




	/** Returns a list of the associated product_templates
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved.
	 * Contributor(s): ______________________________________..
	*/

        function create_export_query(&$order_by, &$where)
        {
			
			$export_object = new CustomQuery();
			return $export_object->create_export_query();
						

        }

	function save_relationship_changes($is_update)
    {
    }
    
    
    function clear_deleted($id){

    //first update and remove report_id's for any datasets
    		$query = "update data_sets set report_id='' where report_id='$id' and deleted=0";
			
			$this->db->query($query,true,"error removing data sets from reports: ");
    	
			$this->mark_deleted($id);

	//end function clear_deleted
	}


	function mark_relationships_deleted($id)
	{
	}

	function fill_in_additional_list_fields()
	{
		$this->fill_in_additional_detail_fields();
	}

	function fill_in_additional_detail_fields()
	{
		$this->assigned_name = get_assigned_team_name($this->team_id);
		$this->get_scheduled_query();
	}
	
	function get_scheduled_query(){	
		
		$query = "	SELECT  
					$this->schedules_table.id schedule_id,
                    $this->schedules_table.active active,
                    $this->schedules_table.next_run next_run
                    from ".$this->schedules_table."
					where ".$this->schedules_table.".report_id = '".$this->id."'
					and ".$this->schedules_table.".deleted=0
					";
		$result = $this->db->query($query,true," Error filling in additional schedule query: ");

		// Get the id and the name.
		$row = $this->db->fetchByAssoc($result);

		if($row != null){
			$this->schedule_id = $row['schedule_id'];
			$this->active = $row['active'];
			$this->next_run = $row['next_run'];
		} else {
			$this->schedule_id = "";
			$this->active = "";
			$this->next_run = "";
		}
	//end get_scheduled_query
	}	
	
	
	function get_list_view_data(){
		global $timedate;
		global $app_strings, $mod_strings;
		global $app_list_strings;
		

		global $current_user;
		
		if(empty($this->published)) $this->published="0";

		$temp_array = parent::get_list_view_data();
		$temp_array['NAME'] = (($this->name == "") ? "<em>blank</em>" : $this->name);
		$temp_array['ID'] = $this->id;
		
		//report scheduling
		if(isset($this->schedule_id) && $this->active == 1){
			$is_scheduled_img = SugarThemeRegistry::current()->getImage/*ALTFIXED*/('scheduled_inline.png','border="0" align="absmiddle"',null,null,'.gif',$mod_strings['LBL_SCHEDULE_EMAIL']);
			$is_scheduled = $timedate->to_display_date_time($this->next_run);
		} else {
			$is_scheduled_img = SugarThemeRegistry::current()->getImage/*ALTFIXED*/('unscheduled_inline.png','border="0" align="absmiddle"',null,null,'.gif',$mod_strings['LBL_SCHEDULE_EMAIL']);
			$is_scheduled = $mod_strings['LBL_NONE'];
		}
		
		$temp_array['IS_SCHEDULED'] = $is_scheduled;
		$temp_array['IS_SCHEDULED_IMG'] = $is_scheduled_img;
		
		return $temp_array;
	}
	/**
		builds a generic search based on the query string using or
		do not include any $this-> because this is called on without having the class instantiated
	*/
	function build_generic_where_clause ($the_query_string) {
	$where_clauses = Array();
	$the_query_string = $GLOBALS['db']->quote($the_query_string);
	array_push($where_clauses, "name like '$the_query_string%'");
	if (is_numeric($the_query_string)) {
		array_push($where_clauses, "mft_part_num like '%$the_query_string%'");
		array_push($where_clauses, "vendor_part_num like '%$the_query_string%'");
	}

	$the_where = "";
	foreach($where_clauses as $clause)
	{
		if($the_where != "") $the_where .= " or ";
		$the_where .= $clause;
	}


	return $the_where;
	
	//end function
	}


	function get_data_sets($orderBy=""){
		// First, get the list of IDs.
		$query = 	"SELECT $this->rel_dataset.id from $this->rel_dataset
					 where $this->rel_dataset.report_id='$this->id'
					 AND $this->rel_dataset.deleted=0 ".$orderBy;

		return $this->build_related_list($query, new DataSet());
	//end get_data_sets
	}

//end class ReportMaker
}

?>