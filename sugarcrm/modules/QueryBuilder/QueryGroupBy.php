<?php
if (! defined('sugarEntry') || ! sugarEntry) die('Not A Valid Entry Point');
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
 * $Id: QueryGroupBy.php 45763 2009-04-01 19:16:18Z majed $
 * Description:
 ********************************************************************************/
require_once ('modules/QueryBuilder/QueryBuilder.php');


// ProductTemplate is used to store customer information.
class QueryGroupBy extends QueryBuilder
{
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
    //construction for y axis
    var $column_name;
    var $column_module;
    var $column_type;
    //general construction
    var $parent_id;
    var $groupby_axis;
    var $groupby_type;
    var $groupby_calc_module;
    var $groupby_calc_field;
    var $groupby_module;
    var $groupby_field;
    var $groupby_calc_type;
    var $groupby_qualifier_qty;
    var $groupby_qualifier;
    var $groupby_qualifier_start;
    var $list_order_x;
    var $list_order_y;
    var $table_name = "query_groupbys";
    var $module_dir = "QueryBuilder";
    var $object_name = "QueryGroupBy";
    var $new_schema = true;
	public $disable_row_level_security = true;
    var $column_fields = Array("id" , "date_entered" , "date_modified" , "modified_user_id" , "created_by" , "groupby_axis" , "groupby_type" , "groupby_calc_module" , "groupby_calc_field" , "groupby_module" , "groupby_field" , "groupby_calc_type" , "groupby_qualifier_qty" , "groupby_qualifier" , "groupby_qualifier_start" , "list_order_x" , "list_order_y" , "parent_id");
    // This is used to retrieve related fields from form posts.
    var $additional_column_fields = Array("column_name" , "column_type" , "column_module");
    // This is the list of fields that are in the lists.
    var $list_fields = array();
    // This is the list of fields that are required
    var $required_fields = array("column_name" => 1);
    //Controller Array for list_order stuff
    var $controller_def = Array("list_x" => "Y" , "list_y" => "Y" , "parent_var" => "parent_id" , "start_var" => "list_order_x" , "start_axis" => "x");
    function get_summary_text ()
    {
        return "$this->name";
    }
    /** Returns a list of the associated product_templates
     * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
     * All Rights Reserved.
     * Contributor(s): ______________________________________..
     */

    function create_export_query (&$order_by, &$where)
    {
    }
    function save_relationship_changes ($is_update)
    {
    }
    function mark_relationships_deleted ($id)
    {
    }
    function fill_in_additional_list_fields ()
    {
    }
    function fill_in_additional_detail_fields ()
    {
    }
    function clear_deleted ()
    {
        //if this has a column, delete the column too
        if ($this->groupby_axis == "Columns")
        {
            $query = "delete from query_columns where id='$this->parent_id' and deleted=0";
            $this->db->query($query, true, "Error deleting groupby columns: ");
        }
        $query = "delete from query_groupbys where id='$this->id' and deleted=0";
        $this->db->query($query, true, "Error deleting groupby row: ");
        //end function clear_groupby
    }
    function get_list_view_data ()
    {
    }
    function build_generic_where_clause ($the_query_string)
    {
    }
    function retrieve_groupby_display (& $xtemplate_object, $block_name, $main_block_name = "main")
    {
        $temp_groupby_count = 1;
        // First, get the list of columns currently in query
        $query = "SELECT $this->table_name.*, query_columns.id as 'column_record' from $this->table_name
					LEFT JOIN query_columns ON query_columns.id = $this->table_name.parent_id 
					where $this->table_name.parent_id='$this->parent_id'
					 AND $this->table_name.deleted=0
					 ORDER BY $this->table_name.list_order_x, $this->table_name.list_order_y
					 ";
        $result = $this->db->query($query, true, " Error retrieving display group by fields: ");
        if ($this->db->getRowCount($result) > 0) 
        {
            // Print out the columns
            while ($row = $this->db->fetchByAssoc($result)) 
            {
                //$groupby_table = $this->get_module_info($row['groupby_module']);
                $groupby_bean = $this->get_module_info($row['groupby_module']);
                //run query2 more than once if you have additional group by fields
                for ($i = 0; $i < $temp_groupby_count; $i ++) 
                {
                    if (! empty($row['groupby_type']) && $row['groupby_type'] == "Time") 
                    {
                        $time_count = $this->get_time_info($row, $xtemplate_object, $main_block_name, $block_name, $temp_groupby_count);
                    } 
                    else 
                    {
                        /*
				//THIS IS MOVED- CAN REMOVE THIS AREA - MAYBE Move the below logic into the QueryBuilder module, because this can be used for other stuff as well.									
				//Check to see if the groupby_field is a custom field or not
				global $dictionary;
				if(!empty($dictionary[$groupby_bean->object_name]['fields'][$row['groupby_field']]['custom_type'])){
				//field is present in the module's custom table.  Retrieve this table and use as query	
					$custom_join = $groupby_bean->custom_fields->getJOIN();	
					$field_select = $groupby_bean->table_name."_cstm.".$row['groupby_field'];
		
				} 
                else 
                {
				//field is not custom and present in module table		
					$field_select = $groupby_bean->table_name.".".$row['groupby_field'];
				}
				*/
                        $field_select = $this->get_field_table($row['groupby_module'], $row['groupby_field']);
                        $query2 = "	SELECT $field_select
									from $groupby_bean->table_name";
                        if (isset($custom_join)) 
                        {
                            $query2 .= $custom_join['join'];
                        }
                        $query2 .= " 	where $groupby_bean->table_name.deleted=0
					 				GROUP BY $field_select;
					 			";
                        $result2 = $this->db->query($query2, true, " Error retrieving display group by fields: ");
                        if ($this->db->getRowCount($result2) > 0) 
                        {
                            $height_count = $this->db->getRowCount($result2) * $temp_groupby_count;
                            $row_height = 100 / $height_count;
                            //Fields so you can edit an existing groupby
                            $xtemplate_object->assign("DISPLAY_GROUPBY_FIELD", $row['groupby_field']);
                            $xtemplate_object->assign("DISPLAY_COLSPAN", $this->db->getRowCount($result2));
                            //$xtemplate_object->assign("DISPLAY_GROUPBY_MODULE", $row['groupby_module']);
                            $xtemplate_object->assign("GROUPBY_RECORD", $row['id']);
                            $xtemplate_object->assign("COLUMN_RECORD", $row['column_record']);
                            // Print out the columns
                            while ($row2 = $this->db->fetchByAssoc($result2)) 
                            {
                                $groupby_field = $row2[$row['groupby_field']];
                                if (empty($groupby_field))
                                    $groupby_field = "<em>blank</em>";
                                $xtemplate_object->assign("GROUPBY_HEIGHT", $row_height);
                                $xtemplate_object->assign("GROUPBY_FIELD", $groupby_field);
                                $xtemplate_object->parse($main_block_name . "." . $block_name . ".field");
                                //end while query2
                            }
                            //end if query2
                        }
                        //end if this is not a groupby time and this is standard group by
                    }
                    //end the for loop		
                }
                if (! empty($time_count)) 
                {
                    $new_count = $time_count;
                    //echo "THERE";
                    $time_count = null;
                } 
                else 
                {
                    //echo "HERE";
                    $new_count = $this->db->getRowCount($result2);
                }
                //echo "COUNT BEFORE".$temp_groupby_count."<BR>";
                //echo "COUNT NEW".$new_count."<BR>";
                $temp_groupby_count = $new_count * $temp_groupby_count;
                //echo "COUNT".$temp_groupby_count."<BR>";
                $xtemplate_object->parse($main_block_name . "." . $block_name);
                //end while
            }
            //end if data exists
        }
        //end function retrieve_column_display
    }
    function check_groupby_type ()
    {
        //this function checks the groupby_type and clears out the appropriate fields
        if ($this->groupby_axis == "Rows") 
        {
            $this->groupby_calc_type = "";
            $this->groupby_calc_field = "";
            $this->groupby_calc_module = "";
        }
        if ($this->groupby_type != "Time") 
        {
            $this->groupby_qualifier = "";
            $this->groupby_qualifier_start = "";
            $this->groupby_qualifier_qty = "";
        }
        //end function check_groupby_type
    }
    ///////////////time interval library information
    function get_time_info (& $groupby_array, & $xtemplate_object, $main_block_name, $block_name, $height_count)
    {
        //$this->groupby_qualifier;
        //$this->groupby_qualifier_qty;
        //$this->groupby_qualifier_start;
        $height_count = $groupby_array['groupby_qualifier_qty'] * $height_count;
        $row_height = 100 / $height_count;
        for ($i = 0; $i < $groupby_array['groupby_qualifier_qty']; $i ++) 
        {
            $groupby_field = $groupby_array['groupby_qualifier'] . "-" . $i;
            $xtemplate_object->assign("GROUPBY_HEIGHT", $row_height);
            $xtemplate_object->assign("GROUPBY_FIELD", $groupby_field);
            $xtemplate_object->assign("DISPLAY_COLSPAN", $groupby_array['groupby_qualifier_qty']);
            $xtemplate_object->assign("GROUPBY_RECORD", $groupby_array['id']);
            $xtemplate_object->assign("COLUMN_RECORD", $groupby_array['column_record']);
            $xtemplate_object->parse($main_block_name . "." . $block_name . ".field");
        }
        return $groupby_array['groupby_qualifier_qty'];
        //end function get_time_info
    }
    ////BUILDING THE QUERY PARTS//////////
    function get_select_part (& $select_array)
    {
        global $db;
        if (! empty($this->groupby_type) && $this->groupby_type == "Time")
        {
            //Calculate out time interval parts	
            //default mulitplier value
            $interval_multiplier = 1;
            $qualifier = $this->groupby_qualifier;
            if ($this->groupby_qualifier == "Week") 
            {
                //there is no week extractor in mysql, so you have to convert
                $interval_multiplier = 7;
                $qualifier = "DAY";
            }
            if ($this->groupby_qualifier == "Quarter") 
            {
                //Still need to calculate this ---
            }
            //This is to calculate the date interval correctly.  When you have week, you have to convert to day
            //and say that you have a interval multiplier of 7 for example	
            $start_interval = $this->groupby_qualifier_start * $interval_multiplier;
            $next_interval = $start_interval + $interval_multiplier;
            //get the field table data for the time interval calculation field
            $field_table = $this->get_field_table($this->groupby_module, $this->groupby_field);
            $calc_field_table = $this->get_field_table($this->groupby_calc_module, $this->groupby_calc_field);
            //Deal with the groupby type, like SUM ,AVG, min, max etc.
            //If the group_by_cal_type is COUNT, then still do SUM, but have the calc_field_table evaluate to 1
            if (! empty($this->groupby_calc_type) && $this->groupby_calc_type == "COUNT") 
            {
                $calc_field_table = 1;
                $groupby_calc_type = "SUM";
            } 
            else 
            {
                $groupby_calc_type = $this->groupby_calc_type;
            }
            for ($i = 0; $i < $this->groupby_qualifier_qty; $i ++) 
            {
                //DATEADD(m, 5, GETDATE())
                $dateexpr = "$field_table >= ".$db->convert($db->convert('', "today"), "add_date", array($start_interval, $qualifier)).
                        " AND $field_table < ".$db->convert($db->convert('', "today"), "add_date", array($next_interval, $qualifier));
                $select_part = $groupby_calc_type . "(IF($dateexpr, $calc_field_table, 0)) as ".$db->quoted($this->groupby_qualifier);
                $start_interval = $next_interval;
                $next_interval = $next_interval + $interval_multiplier;
                array_push($select_array, $select_part);
                //end qualifier qty for loop
            }
            //end if the groupby_type is time
        }
        //end function get_select_part
    }
}
?>
