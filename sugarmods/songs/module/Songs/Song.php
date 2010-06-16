<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: Song.php 56115 2010-04-26 17:08:09Z kjing $
 * Description: The primary Function of this file is to manage all the data
 * used by other files in this nodule. It should extend the SugarBean which impelments
 * all the basic database operations. Any custom behaviors can be implemented here by
 * implemeting functions available in the SugarBean.
 ********************************************************************************/

/* Include all other system or application files that you need to reference here.*/
include_once('config.php');   /*Include this file if you want to access sugar specific settings*/
require_once('data/SugarBean.php'); /*Include this file since we are extending SugarBean*/
require_once('include/utils.php'); /* Include this file if you want access to Utility methods such as return_module_language,return_mod_list_strings_language, etc ..*/

class Song extends SugarBean {
	/* Foreach instance of the bean you will need to access the fields in the table.
	 * So define a variable for each one of them, the varaible name should be same as the field name
	 * Use this module's vardef file as a reference to create these variables.
	 */
	var $id;
	var $date_entered;
	var $created_by;
	var $date_modified;
	var $modified_by;
	var $deleted;
	var $title;
	var $length;
	var $description;
	var $bitrate;
	var $explicit;
	var $genre;
	var $format;

	/* End field definitions*/

	/* variable $table_name is used by SugarBean and methods in this file to constructs queries
	 * set this variables value to the table associated with this bean.
	 */
	var $table_name = 'songs';

	/*This  variable overrides the object_name variable in SugarBean, wher it has a value of null.*/
	var $object_name = 'Song';

	/**/
	var $module_dir = 'Songs';

	/* This is a legacy variable, set its value to true for new modules*/
	var $new_schema = true;

	/* $column_fields holds a list of columns that exist in this bean's table. This list is referenced
	 * when fetching or saving data for the bean. As you modify a table you need to keep this up to date.
	 */
	var $column_fields = Array(
			'id'
			,'title'
			,'length'
			,'description'
			,'bitrate'
			,'explicit'
			,'genre'
			,'format'
	);

	// This is used to retrieve related fields from form posts.
	var $additional_column_fields = Array('contact_id','product_id');
	var $relationship_fields = Array('contact_id'=>'artists', 'product_id'=>'albums',
							   );

	/* List forms usually show less data than the detail forms, this list is used to construct data for list forms.
	 * Fields in this list need not be database fields only, if you have some computed fields that go in the list add
	 * them to this list. Also create a variable in the bean.

	 var $list_fields = array('id', 'field1', 'field2', 'field3', 'field4');
	 todo remove this, since the system uses vardefs to achieve this*/

	/* This is the list of required fields, It is used by some of the utils methods to build the required fields validation JavaScript */
	/* The script is only generated for the Edit View*/
	var $required_fields =  array('title'=>1);

	/*This bean's constructor*/
	function Song() {
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
        $query = "SELECT ";
        $query .= " songs.* ";

		//If custom fields exist append the select list here.
        if($custom_join){
			$query .= $custom_join['select'];
		}

		//append the WHERE clause to the $query string.
        $query .= " FROM songs ";

		//Add custom fields join condition.
		if($custom_join){
			$query .= $custom_join['join'];
		}

		//Append additional filter conditions.
		$where_auto = " (songs.deleted=0)";

		//if the function recevied a where clause append it.
		if($where != "")
			$query .= "where $where AND ".$where_auto;
		else
			$query .= "where ".$where_auto;

		//append the order by clause.
		if($order_by != "")
			$query .= " ORDER BY $order_by";
		else
			$query .= " ORDER BY songs.title";

		return $query;
	}

	function create_export_query()
	{
		return $this->create_list_query();
	}
}
?>
