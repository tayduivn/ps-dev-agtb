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
/* Include all other system or application files that you need to reference here.*/
   /*Include this file if you want to access sugar specific settings*/

require_once('data/SugarBean.php'); /*Include this file since we are extending SugarBean*/
require_once('include/utils.php'); /* Include this file if you want access to Utility methods such as return_module_language,return_mod_list_strings_language, etc ..*/

require_once('modules/Threads/Thread.php'); // for pulling parent forum

class Post extends SugarBean {
	/* Foreach instance of the bean you will need to access the fields in the table.
	 * So define a variable for each one of them, the varaible name should be same as the field name
	 * Use this module's vardef file as a reference to create these variables.
	 */
	var $name;
	var $id;
	var $date_entered;
	var $created_by;
	var $date_modified;
	var $modified_user_id;
	var $deleted;
	var $title;
    var $description_html;
    var $thread_id;

    // non-db
    var $created_by_user;
    var $modified_by_user;
    var $thread_name;
    
	/* End field definitions*/

	/* variable $table_name is used by SugarBean and methods in this file to constructs queries
	 * set this variables value to the table associated with this bean.
	 */
	var $table_name = 'posts';
	
	/*This  variable overrides the object_name variable in SugarBean, wher it has a value of null.*/
	var $object_name = 'Posts';
	
	/**/
	var $module_dir = 'Posts';
	
	/* This is a legacy variable, set its value to true for new modules*/
	var $new_schema = true;

	/* $column_fields holds a list of columns that exist in this bean's table. This list is referenced
	 * when fetching or saving data for the bean. As you modify a table you need to keep this up to date.
	 */
	var $column_fields = Array(
			'id',
            'title',
            'description_html',
            'thread_id',
    );
    
	// This is used to retrieve related fields from form posts.
//	var $additional_column_fields = Array('account_id','bug_id');
//	var $relationship_fields = Array('account_id'=>'parent_id', 'bug_id'=>'parent_id', );

	/* This is the list of required fields, It is used by some of the utils methods to build the required fields validation JavaScript */
	/* The script is only generated for the Edit View*/
	var $required_fields =  array('title'=>1);

	/*This bean's constructor*/
	function Post() {
		/*Call the parent's constructor which will setup a database connection, logger and other settings.*/
		parent::SugarBean();
		//BEGIN SUGARCRM flav=pro ONLY 
		$this->disable_row_level_security=true;
		//END SUGARCRM flav=pro ONLY 
		
		
	}

	/* This method should return the summary text which is used to build the bread crumb navigation*/
	/* Generally from this method you would return value of a field that is required and is of type string*/ 
	function get_summary_text()
	{
		return "$this->title";
	}

	function bean_implements($interface){
		switch($interface){
			case 'ACL': return true;
		}
		return false;
	}


	/* This method is used to generate query for the list form. The base implementation of this method
	 * uses the table_name and list_field varaible to generate the basic query and then  adds the custom field
	 * join and team filter. If you are implementing this function do not forget to consider the additional conditions.
	 */
	function create_list_query($order_by, $where)
	{
		//Build the join condition for custom fields, the custom field array was populated
		//when you invoked the constructor for the SugarBean.
		$custom_join = $this->custom_fields->getJOIN();
        
   		//Build the select list for the query. 
        $query = "SELECT posts.* ";

		//If custom fields exist append the select list here.
        if($custom_join){
			$query .= $custom_join['select'];
		}
		
		//append the WHERE clause to the $query string.
        $query .= " FROM posts ";

		//Add custom fields join condition.
		if($custom_join){
			$query .= $custom_join['join'];
		}

		//Append additional filter conditions.
    if($_REQUEST['module'] == 'Threads')
      $where_auto = " (posts.deleted=0 and posts.thread_id='".$GLOBALS['db']->quote($_REQUEST['record'])."')";
    else
  		$where_auto = " (posts.deleted=0)";

		//if the function recevied a where clause append it.
		if($where != "")
			$query .= "where $where AND ".$where_auto;
		else	
			$query .= "where ".$where_auto;

		//append the order by clause.
		if($order_by != "")
			$query .= " ORDER BY $order_by";
		else
			$query .= " ORDER BY posts.title";

		return $query;
	}

	function create_export_query()
	{
		return $this->create_list_query();
	}
    
    function fill_in_additional_list_fields()
    {
      $this->created_by_user = get_assigned_user_name($this->created_by);
      $this->modified_by_user = get_assigned_user_name($this->modified_user_id);
      
      // pulls the parent forum
      $parent_thread = new Thread();
      $parent_thread->retrieve($this->thread_id);
      // retreive automatically handles team security
      $this->thread_name = $parent_thread->title;
    }
    
    function build_generic_where_clause($title, $body = "", $user = "")
    {
      $where = "deleted=0 ";
      $where .= "and title like ('%".$GLOBALS['db']->quote($title)."%') ";
      if($body != "")
        $where .= " and description_html like ('%".$GLOBALS['db']->quote($body)."%') ";
      
      if (isset($user) && is_array($user) && !(count($user) == 1 && $user[0] == ""))
      {
        $count = count($user);
        if ($count > 0 ){
          $where .= " AND ";
          $where .= "created_by IN(";
          foreach ($user as $key => $val) {
            $where .= "'$val'";
            $where .= ($key == $count - 1) ? ")" : ", ";
          }
        }
      }
      
      return $where;
    }
}
?>
