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

include_once('include/database/MssqlHelper.php');

class FreeTDSHelper extends MssqlHelper 
{
    /**
	 * @see DBHelper::massageValue()
	 */
	public function massageValue(
        $val, 
        $fieldDef
        )
    {
        if (!$val) 
            return "''";
        
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
        case 'longtext':
        case 'text':
        case 'ntext':		  
        case 'enum':
        case 'multienum':
        case 'blob':
        case 'longblob':
        case 'clob':
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
        }
        
        return $val;
	}	
    
    /** 
     * Returns the valid type for a column given the type in fieldDef
     *
     * @param  string $type field type
     * @param  string $name field name
     * @param  string $table table name
     * @return string valid type for the given field
     */
    public function getColumnType(
        $type, 
        $name='', 
        $table=''
        )
    {
		$map = array( 
            'int'      => 'int',
            'double'   => 'float',
            'float'    => 'float',
            'uint'     => 'int',
            'ulong'    => 'int',
            'long'     => 'bigint',
            'short'    => 'smallint',
            'varchar'  => 'nvarchar',
            'nvarchar' => 'nvarchar',
            'longtext' => 'ntext',
            'text'     => 'ntext',
            'ntext'    => 'ntext',
            'date'     => 'datetime',
            'enum'     => 'nvarchar',
            'multienum'=> 'ntext',
            'datetime' => 'datetime',
            'datetimecombo' => 'datetime',
            'time'     => 'datetime',
            'bool'     => 'bit',
            'tinyint'  => 'tinyint',
            'char'     => 'char',
            'nchar'    => 'nchar',
            'blob'     => 'ntext',
            'longblob' => 'ntext',
            'decimal'  => 'decimal',
            'decimal2' => 'decimal',
            'currency' => 'decimal(26,6)',
            'id'       => 'nvarchar(36)',
            'url'=>'nvarchar',
            'encrypt'=>'nvarchar',
            );
            
        return $map[$type];
    }
    
    /**
     * @see DBHelper::oneColumnSQLRep()
     */
	protected function oneColumnSQLRep(
        $fieldDef,
        $ignoreRequired = false,
        $table = '',
        $return_as_array = false
        )
    {
        $ref = parent::oneColumnSQLRep($fieldDef,$ignoreRequired,$table,true);
        
        
        if ( $ref['colType'] == 'nvarchar' 
                || $ref['colType'] == 'nchar' ) {
            if( !empty($fieldDef['len']))
                $ref['colType'] .= "(".$fieldDef['len'].")";
            else 
                $ref['colType'] .= "(255)";
        }
   
        if ( $return_as_array )
            return $ref;
        else
            return "{$ref['name']} {$ref['colType']} {$ref['default']} {$ref['required']} {$ref['auto_increment']}";
    }

}

?>
