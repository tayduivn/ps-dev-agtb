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
 * $Id: QueryGlue.php 13782 2006-06-06 17:58:55Z majed $
 * Description:
 ********************************************************************************/

//Library of functions used to compile the query for run
require_once('modules/QueryBuilder/QueryGroupBy.php');
require_once('modules/QueryBuilder/QueryCalc.php');
require_once('modules/QueryBuilder/QueryColumn.php');
require_once('modules/QueryBuilder/QueryBuilder.php');

class QueryGlue {
	
	var $seed_object;
	
	var $select_part = "";
	var $from_part = "";
	var $join_part = "";
	var $where_part ="";
	var $group_by_part ="";
	var $order_by_part = "";
	
	var $select_array = array();
	var $rel_mod_array = array();
	
	function QueryGlue(& $seed_object){
		

		
		$this->seed_object = $seed_object;
		
	}		
	
	
///////////////////////SELECT PART//////////////////////



	function build_select(){
		
		
		
		//get column order information
		
		$query = "	SELECT query_columns.*, query_groupbys.id as 'groupby_id', query_calcs.id as 'calc_id'
					FROM query_columns
					LEFT JOIN query_groupbys ON query_groupbys.parent_id = query_columns.id
					LEFT JOIN query_calcs ON query_calcs.parent_id = query_columns.id
					WHERE query_columns.parent_id='".$this->seed_object->id."'
					ORDER BY query_columns.list_order_x
					";
	
		
		
		$result = $this->seed_object->db->query($query,true," Error retrieving display column for glueing: ");

			if($this->seed_object->db->getRowCount($result) > 0){
		
				// Print out the calculation column info
				while($row = $this->seed_object->db->fetchByAssoc($result)){

					
//Column Type Display ///////					
					if(!empty($row['column_type']) && $row['column_type']=="Display"){
					
						//get the corresponding table name for the field, includes custom lookup
						$field_table = $this->seed_object->get_field_table($row['column_module'], $row['column_name']);
						//add piece to the select array
						array_push($this->select_array,$field_table);

						$this->add_to_rel_array($row['column_module']);
						
					//end if column_type is display
					}
					
					
//Column Type Calculation ///////						
					if(!empty($row['column_type']) && $row['column_type']=="Calculation"){

						$column_object = new QueryColumn();
						$calc_object = $column_object->get_calc_object($row['id']);
						$calc_object->get_select_part($this->select_array);
						$this->add_to_rel_array($calc_object->calc_module);
	
					//end if column_type is calculation
					}
					
					
						
//Column Type Group By ///////						
					if(!empty($row['column_type']) && $row['column_type']=="Group By"){
					
						$groupby_object = new QueryGroupBy();
						$groupby_object->retrieve($row['groupby_id']);
						$groupby_object->get_select_part($this->select_array);	
						$this->add_to_rel_array($groupby_object->groupby_module);						
												
					//end if column_type is calculation
					}	
					
						
						
				//end while columns as fetch rows
				}
		
		
			//end if columns exist
			}
	
			
			
	//end function build_select		
	}
	
	
	
	function add_to_rel_array($module_name){
		
			
		if(!empty($module_name) && $module_name!=$this->seed_object->base_module){
			
			
			$this->rel_mod_array[$module_name] = $module_name;
			
		//end if	
		}	
			
	//end function add_to_rel_array	
	}
	
	function glue_select($for_display=false){
		
			$select_part = "";
		
		foreach($this->select_array as $part ){
			
			if(!empty($select_part)){
				$select_part .=",";
			}	
			
			if($for_display==true){
				$select_part .= $part."<BR>";
			} else {
				$select_part .= $part;
			}
		}
		
		
			return $select_part;
		
	//end function glue_select	
	}
	
	
	
	
//end class QueryGlue
}	




?>
