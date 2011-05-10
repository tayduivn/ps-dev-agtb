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
 * $Id: QueryCalc.php 45763 2009-04-01 19:16:18Z majed $
 * Description:
 ********************************************************************************/




require_once('modules/QueryBuilder/QueryBuilder.php');
require_once('modules/QueryBuilder/QueryGroupBy.php');
require_once('modules/QueryBuilder/QueryColumn.php');



// ProductTemplate is used to store customer information.
class QueryCalc extends QueryBuilder {
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

	//construction
	var $name;
	var $calc_field;
	var $calc_module;
	var $parent_id;
	var $type;
	var $calc_type;
	var $calc_query_type;
	var $calc_order;
	var $calc_group_condition;
	var $filter_group_condition;
	var $filter_group;
	
	
	//used for display and passing purposes only
	var $query_id;
	

	var $table_name = "query_calcs";
	var $module_dir = "QueryBuilder";
	var $object_name = "QueryCalc";
	
	var $new_schema = true;

	var $column_fields = Array("id"
		,"name"
		,"date_entered"
		,"date_modified"
		,"modified_user_id"
		,"created_by"
		,"calc_field"
		,"calc_module"
		,"type"
		,"calc_type"
		,"calc_query_type"
		,"parent_id"
		,"calc_order"
		,"filter_group"
		,"filter_group_condition"
		,"calc_group_condition"
		);


	// This is used to retrieve related fields from form posts.
	var $additional_column_fields = Array();

	// This is the list of fields that are in the lists.
	var $list_fields = array();
	// This is the list of fields that are required
	var $required_fields =  array("name"=>1);

	function QueryCalc() {
		parent::SugarBean();

		$this->disable_row_level_security =true;

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

	}
	
	function clear_deleted(){

			$query = "delete from query_columns where id='$this->id' and deleted=0";
			
	
			
			$this->db->query($query,true,"Error deleting columns: ");
	
	//end function clear_deleted
	}
	
	
	

	function build_generic_where_clause ($the_query_string) {

	}


	
	function retrieve_calc_display(& $xtemplate_object, $block_name, $main_block_name="main"){
		
		// First, get the list of columns currently in query
		$query = 	"SELECT * from $this->table_name
					 where $this->table_name.parent_id='$this->parent_id'
					 AND $this->table_name.deleted=0
					 ";
		
		$result = $this->db->query($query,true," Error retrieving display calculations columns: ");

		if($this->db->getRowCount($result) > 0){
		
		// Print out the calculation column info
		$row = $this->db->fetchByAssoc($result);
			
			

				$xtemplate_object->assign("COLUMN_RECORD", $this->parent_id);
				$xtemplate_object->assign("RECORD", $this->query_id);
				$xtemplate_object->assign("DISPLAY_NAME", $row['name']);

				$xtemplate_object->parse($main_block_name.".".$block_name.".field");

			
			$xtemplate_object->parse($main_block_name.".".$block_name);

		//end if data exists
		}	
		
	//end function retrieve_calc_display
	}
	
	

	
////////////////////////////Query building functions////////////


	function get_select_part(& $select_array){
	
		//Calculation Type Standard ///////								
		if(!empty($this->type) && $this->type=="Standard"){
						
				//get the corresponding table name for the field, includes custom lookup
				$field_table = $this->get_field_table($this->calc_module, $this->calc_field);
				//add piece to the select array
				array_push($select_array, $this->calc_type."(".$field_table.")");
			
		//end if this is a standard calculation
		}
		
		if(!empty($this->type) && $this->type=="Math"){
		
			$calculation_part = $this->get_total_subcalc_start();
			array_push($select_array, $this->calc_type."( ".$calculation_part." )");
			
			
		//end if this is a math calc calculation
		}
		
		
							
	//end function get_select_part
	}

	function get_total_subcalc_start($display=false){
			$parent_id = $this->id;
			$parent_filter_id = "";
			return $this->get_total_subcalc($display, $parent_id, $parent_filter_id);
	
	//end function get_total_subcalc_start
	}	
	
	
	function get_total_subcalc($display=false, $parent_id, $parent_filter_id, $parent_filter_group=""){
	if(!isset($calc_part)) $calc_part = "";	

		
		// First, get the list of columns currently in query
		$query = 	"SELECT * from query_filters
					 where parent_id='$parent_id'
					 AND parent_filter_id='$parent_filter_id'
					 AND parent_filter_group='$parent_filter_group'
					 AND deleted=0
					 ORDER BY list_order
					 ";
		
		$result = $this->db->query($query,true," Error retrieving column calculation sub calcs: ");

		if($this->db->getRowCount($result) > 0){
		
			// Print out the calculation column info
			while($row = $this->db->fetchByAssoc($result)){
			
				
				if($row['calc_enclosed']=="on"){
					$calc_part .= " ( ";
				}		
				
				if($row['left_type']=="Field"){
					$calc_part .= $this->get_field_table($row['left_module'], $row['left_field']);
				}
				if($row['left_type']=="Value"){
					$calc_part .= $row['left_value'];
				}
				if($row['left_type']=="Group"){
					$calc_part .= $this->get_total_subcalc($display, $parent_id, $row['id'], "LEFT");
				}
				
				//OPERATOR
				$calc_part .= " ".$row['operator']." ";
				
				if($row['right_type']=="Field"){
					$calc_part .= $this->get_field_table($row['right_module'], $row['right_field']);
				}
				if($row['right_type']=="Value"){
					$calc_part .= $row['right_value'];
				}
				if($row['right_type']=="Group"){
					$calc_part .= $this->get_total_subcalc($display, $parent_id, $row['id'], "RIGHT");
				}
				
				if($row['calc_enclosed']=="on"){
					$calc_part .= " ) ";
				}

			//end while
			}
		
		//end if there are sub calc Row
		} else {
		//unfinished calculation
		$calc_part .= " GROUP NEEDED! ";	
		}		

		return $calc_part;
	}
	
	
	
//end class
}

?>
