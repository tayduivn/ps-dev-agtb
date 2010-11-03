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



require_once('CustomFieldsTable.php');
require_once('CustomFieldsTableSchema.php');
require_once('FieldsMetaData.php');



define('CUSTOMFIELDSTABLE_CUSTOM_TABLE_SUFFIX', '_cstm');

class EditCustomFields
{
	var $module_name;

	function EditCustomFields($module_name)
	{
		$this->module_name = $module_name;
	}

	function _get_custom_tbl_name()
	{
		return strtolower($this->module_name)
			. CUSTOMFIELDSTABLE_CUSTOM_TABLE_SUFFIX;
	}

	function module_custom_fields()
	{
		global $moduleList;
		$ret_val = array();
		$module_name = $this->module_name;
		if(in_array($module_name, $moduleList))
		{
			$fields_meta_data = new FieldsMetaData();
			$ret_val = $fields_meta_data->select_by_module($module_name);
		}

		return $ret_val;
	}

	function add_custom_field($name, $label, $data_type, $max_size,
		$required_option, $default_value, $deleted, $ext1, $ext2, $ext3, $audited, $mass_update=0, $duplicate_merge=0, $reportable = true)
	{
		$module_name = $this->module_name;

		$fields_meta_data = new FieldsMetaData();
		$fields_meta_data->name = $name;
		$fields_meta_data->label = $label;
		$fields_meta_data->module = $module_name;
		$fields_meta_data->data_type = $data_type;
		$fields_meta_data->max_size = $max_size;
		$fields_meta_data->required_option = $required_option;
		$fields_meta_data->default_value = $default_value;
		$fields_meta_data->deleted = $deleted;
		$fields_meta_data->ext1 = $ext1;
		$fields_meta_data->ext2 = $ext2;
		$fields_meta_data->ext3 = $ext3;
		$fields_meta_data->audited = $audited;
        $fields_meta_data->duplicate_merge = $duplicate_merge;
		$fields_meta_data->mass_update = $mass_update;
		$fields_meta_date->reportable = $reportable;		
		$fields_meta_data->insert();

		$custom_table_name = $this->_get_custom_tbl_name();
		$custom_table_exists =
			CustomFieldsTableSchema::custom_table_exists($custom_table_name);

		$custom_fields_table_schema =
			new CustomFieldsTableSchema($custom_table_name);

		if(!$custom_table_exists)
		{
			$custom_fields_table_schema->create_table();
		}

		$result = $custom_fields_table_schema->add_column($name, $data_type,
			$required_option, $default_value);

		return $result;
	}

	function get_custom_field($id, &$name, &$label, &$data_type, &$max_size,
      &$required_option, &$default_value, &$deleted, &$ext1, &$ext2, &$ext3, &$audited,&$duplicate_merge,&$reportable)
	{
		$fields_meta_data = new FieldsMetaData($id);
		$name = $fields_meta_data->name;
		$label = $fields_meta_data->label;
		$data_type = $fields_meta_data->data_type;
		$max_size = $fields_meta_data->max_size;
		$required_option = $fields_meta_data->required_option;
		$default_value = $fields_meta_data->default_value;
		$deleted = $fields_meta_data->deleted;
		$ext1 = $fields_meta_data->ext1;
		$ext2 = $fields_meta_data->ext2;
		$ext3 = $fields_meta_data->ext3;
		$audited = $fields_meta_data->audited;		
        $duplicate_merge=$fields_meta_data->duplicate_merge;
        $reportable = $fields_meta_data->reportable;
	}

	function edit_custom_field($id, $name, $label, $data_type, $max_size,
		$required_option, $default_value, $deleted, $ext1, $ext2, $ext3, $audited,$duplicate_merge,$reportable)
	{
		$module_name = $this->module_name;

		// update the meta data
		$fields_meta_data = new FieldsMetaData();
		$fields_meta_data->id = $id;
		$fields_meta_data->name = $name;
		$fields_meta_data->label = $label;
		$fields_meta_data->module = $module_name;
		$fields_meta_data->data_type = $data_type;
		$fields_meta_data->max_size = $max_size;
		$fields_meta_data->required_option = $required_option;
		$fields_meta_data->default_value = $default_value;
		$fields_meta_data->deleted = $deleted;
		$fields_meta_data->ext1 = $ext1;
		$fields_meta_data->ext2 = $ext2;
		$fields_meta_data->ext3 = $ext3;
		$fields_meta_data->audited=$audited;
        $fields_meta_data->duplicate_merge=$duplicate_merge;   
        $fields_meta_data->reportable = $reportable;     
		$fields_meta_data->update();

		// update the schema of the custom table
		$custom_table_name = $this->_get_custom_tbl_name();
		$custom_fields_table_schema =
			new CustomFieldsTableSchema($custom_table_name);

		$custom_fields_table_schema->modify_column($name, $data_type,
			$required_option, $default_value);
	}

	function delete_custom_field($id)
	{
		$module_name = $this->module_name;

		$fields_meta_data = new FieldsMetaData($id);
		$column_name = $fields_meta_data->name;
		$fields_meta_data->delete();

		$custom_table_name = $this->_get_custom_tbl_name();
		$custom_fields_table_schema =
			new CustomFieldsTableSchema($custom_table_name);

		$custom_fields_table_schema->drop_column($column_name);
	}
}

?>
