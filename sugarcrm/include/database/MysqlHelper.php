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
* $Id: MysqlHelper.php 56151 2010-04-28 21:02:22Z jmertic $
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
require_once('include/database/DBHelper.php');

class MysqlHelper extends DBHelper
{
    /**
     * Maximum length of identifiers
     */
    protected $maxNameLengths = array(
        'table' => 64,
        'column' => 64,
        'index' => 64,
        'alias' => 256
    );
    
    /**
     * @see DBHelper::createTableSQL()
     */
    public function createTableSQL(
        SugarBean $bean
        )
    {
        $tablename = $bean->getTableName();
        $fieldDefs = $bean->getFieldDefinitions();
        $indices   = $bean->getIndices();
        $engine    = $this->getEngine($bean);
        return $this->createTableSQLParams($tablename, $fieldDefs, $indices, $engine);
	}

    /**
     * Generates sql for create table statement for a bean.
     *
     * @param  string $tablename
     * @param  array  $fieldDefs
     * @param  array  $indices
     * @param  string $engine optional, MySQL engine to use
     * @return string SQL Create Table statement
    */
    public function createTableSQLParams(
        $tablename,
        $fieldDefs,
        $indices,
        $engine = null
        )
    {
 		if ( empty($engine) && isset($fieldDefs['engine']))
            $engine = $fieldDefs['engine'];
        if ( !$this->isEngineEnabled($engine) )
            $engine = '';

        $sql = parent::createTableSQLParams($tablename,$fieldDefs,$indices);
        if (!empty($engine))
            $sql.= " ENGINE=$engine";

        return $sql;
	}

    /**
     * Returns the name of the engine to use or null if we are to use the default
     *
     * @param  object $bean SugarBean instance
     * @return string
     */
    private function getEngine($bean)
    {
        global $dictionary;
        $engine = null;
        if (isset($dictionary[$bean->getObjectName()]['engine'])) {
			$engine = $dictionary[$bean->getObjectName()]['engine'];
		}
        return $engine;
    }

    /**
     * Returns true if the engine given is enabled in the backend
     *
     * @param  string $engine
     * @return bool
     */
    private function isEngineEnabled(
        $engine
        )
    {
        $engine = strtoupper($engine);

        $r = $this->db->query("SHOW ENGINES");

        while ( $row = $this->db->fetchByAssoc($r) )
            if ( strtoupper($row['Engine']) == $engine )
                return ($row['Support']=='YES' || $row['Support']=='DEFAULT');

        return false;
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
            'int'      => 'int',
            'double'   => 'double',
            'float'    => 'float',
            'uint'     => 'int unsigned',
            'ulong'    => 'bigint unsigned',
            'long'     => 'bigint',
            'short'    => 'smallint',
            'varchar'  => 'varchar',
            'text'     => 'text',
            'longtext' => 'longtext',
            'date'     => 'date',
            'enum'     => 'varchar',
            'relate'   => 'varchar',
            'multienum'=> 'text',
            'html'     => 'text',
            'datetime' => 'datetime',
            'datetimecombo' => 'datetime',
            'time'     => 'time',
            'bool'     => 'bool',
            'tinyint'  => 'tinyint',
            'char'     => 'char',
            'blob'     => 'blob',
            'longblob' => 'longblob',
            'currency' => 'decimal(26,6)',
            'decimal'  => 'decimal',
            'decimal2' => 'decimal',
            'id'       => 'char(36)',
           'url'=>'varchar',
           'encrypt'=>'varchar',
           'file'      => 'varchar',
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
        $ref = parent::oneColumnSQLRep($fieldDef, $ignoreRequired, $table, true);

        if ( $ref['colType'] == 'int'
                && !empty($fieldDef['len']) )
            $ref['colType'] .= "(".$fieldDef['len'].")";

        // bug 22338 - don't set a default value on text or blob fields
        if ( isset($ref['default']) &&
            ($ref['colType'] == 'text' || $ref['colType'] == 'blob'
                || $ref['colType'] == 'longtext' || $ref['colType'] == 'longblob' ))
            $ref['default'] = '';
            
        if ( $return_as_array )
            return $ref;
        else
            return "{$ref['name']} {$ref['colType']} {$ref['default']} {$ref['required']} {$ref['auto_increment']}";
    }

    /**
     * @see DBHelper::changeColumnSQL()
     */
    protected function changeColumnSQL(
        $tablename,
        $fieldDefs,
        $action,
        $ignoreRequired = false
        )
    {
        if ($this->isFieldArray($fieldDefs)){
            foreach ($fieldDefs as $def){
                if ($action == 'drop')
                    $columns[] = $def['name'];
                else
                $columns[] = $this->oneColumnSQLRep($def, $ignoreRequired);
            }
        }else{
            if ($action == 'drop')
                $columns[] = $fieldDefs['name'];
        else
            $columns[] = $this->oneColumnSQLRep($fieldDefs);
        }

        return "alter table $tablename $action column ".implode(",$action column ", $columns);
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

        return "alter table ".$bean->getTableName()." drop column ".implode(", drop column ", $columns);
    }

    /**
     * @see DBHelper::keysSQL
     */
    public function keysSQL(
        $indices,
        $alter_table = false,
        $alter_action = ''
        )
	{
       // check if the passed value is an array of fields.
       // if not, convert it into an array
       if (!$this->isFieldArray($indices))
           $indices[] = $indices;

       $columns = array();
       foreach ($indices as $index) {
           if(!empty($index['db']) && $index['db'] != 'mysql')
               continue;
           if (isset($index['source']) && $index['source'] != 'db')
               continue;
           
           $type = $index['type'];
           $name = $index['name'];

           if (is_array($index['fields']))
               $fields = implode(", ", $index['fields']);
           else
               $fields = $index['fields'];

           switch ($type) {
           case 'unique':
               $columns[] = " UNIQUE $name ($fields)";
               break;
           case 'primary':
               $columns[] = " PRIMARY KEY ($fields)";
               break;
           case 'index':
           case 'foreign':
           case 'clustered':
           case 'alternate_key':
               /**
                * @todo here it is assumed that the primary key of the foreign
                * table will always be named 'id'. It must be noted though
                * that this can easily be fixed by referring to db dictionary
                * to find the correct primary field name
                */
               if ( $alter_table )
                   $columns[] = " INDEX $name ($fields)";
               else
                   $columns[] = " KEY $name ($fields)";
               break;
           case 'fulltext':
               if ($this->full_text_indexing_enabled())
                   $columns[] = " FULLTEXT ($fields)";
               else
                   $GLOBALS['log']->debug('MYISAM engine is not available/enabled, full-text indexes will be skipped. Skipping:',$name);
               break;
          }
       }
       $columns = implode(", $alter_action ", $columns);
       if(!empty($alter_action)){
           $columns = $alter_action . ' '. $columns;
       }
       return $columns;
    }

    /**
     * @see DBHelper::setAutoIncrement()
     */
 	protected function setAutoIncrement(
        $table,
        $field_name
        )
    {
		return "auto_increment";
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
        $this->db->query( "ALTER TABLE $table AUTO_INCREMENT = $start_value;");

    	return true;
    }

    /**
     * Returns the next value for an auto increment
     *
     * @param  string $table tablename
     * @param  string $field_name
     * @return string
     */
    public function getAutoIncrement(
        $table,
        $field_name
        )
    {

        $result = $this->db->query("SHOW TABLE STATUS LIKE '$table'");
        $row = $this->db->fetchByAssoc($result);
        if (!empty($row['Auto_increment']))
            return $row['Auto_increment'];

    	return "";
    }

   	/**
     * @see DBHelper::get_indices()
     */
    public function get_indices(
        $tablename
        )
    {
        //find all unique indexes and primary keys.
        $result = $this->db->query("SHOW INDEX FROM $tablename");

        $indices = array();
        while (($row=$this->db->fetchByAssoc($result)) !=null) {
            $index_type='index';
            if ($row['Key_name'] =='PRIMARY') {
                $index_type='primary';
            }
            elseif ( $row['Non_unique'] == '0' ) {
                $index_type='unique';
            }
            $name = strtolower($row['Key_name']);
            $indices[$name]['name']=$name;
            $indices[$name]['type']=$index_type;
            $indices[$name]['fields'][]=strtolower($row['Column_name']);
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
        $result = $this->db->query("DESCRIBE $tablename");

        $columns = array();
        while (($row=$this->db->fetchByAssoc($result)) !=null) {
            $name = strtolower($row['Field']);
            $columns[$name]['name']=$name;
            $matches = array();
            preg_match_all("/(\w+)(?:\(([0-9]+,?[0-9]*)\)|)( unsigned)?/i", $row['Type'], $matches);
            $columns[$name]['type']=strtolower($matches[1][0]);
            if ( isset($matches[2][0]) && in_array(strtolower($matches[1][0]),array('varchar','char','varchar2','int','decimal','float')) )
                $columns[$name]['len']=strtolower($matches[2][0]);
            if ( stristr($row['Extra'],'auto_increment') )
                $columns[$name]['auto_increment'] = '1';
            if ($row['Null'] == 'NO' && !stristr($row['Key'],'PRI'))
                $columns[$name]['required'] = 'true';
            if (!empty($row['Default']) )
                $columns[$name]['default'] = $row['Default'];
        }
        return $columns;
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
                $sql = "ALTER TABLE {$table} DROP INDEX $name";
            else
                $sql = "ALTER TABLE {$table} ADD CONSTRAINT UNIQUE {$name} ({$fields})";
            break;
        case 'primary':
            if ($drop)
                $sql = "ALTER TABLE {$table} DROP PRIMARY KEY";
            else
                $sql = "ALTER TABLE {$table} ADD CONSTRAINT PRIMARY KEY ({$fields})";
            break;
        case 'foreign':
            if ($drop)
                $sql = "ALTER TABLE {$table} DROP FOREIGN KEY ({$fields})";
            else
                $sql = "ALTER TABLE {$table} ADD CONSTRAINT FOREIGN KEY {$name} ({$fields}) REFERENCES {$foreignTable}({$foreignfields})";
            break;
        }
        return $sql;
    }

    /**
     * @see DBHelper::number_of_columns()
     */
    public function number_of_columns(
        $table_name
        )
    {
        $result = $this->db->query("DESCRIBE $table_name");

        return ($this->db->getRowCount($result));
    }

	/**
     * @see DBHelper::full_text_indexing_enabled()
     */
    protected function full_text_indexing_enabled(
        $dbname = null
        )
    {
		return $this->isEngineEnabled('MyISAM');
	}

    /**
     * @see DBHelper::massageFieldDef()
     */
    public function massageFieldDef(
        &$fieldDef,
        $tablename
        )
    {
        DBHelper::massageFieldDef($fieldDef,$tablename);

        if ( isset($fieldDef['default']) &&
            ($fieldDef['dbType'] == 'text'
                || $fieldDef['dbType'] == 'blob'
                || $fieldDef['dbType'] == 'longtext'
                || $fieldDef['dbType'] == 'longblob' ))
            unset($fieldDef['default']);
        if ($fieldDef['dbType'] == 'uint')
            $fieldDef['len'] = '10';
        if ($fieldDef['dbType'] == 'ulong')
            $fieldDef['len'] = '20';
        if ($fieldDef['dbType'] == 'bool')
            $fieldDef['type'] = 'tinyint';
        if ($fieldDef['dbType'] == 'bool' && empty($fieldDef['default']) )
            $fieldDef['default'] = '0';
        if (($fieldDef['dbType'] == 'varchar' || $fieldDef['dbType'] == 'enum') && empty($fieldDef['len']) )
            $fieldDef['len'] = '255';
        if ($fieldDef['dbType'] == 'uint')
            $fieldDef['len'] = '10';
        if ($fieldDef['dbType'] == 'int' && empty($fieldDef['len']) )
            $fieldDef['len'] = '11';
    }
}
?>
