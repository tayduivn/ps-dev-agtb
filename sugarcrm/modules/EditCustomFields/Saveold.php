<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/




require_once('modules/EditCustomFields/CustomFieldsTableSchema.php');

$fields_meta_data = new FieldsMetaData();

////
//// save the metadata to the fields_meta_data table
////

foreach($fields_meta_data->column_fields as $field)
{
	if(isset($_REQUEST[$field]))
	{
		$fields_meta_data->$field = $_REQUEST[$field];
	}
}

$fields_meta_data->save();

////
//// create/modify the custom field table
////

$new_field = empty($_REQUEST['id']);
$new_field = true;

$custom_table_name = strtolower($fields_meta_data->custom_module) . '_cstm';
$custom_fields_table_schema = new
	CustomFieldsTableSchema($custom_table_name);
if(!CustomFieldsTableSchema::custom_table_exists($custom_table_name))
{
	$custom_fields_table_schema->create_table();
}

$column_name = $fields_meta_data->name;
$field_label = $fields_meta_data->label;
$data_type = $fields_meta_data->data_type;
$max_size = $fields_meta_data->max_size;
$required = $fields_meta_data->required_option;
$default_value = $fields_meta_data->default_value;

$module_dir = $fields_meta_data->custom_module;

if($new_field)
{
	$custom_fields_table_schema->add_column($column_name, $data_type,
		$required, $default_value);

	$class_name = $beanList[$fields_meta_data->custom_module];
	$custom_field = new DynamicField($fields_meta_data->custom_module);
	require_once("modules/$module_dir/$class_name.php");
	$sugarbean_module = new $class_name();
	$custom_field->setup($sugarbean_module);

	$custom_field->addField($field_label, $data_type, $max_size, 'optional',
		$default_value, '', '');
}






if(isset($_REQUEST['form']))
{
	// we are doing the save from a popup window
	echo '<script>opener.window.location.reload();self.close();</script>';
	die();
}
else
{
	// need to refresh the page properly

	$return_module = empty($_REQUEST['return_module']) ? 'EditCustomFields'
		: $_REQUEST['return_module'];

	$return_action = empty($_REQUEST['return_action']) ? 'index'
		: $_REQUEST['return_action'];

	$return_module_select = empty($_REQUEST['return_module_select']) ? 0
		: $_REQUEST['return_module_select'];

	header("Location: index.php?action=$return_action&module=$return_module&module_select=$return_module_select");

}
?>
