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
 * $Id: QueryColumn.php 45763 2009-04-01 19:16:18Z majed $
 * Description:
 ********************************************************************************/




require_once('modules/QueryBuilder/QueryBuilder.php');
require_once('modules/QueryBuilder/QueryGroupBy.php');
require_once('modules/QueryBuilder/QueryCalc.php');



// ProductTemplate is used to store customer information.
class QueryColumn extends QueryBuilder {
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
	var $column_name;
	var $column_module;
	var $parent_id;
	var $column_type;
	var $list_order_x;
	var $list_order_y;
	

	var $table_name = "query_columns";
	var $module_dir = "QueryBuilder";
	var $object_name = "QueryColumn";
	
	var $new_schema = true;

	var $column_fields = Array("id"
		,"column_name"
		,"date_entered"
		,"date_modified"
		,"modified_user_id"
		,"created_by"
		,"column_module"
		,"column_type"
		,"parent_id"
		,"list_order_x"
		,"list_order_y"
		);


	// This is used to retrieve related fields from form posts.
	var $additional_column_fields = Array();

	// This is the list of fields that are in the lists.
	var $list_fields = array();
	// This is the list of fields that are required
	var $required_fields =  array();

//Controller Array for list_order stuff
	var $controller_def = Array(
		 "list_x" => "Y"
		,"list_y" => "Y"
		,"parent_var" => "parent_id"
		,"start_var" => "list_order_x"
		,"start_axis" => "x"
		);		
	
	
	
	
	function QueryColumn() {
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
	
	//used when 
	function clear_child_calc_info(){
		
			$calc_id = $this->get_calc_id();
		
			$query = "delete from query_filters where parent_id='$calc_id' and deleted=0";
			$this->db->query($query,true,"Error deleting columns: ");
		
			$query = "delete from query_calcs where id='$calc_id' and deleted=0";
			$this->db->query($query,true,"Error deleting columns: ");
	}	
	

	function build_generic_where_clause ($the_query_string) {

	}

	function get_calc_id(){
		
			$query = "SELECT id FROM query_calcs where parent_id='$this->id' and deleted=0";
			$result = $this->db->query($query,true,"Error getting column calc: ");
			
			if($this->db->getRowCount($result) > 0){
				$row = $this->db->fetchByAssoc($result);	
				return $row['id'];
			}	
			
			
	}	
	
	function get_calc_object($parent_id){
		
			$query = "SELECT id FROM query_calcs where parent_id='$parent_id' and deleted=0";
			$result = $this->db->query($query,true,"Error getting column calc: ");
			
			if($this->db->getRowCount($result) > 0){
				$row = $this->db->fetchByAssoc($result);	
				
				$calc_object = new QueryCalc();
				$calc_object->retrieve($row['id']);
				
				return $calc_object;
			}	
		
		
	//end function get_calc_object
	}
	
	
	function retrieve_columns_display(& $xtemplate_object, $block_name, $main_block_name="main"){
		
		// First, get the list of columns currently in query
		$query = 	"SELECT * from $this->table_name
					 where $this->table_name.parent_id='$this->parent_id'
					 AND $this->table_name.deleted=0
					 ORDER by list_order_x, list_order_y
					 ";
		
		$result = $this->db->query($query,true," Error retrieving display columns: ");

		if($this->db->getRowCount($result) > 0){
		
		// Print out the columns
		while($row = $this->db->fetchByAssoc($result)){
			
			
			
			
			if(!empty($row['column_type']) && $row['column_type']=="Display"){
			
				$xtemplate_object->assign("COLUMN_RECORD", $row['id']);
				$xtemplate_object->assign("RECORD", $this->parent_id);
				$xtemplate_object->assign("DISPLAY_COLUMN_MODULE", $row['column_module']);
				$xtemplate_object->assign("DISPLAY_COLUMN_NAME", $row['column_name']);

				$xtemplate_object->parse($main_block_name.".".$block_name.".field");
			
			}
			
			if(!empty($row['column_type']) && $row['column_type']=="Group By"){
			
				
				//Retrieve the y-axis group_bys to display
				$groupby_object = new QueryGroupBy();
				$groupby_object->parent_id = $row['id'];
				$groupby_object->retrieve_groupby_display($xtemplate_object, "column.groupby");
				
			}
			
			if(!empty($row['column_type']) && $row['column_type']=="Calculation"){
			
				
				//Retrieve the calculation columns and their information
				$calc_object = new QueryCalc();
				$calc_object->parent_id = $row['id'];
				$calc_object->query_id = $this->parent_id;
				$calc_object->retrieve_calc_display($xtemplate_object, "column.calc", "main");
				
			}		
			
			$xtemplate_object->parse($main_block_name.".".$block_name);
			
		//end while
		}
		
		//end if data exists
		}	
		
	//end function retrieve_column_display
	}
	
	
	

}

?>
