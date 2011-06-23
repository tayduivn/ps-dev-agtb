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
* $Id: IBMDB2Manager.php 56825 2011-04-28 00:00:00Z fsteegmans $
* Description: This file handles the Data base functionality for the application using IBM DB2.
* It acts as the DB abstraction layer for the application. It depends on helper classes
* which generate the necessary SQL. This sql is then passed to PEAR DB classes.
* The helper class is chosen in DBManagerFactory, which is driven by 'db_type' in 'dbconfig' under config.php.
*
* All the functions in this class will work with any bean which implements the meta interface.
* The passed bean is passed to helper class which uses these functions to generate correct sql.
* Please see DBManager file for details
*
*
* Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
* All Rights Reserved.
* Contributor(s): ______________________________________..
********************************************************************************/

//FILE SUGARCRM flav=ent ONLY

/**
 * Note that we are only supporting LUW 9.7 and higher at this moment
 */
class IBMDB2Manager  extends DBManager
{
    /**+
     * @see DBManager::$dbType
     */
    public $dbType = 'ibm_db2';
    public $variant = 'ibm_db2';
    public $dbName = 'IBM_DB2';

    /**+
     * @var array
     */
    protected $maxNameLengths = array(
        'table' => 128,
        'column' => 128,
        'index' => 128,
        'alias' => 128
    );

    /**+
     * Mapping recommendation derived from MySQL to DB2 guidelines
     * http://www.ibm.com/developerworks/data/library/techarticle/dm-0807patel/index.html
     * @var array
     */
    protected $type_map = array(
            'int'      => 'integer',
            'double'   => 'double',
            'float'    => 'double',
            'uint'     => 'bigint',
            'ulong'    => 'decimal(20,0)',
            'long'     => 'bigint',
            'short'    => 'smallint',
            'varchar'  => 'varchar',
            'text'     => 'clob(65535)',
            'longtext' => 'clob(2000000000)',
            'date'     => 'date',
            'enum'     => 'varchar',
            'relate'   => 'varchar',
            'multienum'=> 'clob(65535)',
            'html'     => 'clob(65535)',
            'datetime' => 'timestamp',
            'datetimecombo' => 'timestamp',
            'time'     => 'time',
            'bool'     => 'smallint', // Per recommendation here: http://publib.boulder.ibm.com/infocenter/db2luw/v9/index.jsp?topic=/com.ibm.db2.udb.apdv.java.doc/doc/rjvjdata.htm
            'tinyint'  => 'smallint',
            'char'     => 'char(1)',
            'blob'     => 'blob(65535)',
            'longblob' => 'blob(2000000000)',
            'currency' => 'decimal(26,6)',
            'decimal'  => 'decimal(20,2)', // Using Oracle numeric precision and scale as DB2 does not support decimal without it
            'decimal2' => 'decimal(30,6)', // Using Oracle numeric precision and scale as DB2 does not support decimal without it
            'id'       => 'char(36)',
            'url'      => 'varchar',
            'encrypt'  => 'varchar',
            'file'     => 'varchar',
            'decimal_tpl' => 'decimal(%d, %d)',

     );

    /**~
     * @var array
     */
    // TODO: Note sure if the $type_class is required, just keeping it for consistency with the MySQL and Oracle implementations
    protected $type_class = array(
            'int'      => 'int',
            'double'   => 'float',
            'float'    => 'float',
            'uint'     => 'int',
            'ulong'    => 'int',
            'long'     => 'int',
            'short'    => 'int',
            'date'     => 'date',
            'datetime' => 'date',
            'datetimecombo' => 'date',
            'time'     => 'time',
            'bool'     => 'bool',
            'tinyint'  => 'int',
            'currency' => 'float',
            'decimal'  => 'float',
            'decimal2' => 'float',
     );

    /**+
     * @var array
     */
    protected $capabilities = array(
        "affected_rows" => true,
        //"select_rows" => false,     // The number of rows cannot be reliably retrieved without executing the whole query
        //"inline_keys" => false,   // Since we still need indexes created separately.
        //"case_sensitive" => false, // DB2 is case insensitive by default
        "fulltext" => true, // DB2 supports this though it needs to be initialized and we are currently not capable of doing though through code. Pending request to IBM
        "auto_increment_sequence" => true, // Opted to use DB2 sequences instead of identity columns because of the restriction of only 1 identity per table
        "limit_subquery" => true,
    );

    /**+
     * Handles logging the error message
     *
     * @param   string   $msg           Message context for last_error
     * @param   bool     $dieOnError    Desired behavior
     * @param   string   $logmsg        Actual error log message
     * @return  string                  last_error message for reuse in consecutive calls
     */
    protected function handleError($msg, $dieOnError, $logmsg)
    {
        if ($this->dieOnError || $dieOnError){
            $GLOBALS['log']->fatal($logmsg);
            sugar_die ($logmsg);
        }
        else {
            $this->last_error = $msg.$logmsg;
            $GLOBALS['log']->error($logmsg);
            return $this->last_error;
        }
        return $msg;
    }

    /**~
     * @see DBManager::checkError()
     */
    public function checkError($msg = '', $dieOnError = false, $stmt = false)
    {
        if (parent::checkError($msg, $dieOnError))
            return true;

        $result = false;
        if (db2_conn_error()) {
            $logmsg = "IBM_DB2 connection error ".db2_conn_error().": ".db2_conn_errormsg();
            $msg = $this->handleError($msg, $dieOnError, $logmsg);
            $result = true;
        }
        if (!empty($stmt) && db2_stmt_error($stmt)) {/* TODO: Add statement resource from context*/
            $logmsg = "IBM_DB2 statement error ".db2_stmt_error($stmt).": ".db2_stmt_errormsg($stmt);
            $this->handleError($msg, $dieOnError, $logmsg);
            $result = true;
        }
        return $result;
    }

    /**~
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
        $GLOBALS['log']->info('Query: ' . $sql);
        $this->checkConnection();
        $this->query_time = microtime(true);
        $db = $this->getDatabase();

        $stmt = $suppress?@db2_prepare($db, $sql):db2_prepare($db, $sql);
		if(!$this->checkDB2STMTerror($stmt)) {
            $sp_msg = '';
            if(preg_match('/^CALL.+,\s*\?/i', $sql))
            {
                // 20110519 Frank Steegmans: Note at the time of this implementation we are not using stored procedures
                // anywhere except for creating full text indexes in add_drop_contraint. Furthermore
                // we are also not using parameterized prepared queries. If either one of these assumptions
                // changes this code needs to be revisited.
                if($suppress) @db2_bind_param($stmt, 1, "sp_msg", DB2_PARAM_OUT);
                else db2_bind_param($stmt, 1, "sp_msg", DB2_PARAM_OUT);
            }

			$rc = $suppress?@db2_execute($stmt):db2_execute($stmt);
	        $this->query_time = microtime(true) - $this->query_time;

            if($rc)
                $GLOBALS['log']->info('Query Execution Time: '.$this->query_time);
            else
                $GLOBALS['log']->error("Query Failed: $sql");

            if(isset($sp_msg) && $sp_msg != '')
            {
                $GLOBALS['log']->info("Return message from stored procedure call '$sql': $sp_msg");
            }

		    if($this->dump_slow_queries($sql)) {
			    $this->track_slow_queries($sql);
			}
		}

		$result = $stmt;
		$this->lastQuery = $sql;
		if($keepResult)
		    $this->lastResult = $result;

		$this->checkError($msg.' Query Failed: ' . $sql, $dieOnError, $stmt);
        return $result;
    }


    /**~
     * Checks for db2_stmt_error in the given resource
     *
     * @param  resource $obj
     * @return bool
     */
    protected function checkDB2STMTerror($obj)
    {
        if(!$obj) return true;

        $err = db2_stmt_error($obj);
        if ($err != false){
            $result = false;
            $GLOBALS['log']->fatal("DB2 Statement error: ".var_export($err, true));
            return true;
        }
        return false;
    }


    /**~
     * Disconnects from the database
     *
     * Also handles any cleanup needed
     */
    public function disconnect()
    {
    	$GLOBALS['log']->debug('Calling IBMDB2::disconnect()');
        if(!empty($this->database)){
            $this->freeResult();
            db2_close($this->database);
            $this->database = null;
        }
    }

    /**+
     * @see DBManager::freeDbResult()
     */
    protected function freeDbResult($dbResult)
    {
        if(!empty($dbResult))
            db2_free_result($dbResult);
    }

    /**+
     * @see DBManager::limitQuery()
     * NOTE that DB2 supports this on my LUW Express-C version but there may be issues
     * prior to 9.7.2. Hence depending on the versions we are supporting we may need
     * to add code for backward compatibility.
     * If we need to support this on platforms that don't support the LIMIT functionality,
     * see here: http://www.channeldb2.com/profiles/blogs/porting-limit-and-offset
     */
    public function limitQuery($sql, $start, $count, $dieOnError = false, $msg = '', $execute = true)
    {
        if ($start < 0)
            $start = 0;
        $GLOBALS['log']->debug('IBM DB2 Limit Query:' . $sql. ' Start: ' .$start . ' count: ' . $count);

        $sql = "SELECT * FROM ($sql) LIMIT $start,$count";
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

    /**~
     * Get list of DB column definitions
     *
     * More info can be found here:
     * http://publib.boulder.ibm.com/infocenter/db2luw/v9/index.jsp?topic=/com.ibm.db2.udb.admin.doc/doc/r0001047.htm
     */
    public function get_columns($tablename)
    {
        $result = $this->query(
            "SELECT * FROM SYSCAT.COLUMNS WHERE TABNAME = '".strtoupper($tablename)."'");

        $columns = array();
        while (($row=$this->fetchByAssoc($result)) !=null) {
            $name = strtolower($row['colname']);
            $columns[$name]['name']=$name;
            $columns[$name]['type']=strtolower($row['typename']);

            switch($columns[$name]['type']) {
                case 'date':
                case 'xml':
                case 'blob':
                case 'clob':
                case 'dbclob': break;
                case 'decimal': $columns[$name]['len'] = $row['length'].','.$row['scale'];
                                break;
                default: $columns[$name]['len'] = $row['length'];
            }
            if ( !empty($row['default']) ) {
                //$matches = array();
                //$row['default'] = html_entity_decode($row['default'],ENT_QUOTES); // Not sure if this is required for DB2
                //if ( preg_match("/'(.*)'/i",$row['default'],$matches) ) // NOT sure if DB2 ever puts () around a default
                //$columns[$name]['default'] = $matches[1];
                $columns[$name]['default'] = $row['default'];
            }
            // TODO add logic to make this generated when there is a sequence being used
            if($row['generated'] == 'A' || $row['generated'] == 'D')
                $columns[$name]['auto_increment'] = '1';
            $columns[$name]['required'] = ( $row['nulls'] == 'N' );
        }
        return $columns;
    }


    /**+
     * @see DBManager::getFieldsArray()
     */
	public function getFieldsArray($result, $make_lower_case = false)
	{
        if(! isset($result) || empty($result)) return 0;

		$field_array = array();
        $count = db2_num_fields($result);
        for($i = 0; $i<$count; $i++) {
            $meta = db2_field_name($result, $i);
            if (!$meta) return array();
            $field_array[]= $make_lower_case ? strtolower($meta) : $meta;
        }
        return $field_array;
    }

    /**+
     * Get number of rows affected by last operation
     * @see DBManager::getAffectedRowCount()
     */
	public function getAffectedRowCount($result)
    {
        return db2_num_rows($result);
    }

    /**~
     * Fetches the next row from the result set
     *
     * @param  resource $result result set
     * @return array
     */
    protected function db2FetchRow($result)
    {
        $row = db2_fetch_assoc($result);
        if ( !$row )
            return false;
        if ($this->checkDB2STMTerror($result) == false) {
            $temp = $row;
            $row = array();
            foreach ($temp as $key => $val)
                // make the column keys as lower case. Trim the val returned
                $row[strtolower($key)] = trim($val);
        }
        else
            return false;

        return $row;
    }

    /**~
     * @see DBManager::fetchByAssoc()
     */
    public function fetchByAssoc($result, $rowNum = -1, $encode = true)
    {
        if (!$result)
            return false;

        if (isset($result) && $result && $rowNum < 0) {
            $row = $this->db2FetchRow($result);
        } else {
            if ($this->getRowCount($result) > $rowNum)
                return array(); // cannot do seek
            $row = $this->db2FetchRow($result);
        }
        if ($row != false && $encode && $this->encode && sizeof($row)>0)
            return array_map('to_html', $row);

        return $row;
    }

    /**+
     * @param  $namepattern     LIKE Style pattern to match the table name
     * @return array|bool       returns false if no match found and an array with the matching list of names
     */
    private function getTablesArrayByName($namepattern)
    {
        if ($db = $this->getDatabase()) {
            $tables = array();
            $result = db2_tables ($db, null, '%', strtoupper($namepattern), 'TABLE');
            if (!empty($result)) {
                while ($row = $this->fetchByAssoc($result)) {
                    if(preg_match('/^sys/i', $row['table_schem']) == 0) // Since we don't know the default schema name
					    $tables[]=strtolower($row['table_name']);       // we filter out all the tables coming from system schemas
                }
                return $tables;
            }
        }

        return false; // no database available
    }

    /**+
     * @see DBManager::getTablesArray()
     */
    public function getTablesArray()
    {
        $this->log->debug('Fetching table list');
        return $this->getTablesArrayByName('%');
    }

    /**~
     * @see DBManager::version()
     * NOTE DBMS_VER may not be adequate to uniquely identify the database system for DB2
     * I.e. as per the discussion with the IBM folks, there DB2 version for different operating
     * systems can be inherently different. Hence we may need to add an implementation indicator
     * to the version. E.g. DBMS_NAME
     */
    public function version()
    {
        $dbinfo = db2_server_info($this->getDatabase());
        if($dbinfo) return $dbinfo->DBMS_VER;
        else return false;
    }

    /**+
     * @see DBManager::tableExists()
     */
    public function tableExists($tableName)
    {
        $this->log->debug("tableExists: $tableName");
        return (bool)$this->getTablesArrayByName($tableName);
    }

    /**+
     * Get tables like expression
     * @param $like string
     * @return array
     */
    public function tablesLike($like)
    {
        $this->log->debug("tablesLike: $like");
        return $this->getTablesArrayByName($like);
    }

    /**+
     * @see DBManager::quote()
     */
    public function quote($string)
    {
        if(is_array($string)) {
            return $this->arrayQuote($string);
        }
        return str_replace("'", "''", $this->quoteInternal($string));
    }

    /**~
     * @see DBManager::connect()
     */
	public function connect(array $configOptions = null, $dieOnError = false)
    {
		global $sugar_config;

        if(is_null($configOptions))
			$configOptions = $sugar_config['dbconfig'];


        if(isset($sugar_config['dbconfigoption']) && isset( $sugar_config['dbconfigoption']['persistent']))
            $persistConnection = $sugar_config['dbconfigoption']['persistent'];
        else
            $persistConnection = false;

        // Creating the connection string dynamically so that we can accommodate all scenarios
        // Starting with user and password as we always need these.
        $dsn = "UID=".$configOptions['db_user_name'].";PWD=".$configOptions['db_password'].";";

        if(isset($configOptions['db_name']) && $configOptions['db_name']!='')
            $dsn = $dsn."DATABASE=".$configOptions['db_name'].";";

        if(!isset($configOptions['db_host_name']) || $configOptions['db_host_name']=='')
            $configOptions['db_host_name'] = 'localhost';   // Connect to localhost by default
        $dsn = $dsn."HOSTNAME=".$configOptions['db_host_name'].";";

        if(!isset($configOptions['db_protocol']) || $configOptions['db_protocol']=='')
            $configOptions['db_protocol'] = 'TCPIP';   // Use TCPIP as default protocol
        $dsn = $dsn."PROTOCOL=".$configOptions['db_protocol'].";";

        if(!isset($configOptions['db_port']) || $configOptions['db_port']=='')
            $configOptions['db_port'] = '50000';   // Use 50000 as the default port
        $dsn = $dsn."PORT=".$configOptions['db_port'].";";

        if(!isset($configOptions['db_options']))
            $configOptions['db_options'] = array();

        if ($persistConnection) {
            $this->database = db2_pconnect($dsn, '', '', $configOptions['db_options']);
        }

        if (!$this->database) {
            $this->database = db2_connect($dsn, '', '', $configOptions['db_options']);
            if($this->database  && $persistConnection){
                $_SESSION['administrator_error'] = "<b>Severe Performance Degradation: Persistent Database Connections "
                    . "not working.  Please set \$sugar_config['dbconfigoption']['persistent'] to false "
                    . "in your config.php file</b>";
            }
        }

        if($this->checkError('Could Not Connect:', $dieOnError))
            $GLOBALS['log']->info("connected to db");

        $GLOBALS['log']->info("Connect:".$this->database);
        return true;
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

    /**~
    * @see DBManager::convert()
    */
   public function convert($string, $type, array $additional_parameters = array())
   {
       if (!empty($additional_parameters)) {
           $additional_parameters_string = ','.implode(',',$additional_parameters);
       } else {
           $additional_parameters_string = '';
       }
       $all_parameters = $additional_parameters;
       if(is_array($string)) {
           $all_parameters = array_merge($string, $all_parameters);
       } elseif (!is_null($string)) {
           array_unshift($all_parameters, $string);
       }

       switch (strtolower($type)) {
           case 'date':
               return "to_date($string, 'YYYY-MM-DD')";
           case 'time':
               return "to_date($string, 'HH24:MI:SS')";
           case 'datetime':
               return "to_date($string, 'YYYY-MM-DD HH24:MI:SS'$additional_parameters_string)";
           case 'today':
               return "sysdate";
           case 'left':
               return "LTRIM($string$additional_parameters_string)";
           case 'date_format':
               if(!empty($additional_parameters[0]) && $additional_parameters[0][0] == "'") {
                   $additional_parameters[0] = trim($additional_parameters[0], "'");
               }
               if(!empty($additional_parameters) && isset($this->date_formats[$additional_parameters[0]])) {
                   $format = $this->date_formats[$additional_parameters[0]];
                   return "TO_CHAR($string, '$format')";
               } else {
                  return "TO_CHAR($string, 'YYYY-MM-DD')";
               }
           case 'time_format':
               if(empty($additional_parameters_string)) {
                   $additional_parameters_string = ",'HH24:MI:SS'";
               }
               return "TO_CHAR($string".$additional_parameters_string.")";
           case 'ifnull':
               if(empty($additional_parameters_string)) {
                   $additional_parameters_string = ",''";
               }
               return "NVL($string$additional_parameters_string)";
           case 'concat':
               return implode("||",$all_parameters);
           case 'text2char':
               return "cast($string as VARCHAR(32000))";
           case 'quarter':
               return "TO_CHAR($string, 'Q')";
           case "length":
               return "LENGTH($string)";
           case 'month':
               return "TO_CHAR($string, 'MM')";
           case 'add_date':
               switch(strtolower($additional_parameters[1])) {
                   case 'quarter':
                       $additional_parameters[0] .= "*3";
                       // break missing intentionally
                   case 'month':
                       return "ADD_MONTHS($string, {$additional_parameters[0]})";
                   case 'week':
                       $additional_parameters[0] .= "*7";
                       // break missing intentionally
                   case 'day':
                       return "($string + $additional_parameters[0])";
                   case 'year':
                       return "ADD_MONTHS($string, {$additional_parameters[0]}*12)";
               }
               break;
           case 'add_time':
               return "$string + {$additional_parameters[0]}/24 + {$additional_parameters[1]}/1440";
       }

       return $string;
   }


    /**+
     * @see DBManager::fromConvert()
     */
    public function fromConvert($string, $type)
    {
        // YYYY-MM-DD HH:MM:SS
        switch($type) {
            case 'date': return substr($string, 0, 10);
            case 'time': return substr($string, 11,8);
            case 'datetime': return substr($string, 0,19);
		}
		return $string;
    }

    /**+
     * @see DBManager::createTableSQLParams()
	 */
	public function createTableSQLParams($tablename, $fieldDefs, $indices)
    {
        $columns = $this->columnSQLRep($fieldDefs, false, $tablename);
        if (empty($columns))
            return false;

        $sql = "CREATE TABLE $tablename ($columns)";
        $GLOBALS['log']->info("IBMDB2Manager.createTableSQLParams: ".$sql);
        return $sql;
	}


	/**~
     * @see DBHelper::oneColumnSQLRep()
     */
    protected function oneColumnSQLRep($fieldDef, $ignoreRequired = false, $table = '', $return_as_array = false)
    {
		if(isset($fieldDef['name'])){
        	if(stristr($this->getFieldType($fieldDef), 'decimal') && isset($fieldDef['len'])) {
				$fieldDef['len'] = min($fieldDef['len'],31); // DB2 max precision is 31 for LUW, may be different for other OSs
			}
		}
        //May need to add primary key and sequence stuff here
		$ref = parent::oneColumnSQLRep($fieldDef, $ignoreRequired, $table, true);
        if ( $return_as_array )
            return $ref;
        else{
            if($ref['required'] == 'NULL') {
                // DB2 doesn't have NULL definition, only NOT NULL
                $ref['required'] = ''; // ONLY important when statement is rendered
            }
            return "{$ref['name']} {$ref['colType']} {$ref['default']} {$ref['required']} {$ref['auto_increment']}";
        }
    }

    protected function alterTableSQL($tablename, $columnspecs)
    {
        return "ALTER TABLE $tablename $columnspecs";
    }

    protected function alterTableColumnSQL($action, $columnspec)
    {
        return "$action COLUMN $columnspec";
    }

    /**
     * 
     * Generates a sequence of SQL statements to accomplish the required column alterations
     *
     * @param  $tablename
     * @param  $def
     * @param bool $ignoreRequired
     * @return void
     */
    protected function alterOneColumnSQL($tablename, $def, $ignoreRequired = false) {
        // Column attributes can only be modified one sql statement at a time
        // http://publib.boulder.ibm.com/infocenter/db2luw/v9/index.jsp?topic=/com.ibm.db2.udb.admin.doc/doc/c0023297.htm
        // Some rework maybe needed when targetting other versions than LUW 9.7
        // http://publib.boulder.ibm.com/infocenter/db2luw/v9r7/index.jsp?topic=/com.ibm.db2.luw.wn.doc/doc/c0053726.html
        $sql = '';
        $req = $this->oneColumnSQLRep($def, $ignoreRequired, $tablename, true);
        $alter = $this->alterTableSQL($tablename, $this->alterTableColumnSQL('ALTER', $req['name']));
        $descr = $this->describeField($req['name'], $tablename);

        $GLOBALS['log']->info("IBMDB2Manager.alterOneColumnSQL old field description for column {$req['name']} in table '$tablename'" . print_r($descr, true));

        // NOTE not checking if datatype changed since it is a compound and as it will not cause any errors if the type is already the same
        $sql .= "$alter SET DATA TYPE {$req['colType']};";
        $sql .= "$alter SET NOT NULL;";

//        if($descr['required'] != $req['required'])
//        {
//            switch($req['required']) {
//                case 'NULL':        $sql .= "$alter DROP NOT NULL;\n";   break;
//                case 'NOT NULL':    $sql .= "$alter SET NOT NULL;\n";    break;
//            }
//        }

//        if($descr['default'] != $req['default'])
//        {
//            if($req['default'] == '') {
//                $sql .= "$alter DROP DEFAULT; ";
//            } else {
//                $sql .= "$alter SET DEFAULT {$req['default']}; ";
//            }
//        }

        return $sql;
    }

    /**
     *
     * Generates the column specific SQL statement to accomplish the change action.
     * This can be used as part of an ALTER TABLE statement for the ADD and DROP or
     * is a standalone sequence of SQL statement for the MODIFY action.
     *
     * @param   string  $tablename
     * @param   array   $def                 Column definition
     * @param   string  $action              Change Action
     * @param   bool    $ignoreRequired
     * @return  string                       Returns the SQL required to change this one column
     */
    protected function changeOneColumnSQL($tablename, $def, $action, $ignoreRequired = false) {
        switch($action) {
            case "ADD":
                $sql = $this->alterTableColumnSQL($action,
                                                  $this->oneColumnSQLRep($def, $ignoreRequired, $tablename));
                break;
            case "DROP":
                $sql = $this->alterTableColumnSQL($action, $def['name']);
                break;
            case "MODIFY":
                $sql = $this->alterOneColumnSQL($tablename, $def, $ignoreRequired);
                break;
            default:
                $sql = null;
                $GLOBALS['log']->fatal("IBMDB2Manager.changeOneColumnSQL unknown change action '$action' for table '$tablename'");
                break;
        }
        return $sql;
    }

    /**
     * @see DBManager::changeColumnSQL()
     */
    protected function changeColumnSQL($tablename, $fieldDefs, $action, $ignoreRequired = false)
    {
        $action = strtoupper($action);
        $columns = array();
        if ($this->isFieldArray($fieldDefs)){
            foreach ($fieldDefs as $def){
                $columns[] = $this->changeOneColumnSQL($tablename, $def, $action, $ignoreRequired);
            }
        } else {
            $columns[] = $this->changeOneColumnSQL($tablename, $fieldDefs, $action, $ignoreRequired);
        }

        if($action == 'MODIFY') {
            $sql =  implode("; ", $columns); // Column alteration statements are 1 or multiple self contained statements
        } else {
            $sql =  $this->alterTableSQL($tablename, implode(" ", $columns));
        }

        return $sql;
    }


    /**+
     * Returns the next value for an auto increment
     *
     * @param  string $table tablename
     * @param  string $field_name
     * @return string
     */
    public function getAutoIncrement($table, $field_name)
    {
        $seqName = $this->_getSequenceName($table, $field_name, true);
        // NOTE that we are not changing the sequence nor can we garantuee that this will be the next value
        $currval = $this->getOne("SELECT PREVVAL FOR $seqName from SYSIBM.SYSDUMMY1");
        if (!empty($currval))
            return $currval + 1 ;
        else
            return "";
    }

    /**+
     * Returns the sql for the next value in a sequence
     *
     * @param  string $table tablename
     * @param  string $field_name
     * @return string
     */
    public function getAutoIncrementSQL($table, $field_name)
    {
        $seqName = $this->_getSequenceName($table, $field_name, true);
        return "NEXTVAL FOR $seqName";
    }


    /**~
     * Generate an DB2 SEQUENCE name similar to Oracle.
     *
     * @param string $table
     * @param string $field_name
     * @param boolean $upper_case
     * @return string
     */
    protected function _getSequenceName($table, $field_name, $upper_case = true)
    {
        $sequence_name = $this->getValidDBName($table. '_' .$field_name . '_seq', true, 'index');
        if($upper_case)
            $sequence_name = strtoupper($sequence_name);
        return $sequence_name;
    }

    /**+
     * @see DBHelper::setAutoIncrement()
     */
    protected function setAutoIncrement($table, $field_name)
    {
      	$this->deleteAutoIncrement($table, $field_name);
        $seqName = $this->_getSequenceName($table, $field_name, true);
      	$this->query("CREATE SEQUENCE $seqName START WITH 0 INCREMENT BY 1 NO MAXVALUE NO CYCLE");
        return "";
    }

    /**+
     * Sets the next auto-increment value of a column to a specific value.
     *
     * @param  string $table tablename
     * @param  string $field_name
     */
    public function setAutoIncrementStart($table, $field_name, $start_value)
    {
        $sequence_name = $this->_getSequenceName($table, $field_name, true);
        if ($this->_findSequence($sequence_name)) {
            $this->query("ALTER SEQUENCE $sequence_name RESTART WITH $start_value");
            return true;
        } else {
            return false;
        }
    }

	/**+
     * @see DBHelper::deleteAutoIncrement()
     */
    public function deleteAutoIncrement($table, $field_name)
    {
	  	$sequence_name = $this->_getSequenceName($table, $field_name, true);
	  	if ($this->_findSequence($sequence_name)) {
            $this->query('DROP SEQUENCE ' .$sequence_name);
        }
    }

    /**+
     * Returns true if the sequence name given is found
     *
     * @param  string $name
     * @return bool   true if the sequence is found, false otherwise
     * TODO: check if some caching here makes sense, keeping in mind bug 43148
     */
    protected function _findSequence($name)
    {
        $uname = strtoupper($name);
        $row = $this->fetchOne("SELECT SEQNAME FROM SYSCAT.SEQUENCES WHERE SEQNAME = '$uname'");
        return !empty($row);
    }

   	/**+
    * @see DBManager::get_indices()
    *
    * NOTE normally the db2_statistics should produce the indices in an implementation indepent manner.
    * However it wasn't producing any results for the LUW Express-C edition running on Vista.
    * Furthermore using a permanent connections resulted in unexplainable PHP errors.
    * Falling back to system views to retrieve this data:
    * http://publib.boulder.ibm.com/infocenter/db2luw/v9/topic/com.ibm.db2.udb.admin.doc/doc/r0001047.htm
    */
    public function get_indices($tablename)
    {
        $tablename = strtoupper($tablename);

		$query = <<<EOQ
                SELECT i.INDNAME, UNIQUERULE, COLNAME, COLSEQ FROM SYSCAT."INDEXES" i
                	INNER JOIN SYSCAT."INDEXCOLUSE" c
		                ON i.INDNAME = c.INDNAME
                WHERE TABNAME = '$tablename'
                ORDER BY i.INDNAME, COLSEQ
EOQ;

        $result = $this->query($query);

        $indices = array();
		while (($row=$this->fetchByAssoc($result)) !=null) {
            $index_type = @self::$indexTypeMap[$row['uniquerule']] or $index_type='index'; // use 'index' as default if rule is not in indexTypeMap
            $name = strtolower($row['indname']);
            $indices[$name]['name']=$name;
            $indices[$name]['type']=$index_type;
            $indices[$name]['fields'][]=strtolower($row['colname']);
        }

        return $indices;
    }
    private static $indexTypeMap = array('D' => 'index', 'P' => 'primary', 'U' => 'unique');


    /**~
     * @see DBHelper::add_drop_constraint()
     * Note: Tested all constructs pending feedback from IBM on text search index creation from code
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
                $sql = "DROP INDEX {$name}";
            else
                $sql = "CREATE INDEX {$name} ON {$table} ({$fields})";
            break;
        // constraints as indices
        case 'unique':
               // NOTE: DB2 doesn't allow null columns in UNIQUE constraint. Hence
               // we will not enforce the uniqueness other than through Indexes which does treats nulls as 1 value.
            if ($drop)
                $sql = "DROP INDEX {$name}";
            else
                $sql = "CREATE UNIQUE INDEX {$name} ON {$table} ({$fields})";
            break;
        case 'primary':
            if ($drop)
                $sql = "ALTER TABLE {$table} DROP PRIMARY KEY";
            else
                $sql = "ALTER TABLE {$table} ADD CONSTRAINT {$name} PRIMARY KEY ({$fields})";
            break;
        case 'foreign':
            if ($drop)
                $sql = "ALTER TABLE {$table} DROP FOREIGN KEY ({$fields})";
            else
                $sql = "ALTER TABLE {$table} ADD CONSTRAINT {$name} FOREIGN KEY ({$fields}) REFERENCES {$definition['foreignTable']}({$definition['foreignField']})";
            break;
        case 'fulltext':
            /**
             * Until we have a better place to put this, here is a reference to how to install text search
             * http://publib.boulder.ibm.com/infocenter/db2luw/v9r7/index.jsp?topic=/com.ibm.db2.luw.admin.ts.doc/doc/c0053115.html
             * http://www.ibm.com/developerworks/data/tutorials/dm-0810shettar/index.html
             * http://publib.boulder.ibm.com/infocenter/db2luw/v9r5/index.jsp?topic=/com.ibm.db2.luw.sql.rtn.doc/doc/r0051989.html
             */
            $local = isset($definition['message_locale']) ? $definition['message_locale'] : "";
            if ($drop)
                $sql = "CALL SYSPROC.SYSTS_DROP('', '{$name}', '{$local}', ?)";
            else
            {
                $options = isset($definition['options']) ? $definition['options'] : "";
                $sql = "CALL SYSPROC.SYSTS_CREATE('', '{$name}', '{$table} ({$fields})', '{$options}', '{$local}', ?)";
            }
            // Note that the message output parameter is bound automatically and logged in query
            $sql = strtoupper($sql); // When using stored procedures DB2 becomes case sensitive.
            break;
        }

        $GLOBALS['log']->info("IBMDB2Manager.add_drop_constraint: ".$sql);
        return $sql;
    }


	/**+
     * @see DBManager::full_text_indexing_installed()
     */
    public function full_text_indexing_installed()
    {
		return true;
		// Part of DB2 since version 9.5 (http://www.ibm.com/developerworks/data/tutorials/dm-0810shettar/index.html)
        // However there doesn't seem to be a programmatic way to create the text search indexes.
        // Pending reply from IBM marking this as unsupported.
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

    /**+
     * Generates SQL for dropping a table.
     *
     * @param  string $name table name
     * @return string SQL statement
     */
	public function dropTableNameSQL($name)
    {
        return parent::dropTableNameSQL(strtoupper($name));
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

    /**+
     * @see DBManager::renameColumnSQL()
     * Only supported
     */
    public function renameColumnSQL($tablename, $column, $newname)
    {
        return "ALTER TABLE $tablename RENAME COLUMN '$column' TO '$newname'";
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

    /**+
     * Quote DB2 search term
     * @param string $term
     * @return string
     */
    protected function quoteTerm($term)
    {
        if(strpos($term, ' ') !== false) {
            return '"'.$term.'"';
        }
        return $term;
    }

    /**~
     * Generate fulltext query from set of terms
     * @param string $fields Field to search against
     * @param array $terms Search terms that may be or not be in the result
     * @param array $must_terms Search terms that have to be in the result
     * @param array $exclude_terms Search terms that have to be not in the result
     */
    public function getFulltextQuery($field, $terms, $must_terms = array(), $exclude_terms = array())
    {
        $condition = array();
        foreach($terms as $term) {
            $condition[] = "?".$this->quoteTerm($term);
        }
        foreach($must_terms as $term) {
            $condition[] = "+".$this->quoteTerm($term);
        }
        foreach($exclude_terms as $term) {
            $condition[] = "-".$this->quoteTerm($term);
        }
        $condition = $this->quoted(join(" ",$condition));

        return "CONTAINS($field, $condition) = 1";
    }

    /**+
     * @return array
     */
    public function getDbInfo()
    {
        return array(
          	"IBM DB2 Client Info" => @db2_client_info($this->database),
      		"IBM DB2 Server Info" => @db2_server_info($this->database),
          );
    }

    public function validateQuery($query)
    {
        $res = $this->getOne("EXPLAIN $query");
        return !empty($res);
    }

    protected function makeTempTableCopy($table)
    {
        $this->log->debug("creating temp table for [$table]...");
        $create = $this->getOne("SHOW CREATE TABLE {$table}");
        if(empty($create)) {
            return false;
        }
        // rewrite DDL with _temp name
        $tempTableQuery = str_replace("CREATE TABLE `{$table}`", "CREATE TABLE `{$table}__uw_temp`", $create);
        $r2 = $this->query($tempTableQuery);
        if(empty($r2)) {
            return false;
        }

        // get sample data into the temp table to test for data/constraint conflicts
        $this->log->debug('inserting temp dataset...');
        $q3 = "INSERT INTO `{$table}__uw_temp` SELECT * FROM `{$table}` LIMIT 10";
        $this->query($q3, false, "Preflight Failed for: {$q3}");
        return true;
    }

    /**
     * Tests an ALTER TABLE query
     * @param string table The table name to get DDL
     * @param string query The query to test.
     * @return string Non-empty if error found
     */
    protected function verifyAlterTable($table, $query)
    {
        $this->log->debug("verifying ALTER TABLE");
    	// Skipping ALTER TABLE [table] DROP PRIMARY KEY because primary keys are not being copied
	    // over to the temp tables
	    if(strpos(strtoupper($query), 'DROP PRIMARY KEY') !== false) {
            $this->log->debug("Skipping DROP PRIMARY KEY");
		    return '';
	    }
        if(!$this->makeTempTableCopy($table)) {
            return 'Could not create temp table copy';
        }

		// test the query on the test table
		$this->log->debug('testing query: ['.$query.']');
		$tempTableTestQuery = str_replace("ALTER TABLE `{$table}`", "ALTER TABLE `{$table}__uw_temp`", $query);
		if (strpos($tempTableTestQuery, 'idx') === false) {
			if(strpos($tempTableTestQuery, '__uw_temp') === false) {
			    return 'Could not use a temp table to test query!';
			}

			$this->log->debug('testing query on temp table: ['.$tempTableTestQuery.']');
			$this->query($tempTableTestQuery, false, "Preflight Failed for: {$query}");
		} else {
			// test insertion of an index on a table
			$tempTableTestQuery_idx = str_replace("ADD INDEX `idx_", "ADD INDEX `temp_idx_", $tempTableTestQuery);
			$this->log->debug('testing query on temp table: ['.$tempTableTestQuery_idx.']');
			$this->query($tempTableTestQuery_idx, false, "Preflight Failed for: {$query}");
		}
		$mysqlError = $this->lastError();
		if(!empty($mysqlError)) {
            return $mysqlError;
		}
        $this->dropTableName("{$table}__uw_temp");

	    return '';
    }

    protected function verifyGenericReplaceQuery($querytype, $table, $query)
    {
        $this->log->debug("verifying $querytype statement");

        if(!$this->makeTempTableCopy($table)) {
            return 'Could not create temp table copy';
        }
        // test the query on the test table
        $this->log->debug('testing query: ['.$query.']');
        $tempTableTestQuery = str_replace("$querytype `{$table}`", "$querytype `{$table}__uw_temp`", $query);
        if(strpos($tempTableTestQuery, '__uw_temp') === false) {
            return 'Could not use a temp table to test query!';
        }

        $this->query($tempTableTestQuery, false, "Preflight Failed for: {$query}");
        $error = $this->lastError(); // empty on no-errors
        $this->dropTableName("{$table}__uw_temp"); // just in case
        return $error;
    }

    /**
     * Tests a DROP TABLE query
     * @param string table The table name to get DDL
     * @param string query The query to test.
     * @return string Non-empty if error found
     */
    public function verifyDropTable($table, $query)
    {
        return $this->verifyGenericReplaceQuery("DROP TABLE", $table, $query);
    }

    /**
     * Execute data manipulation statement, then roll it back
     * @param  $type
     * @param  $table
     * @param  $query
     * @return string
     */
    protected function verifyGenericQueryRollback($type, $table, $query)
    {
        $db = $this->database;
        $this->log->debug("verifying $type statement");
        $stmt = db2_prepare($db, $query);
        if(!$stmt) {
            return 'Cannot prepare statement';
        }
        $ac = db2_autocommit($db);
        db2_autocommit($db, DB2_AUTOCOMMIT_OFF);
        // try query, but don't generate result set and do not commit
        $res = db2_execute($stmt, OCI_DESCRIBE_ONLY|OCI_NO_AUTO_COMMIT);
        // just in case, rollback all changes
        $error = $this->lastError();
        db2_rollback($db);
        db2_free_stmt($stmt); // It would be a good idea to keep this and reuse it.
        db2_autocommit($db, $ac);

        if(!$res) {
            return 'Query failed to execute';
        }
        return $error;
    }

    /**
     * Tests an INSERT INTO query
     * @param string table The table name to get DDL
     * @param string query The query to test.
     * @return string Non-empty if error found
     */
    public function verifyInsertInto($table, $query)
    {
        return $this->verifyGenericQueryRollback("INSERT", $table, $query);
    }

    /**
     * Tests an UPDATE query
     * @param string table The table name to get DDL
     * @param string query The query to test.
     * @return string Non-empty if error found
     */
    public function verifyUpdate($table, $query)
    {
        return $this->verifyGenericQueryRollback("UPDATE", $table, $query);
    }

    /**
     * Tests an DELETE FROM query
     * @param string table The table name to get DDL
     * @param string query The query to test.
     * @return string Non-empty if error found
     */
    public function verifyDeleteFrom($table, $query)
    {
        return $this->verifyGenericQueryRollback("DELETE", $table, $query);
    }

    /**+
     * Check if certain database exists
     * @param string $dbname
     * With DB2 the admin creates the database and we cannot connect without full credentials and the database name.
     */
    public function dbExists($dbname)
    {
        return true;
    }


    /**~
     * Check if certain DB user exists
     * @param string $username
     * DB2 has no concept of a 'database' user. It uses Operating System users that may
     * have or not have access GRANTED to certain aspects of the database. I.e. it will
     * delegate user authentication to the OS.
     */
    public function userExists($username)
    {
        //TODO Should we implement an OS verification if a user exists???
        return true;
    }

    /**+
     * Create DB user
     * @param string $database_name
     * @param string $host_name
     * @param string $user
     * @param string $password
     * DB2 has no concept of a 'database' user. It uses Operating System users that may
     * have or not have access GRANTED to certain aspects of the database. I.e. it will
     * delegate user authentication to the OS.
     */
    public function createDbUser($database_name, $host_name, $user, $password)
    {
        return true;
    }

    /**+
     * Create a database
     * @param string $dbname
     * DB2 does not support the programmatic creation of databases. The admin
     * will have the create the database manually.
     */
    public function createDatabase($dbname)
    {
        return true;
    }

    /**+
     * Drop a database
     * @param string $dbname
     * DB2 does not support the programmatic creation of databases.
     */
    public function dropDatabase($dbname)
    {
       // return $this->query("DROP DATABASE `$dbname`", true);
        return true;
    }

    /**+
     * Check if this driver can be used
     * @return bool
     */
    public function valid()
    {
        return function_exists("db2_connect");
    }
}