<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
* $Id: SqlsrvHelper.php 16822 2006-09-26 17:37:32Z ajay $
* Description: This file handles the Data base functionality for the application specific
* to SQL Server database using the php_sqlsrv extension. It is called by the DBManager class to generate various sql statements.
*
* All the functions in this class will work with any bean which implements the meta interface.
* Please refer the DBManager documentation for the details.
*
* Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
* All Rights Reserved.
* Contributor(s): ______________________________________..
********************************************************************************/
require_once('include/database/MssqlHelper.php');

class SqlsrvHelper extends MssqlHelper
{
	/**
     * @see DBHelper::getColumnType()
     */
    public function getColumnType(
        $type, 
        $name = '', 
        $table = ''
        )
    {
		$columnType = parent::getColumnType($type,$name,$table);
        
		if ( in_array($columnType,array('char','varchar')) )
			$columnType = 'n'.$columnType;
		
        return $columnType;
    }
	
	/**
	 * @see DBHelper::massageValue()
	 */
	public function massageValue(
        $val, 
        $fieldDef
        )
    {
        $type = $this->getFieldType($fieldDef);
        
		switch ($type) {
		case 'int':
		case 'double':
		case 'float':
		case 'uint':
		case 'ulong':
		case 'long':
		case 'short':
		case 'tinyint':
            return $val;
            break;
        }
        
        $qval = $this->quote($val);

        switch ($type) {
        case 'varchar':
        case 'nvarchar':
        case 'char':
        case 'nchar':
        case 'enum':
        case 'multienum':
        case 'id':
            return $qval;
            break;
        case 'date':
            return "$qval";
            break;
        case 'datetime':
            return $qval;
            break;
        case 'time':
            return "$qval";
            break;
        case 'text':
        case 'ntext':		  
        case 'blob':
        case 'longblob':
        case 'clob':
        case 'longtext':
        case 'image':
            return $qval;
            break;
		}
        
        return $val;
	}
	
	/**
	 * Detect if no clustered index has been created for a table; if none created then just pick the first index and make it that
	 *
	 * @see MssqlHelper::indexSQL()
     */
    public function indexSQL( 
        $tableName, 
        $fieldDefs, 
        $indices
        ) 
    {
        foreach ( $indices as $index ) {
            if ( $index['type'] == 'primary' ) {
                return parent::indexSQL($tableName, $fieldDefs, $indices); 
            }
        }
        
        // Change the first index listed to be a clustered one instead ( so we have at least one for the table )
        if ( isset($indices[0]) ) {
            $indices[0]['type'] = 'clustered';
        }
        else {
            $GLOBALS['log']->warning("Table '$tablename' has no indices defined; this could be a problem on SQL Server.");
        }
        
        return parent::indexSQL($tableName, $fieldDefs, $indices); 
    }
    
    /**
     * @see DBHelper::get_indices()
     */
    public function get_indices(
        $tablename
        ) 
    {
        //find all unique indexes and primary keys.
        $query = <<<EOSQL
SELECT LEFT(so.name, 30) TableName, 
        LEFT(si.name, 50) 'Key_name',
        LEFT(sik.key_ordinal, 30) Sequence, 
        LEFT(sc.name, 30) Column_name,
		si.is_unique isunique
    FROM sys.indexes si
        INNER JOIN sys.index_columns sik 
            ON (si.object_id = sik.object_id AND si.index_id = sik.index_id)
        INNER JOIN sys.objects so 
            ON si.object_id = so.object_id
        INNER JOIN sys.columns sc 
            ON (so.object_id = sc.object_id AND sik.column_id = sc.column_id)
    WHERE so.name = '$tablename'
    ORDER BY Key_name, Sequence, Column_name
EOSQL;
        $result = $this->db->query($query);
        
        $indices = array();
        while (($row=$this->db->fetchByAssoc($result)) != null) {
            $index_type = 'index';
            if ($row['Key_name'] == 'PRIMARY')
                $index_type = 'primary';
            elseif ($row['isunique'] == 1 )
                $index_type = 'unique';
            $name = strtolower($row['Key_name']);
            $indices[$name]['name']     = $name;
            $indices[$name]['type']     = $index_type;
            $indices[$name]['fields'][] = strtolower($row['Column_name']);
        }
        return $indices;
    }
}
?>
