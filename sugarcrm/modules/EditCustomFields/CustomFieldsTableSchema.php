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






class CustomFieldsTableSchema
{
	var $db;
	var $table_name;

	function CustomFieldsTableSchema($tbl_name = '')
	{
		global $db;
		$this->db = $db;
		$this->table_name = $tbl_name;
	}

	function _get_column_definition($col_name, $type, $required, $default_value)
	{
		$ret_val = "$col_name $type";
		if($required)
		{
			$ret_val .= ' NOT NULL';
		}

		if(!empty($default_value))
		{
			$ret_val .= " DEFAULT '$default_value'";
		}

		return $ret_val;
	}

	function create_table()
	{
		$column_definition = $this->_get_column_definition('id', 'varchar(100)',
			true, '');
		$query = "CREATE TABLE {$this->table_name} ($column_definition);";

		$result = $this->db->query($query, true,
			'CustomFieldsTableSchema::create_table');

		return $result;
	}

	function add_column($column_name, $data_type, $required, $default_value)
	{
		$column_definition = $this->_get_column_definition($column_name,
			$data_type,
			$required, $default_value);

		$query = "ALTER TABLE {$this->table_name} "
			. "ADD COLUMN $column_definition;";

		$result = $this->db->query($query, true,
			'CustomFieldsTableSchema::add_column');

		return $result;
	}

	function modify_column($column_name, $data_type, $required, $default_value)
	{
		$column_definition = $this->_get_column_definition($column_name,
			$data_type, $required, $default_value);

		$query = "ALTER TABLE {$this->table_name} "
			. "MODIFY COLUMN $column_definition;";

		$result = $this->db->query($query, true,
			'CustomFieldsTableSchema::modify_column');

		return $result;
	}

	function drop_column($column_name)
	{
		$query = "ALTER TABLE $this->table_name "
			. "DROP COLUMN $column_name;";

		$result = $this->db->query($query, true,
			'CustomFieldsTableSchema::drop_column');

		return $result;
	}

	function _get_custom_tables()
	{
		$pattern = '%' . CUSTOMFIELDSTABLE_CUSTOM_TABLE_SUFFIX;
		
        if ($this->db){
            if ($this->db->dbType == 'mysql'){
                $result = $this->db->query("SHOW TABLES LIKE '".$pattern."'");
                $rows=$this->db->fetchByAssoc($result);
                return $rows;                
            }else if ($this->dbType == 'oci8') {
               $sql = "select TABLE_NAME from user_tables where upper(table_name) like '".strtoupper($pattern)."'";
               $result = $this->db->query($sql);
               $rows = $this->db->fetchByAssoc($result);
               return $rows['count'];
            }
        }
        return false;
	}

	/**
	 * @static
	 */
	function custom_table_exists($tbl_name)
	{
		$db = DBManagerFactory::getInstance();
		return 	$db->tableExists($tbl_name);		
	}
}

?>
