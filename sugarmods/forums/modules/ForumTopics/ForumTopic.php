<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id
 * Description:
 ********************************************************************************/

require_once('data/SugarBean.php');
require_once('include/utils.php');

class ForumTopic extends SugarBean {
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

	var $table_name = "forumtopics";

	var $object_name = "ForumTopic";
	var $module_dir = 'ForumTopics';
	var $new_schema = true;
	
	// non-db fields
	var $can_delete;

	// This is used to retrieve related fields from form posts.
	var $additional_column_fields = Array();

	function ForumTopic() {
		parent::SugarBean();

		$this->disable_row_level_security = true;

	}

	function get_summary_text()
	{
		return "$this->name";
	}

	function bean_implements($interface){
		switch($interface){
			case 'ACL': return false;
		}
		return false;
	}


	function get_topics($add_blank=false)
	{
		$query = "SELECT name FROM $this->table_name where deleted=0 ";
		$query .= " order by list_order asc";
		$result = $GLOBALS['db']->query($query, false);
		$GLOBALS['log']->debug("get_topics");

		$list = array();
		if ($add_blank) {
			$list['']='';
		}
		while (($row = $this->db->fetchByAssoc($result)) != null) {
			$list[$row['name']] = $row['name'];
			$GLOBALS['log']->debug("row name is:".$row['name']);
		}
		return $list;
	}

	function &get_order($category_name)
	{
		$seed = new ForumTopic();
		$query = "SELECT list_order from forumtopics where name='".$GLOBALS['db']->quote($category_name)."' ";
		$result = $GLOBALS['db']->query($query, false);
		$row = $seed->db->fetchByAssoc($result);
		$GLOBALS['log']->debug("get_topics: result is ".$row);
		
		
		return $row['list_order'];
	}
	
	function create_list_query($order_by, $where, $show_deleted = 0)
	{
		$custom_join = $this->custom_fields->getJOIN();
                $query = "SELECT ";
               
                $query .= " $this->table_name.* ";
                if($custom_join){
   				$query .= $custom_join['select'];
 			}
                $query .= " FROM ".$this->table_name." ";
                if($custom_join){
  				$query .= $custom_join['join'];
			}
		$where_auto = '1=1';
				if($show_deleted == 0){
                	$where_auto = "$this->table_name.deleted=0";
				}else if($show_deleted == 1){
                	$where_auto = "$this->table_name.deleted=1";
				}

		if($where != "")
			$query .= "where ($where) AND ".$where_auto;
		else
			$query .= "where ".$where_auto;

		if(!empty($order_by))
			$query .= " ORDER BY $order_by";

		return $query;
	}

	function fill_in_additional_list_fields()
	{
		$this->fill_in_additional_detail_fields();
		
		$res = $GLOBALS['db']->query("select * from forums where category='".$this->name."' and deleted=0");
		$num_rows = $this->db->getRowCount($res);
		$this->can_delete = ($num_rows > 0 ? false : true);
		//echo $this->name." - can delete? - ".($this->can_delete ? "yes" : "no")."<BR>";
	}

	function fill_in_additional_detail_fields() {
		$res = $GLOBALS['db']->query("select * from forums where category='".$this->name."' and deleted=0");
		$num_rows = $this->db->getRowCount($res);
		$this->can_delete = ($num_rows > 0 ? false : true);
	}

	function get_list_view_data(){
		$temp_array = $this->get_list_view_array();
        $temp_array["ENCODED_NAME"]=$this->name;
//	$temp_array["ENCODED_NAME"]=htmlspecialchars($this->name, ENT_QUOTES);
    	return $temp_array;

	}
	/**
		builds a generic search based on the query string using or
		do not include any $this-> because this is called on without having the class instantiated
	*/
	function build_generic_where_clause ($the_query_string) {
	$where_clauses = Array();
	$the_query_string = PearDatabase::quote(from_html($the_query_string));
	array_push($where_clauses, "name like '$the_query_string%'");

	$the_where = "";
	foreach($where_clauses as $clause)
	{
		if($the_where != "") $the_where .= " or ";
		$the_where .= $clause;
	}


	return $the_where;
}


}

?>
