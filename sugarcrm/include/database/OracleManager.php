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
* $Id: OracleManager.php 56825 2010-06-04 00:09:04Z smalyshev $
* Description: This file handles the Data base functionality for the application using oracle.
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

class OracleManager extends DBManager
{
    /**
     * @see DBManager::$dbType
     */
    public $dbType = 'oci8';

    /**
     * @see DBManager::$backendFunctions
     */
    protected $backendFunctions = array(
        'free_result' => 'oci_free_statement',
        'close'       => 'oci_close',
        'row_count'   => 'oci_num_rows',
        );

    /**
     * internal property if this is a new query or not
     */
    private $newQuery = true;

	/**
     * contains the last result set returned from query()
     */
    protected $_lastResult;

    protected $capabilities = array(
        "affected_rows" => true,
    );

    /**
     * @see DBManager::createTable()
	 */
    public function createTable(
        SugarBean $bean
        )
    {
        parent::createTable($bean);

        // handle constraints and indices
        $indicesArr = $this->helper->createConstraintSql($bean);
        if (count($indicesArr) > 0)
        	foreach ($indicesArr as $indexSql)
        		$bean->db->query($indexSql);
    }

    /**
     * @see DBManager::createTable()
	 */
    public function createTableParams(
        $tablename,
        $fieldDefs,
        $indices
        )
    {
        parent::createTableParams($tablename,$fieldDefs,$indices);
        if (!empty($fieldDefs)) {
            // handle constraints and indices
            $indicesArr = $this->helper->getConstraintSql($indices, $tablename);
            if (count($indicesArr) > 0)
                foreach ($indicesArr as $indexSql)
                    $this->query($indexSql);
        }
    }

    public function repairTableParams(
        $tablename,
        $fielddefs,
        $indices,
        $execute = true,
        $engine = null
        )
    {
        //Modules with names close to 30 characters may have index names over 30 characters, we need to clean them
        foreach ($indices as $key => $value) {
            $indices[$key]['name'] = OracleHelper::getValidDBName($value['name'], true, 'index');
        }

        return parent::repairTableParams($tablename,$fielddefs,$indices,$execute,$engine);

    }
    /**
     * @see DBManager::version()
     */
    public function version()
    {
        return $this->getOne(
            "SELECT version FROM product_component_version
                WHERE product like '%Oracle%'");
    }

    /**
     * Checks for oci_errors in the given resource
     *
     * @param  resource $obj
     * @return bool
     */
    private function checkOCIerror(
        $obj
        )
    {
        $err = oci_error($obj);
        if ($err != false){
            $result = false;
            $GLOBALS['log']->fatal("OCI error:".var_export($err, true));
            return true;
        }
        return false;
    }

    /**
     * @see DBManager::checkError()
     */
    public function checkError(
        $msg = '',
        $dieOnError = false
        )
    {
        if (parent::checkError($msg, $dieOnError))
            return true;

        $err = oci_error($this->getDatabase());
        if ($err){
            $error = $err['code']."-".$err['message'];
            if($this->dieOnError || $dieOnError){
                $GLOBALS['log']->fatal("Oracle error: $error");
                sugar_die($GLOBALS['app_strings']['ERR_DB_FAIL']);
            }else{
                $this->last_error = $msg."Oracle error: $error";
                $GLOBALS['log']->error("Oracle error: $error");
            }
            return true;
        }
        return false;
    }

	/**
     * Parses and runs queries
     *
     * @param  string   $sql               SQL Statement to execute
     * @param  bool     $dieOnError        True if we want to call die if the query returns errors
     * @param  string   $msg               Message to log if error occurs
     * @param  bool     $suppress          Flag to suppress all error output unless in debug logging mode.
     * @param  bool     $endSessionOnError True if we want to end the session if the query returns errors
     * @return resource result set
     */
    public function query(
        $sql,
        $dieOnError = false,
        $msg = '',
        $suppress = false,
        $endSessionOnError = false
        )
    {
        parent::countQuery($sql);
        $GLOBALS['log']->info('Query:' . $sql);
        $this->checkConnection();
        $this->query_time = microtime(true);

        if ($suppress) {
			$stmt = @oci_parse($this->getDatabase(), $sql);
		    $err = oci_error($this->getDatabase());

			if($err != false)
				$GLOBALS['log']->debug("OCI error:".var_export($err, true));
			else {
				@oci_execute($stmt);

	            $this->query_time = microtime(true) - $this->query_time;
	            $GLOBALS['log']->info('Query Execution Time:'.$this->query_time);
	            //BEGIN SUGARCRM flav=pro ONLY
				if($this->dump_slow_queries($sql)) {
				   $this->track_slow_queries($sql);
				}
				//END SUGARCRM flav=pro ONLY
			}

			$result = $stmt;
		}
        else {
		    $stmt = oci_parse($this->getDatabase(), $sql);
		    $err = oci_error($this->getDatabase());

		    if ($err != false) {
                $GLOBALS['log']->fatal("OCI error:".var_export($err, true));
                $result = false;
		    }
            else {
                oci_execute($stmt);

	            $this->query_time = microtime(true) - $this->query_time;
	            $GLOBALS['log']->info('Query Execution Time:'.$this->query_time);
	            //BEGIN SUGARCRM flav=pro ONLY
				if($this->dump_slow_queries($sql)) {
				   $this->track_slow_queries($sql);
				}
				//END SUGARCRM flav=pro ONLY

                $result = $stmt;
		    }
		    $this->lastmysqlrow = -1;
		    $this->newQuery = true;
		    $this->lastQuery = $sql;
		    $this->_lastResult = $result;

		    if ( $this->checkError($msg.' Query Failed: ' . $sql, $dieOnError) ) {
		        $result = false;
		        if ($endSessionOnError) {
                    global $app_strings;
                    if (!empty($app_strings['ERR_DATABASE_CONN_DROPPED'])) {
                        echo $app_strings['ERR_DATABASE_CONN_DROPPED'];
                    }
                    else {
                        echo("Error executing a query. Possibly, your database dropped the connection. Please refresh this page, you may need to restart you web server.");
                    }
                    sugar_cleanup(true);
                }
            }
		}

        return $result;
    }

    /**
     * @see DBManager::describeField()
     */
	protected function describeField(
        $name,
        $tablename
        )
    {
        global $table_descriptions;
        if(isset($table_descriptions[$tablename]) && isset($table_descriptions[$tablename][$name])){
            return 	$table_descriptions[$tablename][$name];
        }
        $table_descriptions[$tablename] = array();

        $sql = sprintf( "SELECT COLUMN_NAME
			   , DATA_TYPE
			   , CASE WHEN DATA_TYPE = 'VARCHAR2' THEN DATA_LENGTH ELSE NULL END AS CHARACTER_MAXIMUM_LENGTH
			   , NULLABLE
			   , DATA_DEFAULT
			FROM user_tab_columns
			WHERE TABLE_NAME = '%s'",
			strtoupper($tablename)
        );


        $result = $this->query($sql);
        while ($row = $this->fetchByAssoc($result) ) {
			//Oracle is very strict with reserved words. null and default are both
			//reserved words and should not be used as column aliases. so to fix the
			//problem, we use the fixedKeys array workaround.
        	$fixedKeys = array(
        		'Field' => strtolower($row[strtolower('COLUMN_NAME')]),
        		'Type' => strtolower($row[strtolower('DATA_TYPE')]),
        		'CHARACTER_MAXIMUM_LENGTH' => strtolower($row[strtolower('CHARACTER_MAXIMUM_LENGTH')]),
        		'Null' => strtolower($row[strtolower('NULLABLE')]),
        		'Default' => strtolower($row[strtolower('DATA_DEFAULT')])
        	);

            $table_descriptions[$tablename][$fixedKeys['Field']] = $fixedKeys;
        }
        if(isset($table_descriptions[$tablename][$name])){
            return 	$table_descriptions[$tablename][$name];
        }
        return array();
    }

    /**
     * @see DBManager::describeIndex()
     */
    protected function describeIndex(
        $name,
        $tablename
        )
    {
		$repair_table=((strtolower($tablename) == 'repair_table') ? true : false);

		$orig_name=$name;
		$name = ($repair_table ? $this->helper->repair_index_name($name) : $name);
    	$name = $this->helper->fixIndexName($name);

        global $table_descriptions;
        if(isset($table_descriptions[$tablename]) && isset($table_descriptions[$tablename]['indexes']) && isset($table_descriptions[$tablename]['indexes'][$name])){
            return 	$table_descriptions[$tablename]['indexes'][$name];
        }

        $table_descriptions[$tablename]['indexes'] = array();

        $result = $this->helper->get_indices($tablename,$name);

		foreach($result as $index_name => $row) {
            if(!isset($table_descriptions[$tablename]['indexes'][$orig_name]))
                $table_descriptions[$tablename]['indexes'][$orig_name] = array();

            if (isset($row['name']))
            	$row['name']=$orig_name;

            $table_descriptions[$tablename]['indexes'][$orig_name]['Column_name'] = $row;
		}


		if(isset($table_descriptions[$tablename]['indexes'][$name]))
            return 	$table_descriptions[$tablename]['indexes'][$name];

        return array();
    }

    /**
     * @see DBManager::checkQuery()
     */
    protected function checkQuery(
        $sql
        )
    {
        $name = (empty($GLOBALS['current_user']) || empty($GLOBALS['current_user']->user_name))
            ? 'generic' : $GLOBALS['current_user']->user_name;
        $id = 'sugar' .$name;
        $sql = "EXPLAIN PLAN SET statement_id='" . $id . "' FOR " . $sql ;

        $this->query($sql);

        $result = $this->query("SELECT * FROM plan_table WHERE statement_id='$id' AND object_type='TABLE' AND options='FULL'");
        $badQuery = array();
        $minCost = (!empty($GLOBALS['sugar_config']['check_query_cost']))?$GLOBALS['sugar_config']['check_query_cost']:10;
        while ($row = $this->fetchByAssoc($result)) {
            if ($row['cost'] < $minCost)
                continue;

            $table = $row['object_name'];
            $badQuery[$table] = '';
            if($row['options'] == 'FULL')
                $badQuery[$table]  .=  ' Full Table Scan[cost:' . $row['cost'] . ' cpu:' . $row['cpu_cost'] . ' io:'
                    . $row['io_cost'] . '];';
        }
        if (!empty($badQuery)) {
            foreach ($badQuery as $table=>$data ) {
                if(!empty($data)){
                    $warning = ' Table:' . $table . ' Data:' . $data;
                    //BEGIN SUGARCRM flav=int ONLY
                    _pp('Warning Check Query:' . $warning);
                    //END SUGARCRM flav=int ONLY
                    if(!empty($GLOBALS['sugar_config']['check_query_log'])){
                        $GLOBALS['log']->fatal($sql);
                        $GLOBALS['log']->fatal('CHECK QUERY:' .$warning);
                    }else{
                        $GLOBALS['log']->warn('CHECK QUERY:' .$warning);
                    }
                }
            }
        }
        $this->query("DELETE FROM plan_table WHERE statement_id='$id'");
    }

    /**
     * Runs a limit query: one where we specify where to start getting records and how many to get
     *
     * @param  string   $sql
     * @param  int      $start
     * @param  int      $count
     * @param  boolean  $dieOnError
     * @param  string   $msg
     * @param  bool     $execute    optional, false if we just want to return the query
     * @return resource query result
     */
    public function limitQuery(
        $sql,
        $start,
        $count,
        $dieOnError = false,
        $msg = '',
        $execute = true)
    {
        $matches = array();
        preg_match('/^(.*SELECT)(.*?FROM.*WHERE)(.*)$/is',$sql, $matches);
        $GLOBALS['log']->debug('Limit Query:' . $sql. ' Start: ' .$start . ' count: ' . $count);
        if ($start ==0 && !empty($matches[3])) {
            $sql = 'SELECT /*+ FIRST_ROWS('. $count . ') */ * FROM (' . $matches[1]. $matches[2]. $matches[3] . ') MSI WHERE ROWNUM <= '.$count;
            if(!empty($GLOBALS['sugar_config']['check_query'])){
            	$this->checkQuery($sql);
         	}
            return $this->query( $sql);
        }

        $start++; //count is 1 based.

        if($count != 1)
            $next = $start + $count -1;
        else
            $next=$start;

        if (!empty($matches[2])) {
            $sql = "SELECT /*+ FIRST_ROWS($count) */ * FROM (SELECT  ROWNUM as orc_row, MSI.* FROM (".$sql. ') MSI  WHERE ROWNUM <= '. $next . ') WHERE  orc_row >= ' . $start;
            if (!empty($GLOBALS['sugar_config']['check_query']))
                $this->checkQuery($sql);

            return $this->query($sql);
        }
        if (
                //BEGIN SUGARCRM flav=int ONLY
                true ||
                //END SUGARCRM flav=int ONLY
                !empty($GLOBALS['sugar_config']['check_query']))
            $this->checkQuery($sql);

        $query = "SELECT * FROM (SELECT ROWNUM AS orc_row , MSI.* FROM ($sql) MSI where ROWNUM <= $next) WHERE orc_row >= $start";
        if ($execute)
            return $this->query($query, $dieOnError, $msg);

        return $query;
    }

    /**
     * Alias of limitQuery() with the last parameter as true
     * @see OracleManager::limitQuery()
     */
    public function limitQuerySql(
        $sql,
        $start,
        $count,
        $dieOnError=false,
        $msg=''
        )
    {
        return $this->limitQuery($sql,$start,$count,$dieOnError,$msg,false);
    }

    /**
     * @see DBManager::getFieldsArray()
     */
	public function getFieldsArray(
        &$result,
        $make_lower_case = false
        )
	{
		$field_array = array();

        if(! isset($result) || empty($result))
            return 0;

        $i = 1;
        $count = oci_num_fields($result);
        $count_tag = $count + 1;
        while ($i < $count_tag) {
            $meta = oci_field_name($result, $i);
            if (!$meta)
                return 0;
            if($make_lower_case==true)
                $meta = strtolower($meta);
            $field_array[] = $meta;

            $i++;
        }

        return $field_array;

    }

    /**
     * Uses the same backend function as DBManager::getRowCount(), but we need to pass in the last result
     * set used into the function.
     *
     * @see DBManager::getRowCount()
     * @see DBManager::getAffectedRowCount()
     */
	public function getAffectedRowCount()
    {
        return $this->getRowCount($this->_lastResult);
    }




    /**
     * Fetches the next row from the result set
     *
     * @param  resource $result result set
     * @return array
     */
    private function ociFetchRow(
        $result
        )
    {
        $row = oci_fetch_array($result, OCI_ASSOC+OCI_RETURN_NULLS+OCI_RETURN_LOBS);
        if ( !$row )
            return false;
        if ($this->checkOCIerror($result) == false) {
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

    /**
     * @see DBManager::fetchByAssoc()
     */
    public function fetchByAssoc(
        &$result,
        $rowNum = -1,
        $encode = true
        )
    {
        if (!$result)
            return false;

        if (isset($result) && $result && $rowNum < 0) {
            $row = $this->ociFetchRow($result);
        }
        else {
            if ($this->getRowCount($result) > $rowNum)
                return array(); // cannot do seek in oracle
            $this->lastmysqlrow = $rowNum;
            $row = $this->ociFetchRow($result);
        }
        if ($this->newQuery) {
            $this->newQuery = false;
        }
        if ($row != false && $encode && $this->encode && sizeof($row)>0)
            return array_map('to_html', $row);

        return $row;
    }

    /**
     * @see DBManager::getTablesArray()
     */
    public function getTablesArray()
    {
        $GLOBALS['log']->debug('ORACLE fetching table list');

        if($this->getDatabase()) {
            $tables = array();
            $r = $this->query('SELECT TABLE_NAME FROM USER_TABLES');
            if (is_resource($r)) {
                while ($a = $this->fetchByAssoc($r))
                    $tables[] = strtolower($a['table_name']);

                return $tables;
            }
        }

        return false; // no database available
    }

    /**
     * @see DBManager::addIndexes()
     */
    public function addIndexes(
        $tablename,
        $indexes,
        $execute = true
        )
    {
        $alters = $this->getHelper()->getConstraintSQL($indexes,$tablename);

        $sql = "";
        foreach ($alters as $stmt) {
        	if ($execute)
                $this->query($stmt);
        	$sql = $stmt . "\n";
        }

        return $sql;
    }

    /**
     * @see DBManager::dropIndexes()
     */
    public function dropIndexes(
        $tablename,
        $indexes,
        $execute = true
        )
    {
        $sql = '';
        foreach ($indexes as $index) {
            if (empty($sql))
                $sql .= $this->getHelper()->add_drop_constraint($tablename,$index,true);
            else
                $sql .= "; " . $this->getHelper()->add_drop_constraint($tablename,$index,true);
        }
        if (!empty($sql) && $execute)
            $this->query($sql);

        return $sql;
    }

    /**
     * @see DBManager::tableExists()
     */
    public function tableExists(
        $tableName
        )
    {
        $GLOBALS['log']->info("tableExists: $tableName");

        if ($this->getDatabase()){
            $this->tableName = strtoupper($tableName);
            $sql = "select count(*) count from user_tables where upper(table_name) like '$this->tableName'";
            $count = $this->getOne($sql);
            return ($count == 0) ? false : true;
        }

        return false;
    }

    /**
     * @see DBManager::update()
     */
    public function update(
        SugarBean $bean,
        array $where = array()
        )
    {
        $sql = $this->getHelper()->updateSQL($bean,$where);

        $ret = $this->AltlobExecute($bean, $sql);
        oci_commit($this->getDatabase()); //moved here from sugarbean
        $this->tableName = $bean->getTableName();
        $msg = "Error inserting into table: ".$this->tableName;
        $this->checkError($msg.' Query Failed: ' . $sql, true);
    }

    /**
     * Executes an insert or update for the Emails module
     *
     * @param  object   $bean SugarBean instance
     * @param  string   $sql  SQL statement
     * @return resource
     */
    public function insertUpdateForEmail(
        SugarBean $bean,
        $sql
        )
    {
        $ret = $this->AltlobExecute($bean, $sql);
        oci_commit($this->getDatabase()); //moved here from sugarbean
        return $ret;
    }

    /**
     * @see DBManager::insert()
     */
    public function insert(
        SugarBean $bean
        )
    {
        $sql = $this->helper->insertSQL($bean);
        $ret = $this->AltlobExecute($bean, $sql);

        //jc: when this was moved it was moved as written in sugarbean ($this->dbManager->database).
        //this raised an error each time this method is called because $this is a child class of DbManager
      	//so $this does not have a dbManager.
        oci_commit($this->getDatabase()); //moved here from sugarbean.
        $this->tableName = $bean->getTableName();
        $msg = "Error inserting into table: ".$this->tableName;
        $this->checkError($msg.' Query Failed: ' . $sql, true);
    }

    /**
     * Executes a query, with special handling for Oracle CLOB and BLOB field type
     *
     * @param  object   $bean SugarBean instance
     * @param  string   $sql  SQL statement
     * @return resource
     */
    private function AltlobExecute(
        SugarBean $bean,
        $sql
        )
    {
    	$GLOBALS['log']->debug($sql);
        $this->checkConnection();
        if(empty($sql)){
            return;
        }

        $lob_fields=array();
        $lob_field_type=array();
        $lobs=array();
        foreach ($bean->field_defs as $fieldDef){
            $type = $this->helper->getFieldType($fieldDef);
            if (isset($fieldDef['source']) && $fieldDef['source']!='db') {
                continue;
            }

            //not include the field if a value is not set...
            if (!isset($bean->$fieldDef['name'])) continue;

            $lob_type = false;
            if ($type == 'longtext' or  $type == 'text' or $type == 'clob' or $type == 'multienum') $lob_type = OCI_B_CLOB;
            else if ($type == 'blob' || $type == 'longblob') $lob_type = OCI_B_BLOB;

            // this is not a lob, continue;
            if ($lob_type === false) continue;

            $lob_fields[$fieldDef['name']]=":".$fieldDef['name'];
            $lob_field_type[$fieldDef['name']]=$lob_type;
        }

        if (count($lob_fields) > 0 ) {
            $sql .= " RETURNING ".implode(",", array_keys($lob_fields)).' INTO '.implode(",", array_values($lob_fields));
        }

        $stmt = oci_parse($this->database, $sql);
        $err = oci_error($this->database);
        if ($err != false){
            return false;
        }

        foreach ($lob_fields as $key=>$descriptor) {
            $newlob = oci_new_descriptor($this->database, OCI_D_LOB);
            oci_bind_by_name($stmt, $descriptor, $newlob, -1, $lob_field_type[$key]);
            $lobs[$key] = $newlob;
        }

        oci_execute($stmt,OCI_DEFAULT);
        $err = oci_error($stmt);
        if ($err != false){
            $GLOBALS['log']->fatal($sql.">>".$err['code'].":".$err['message']);
            $result = false;
        }
        else {
            foreach ($lobs as $key=>$lob){
                $val = $bean->getFieldValue($key);
                if (empty($val)) $val=" ";
                $lob->save($val);
            }
            oci_commit($this->database);
            $result = true;
        }

        // free all the lobs.
        foreach ($lobs as $lob){
            $lob->free();
        }
        oci_free_statement($stmt);

        return $result;
    }

    /**
     * will set lob values for the passed sql and execute the sql
     *
     * @deprecated use AltlobExecute() instead
     *
     * @param  object   $bean SugarBean instance
     * @param  string   $sql  SQL statement
     * @return resource
     */
    private function lobExecute(
        SugarBean $bean,
        $sql
        )
    {
        $GLOBALS['log']->info('call to OracleManager::lobExecute() is deprecated');

        $this->checkConnection();
        if(empty($sql)){
            return;
        }
        //$GLOBALS['log']->fatal("lobExecute sql : $sql");
        $stmt = oci_parse($this->database, $sql);
        $err = oci_error($this->database);
        if ($err != false){
            return false;
        }
        // set all the lobs

        $lobs = array();

        foreach ($bean->field_defs as $fieldDef){
            $type = $this->helper->getFieldType($fieldDef);
            if (isset($fieldDef['source']) && $fieldDef['source']!='db') {
                continue;
            }

            $lob_type = false;
            if ($type == 'longtext' or $type == 'text' or $type == 'clob' or $type == 'multienum') $lob_type = OCI_B_CLOB;
            else if ($type == 'blob' || $type == 'longblob') $lob_type = OCI_B_BLOB;

            // this is not a lob, continue;
            if ($lob_type === false) continue;

            $val = $bean->getFieldValue($fieldDef['name']);
            if (!isset($bean->$fieldDef['name'])) continue; // no value
            $newlob = oci_new_descriptor($this->database, OCI_D_LOB);
            oci_bind_by_name($stmt, ':'.$fieldDef['name'], $newlob, -1, $lob_type);
            if(empty($val)){
                $newlob->WriteTemporary(' ');
            }else{
                $newlob->WriteTemporary($val);
            }

            $lobs[] = $newlob;
        }
        oci_execute($stmt);
        $err = oci_error($stmt);
        if ($err != false){

            $GLOBALS['log']->fatal($sql.">>".$err['code'].":".$err['message']);
            $result = false;
        }
        else {
            // commit since it is a DML stantement. NO ROLLBACKS
            oci_commit($this->database);
            $result = true;
        }

        oci_free_statement($stmt);
        // free all the lobs.
        foreach ($lobs as $lob){
            $lob->close();
            $lob->free();

        }


        return $result;
    }

    /**
     * @see DBManager::quote()
     */
    public function quote(
        $string,
        $isLike = true
        )
    {
        return OracleHelper::magic_quotes_oracle($string);
    }

	/**
     * @see DBManager::connect()
     */
    public function connect(
        array $configOptions = null,
        $dieOnError = false
        )
    {
        global $sugar_config;

        if(!$configOptions)
			$configOptions = $sugar_config['dbconfig'];

		if($sugar_config['dbconfigoption']['persistent'] == true){
            $this->database = oci_pconnect($configOptions['db_user_name'], $configOptions['db_password'],$configOptions['db_name']);
            $err = oci_error();
            if ($err != false) {
	            $GLOBALS['log']->debug("oci_error:".var_export($err, true));
            }
		}
            if(!$this->database){
                $this->database = oci_connect($configOptions['db_user_name'],$configOptions['db_password'],$configOptions['db_name']);
                if (!$this->database) {
                	$err = oci_error();
                	if ($err != false) {
			            $GLOBALS['log']->debug("oci_error:".var_export($err, true));
                	}
                	$GLOBALS['log']->fatal("Could not connect to server ".$this->dbName." as ".$this->userName.".");
                	sugar_die($GLOBALS['app_strings']['ERR_NO_DB']);
                }
                if($this->database && $sugar_config['dbconfigoption']['persistent'] == true){
                    $_SESSION['administrator_error'] = "<B>Severe Performance Degradation: Persistent Database Connections not working.  Please set \$sugar_config['dbconfigoption']['persistent'] to false in your config.php file</B>";
                }
            }
            //set oracle date format to be yyyy-mm-dd
            // ora_commiton($this->database);
            //settings for function based index.
            /* cn: This alters CREATE TABLE statements to explicitly create char-length varchar2() columns
             * at create time vs. byte-length columns.  the other option is to switch to nvarchar2()
             * which has char-length semantics by default.
             */
            $this->query("alter session set
                nls_date_format = 'YYYY-MM-DD hh24:mi:ss'
                QUERY_REWRITE_INTEGRITY = TRUSTED
                QUERY_REWRITE_ENABLED = TRUE
                NLS_LENGTH_SEMANTICS=CHAR");

		if($this->checkError('Could Not Connect', $dieOnError))
			$GLOBALS['log']->info("connected to db");

        $GLOBALS['log']->info("Connect:".$this->database);

	}

	 /**
     * @see DBManager::convert()
     */
    public function convert(
        $string,
        $type,
        array $additional_parameters = array(),
        array $additional_parameters_oracle_only = array()
        )
    {
        // convert the parameters array into a comma delimited string
        $additional_parameters_string = '';
        foreach ($additional_parameters as $value) {
			$additional_parameters_string.=",".$value;
		}
	    $additional_parameters_string_oracle_only='';
		foreach ($additional_parameters_oracle_only as $value) {
			$additional_parameters_string_oracle_only.=",".$value;
		}
    	if (!empty($additional_parameters_string_oracle_only)) {
			$additional_parameters_string=$additional_parameters_string_oracle_only;
		}
		if ( $type == 'CONCAT' && empty($additional_parameters_oracle_only) ) {
		    return $string;
		}
        switch ($type) {
        case 'date': return "to_date($string, 'YYYY-MM-DD')";
        case 'time': return "to_date($string, 'HH24:MI:SS')";
        case 'datetime': return "to_date($string, 'YYYY-MM-DD HH24:MI:SS'".$additional_parameters_string.")";
        case 'today': return "sysdate";
        case 'left': return "LTRIM($string".$additional_parameters_string.")";
        case 'date_format': return "TO_CHAR($string".$additional_parameters_string.")";
        case 'time_format': return "TO_CHAR($string".$additional_parameters_string.")";
        case 'IFNULL': return "NVL($string".$additional_parameters_string.")";
        case 'CONCAT': return "$string||".implode("||",$additional_parameters_oracle_only);
        case 'text2char': return "to_char($string)";
        }

        return "$string";
    }

    /**
     * @see DBManager::fromConvert()
     */
    public function fromConvert(
        $string,
        $type)
    {
        switch($type) {
        case 'date': return substr($string, 0,11);
        case 'time': return substr($string, 11);
		}

		return $string;
    }

    protected function isNullable($vardef)
    {
        if(!empty($vardef['type']) && $vardef['type'] == 'clob') {
            return false;
        }
		return parent::isNullable($vardef);
    }

    /**
     * @see DBManager::createTableSQLParams()
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
     * Does this type represent text (i.e., non-varchar) value?
     * @param string $type
     */
    public function isTextType($type)
    {
        $type = strtolower($type);
        return ($type == 'clob');
    }
}

