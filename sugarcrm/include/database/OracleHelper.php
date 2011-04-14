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
* $Id: OracleHelper.php 56133 2010-04-28 02:09:10Z jmertic $
* Description: This file handles the Data base functionality for the application specific
* to oracle database. It is called by the DBManager class to generate various sql statements.
*
* All the functions in this class will work with any bean which implements the meta interface.
* Please refer the DBManager documentation for the details. 
* 
* Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
* All Rights Reserved.
* Contributor(s): ______________________________________..
********************************************************************************/

//FILE SUGARCRM flav=ent ONLY
require_once('include/database/DBHelper.php');

class OracleHelper extends DBHelper 
{
    /**
     * returns SQL to create constraints or indices
     *
     * @param  object $bean SugarBean instance
     * @return string SQL statement
     */
	public function createConstraintSql(
        SugarBean &$bean
        ) 
    {
		return $this->getConstraintSql($bean->getIndices(), $bean->getTableName());
	}
    
	/**
     * @see DBHelper::createTableSQLParams()
	 */
	public function createTableSQLParams(
        $tablename, 
        $fieldDefs, 
        $indices,
        $engine = null
        )
    {
        $columns = $this->columnSQLRep($fieldDefs, false, $tablename);
        if(empty($columns))
 			return false;

        return "CREATE TABLE $tablename ($columns)";
	}

    /**
	 * @see DBHelper::massageValue()
	 */
	public function massageValue(
        $val, 
        $fieldDef
        )
    {
        if ($val === 0) 
            return $val;

        // escape all quotes in oracle style
        $val = str_replace("'", "''", $val);

        switch ($this->getFieldType($fieldDef)){
        case 'int':
        case 'double':
        case 'float':
        case 'uint':
        case 'ulong':
        case 'long':
        case 'short':
        case 'bool':
            if (!empty($fieldDef['required']) && $fieldDef['required'] == true && $val == ''){
                if (isset($fieldDef['default'])){
                    return $fieldDef['default'];
                }
                return 0;	
            }
            if ($val == '') 
                return "''";		  	
            return $val;
            break;
        case 'varchar':
        case 'enum':
        case 'char':
        case 'id':
            return "'$val'";
            break;
        case 'blob':
        case 'longblob': 
            return "EMPTY_BLOB()";
            break;
        case 'multienum':
        case 'longtext':   	
        case 'text':                  
        case 'clob':
        case 'html':
            return "EMPTY_CLOB()";
            break;
        case 'date':
            $val = explode(" ", $val); // make sure that we do not pass the time portion
            $val = $val[0];            // get the date portion
            return "TO_DATE('$val', 'YYYY-MM-DD')";
            break;
        case 'datetime':
            return "TO_DATE('$val', 'YYYY-MM-DD HH24:MI:SS')";
            break;
        case 'time':
            return "TO_DATE('$val', 'HH24:MI:SS')";
            break;
        default:
            if (!$val or $val == '') 
                return "''";
            return $val;
            break;
		}
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
		//Bug 25814
		if(isset($fieldDef['name'])){
			$name = $fieldDef['name'];
	        $type = $this->getFieldType($fieldDef);
	        $colType = $this->getColumnType($type, $name, $table);
	    	if(stristr($colType, 'decimal')){
				$fieldDef['len'] = min($fieldDef['len'],38);
			}
		}
		
		return parent::oneColumnSQLRep($fieldDef, $ignoreRequired, $table, $return_as_array);
	}
	
	/**
	 * returns true if the field is nullable
	 *
	 * @param  string $tableName
	 * @param  string $fieldName
	 * @return bool
	 */
	private function isNullable(
		$tableName,
		$fieldName
		)
	{
		return $this->db->getOne(
            "SELECT nullable FROM user_tab_columns 
				WHERE TABLE_NAME = '".strtoupper($tableName)."'
					AND COLUMN_NAME = '".strtoupper($fieldName)."'") == 'Y';
	}
	 
	/**
     * @see DBHelper::getColumnType()
     */
    public function getColumnType(
        $type, 
        $name = '', 
        $table = ''
        )
    {
        $map = array( 
            'int'      => 'number',
            'double'   => 'number(30,10)',
            'float'    => 'number(30,6)',
            'uint'     => 'number(15)',
            'ulong'    => 'number(38)',
            'long'     => 'number(38)',
            'short'    => 'number(3)',
            'varchar'  => 'varchar2',
            'text'     => 'clob',
            'longtext' => 'clob',
            'date'     => 'date',
            'enum'     => 'varchar2(255)',
            'relate'     => 'varchar2',
            'multienum'=> 'clob',
            'html'     => 'clob',
            'datetime' => 'date',
            'datetimecombo' => 'date',
            'time'     => 'date',
            'bool'     => 'number(1)',
            'tinyint'  => 'number(3)',
            'char'     => 'char',
            'id'       => 'varchar2(36)',
            'blob'     => 'blob',
            'longblob' => 'blob',
            'currency' => 'number(26,6)',
            'decimal'  => 'number (20,2)',
            'decimal2' => 'number (30,6)',
            'url'=>'varchar2(255)',
            'encrypt'=>'varchar2(255)',
            );
                    
		return $map[$type];
	}
	
	/**
     * @see DBHelper::changeColumnSQL()
     *
     * Oracle's ALTER TABLE syntax is a bit different from the other rdbmss
     */
    protected function changeColumnSQL(
        $tablename, 
        $fieldDefs, 
        $action, 
        $ignoreRequired = false
        )
    {
        
        $tablename = strtoupper($tablename);
        $action = strtoupper($action);
        
        $columns = "";
        if ($this->isFieldArray($fieldDefs)) {
            /**
             *jc: if we are dropping columns we do not need the
             * column definition data provided with the oneColumnSQLRep
             * method. instead we only need the column names.
             */
        	$addColumns = array();
			foreach($fieldDefs as $def) {
                switch(strtoupper($action)) {
                case 'DROP': 
                    $addColumns[] = $def['name']; 
                    break;
                case 'ADD':
                case 'MODIFY':
					$colArray = $this->oneColumnSQLRep($def, $ignoreRequired, $tablename, true);
					$isNullable = $this->isNullable($tablename,$colArray['name']);
					if ( !$ignoreRequired 
							&& ( $isNullable == ( $colArray['required'] == 'NULL' ) ) )
					  	$colArray['required'] = '';
					$addColumns[] = "{$colArray['name']} {$colArray['colType']} {$colArray['default']} {$colArray['required']} {$colArray['auto_increment']}";
                	break;
                }
			}
        	$columns = "(" . implode(",", $addColumns) . ")";
        }
        else {
            switch(strtoupper($action)) {
            case 'DROP': 
                $columns = $fieldDefs['name']; 
                break;
            case 'ADD':
            case 'MODIFY':
				$colArray = $this->oneColumnSQLRep($fieldDefs, $ignoreRequired, $tablename, true);
				$isNullable = $this->isNullable($tablename,$colArray['name']);
				if ( !$ignoreRequired 
						&& ( $isNullable == ( $colArray['required'] == 'NULL' ) ) )
					$colArray['required'] = '';
				$columns = "{$colArray['name']} {$colArray['colType']} {$colArray['default']} {$colArray['required']} {$colArray['auto_increment']}";
				break;
            }
        }
        if ( $action == 'DROP' )
            $action = 'DROP COLUMN';
        return ($columns == '' || empty($columns)) 
            ? "" 
            : "ALTER TABLE $tablename $action $columns";
    }
    
	/**
     * @see DBHelper::dropTableNameSQL()
     */
    public function dropTableNameSQL(
        $name
        )
    {
		return "DROP TABLE ". strtoupper($name);
    }
    
    /**
     * Fixes an Oracle index name
     *
     * Oracle has a strict limit on the size of object names (30 characters). errors will
     * occur if this is not checked. indexes should follow the naming convention as follows
     * 
     *   idx_[table name]_[column_](_[column2] ...)
     * 
     * and columns should be abbreviated by the first three letters or the following abbreviation
     * chart
     * 
     * 		u = assigned user
     *		t = assigned team
     * 		d = deleted
     * 		n = name
     *
     * @param  string $name index name
     * @return string
     */
    public function fixIndexName(
        $name
        ) 
    {
    	$result = $this->db->query(
            "SELECT COUNT(*) CNT 
                FROM USER_INDEXES 
                WHERE INDEX_NAME = '$name' 
                    OR INDEX_NAME = '".strtoupper($name)."'");
		$row = $this->db->fetchByAssoc($result);
		return ($row['cnt'] > 1) ? $name . (intval($row['cnt']) + 1) : $name;
    }

    /**
     * Generates an index name for the repair table
     *
     * If the last character is not an 'r', make it that; else make it '1'
     *
     * @param  string $index_name
     * @return string
     */
	public function repair_index_name(
        $index_name
        ) 
    {
		$last_char='r';
		if (substr($index_name,strlen($index_name) -1,1) =='r')
			$last_char='1';
		
		return substr($index_name,0,strlen($index_name)-1). $last_char;
	}
	
	/**
     * returns a SQL query that creates the indices as defined in metadata
     * @param  array  $indices Assoc array with index definitions from vardefs
     * @param  string $table Focus table
     * @return array  Array of SQL queries to generate indices
     */
	public function getConstraintSql(
        $indices, 
        $table
        )
    {
        if (!$this->isFieldArray($indices)) 
            $indices[] = $indices;
		
        $columns = array();
		
        /** 
         * Oracle requires indices to be defined as ALTER TABLE statements except for PRIMARY KEY 
         * and UNIQUE (which can defined inline with the CREATE TABLE)
         */
        $ret = '';
		foreach ($indices as $index) {
            if(!empty($index['db']) && $index['db'] != 'oci8')
                continue;
            
            $type = '';
            if (!empty($index['type']))
                $type = $index['type'];

            $name = '';
            if (!empty($index['name'])) {
                /**
                 * jc: no two objects in an oracle database can have the same index names placed on them.
                 * because of the repair database script architecture the temporary repair table will attempt
                 * to add the same indices with the same names to the repair table and will generate an
                 * error. so we append an r to the eend of the index name for these cases.
                 */
                $name = $this->fixIndexName($index['name']);
                $name = ((strtolower($table) == 'repair_table') 
                    ? $this->repair_index_name($name) : $name);
            }
            
            $fields = '';
            if (!empty($index['fields']))
                $fields = (is_array($index['fields'])) 
                    ? implode(",", $index['fields']) : $index['fields'];

            switch ($type){
            // generic indices
            case 'alternate_key':
            case 'index':
            case 'clustered':
                $columns[] = "CREATE INDEX {$name} ON {$table} ({$fields})";
                break;
            // constraints as indices
            case 'unique':
                $columns[] = "ALTER TABLE {$table} ADD CONSTRAINT {$name} UNIQUE ({$fields})";
                break;
            case 'primary':
                $columns[] = "ALTER TABLE {$table} ADD CONSTRAINT {$name} PRIMARY KEY ({$fields})";
                break;
            case 'foreign':
                $columns[] = "ALTER TABLE {$table} ADD CONSTRAINT {$name} FORIEGN KEY ({$fields}) REFERENCES {$index['foreignTable']}.({$index['foreignField']})";
                break;
            case 'fulltext':
                if ($this->full_text_indexing_enabled()) {				
                    $indextype=$index['indextype'];
                    $parameters="";
                    //add parameters attribute if oracle version of 10 or more.
                    $ver = $this->db->version();
                    $tok = strtok($ver, '.');
                    if ($tok !== false && $tok > 9) {
                        $parameters = isset($index['parameters']) 
                            ? "parameters ('". $index['parameters']. "')" : "";     						
                    }	
                    $columns[] = "CREATE INDEX {$name} ON $table($fields) INDEXTYPE IS $indextype $parameters";
                }					
                break;
            default:
                $columns[] = "SELECT 1 FROM DUAL";
            }
		}
		
		return $columns;
	} 
      
    /**
     * @see DBHelper::getAutoIncrement()
     */
    public function getAutoIncrement(
        $table, 
        $field_name
        )
    {
	$result = $this->db->query("SELECT max($field_name) currval FROM $table");
        $row = $this->db->fetchByAssoc($result);
        if (!empty($row['currval']))
            return $row['currval'] + 1 ;
            
        return "";
    }
    
/**
     * @see DBHelper::getAutoIncrement()
     */
    public function getAutoIncrementSQL(
        $table, 
        $field_name
        )
    {
        return $this->_getSequenceName($table, $field_name, true) . '.nextval';
    }
	  
    /**
     * @see DBHelper::setAutoIncrement()
     */
    protected function setAutoIncrement(
        $table, 
        $field_name
        )
    {	
      	$this->deleteAutoIncrement($table, $field_name);
      	$this->db->query(
            'CREATE SEQUENCE ' . $this->_getSequenceName($table, $field_name, true) . 
                ' START WITH 0 increment by 1 nomaxvalue minvalue 0');
		$this->db->query(
            'SELECT ' . $this->_getSequenceName($table, $field_name, true) . 
                '.NEXTVAL FROM DUAL');
        
        return "";
    }
	  
	/**
     * Sets the next auto-increment value of a column to a specific value.
     *
     * @param  string $table tablename
     * @param  string $field_name
     */
    public function setAutoIncrementStart(
        $table,
        $field_name,
        $start_value
        )
    {
    	$sequence_name = _getSequenceName($table, $field_name, true);
    	$result = $this->db->query("SELECT {$sequence_name}.NEXTVAL currval FROM DUAL");
    	$row = $this->db->fetchByAssoc($result);
    	$current = $row['currval'];
    	$change = $start_value - $current - 1;
    	$this->db->query("ALTER SEQUENCE {$sequence_name} INCREMENT BY $change");
        $this->db->query("SELECT {$sequence_name}.NEXTVAL FROM DUAL");
        $this->db->query("ALTER SEQUENCE {$sequence_name} INCREMENT BY 1");
                        
    	return true;
    }
	  
	/**
     * @see DBHelper::deleteAutoIncrement()
     */
    public function deleteAutoIncrement(
        $table, 
        $field_name
        )
    {
	  	$sequence_name = $this->_getSequenceName($table, $field_name, true);
	  	if ($this->_findSequence($sequence_name)) {
            $this->db->query('DROP SEQUENCE ' .$sequence_name);
        }
    }
    
    
    /** 
     * Escapes single quotes in a string with a single quote.
     *
     * Calls magic_quotes_oracle_ForEmail(), but html decodes the string first
     * 
     * @deprecated
     * @param  string $theString
     * @return string
     */    
    public static function magic_quotes_oracle(
        $theString
        ) 
    {
        $GLOBALS['log']->info('call to OracleHelper::magic_quotes_oracle() is deprecated');
        return self::magic_quotes_oracle_ForEmail(from_html($theString));
    }

	/** 
     * Escapes single quotes in a string with a single quote.
     * 
     * Does not html decode the string
     *
     * @deprecated
     * @param  string $theString
     * @return string
     */    
    public static function magic_quotes_oracle_ForEmail(
        $theString
        ) 
    {
        $GLOBALS['log']->info('call to OracleHelper::magic_quotes_oracle_ForEmail() is deprecated');
        if (empty($theString)) 
            return $theString;
	  	
        return str_replace("'","''",$theString);
    }
	
    /**
     * @see DBHelper::updateSQL()
     */
    public function updateSQL(
        SugarBean $bean, 
        array $where = array()
        )
    {
		// get field definitions
        $primaryField = $bean->getPrimaryFieldDefinition();
        $columns = array();

		// get column names and values
		foreach ($bean->getFieldDefinitions() as $fieldDef) {
            $name = $fieldDef['name'];
            if ($name == $primaryField['name']) 
                continue;
            if (isset($bean->$name) 
                    && (!isset($fieldDef['source']) || $fieldDef['source'] == 'db')) {
                $val = $bean->getFieldValue($name);
			   	// clean the incoming value..
			   	$val = from_html($val);
                
                if (strlen($val) <= 0) 
                    $val = null;

		        // need to do some thing about types of values
		        $val = $this->massageValue($val, $fieldDef);
		        $columns[] = "$name=$val";
            }
		}

		if (sizeof ($columns) == 0) 
            return ""; // no columns set

		$where = $this->updateWhereArray($bean, $where);
        $where = $this->getWhereClause($bean, $where);
		
        // get the entire sql
		return "update ".$bean->getTableName()." set ".implode(",", $columns)." $where ";
	}
    
    /**
     * @see DBHelper::get_indices()
     */
    public function get_indices(
        $tablename,
        $indexname = null
        ) 
    {
		$tablename = strtoupper($tablename);
		$indexname = strtoupper($indexname);
		
        //find all unique indexes and primary keys.
		$query = <<<EOQ
select a.index_name, c.column_name, b.constraint_type, c.column_position
    from user_indexes a 
        inner join user_ind_columns c  
            on c.index_name = a.index_name
        left join user_constraints b 
            on b.constraint_name = a.index_name 
                and b.table_name='$tablename'
    where a.table_name='$tablename'
        and a.index_type='NORMAL'
EOQ;
        if (!empty($indexname)) {
            $query .= " and a.index_name='$indexname'";
        }
        $query .= " order by a.index_name,c.column_position";
        $result = $this->db->query($query);

        $indices = array();
		while (($row=$this->db->fetchByAssoc($result)) !=null) {
            $index_type='index';
            if ($row['constraint_type'] =='P')
                $index_type='primary';
            if ($row['constraint_type'] =='U')
                $index_type='unique';
            
            $name = strtolower($row['index_name']);
            $indices[$name]['name']=$name;
            $indices[$name]['type']=$index_type;
            $indices[$name]['fields'][]=strtolower($row['column_name']);
        }
		
        return $indices;
	}
    
    /**
     * @see DBHelper::get_columns()
     */
    public function get_columns(
        $tablename
        ) 
    {
        //find all unique indexes and primary keys.
        $result = $this->db->query(
            "SELECT * FROM user_tab_columns WHERE TABLE_NAME = '".strtoupper($tablename)."'");
        
        $columns = array();
        while (($row=$this->db->fetchByAssoc($result)) !=null) {
            $name = strtolower($row['column_name']);
            $columns[$name]['name']=$name;
            $columns[$name]['type']=strtolower($row['data_type']);
            if ( $columns[$name]['type'] == 'number' ) {
                $columns[$name]['len']= 
                    ( !empty($row['data_precision']) ? $row['data_precision'] : '3');
                if ( !empty($row['data_scale']) )
                    $columns[$name]['len'].=','.$row['data_scale'];
            }
            elseif ( in_array($columns[$name]['type']
                ,array('date','clob','blob')) ) {
                // do nothing
            }
            else    
                $columns[$name]['len']=strtolower($row['char_length']);
            if ( !empty($row['data_default']) ) {
                $matches = array();
                $row['data_default'] = html_entity_decode($row['data_default'],ENT_QUOTES);
                if ( preg_match("/'(.*)'/i",$row['data_default'],$matches) )
                    $columns[$name]['default'] = $matches[1];
            }
            
            $sequence_name = $this->_getSequenceName($tablename, $row['column_name'], true);
            if ($this->_findSequence($sequence_name))
                $columns[$name]['auto_increment'] = '1';
            elseif ( $row['nullable'] == 'N' )
                $columns[$name]['required'] = 'true';
        }
        return $columns;
    }
    
    /**
     * Returns true if the sequence name given is found
     *
     * @param  string $name
     * @return bool   true if the sequence is found, false otherwise
     */
    private function _findSequence(
        $name
        )
    {
        static $sequences;
        
        global $sugar_config;
        $db_user_name = isset($sugar_config['dbconfig']['db_user_name'])?$sugar_config['dbconfig']['db_user_name']:'';
        $db_user_name = strtoupper($db_user_name);
        
        if ( !is_array($sequences) ) {
            $result = $this->db->query(
                "SELECT SEQUENCE_NAME FROM ALL_SEQUENCES WHERE SEQUENCE_OWNER='$db_user_name' ");
            while ( $row = $this->db->fetchByAssoc($result) )
                $sequences[] = $row['sequence_name'];
        }
        if ( !is_array($sequences) )
            return false;
        else    
            return in_array($name,$sequences);
    }
	
	/**
     * @see DBHelper::add_drop_constraint()
     */
    public function add_drop_constraint(
        $table,
        $definition, 
        $drop = false
        ) 
    {
        $type         = $definition['type'];
        $fields       = implode(',',$definition['fields']);
        $name         = $definition['name'];
        $foreignTable = isset($definition['foreignTable']) ? $definition['foreignTable'] : array();
        $sql          = '';
        
        switch ($type){
        // generic indices
        case 'index':
        case 'alternate_key':
            if ($drop)
                $sql = "DROP INDEX {$name} ";
            else
                $sql = "CREATE INDEX {$name} ON {$table} ({$fields})";
            break;
        // constraints as indices
        case 'unique':
            if ($drop)
                $sql = "ALTER TABLE {$table} DROP UNIQUE ({$fields})";
            else
                $sql = "ALTER TABLE {$table} ADD CONSTRAINT {$name} UNIQUE ({$fields})";
            break;
        case 'primary':
            if ($drop)
                $sql = "ALTER TABLE {$table} DROP PRIMARY KEY CASCADE";
            else
                $sql = "ALTER TABLE {$table} ADD CONSTRAINT {$name} PRIMARY KEY ({$fields})";
            break;
        case 'foreign':
            if ($drop)
                $sql = "ALTER TABLE {$table} DROP FOREIGN KEY ({$fields})";
            else
                $sql = "ALTER TABLE {$table} ADD CONSTRAINT {$name} FORIEGN KEY ({$fields}) REFERENCES {$foreignTable}({$foreignfields})";
            break;
        }
        return $sql;
	}

    /**
     * @see DBHelper::rename_index()
     */
    public function rename_index(
        $old_definition,
        $new_definition,
        $table_name
        ) 
    {
        return "ALTER INDEX {$old_definition['name']} RENAME TO {$new_definition['name']}";        
    }
   
    /**
     * @see DBHelper::number_of_columns()
     */
    public function number_of_columns(
        $table_name
        ) 
    {
        $table_name = strtoupper($table_name);
        $result = $this->db->query(
            "select count(*) cols 
                from user_tab_columns 
                where table_name='$table_name'");
        $row = $this->db->fetchByAssoc($result);
        if (!empty($row)) {
            return $row['cols'];
        }
        return 0;
    }

	/**
     * @see DBHelper::full_text_indexing_enabled()
     */
    protected function full_text_indexing_enabled(
        $dbname = null
        ) 
    {
		return true;
    }
    
    /**
     * @see DBHelper::massageFieldDef()
     */
    public function massageFieldDef(
        &$fieldDef,
        $tablename
        )
    {
        parent::massageFieldDef($fieldDef,$tablename);
        
        if ($fieldDef['name'] == 'id')
            $fieldDef['required'] = 'true';
        if ($fieldDef['dbType'] == 'decimal')
            $fieldDef['len'] = '20,2';
        if ($fieldDef['dbType'] == 'decimal2')
            $fieldDef['len'] = '30,6';
        if ($fieldDef['dbType'] == 'double')
            $fieldDef['len'] = '30,10';
        if ($fieldDef['dbType'] == 'float')
            $fieldDef['len'] = '30,6';
        if ($fieldDef['dbType'] == 'uint')
            $fieldDef['len'] = '15';
        if ($fieldDef['dbType'] == 'ulong')
            $fieldDef['len'] = '38';
        if ($fieldDef['dbType'] == 'long')
            $fieldDef['len'] = '38';
        if ($fieldDef['dbType'] == 'enum')
            $fieldDef['len'] = '255';
        if ($fieldDef['dbType'] == 'bool')
            $fieldDef['len'] = '1';
        if ($fieldDef['dbType'] == 'id')
            $fieldDef['len'] = '36';
        if ($fieldDef['dbType'] == 'currency')
            $fieldDef['len'] = '26,6';
        if ($fieldDef['dbType'] == 'short')
            $fieldDef['len'] = '3';
        if ($fieldDef['dbType'] == 'tinyint')
            $fieldDef['len'] = '3';
        if ($fieldDef['dbType'] == 'int')
            $fieldDef['len'] = '3';
        if ($fieldDef['type'] == 'int' && empty($fieldDef['len']) )
            $fieldDef['len'] = '';
        if ($fieldDef['dbType'] == 'enum')
            $fieldDef['len'] = '255';
        if ($fieldDef['type'] == 'varchar2' && empty($fieldDef['len']) )
            $fieldDef['len'] = '255';
        
    }
    
    /**
     * Generate an Oracle SEQUENCE name. If the length of the sequence names exceeds a certain amount
     * we will use an md5 of the field name to shorten.
     *
     * @param string $table
     * @param string $field_name
     * @param boolean $upper_case
     * @return string
     */
    protected function _getSequenceName(
        $table, 
        $field_name, 
        $upper_case = true
        )
    {
        $sequence_name = $table. '_' .$field_name . '_seq';
        if(strlen($sequence_name) > 30)
            $sequence_name = $table. '_' .$this->_generateMD5Name($field_name) . '_seq';
        if($upper_case)
            $sequence_name = strtoupper($sequence_name);
        return $sequence_name;
    }
    
    /**
     * Used in OracleHelper to generate SEQUENCE names. This could also be used
     * by an upgrade script to upgrading sequences.  It will take in a name
     * and md5 the name and only return $length characters.
     *
     * @param string $name - name of the orignal sequence
     * @param int $length - length of the desired md5 sequence.
     * @return string
     */
    protected function _generateMD5Name(
        $name, 
        $length = 6
        )
    {
        $md5_name = md5($name);
        //this should generate a 32 character string
        //now that we have this md5 representation, let's
        //cut it so we only have $length number of chars
        return substr($md5_name, 0, $length);
    }
    
    /**
     * @see DBHelper::deleteColumnSQL()
     */
	public function deleteColumnSQL(
        SugarBean $bean,
        $fieldDefs
        )
    {
        if ($this->isFieldArray($fieldDefs))
            foreach ($fieldDefs as $fieldDef)
                $columns[] = $fieldDef['name'];
        else
            $columns[] = $fieldDefs['name'];

        return "alter table ".$bean->getTableName()." drop column (".implode(", ", $columns).")";
	}
}
?>
