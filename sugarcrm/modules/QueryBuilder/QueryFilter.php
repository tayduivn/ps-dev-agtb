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
 * $Id: QueryFilter.php 51443 2009-10-12 20:34:36Z jmertic $
 * Description:
 ********************************************************************************/




require_once('modules/QueryBuilder/QueryBuilder.php');
require_once('modules/QueryBuilder/QueryGroupBy.php');
require_once('modules/QueryBuilder/QueryColumn.php');
require_once('modules/QueryBuilder/QueryCalc.php');



// ProductTemplate is used to store customer information.
class QueryFilter extends QueryBuilder {
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
	var $left_field;
	var $left_module;
	var $operator;
	var $right_field;
	var $right_module;
	var $parent_id;
	var $filter_type;
	var $calc_enclosed;
	var $list_order;
	var $left_type;
	var $right_type;
	var $right_value;
	var $left_value;
	var $parent_filter_id;
	var $parent_filter_group;
	
	
	//used for connecting the sub-query in the column popup
	var $column_id;
	var $query_id;
	
	var $table_name = "query_filters";
	var $module_dir = "QueryBuilder";
	var $object_name = "QueryFilter";
	
	var $new_schema = true;

	var $column_fields = Array("id"
		,"name"
		,"date_entered"
		,"date_modified"
		,"modified_user_id"
		,"created_by"
		,"left_field"
		,"left_module"
		,"operator"
		,"right_field"
		,"right_module"
		,"filter_type"
		,"parent_id"
		,"calc_enclosed"
		,"list_order"
		,"left_type"
		,"right_type"
		,"left_value"
		,"right_value"
		,"parent_filter_id"
		,"parent_filter_group"
		);


	// This is used to retrieve related fields from form posts.
	var $additional_column_fields = Array();

	// This is the list of fields that are in the lists.
	var $list_fields = array("operator", "left_field", "right_field", "calc_enclosed", "parent_id", "query_id", "column_id", "id", "list_order", "left_type", "right_type");
	// This is the list of fields that are required
	var $required_fields =  array();

	function QueryFilter() {
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

		global $app_strings, $mod_strings;
		global $app_list_strings;

		global $current_user;
		
		if(empty($this->calc_enclosed)) $this->calc_enclosed = "off";

		$temp_array = Array();
		$temp_array['OPERATOR'] = $app_list_strings['query_calc_oper_dom'][$this->operator];
		$temp_array['CALC_ENCLOSED'] = $this->calc_enclosed;
		$temp_array['LEFT_FIELD'] = $this->left_field;
		$temp_array['RIGHT_FIELD'] = $this->right_field;
		$temp_array['ID'] = $this->id;
		$temp_array['LIST_ORDER'] = $this->list_order;
		
		$temp_array['LEFT_TYPE'] = $this->left_type;
		$temp_array['RIGHT_TYPE'] = $this->right_type;
		
		
		return $temp_array;
			
			
	
	}
	
	function clear_deleted($id){

			$query = "delete from query_filters where id='$id' and deleted=0";
			
			$this->db->query($query,true,"Error deleting columns: ");
	
	//end function clear_deleted
	}
	
	
	

	function build_generic_where_clause ($the_query_string) {

	}
	
	
	
	
	function save_group_id(){
		
		
		
		
		//if left_type is group, then size id to parent_id in child filter
		if($this->left_type=="Group"){
			$this->save_parent_filter_id($this->left_group);
	
		//end if left type
		}	
			
		//if right_type
		if($this->right_type=="Group"){
			$this->save_parent_filter_id($this->right_group);
		//end if right type		
		}
	
	
	//end save_group_id
	}
	
	function save_parent_filter_id($child_filter_id){
		
			$query = "UPDATE query_filters SET parent_filter_id='$this->id where id='$child_filter_id' and deleted=0";		
			$this->db->query($query,true,"Error updating parent filter Id ");

	//end function save_parent_filter_id
	}

	function get_filter_group_array(){
		
	$group_array = array("" => "None");
		
		
	//compile list
			$query = 	"	SELECT * from query_filters
					 		where left_type='Group' OR right_type='Group'
					 		AND deleted=0
					 		ORDER BY list_order
						 ";
		
		$result = $this->db->query($query,true," Error retrieving column calculation sub calcs: ");

		if($this->db->getRowCount($result) > 0){
		
			// Print out the calculation column info
			while($row = $this->db->fetchByAssoc($result)){
				
				$safety = $this->safety_check($row['id']);
				if($safety==true){
				
					if($row['left_type']=="Group"){
						$array_key = "LEFT::".$row['id'];
						$group_array[$array_key] = "LEFT GROUP- ".$row['list_order'];
					}
					if($row['right_type']=="Group"){
						$array_key = "RIGHT::".$row['id'];
						$group_array[$array_key] = "RIGHT GROUP- ".$row['list_order'];
					}
	
				//if safety is true
				}
	

				
			//end while rows exist		
			}		
				
		//end if there are any rows
		}		
				
		return $group_array;
		
	//end get_filter_group_array
	}	
	
	
	function safety_check($id){
		
		
		//check to make sure no one else has picked this slot
		//check to make sure no circular logic exists
		
		return true;
		
	//end function safety_check
	}	

	function glue_parent_filter_id(){
		
		return $this->parent_filter_group."::".$this->parent_filter_id;
		
	//end glue_parent_filter_id
	}	
	
	function split_parent_filter_id(){
		
		$split_parts = explode("::", $this->parent_filter_id);
		
		$this->parent_filter_id = $split_parts[1];
		$this->parent_filter_group = $split_parts[0];
		
	//end split_parent_filter_id
	}	
	
	
	function retrieve_calc_display(& $xtemplate_object, $block_name, $main_block_name="main", $column_id){
		
		// First, get the list of columns currently in query
		$query = 	"SELECT * from $this->table_name
					 where $this->table_name.parent_id='$this->parent_id'
					 AND $this->table_name.deleted=0
					 ";
		
		$result = $this->db->query($query,true," Error retrieving display calculations columns: ");

		if($this->db->getRowCount($result) > 0){
		
		// Print out the calculation column info
		$row = $this->db->fetchByAssoc($result);
			
			

				$xtemplate_object->assign("COLUMN_RECORD", $column_id);
				$xtemplate_object->assign("RECORD", $this->parent_id);
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
				$field_table = $this->seed_object->get_field_table($this->calc_module, $this->calc_field);
				//add piece to the select array
				array_push($select_array, $this->calc_type."(".$field_table.")");
			
		//end if this is a standard calculation
		}
		if(!empty($calc_object->type) && $calc_object->type=="Sub Query"){
		
		
			
			
			
		//end if this is a sub query calculation
		}	
		
		if(!empty($calc_object->type) && $calc_object->type=="Math Calc"){
		
		
			
			
			
		//end if this is a math calc calculation
		}
		
		
							
	//eend function get_select_part
	}							
	

}

?>
