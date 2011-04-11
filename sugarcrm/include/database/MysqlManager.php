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
* $Id: MysqlManager.php 53409 2010-01-04 03:31:15Z roger $
* Description: This file handles the Data base functionality for the application.
* It acts as the DB abstraction layer for the application. It depends on helper classes
* which generate the necessary SQL. This sql is then passed to PEAR DB classes.
* The helper class is chosen in DBManagerFactory, which is driven by 'db_type' in 'dbconfig' under config.php.
*
* All the functions in this class will work with any bean which implements the meta interface.
* The passed bean is passed to helper class which uses these functions to generate correct sql.
*
* The meta interface has the following functions:
* getTableName()	        	Returns table name of the object.
* getFieldDefinitions()	    	Returns a collection of field definitions in order.
* getFieldDefintion(name)		Return field definition for the field.
* getFieldValue(name)	    	Returns the value of the field identified by name.
*                           	If the field is not set, the function will return boolean FALSE.
* getPrimaryFieldDefinition()	Returns the field definition for primary key
*
* The field definition is an array with the following keys:
*
* name 		This represents name of the field. This is a required field.
* type 		This represents type of the field. This is a required field and valid values are:
*           �   int
*           �   long
*           �   varchar
*           �   text
*           �   date
*           �   datetime
*           �   double
*           �   float
*           �   uint
*           �   ulong
*           �   time
*           �   short
*           �   enum
* length    This is used only when the type is varchar and denotes the length of the string.
*           The max value is 255.
* enumvals  This is a list of valid values for an enum separated by "|".
*           It is used only if the type is �enum�;
* required  This field dictates whether it is a required value.
*           The default value is �FALSE�.
* isPrimary This field identifies the primary key of the table.
*           If none of the fields have this flag set to �TRUE�,
*           the first field definition is assume to be the primary key.
*           Default value for this field is �FALSE�.
* default   This field sets the default value for the field definition.
*
*
* Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
* All Rights Reserved.
* Contributor(s): ______________________________________..
********************************************************************************/

//Technically we can port all the functions in the latest bean to this file
// that is what PEAR is doing anyways.

class MysqlManager extends DBManager
{
    /**
     * @see DBManager::$dbType
     */
    public $dbType = 'mysql';

    protected $maxNameLengths = array(
        'table' => 64,
        'column' => 64,
        'index' => 64,
        'alias' => 256
    );

    protected $type_map = array(
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
            'url'      => 'varchar',
            'encrypt'  => 'varchar',
            'file'     => 'varchar',
            'decimal_tpl' => 'decimal(%d, %d)',

     );

    protected $capabilities = array(
        "affected_rows" => true,
        "select_rows" => true,
        "inline_keys" => true,
    );

    /**
     * @see DBManager::checkError()
     */
    public function checkError($msg = '', $dieOnError = false)
    {
        if (parent::checkError($msg, $dieOnError))
            return true;

        if (mysql_errno($this->getDatabase())) {
            if ($this->dieOnError || $dieOnError){
                $GLOBALS['log']->fatal("MySQL error ".mysql_errno($this->database).": ".mysql_error($this->database));
                sugar_die ($GLOBALS['app_strings']['ERR_DB_FAIL']);
            }
            else {
                $this->last_error = $msg."MySQL error ".mysql_errno($this->database).": ".mysql_error($this->database);
                $GLOBALS['log']->error("MySQL error ".mysql_errno($this->database).": ".mysql_error($this->database));

            }
            return true;
        }
        return false;
    }

    /**
     * Parses and runs queries
     *
     * @param  string   $sql        SQL Statement to execute
     * @param  bool     $dieOnError True if we want to call die if the query returns errors
     * @param  string   $msg        Message to log if error occurs
     * @param  bool     $suppress   Flag to suppress all error output unless in debug logging mode.
     * @param  bool     $keepResult True if we want to push this result into the $lastResult array.
     * @return resource result set
     */
    public function query($sql, $dieOnError = false, $msg = '', $suppress = false, $keepResult = false)
    {
        parent::countQuery($sql);
        $GLOBALS['log']->info('Query:' . $sql);
        $this->checkConnection();
        $this->query_time = microtime(true);
        $this->lastsql = $sql;
        //BEGIN SUGARCRM flav=ent ONLY
        if ($suppress==true) {
            //suppress flag is when you are using CSQL and make a bad query.
            //We don't want any php errors to appear
            $result = @mysql_query($sql, $this->database);
        } else {
        //END SUGARCRM flav=ent ONLY
            $result = mysql_query($sql, $this->database);
        //BEGIN SUGARCRM flav=ent ONLY
        }
        //END SUGARCRM flav=ent ONLY

        $this->query_time = microtime(true) - $this->query_time;
        $GLOBALS['log']->info('Query Execution Time:'.$this->query_time);

        //BEGIN SUGARCRM flav=pro ONLY
        if($this->dump_slow_queries($sql)) {
		   $this->track_slow_queries($sql);
        }
        //END SUGARCRM flav=pro ONLY

        if($keepResult)
            $this->lastResult = $result;

        $this->checkError($msg.' Query Failed:' . $sql . '::', $dieOnError);
        return $result;
    }

    /**
     * Returns the number of rows affected by the last query
     *
     * @return int
     */
    public function getAffectedRowCount($result)
    {
        return mysql_affected_rows($this->getDatabase());
    }

    /**
     * Disconnects from the database
     *
     * Also handles any cleanup needed
     */
    public function disconnect()
    {
    	$GLOBALS['log']->debug('Calling MySQL::disconnect()');
        if(!empty($this->database)){
            $this->freeResult();
            mysql_close($this->database);
            $this->database = null;
        }
    }

    /**
     * @see DBManager::freeDbResult()
     */
    protected function freeDbResult($dbResult)
    {
        if(!empty($dbResult))
            mysql_free_result($dbResult);
    }

    /**
     * Returns the number of rows returned by the result
     *
     * @param  resource $result
     * @return int
     */
    public function getRowCount($result)
    {
        if(!empty($result)) {
            return mysql_num_rows($result);
		}
		return 0;
	}

    /**
     * @see DBManager::limitQuery()
     */
    public function limitQuery($sql, $start, $count, $dieOnError = false, $msg = '', $execute = true)
    {
        if ($start < 0)
            $start = 0;
        $GLOBALS['log']->debug('Limit Query:' . $sql. ' Start: ' .$start . ' count: ' . $count);

        $sql = "$sql LIMIT $start,$count";
        $this->lastsql = $sql;

        if(!empty($GLOBALS['sugar_config']['check_query'])){
            $this->checkQuery($sql);
        }
        if(!$execute) {
            return $sql;
        }

        return $this->query($sql, $dieOnError, $msg);
    }


    /**
     * @see DBManager::checkQuery()
     */
    protected function checkQuery($sql)
    {
        $result   = $this->query('EXPLAIN ' . $sql);
        $badQuery = array();
        while ($row = $this->fetchByAssoc($result)) {
            if (empty($row['table']))
                continue;
            $badQuery[$row['table']] = '';
            if (strtoupper($row['type']) == 'ALL')
                $badQuery[$row['table']]  .=  ' Full Table Scan;';
            if (empty($row['key']))
                $badQuery[$row['table']] .= ' No Index Key Used;';
            if (!empty($row['Extra']) && substr_count($row['Extra'], 'Using filesort') > 0)
                $badQuery[$row['table']] .= ' Using FileSort;';
            if (!empty($row['Extra']) && substr_count($row['Extra'], 'Using temporary') > 0)
                $badQuery[$row['table']] .= ' Using Temporary Table;';
        }

        if ( empty($badQuery) )
            return true;

        foreach($badQuery as $table=>$data ){
            if(!empty($data)){
                $warning = ' Table:' . $table . ' Data:' . $data;
                //BEGIN SUGARCRM flav=int ONLY
                // _pp('Warning Check Query:' . $warning);
                //END SUGARCRM flav=int ONLY
                if(!empty($GLOBALS['sugar_config']['check_query_log'])){
                    $GLOBALS['log']->fatal($sql);
                    $GLOBALS['log']->fatal('CHECK QUERY:' .$warning);
                }
                else{
                    $GLOBALS['log']->warn('CHECK QUERY:' .$warning);
                }
            }
        }

        return false;
    }

   	/**
     * @see DBManager::get_columns()
     */
    public function get_columns($tablename)
    {
        //find all unique indexes and primary keys.
        $result = $this->query("DESCRIBE $tablename");

        $columns = array();
        while (($row=$this->fetchByAssoc($result)) !=null) {
            $name = strtolower($row['Field']);
            $columns[$name]['name']=$name;
            $matches = array();
            preg_match_all('/(\w+)(?:\(([0-9]+,?[0-9]*)\)|)( unsigned)?/i', $row['Type'], $matches);
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
     * @see DBManager::getFieldsArray()
     */
    public function getFieldsArray($result, $make_lower_case=false)
    {
        $field_array = array();

        if(empty($result))
            return 0;

        $fields = mysql_num_fields($result);
        for ($i=0; $i < $fields; $i++) {
            $meta = mysql_fetch_field($result, $i);
            if (!$meta)
                return array();

            if($make_lower_case == true)
                $meta->name = strtolower($meta->name);

            $field_array[] = $meta->name;
        }

        return $field_array;
    }

    /**
     * @see DBManager::fetchByAssoc()
     */
    public function fetchByAssoc($result, $rowNum = -1, $encode = true)
    {
        if (!$result)
            return false;

        if ($result && $rowNum > -1) {
            if ($this->getRowCount($result) > $rowNum)
                mysql_data_seek($result, $rowNum);
        }

        $row = mysql_fetch_assoc($result);

        if ($encode && $this->encode && is_array($row))
            return array_map('to_html', $row);

        return $row;
    }

    /**
     * @see DBManager::getTablesArray()
     */
    public function getTablesArray()
    {
        $GLOBALS['log']->debug('Fetching table list');

        if ($this->getDatabase()) {
            $tables = array();
            $r = $this->query('SHOW TABLES');
            if (!empty($r)) {
                while ($a = $this->fetchByAssoc($r)) {
                    $row = array_values($a);
					$tables[]=$row[0];
                }
                return $tables;
            }
        }

        return false; // no database available
    }

    /**
     * @see DBManager::version()
     */
    public function version()
    {
        return $this->getOne("SELECT version() version");
    }

    /**
     * @see DBManager::tableExists()
     */
    public function tableExists($tableName)
    {
        $GLOBALS['log']->info("tableExists: $tableName");

        if ($this->getDatabase()) {
            $result = $this->getOne("SHOW TABLES LIKE ".$this->quoted($tableName));
            return !empty($result);
        }

        return false;
    }

    /**
     * @see DBManager::quote()
     */
    public function quote($string)
    {
        if(is_array($string)) {
            return $this->arrayQuote($string);
        }
        return mysql_real_escape_string(parent::quote($string), $this->getDatabase());
    }

    /**
     * @see DBManager::connect()
     */
	public function connect(array $configOptions = null, $dieOnError = false)
    {
		global $sugar_config;

        if(is_null($configOptions))
			$configOptions = $sugar_config['dbconfig'];

        if ($sugar_config['dbconfigoption']['persistent'] == true) {
            $this->database = @mysql_pconnect(
                $configOptions['db_host_name'],
                $configOptions['db_user_name'],
                $configOptions['db_password']
                );
        }

        if (!$this->database) {
            $this->database = mysql_connect(
                    $configOptions['db_host_name'],
                    $configOptions['db_user_name'],
                    $configOptions['db_password']
                    );
            if(empty($this->database)) {
                $GLOBALS['log']->fatal("Could not connect to server ".$configOptions['db_host_name']." as ".$configOptions['db_user_name'].":".mysql_error());
                sugar_die($GLOBALS['app_strings']['ERR_NO_DB']);
            }
            // Do not pass connection information because we have not connected yet
            if($this->database  && $sugar_config['dbconfigoption']['persistent'] == true){
                $_SESSION['administrator_error'] = "<b>Severe Performance Degradation: Persistent Database Connections "
                    . "not working.  Please set \$sugar_config['dbconfigoption']['persistent'] to false "
                    . "in your config.php file</b>";
            }
        }
        if(!@mysql_select_db($configOptions['db_name'])) {
            $GLOBALS['log']->fatal( "Unable to select database {$configOptions['db_name']}: " . mysql_error($this->database));
            sugar_die($GLOBALS['app_strings']['ERR_NO_DB']);
        }

        // cn: using direct calls to prevent this from spamming the Logs
        $charset = "SET CHARACTER SET utf8";
        if(isset($sugar_config['dbconfigoption']['collation']) && !empty($sugar_config['dbconfigoption']['collation']))
        	$charset .= " COLLATE {$sugar_config['dbconfigoption']['collation']}";
        mysql_query($charset, $this->database); // no quotes around "[charset]"
        mysql_query("SET NAMES 'utf8'", $this->database);

        if($this->checkError('Could Not Connect:', $dieOnError))
            $GLOBALS['log']->info("connected to db");

        $GLOBALS['log']->info("Connect:".$this->database);
    }

    /**
     * @see DBManager::repairTableParams()
     *
     * For MySQL, we can write the ALTER TABLE statement all in one line, which speeds things
     * up quite a bit. So here, we'll parse the returned SQL into a single ALTER TABLE command.
     */
    public function repairTableParams($tablename, $fielddefs, $indices, $execute = true, $engine = null)
    {
        $sql = parent::repairTableParams($tablename,$fielddefs,$indices,false,$engine);

        if ( $sql == '' )
            return '';

        if ( stristr($sql,'create table') )
        {
            if ($execute) {
                $msg = "Error creating table: ".$tablename. ":";
                $this->query($sql,true,$msg);
	        }
            return $sql;
        }

        // first, parse out all the comments
        $match = array();
        preg_match_all('!/\*.*?\*/!is', $sql, $match);
        $commentBlocks = $match[0];
        $sql = preg_replace('!/\*.*?\*/!is','', $sql);

        // now, we should only have alter table statements
        // let's replace the 'alter table name' part with a comma
        $sql = preg_replace("!alter table $tablename!is",', ', $sql);

        // re-add it at the beginning
        $sql = substr_replace($sql,'',strpos($sql,','),1);
        $sql = str_replace(";","",$sql);
        $sql = str_replace("\n","",$sql);
        $sql = "ALTER TABLE $tablename $sql";

        if ( $execute )
            $this->query($sql,'Error with MySQL repair table');

        // and re-add the comments at the beginning
        $sql = implode("\n",$commentBlocks) . "\n". $sql . "\n";

        return $sql;
    }

    /**
     * @see DBManager::convert()
     */
    public function convert($string, $type, array $additional_parameters = array())
    {
        // convert the parameters array into a comma delimited string
        array_unshift($additional_parameters, $string);
        $all_strings = join(",", $additional_parameters);

        switch (strtolower($type)) {
            case 'today':
                return "CURDATE()";
            case 'left':
                return "LEFT($all_strings)";
            case 'date_format':
                if(empty($additional_parameters)) {
                    return "DATE_FORMAT($string, '%Y-%m-%d')";
                } else {
                    return "DATE_FORMAT($string, '{$additional_parameters[0]}')";
                }
            case 'datetime':
                return "DATE_FORMAT($string, '%Y-%m-%d %H:%i:%s')";
            case 'ifnull':
                return "IFNULL($all_strings)";
            case 'concat':
                return "CONCAT($all_strings)";
            case 'quarter':
                    return "QUARTER($string)";
            case "length":
                    return "LENGTH($string)";
        }

        return $string;
    }

    /**
     * Returns the name of the engine to use or null if we are to use the default
     *
     * @param  object $bean SugarBean instance
     * @return string
     */
    protected function getEngine($bean)
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
    protected function isEngineEnabled($engine)
    {
        $engine = strtoupper($engine);

        $r = $this->query("SHOW ENGINES");

        while ( $row = $this->fetchByAssoc($r) )
            if ( strtoupper($row['Engine']) == $engine )
                return ($row['Support']=='YES' || $row['Support']=='DEFAULT');

        return false;
    }

    /**
     * @see DBManager::createTableSQL()
     */
    public function createTableSQL(SugarBean $bean)
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
    public function createTableSQLParams($tablename, $fieldDefs, $indices, $engine = null)
    {
 		if ( empty($engine) && isset($fieldDefs['engine']))
            $engine = $fieldDefs['engine'];
        if ( !$this->isEngineEnabled($engine) )
            $engine = '';

        $columns = $this->columnSQLRep($fieldDefs, false, $tablename);
        if (empty($columns))
            return false;

        $keys = $this->keysSQL($indices);
        if (!empty($keys))
            $keys = ",$keys";

        // cn: bug 9873 - module tables do not get created in utf8 with assoc collation
        $sql = "CREATE TABLE $tablename ($columns $keys) CHARACTER SET utf8 COLLATE utf8_general_ci";

	    if (!empty($engine))
            $sql.= " ENGINE=$engine";

        return $sql;
	}

    /**
     * @see DBManager::oneColumnSQLRep()
     */
	protected function oneColumnSQLRep($fieldDef, $ignoreRequired = false, $table = '', $return_as_array = false)
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
     * @see DBManager::changeColumnSQL()
     */
    protected function changeColumnSQL($tablename, $fieldDefs, $action, $ignoreRequired = false)
    {
        if ($this->isFieldArray($fieldDefs)){
            foreach ($fieldDefs as $def){
                if ($action == 'drop')
                    $columns[] = $def['name'];
                else
                    $columns[] = $this->oneColumnSQLRep($def, $ignoreRequired);
            }
        } else {
            if ($action == 'drop')
                $columns[] = $fieldDefs['name'];
        else
            $columns[] = $this->oneColumnSQLRep($fieldDefs);
        }

        return "ALTER TABLE $tablename $action COLUMN ".implode(",$action column ", $columns);
    }

    /**
     * Generates SQL for key specification inside CREATE TABLE statement
     *
     * The passes array is an array of field definitions or a field definition
     * itself. The keys generated will be either primary, foreign, unique, index
     * or none at all depending on the setting of the "key" parameter of a field definition
     *
     * @param  array  $indices
     * @param  bool   $alter_table
     * @param  string $alter_action
     * @return string SQL Statement
     */
    protected function keysSQL($indices, $alter_table = false, $alter_action = '')
	{
       // check if the passed value is an array of fields.
       // if not, convert it into an array
       if (!$this->isFieldArray($indices))
           $indices[] = $indices;

       $columns = array();
       foreach ($indices as $index) {
           if(!empty($index['db']) && $index['db'] != $this->dbType)
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
     * @see DBManager::setAutoIncrement()
     */
 	protected function setAutoIncrement($table, $field_name)
    {
		return "auto_increment";
	}

   	/**
     * Sets the next auto-increment value of a column to a specific value.
     *
     * @param  string $table tablename
     * @param  string $field_name
     */
    public function setAutoIncrementStart($table, $field_name, $start_value)
    {
        $start_value = (int)$start_value;
        return $this->query( "ALTER TABLE $table AUTO_INCREMENT = $start_value;");
    }

    /**
     * Returns the next value for an auto increment
     *
     * @param  string $table tablename
     * @param  string $field_name
     * @return string
     */
    public function getAutoIncrement($table, $field_name)
    {
        $result = $this->query("SHOW TABLE STATUS LIKE '$table'");
        $row = $this->fetchByAssoc($result);
        if (!empty($row['Auto_increment']))
            return $row['Auto_increment'];

    	return "";
    }

   	/**
     * @see DBManager::get_indices()
     */
    public function get_indices($tablename)
    {
        //find all unique indexes and primary keys.
        $result = $this->query("SHOW INDEX FROM $tablename");

        $indices = array();
        while (($row=$this->fetchByAssoc($result)) !=null) {
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
     * @see DBManager::add_drop_constraint()
     */
    public function add_drop_constraint($table, $definition, $drop = false)
    {
        $type         = $definition['type'];
        $fields       = implode(',',$definition['fields']);
        $name         = $definition['name'];
        $sql          = '';

        switch ($type){
        // generic indices
        case 'index':
        case 'alternate_key':
        case 'clustered':
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
                $sql = "ALTER TABLE {$table} ADD CONSTRAINT FOREIGN KEY {$name} ({$fields}) REFERENCES {$definition['foreignTable']}({$definition['foreignField']})";
            break;
        }
        return $sql;
    }

    /**
     * Runs a query and returns a single row
     *
     * @param  string   $sql        SQL Statement to execute
     * @param  bool     $dieOnError True if we want to call die if the query returns errors
     * @param  string   $msg        Message to log if error occurs
     * @param  bool     $suppress   Message to log if error occurs
     * @return array    single row from the query
     */
    public function fetchOne($sql, $dieOnError = false, $msg = '', $suppress = false)
    {
        if(stripos($sql, ' LIMIT ') === false) {
            // little optimization to just fetch one row
            $sql .= " LIMIT 0,1";
        }
        return parent::fetchOne($sql, $dieOnError, $msg, $suppress);
    }

	/**
     * @see DBManager::full_text_indexing_enabled()
     */
    protected function full_text_indexing_enabled($dbname = null)
    {
		return $this->isEngineEnabled('MyISAM');
	}

    /**
     * @see DBManager::massageFieldDef()
     */
    public function massageFieldDef(&$fieldDef, $tablename)
    {
        parent::massageFieldDef($fieldDef,$tablename);

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

    /**
     * Generates SQL for dropping a table.
     *
     * @param  string $name table name
     * @return string SQL statement
     */
	public function dropTableNameSQL($name)
    {
		return "DROP TABLE IF EXISTS ".$name;
	}

    public function dropIndexes($tablename, $indexes, $execute = true)
    {
        $sql = array();
        foreach ($indexes as $index) {
            $name =$index['name'];
            if($execute) {
               unset(self::$index_descriptions[$tablename][$name]);
            }
            if ($index['type'] == 'primary') {
                $sql[] = 'DROP PRIMARY KEY';
            } else {
                $sql[] = "DROP INDEX $name";
            }
        }
        if (!empty($sql)) {
            $sql = "ALTER TABLE $tablename ".join(",", $sql);
            if($execute)
                $this->query($sql);
        } else {
            $sql = '';
        }
        return $sql;
	}

    /**
     * List of available collation settings
     * @return string
     */
    public function getDefaultCollation()
    {
        return "utf8_general_ci";
    }

    /**
     * List of available collation settings
     * @return array
     */
    public function getCollationList()
    {
	    $q = "SHOW COLLATION LIKE 'utf8%'";
	    $r = $this->query($q);
	    $res = array();
    	while($a = $this->fetchByAssoc($r)) {
    	    $res[] = $a['Collation'];
    	}
        return $res;
    }

    /**
     * (non-PHPdoc)
     * @see DBManager::renameColumnSQL()
     */
    public function renameColumnSQL($tablename, $column, $newname)
    {
        $field = $this->describeField($column, $tablename);
        $field['name'] = $newname;
        return "ALTER TABLE $tablename CHANGE COLUMN $column ".$this->oneColumnSQLRep($field);
    }

    public function emptyValue($type)
    {
        $ctype = $this->getColumnType($type);
        if($ctype == "datetime") {
            return $this->convert($this->quoted("0000-00-00 00:00:00"), "datetime");
        }
        if($ctype == "date") {
            return $this->convert($this->quoted("0000-00-00"), "date");
        }
        return parent::emptyValue($type);
    }

    public function lastError()
    {
        return mysql_error();
    }

    public function getFulltextQuery($field, $condition)
    {
        return "CONTAINS($field, ".$this->quoted($condition).")";
    }
}
