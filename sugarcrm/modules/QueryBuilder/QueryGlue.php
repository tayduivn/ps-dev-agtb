<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/*********************************************************************************

 * Description:
 ********************************************************************************/

//Library of functions used to compile the query for run

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
	
    public function __construct(& $seed_object)
    {
		

		
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
