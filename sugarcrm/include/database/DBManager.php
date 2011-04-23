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
* $Id: DBManager.php 56151 2010-04-28 21:02:22Z jmertic $
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

abstract class DBManager
{
    /**
     * Name of database table we are dealing with
     */
    protected $tableName;

    /**
     * Name of database
     * @var resource
     */
    public $database = null;

    /**
     * Indicates whether we should die when we get an error from the DB
     */
    protected $dieOnError = false;

    /**
     * Indicates whether we should html encode the results from a query by default
     */
    protected $encode = true;

    /**
     * Records the execution time of the last query
     */
    protected $query_time = 0;

    /**
     * Last error message from the DB backend
     */
    protected $last_error = '';

    /**
     * Registry of available result sets
     */
    protected $lastResult;

    /**
     * Current query count
     */
    private static $queryCount = 0;

    /**
     * Query threshold limit
     */
    private static $queryLimit = 0;

    /**
     * Array of prepared statements and their correspoding parsed tokens
     */
    protected $preparedTokens = array();

    /**
     * TimeDate instance
     * @var TimeDate
     */
    protected $timedate;

    /**
     * PHP Logger
     * @var Logger
     */
    protected $log;

    /**
     * Table descriptions
     * @var array
     */
    protected static $table_descriptions = array();

    /**
     * Index descriptions
     * @var array
     */
    protected static $index_descriptions = array();

    /**
     * Maximum length of identifiers
     * @var array
     */
    protected $maxNameLengths = array('column' => 64);

    /**
     * Type names map
     * @var array
     */
    protected $type_map = array();

    /**
     * Capabilities this DB supports. Supported list:
     * affected_rows	Can report query affected rows for UPDATE/DELETE
     * select_rows		Can report row count for SELECT
     * case_sensitive	Supports case-sensitive text columns
     * fulltext			Supports fulltext search indexes
     * inline_keys		Supports defining keys together with the table
     * auto_increment_sequence Autoincrement support implemented as sequence
     * limit_subquery   Supports LIMIT clauses in subqueries
     *
     * Special cases:
     * fix:expandDatabase - needs expandDatabase fix, see expandDatabase.php
     * TODO: verify if we need these cases
     */
    protected $capabilities = array();

    public function __construct()
    {
        $this->timedate = TimeDate::getInstance();
        $this->log = $GLOBALS['log'];
    }

    /**
     * Wrapper for those trying to access the private and protected class members directly
     */
    public function __get($p)
    {
        $GLOBALS['log']->info('call to DBManagerFactory::$'.$p.' is deprecated');
        return $this->$p;
    }

    /**
     * Returns the current tablename
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Returns the current database handle
     *
     * @return resource
     */
    public function getDatabase()
    {
        $this->checkConnection();
        return $this->database;
    }

    /**
     * Returns this instance's DBHelper
     * @deprecated
     * @return object DBHelper instance
     */
    public function getHelper()
    {
        return $this;
    }

    /**
     * Checks for database not being connected
     *
     * @param  string $msg        message to prepend to the error message
     * @param  bool   $dieOnError true if we want to die immediately on error
     * @return bool
     */
    public function checkError($msg = '', $dieOnError = false)
    {
    	$userMsg = inDeveloperMode()?"$msg: ":"";

        if (!isset($this->database)) {
            $GLOBALS['log']->error("Database Is Not Connected");
            if($this->dieOnError || $dieOnError)
                sugar_die ($userMsg."Database Is Not Connected");
            else
                $this->last_error = $userMsg."Database Is Not Connected";
            return true;
        }
        return false;
    }

    /**
     * This method is called by every method that runs a query.
     * If slow query dumping is turned on and the query time is beyond
     * the time limit, we will log the query. This function may do
     * additional reporting or log in a different area in the future.
     *
     * @param  string  $query query to log
     * @return boolean true if the query was logged, false otherwise
     */
    protected function dump_slow_queries($query)
    {
        global $sugar_config;

        $do_the_dump = isset($sugar_config['dump_slow_queries'])
            ? $sugar_config['dump_slow_queries'] : false;
        $slow_query_time_msec = isset($sugar_config['slow_query_time_msec'])
            ? $sugar_config['slow_query_time_msec'] : 5000;

        if($do_the_dump) {
            if($slow_query_time_msec < ($this->query_time * 1000)) {
                // Then log both the query and the query time
                $GLOBALS['log']->fatal('Slow Query (time:'.$this->query_time."\n".$query);
                return true;
            }
        }
        return false;
    }

    //BEGIN SUGARCRM flav=pro ONLY
    /**
     * Tracks slow queries in the tracker database table
     *
     * @param $query string value of query to track
     */
	protected function track_slow_queries($query)
    {
        $trackerManager = TrackerManager::getInstance();
        if($trackerManager->isPaused()) {
           return;
        }

        if($monitor = $trackerManager->getMonitor('tracker_queries')){
        	$monitor->setValue('date_modified', $this->timedate->nowDb());
        	$monitor->setValue('text', $query);
        	$monitor->setValue('sec_total', $this->query_time);

        	//Save the monitor to cache (do not flush)
        	$trackerManager->saveMonitor($monitor, false);
		}
	}

	/**
	 * addDistinctClause
	 * This method takes a SQL statement and checks if the disable_count_query setting is enabled
	 * before altering it.  The alteration modifies the way the team security queries are made by
	 * changing it from a subselect to a distinct clause; hence the name of the method.
	 *
	 * @param $sql String value of SQL statement to alter
	 */
	protected function addDistinctClause(&$sql)
	{
        if(!empty($GLOBALS['sugar_config']['disable_count_query']) && (stripos($sql, 'count(*)') === false && stripos($sql, '(select tst.team_set_id from') !==false)){
			if(stripos( $sql, 'UNION ALL') !== false){
				$parts = explode('UNION ALL', $sql);
			}else{
				$parts = array($sql);
			}
			$newSql = '';
			foreach($parts as $p=>$part){
				if(preg_match_all('/INNER JOIN \((select tst\.team_set_id[^\)]*)\)\s*(\w*)_tf on \w*_tf\.team_set_id  = \w*\.team_set_id/i', $part, $matches,  PREG_SET_ORDER  )) {
					$search = array();
					$replace = array();
					$table = $matches[0][2];
					$search[] =  'INNER JOIN (select tst.team_set_id from team_sets_teams tst';
					$replace[] =  ' INNER JOIN team_sets_teams tst ON tst.team_set_id = ' . $table . '.team_set_id';
					$search[] = 'group by tst.team_set_id) ' . $table . '_tf on ' . $table . '_tf.team_set_id  = ' . $table .'.team_set_id';
					$replace[] = '';
					$selectPos = stripos($part , 'select');
					if($selectPos !== false){
						$distinctPos = stripos($part , 'distinct', $selectPos);
						if($distinctPos === false || $distinctPos > 20){
							$part = substr($part, 0, $selectPos + 6) .' DISTINCT ' . substr( $part, $selectPos + 7);
						}
					}
					$part= str_replace($search, $replace, $part);
				}
                if( $p < count($parts) - 1 )$part .= 'UNION ALL';
                $newSql .= $part;

			}
			if(!empty($newSql))$sql = $newSql;
		}
	}

	//END SUGARCRM flav=pro ONLY
   /**
    * Scans order by to ensure that any field being ordered by is.
    *
    * It will throw a warning error to the log file - fatal if slow query logging is enabled
    *
    * @param  string $sql         query to be run
    * @param  bool   $object_name optional, object to look up indices in
    * @return bool   true if an index is found false otherwise
    */
   protected function checkQuery($sql, $object_name = false)
   {
       $match = array();
       preg_match_all("'.* FROM ([^ ]*).* ORDER BY (.*)'is", $sql, $match);
       $indices = false;
       if (!empty($match[1][0]))
           $table = $match[1][0];
       else
           return false;

       if (!empty($object_name) && !empty($GLOBALS['dictionary'][$object_name]))
           $indices = $GLOBALS['dictionary'][$object_name]['indices'];

       if (empty($indices)) {
           foreach ( $GLOBALS['dictionary'] as $current ) {
               if ($current['table'] == $table){
                   $indices = $current['indices'];
                   break;
               }
           }
       }
       if (empty($indices)) {
           $GLOBALS['log']->warn('CHECK QUERY: Could not find index definitions for table ' . $table);
           return false;
       }
       if (!empty($match[2][0])) {
           $orderBys = explode(' ', $match[2][0]);
           foreach ($orderBys as $orderBy){
               $orderBy = trim($orderBy);
               if (empty($orderBy))
                   continue;
               $orderBy = strtolower($orderBy);
               if ($orderBy == 'asc' || $orderBy == 'desc')
                   continue;

               $orderBy = str_replace(array($table . '.', ','), '', $orderBy);

               foreach ($indices as $index)
                   if (empty($index['db']) || $index['db'] == $this->dbType)
                       foreach ($index['fields'] as $field)
                           if ($field == $orderBy)
                               return true;

               $warning = 'Missing Index For Order By Table: ' . $table . ' Order By:' . $orderBy ;
               if (!empty($GLOBALS['sugar_config']['dump_slow_queries']))
                   $GLOBALS['log']->fatal('CHECK QUERY:' .$warning);
               else
                   $GLOBALS['log']->warn('CHECK QUERY:' .$warning);
           }
       }
       return false;
    }

    /**
     * Returns the time the last query took to execute
     *
     * @return int
     */
    public function getQueryTime()
    {
        return $this->query_time;
    }

    /**
     * Checks the current connection; if it is not connected then reconnect
     */
    public function checkConnection()
    {
        $this->last_error = '';
        if (!isset($this->database))
            $this->connect();
    }

    /**
     * Sets the dieOnError value
     *
     * @param bool $value
     */
    public function setDieOnError($value)
    {
        $this->dieOnError = $value;
    }

    /**
	 * Implements a generic insert for any bean.
	 *
	 * @param object $bean SugarBean instance
	 */
    public function insert(SugarBean $bean)
    {
        $sql = $this->insertSQL($bean);
        $this->tableName = $tablename =  $bean->getTableName();
        $msg = "Error inserting into table: $tablename:";
        return $this->query($sql,true,$msg);
    }

    /**
     * Insert data into table by parameter definition
     * @param string $table
     * @param array $field_defs Definitions in vardef-like format
     * @param array $data Key/value to insert
     */
    public function insertParams($table, $field_defs, $data)
    {
        $values = array();
		foreach ($field_defs as $field => $fieldDef)
		{
            if (isset($fieldDef['source']) && $fieldDef['source'] != 'db')  continue;
            //custom fields handle there save seperatley

            if(isset($data[$field])) {
                // clean the incoming value..
                $val = from_html($data[$field]);
            } else {
                continue;
            }

            //handle auto increment values here only need to do this on insert not create
            // TODO: do we really need to do this?
            if (!empty($fieldDef['auto_increment'])) {
                $auto = $this->getAutoIncrementSQL($table, $fieldDef['name']);
                if(!empty($auto)) {
                    $values[$field] = $auto;
                }
            } elseif ($fieldDef['name'] == 'deleted') {
                $values['deleted'] = (int)$val;
            } else {
                // need to do some thing about types of values
                $values[$field] = $this->massageValue($val, $fieldDef);
            }
		}
		if (empty($values))
            return true; // no columns set

		// get the entire sql
		$query = "INSERT INTO $table (".implode(",", array_keys($values)).")
                    VALUES (".implode(",", $values).")";
		return $this->query($query);
    }

    /**
     * Implements a generic update for any bean
     *
     * @param object $bean  Sugarbean instance
     * @param array  $where values with the keys as names of fields.
     * If we want to pass multiple values for a name, pass it as an array
     * If where is not passed, it defaults to id of table
     */
    public function update(SugarBean $bean, array $where = array())
    {
        $sql = $this->updateSQL($bean, $where);
        $this->tableName = $tablename = $bean->getTableName();
        $msg = "Error updating table: $tablename:";
        return $this->query($sql,true,$msg);
    }

    /**
	 * Implements a generic delete for any bean identified by id
     *
     * @param object $bean  Sugarbean instance
     * @param array  $where values with the keys as names of fields.
	 * If we want to pass multiple values for a name, pass it as an array
	 * If where is not passed, it defaults to id of table
	 */
    public function delete(SugarBean $bean, array $where = array())
    {
        $sql = $this->deleteSQL($bean, $where);
        $this->tableName = $bean->getTableName();
        $msg = "Error deleting from table: ".$this->tableName. ":";
        return $this->query($sql,true,$msg);
    }

    /**
     * Implements a generic retrieve for any bean identified by id
     *
     * If we want to pass multiple values for a name, pass it as an array
     * If where is not passed, it defaults to id of table
     *
     * @param  object   $bean  Sugarbean instance
     * @param  array    $where values with the keys as names of fields.
     * @return resource result from the query
     */
    public function retrieve(SugarBean $bean, array $where = array())
    {
        $sql = $this->retrieveSQL($bean, $where);
        $this->tableName = $bean->getTableName();
        $msg = "Error retriving values from table:".$this->tableName. ":";
        return $this->query($sql,true,$msg);
    }

    /**
     * Implements a generic retrieve for a collection of beans.
     *
     * These beans will be joined in the sql by the key attribute of field defs.
     * Currently, this function does support outer joins.
     *
     * @param  array $beans Sugarbean instance(s)
     * @param  array $cols  columns to be returned with the keys as names of bean as identified by
     * get_class of bean. Values of this array is the array of fieldDefs to be returned for a bean.
     * If an empty array is passed, all columns are selected.
     * @param  array $where  values with the keys as names of bean as identified by get_class of bean
     * Each value at the first level is an array of values for that bean identified by name of fields.
     * If we want to pass multiple values for a name, pass it as an array
     * If where is not passed, all the rows will be returned.
     * @return resource
     */
    public function retrieveView(array $beans, array $cols = array(), array $where = array())
    {
        $sql = $this->retrieveViewSQL($beans, $cols, $where);
        $msg = "Error retriving values from View Collection:";
        return $this->query($sql,true,$msg);
    }


    /**
	 * Implements creation of a db table for a bean.
	 *
	 * NOTE: does not handle out-of-table constraints, use createConstraintSQL for that
     * @param object $bean  Sugarbean instance
     */
    public function createTable(SugarBean $bean)
    {
        $sql = $this->createTableSQL($bean);
        $this->tableName = $tablename = $bean->getTableName();
        $msg = "Error creating table: $tablename:";
        $this->query($sql,true,$msg);
        if(!$this->supports("inline_keys")) {
           // handle constraints and indices
            $indicesArr = $this->createConstraintSql($bean);
            if (count($indicesArr) > 0)
        	    foreach ($indicesArr as $indexSql)
        		    $this->query($indexSql, true, $msg);
        }
    }

    /**
     * returns SQL to create constraints or indices
     *
     * @param  object $bean SugarBean instance
     * @return array list of SQL statements
     */
	protected function createConstraintSql(SugarBean $bean)
    {
		return $this->getConstraintSql($bean->getIndices(), $bean->getTableName());
	}

    /**
     * Implements creation of a db table
     *
     * @param string $tablename
     * @param array  $fieldDefs
     * @param array  $indices
     * @param string $engine    MySQL engine to use
     */
    public function createTableParams($tablename, $fieldDefs, $indices, $engine = null)
    {
        if (!empty($fieldDefs)) {
            $sql = $this->createTableSQLParams($tablename, $fieldDefs, $indices,$engine);
            $res = true;
            if ($sql) {
                $msg = "Error creating table: $tablename";
                $res = ($res and $this->query($sql,true,$msg));
            }
            if(!$this->supports("inline_keys")) {
                // handle constraints and indices
                $indicesArr = $this->getConstraintSql($indices, $tablename);
                if (count($indicesArr) > 0)
                    foreach ($indicesArr as $indexSql)
                        $res = ($res and $this->query($indexSql, true, "Error creating indexes"));
            }
            return $res;
        }
        return false;
    }

    /**
	 * Implements repair of a db table for a bean.
	 *
	 * @param  object $bean    SugarBean instance
     * @param  bool   $execute true if we want the action to take place, false if we just want the sql returned
	 * @return string SQL statement or empty string, depending upon $execute
	 */
    public function repairTable(SugarBean $bean, $execute = true)
    {
        $indices   = $bean->getIndices();
        $fielddefs = $bean->getFieldDefinitions();
        $tablename = $bean->getTableName();

		//Clean the indexes to prevent duplicate definitions
		$new_index = array();
		foreach($indices as $ind_def){
			$new_index[$ind_def['name']] = $ind_def;
		}
		//jc: added this for beans that do not actually have a table, namely
		//ForecastOpportunities
        if($tablename == 'does_not_exist' || $tablename == '')
        	return '';

        global $dictionary;
        $engine=null;
        if (isset($dictionary[$bean->getObjectName()]['engine']) && !empty($dictionary[$bean->getObjectName()]['engine']) )
            $engine = $dictionary[$bean->getObjectName()]['engine'];

        return $this->repairTableParams($tablename, $fielddefs,$new_index,$execute,$engine);
    }

    protected function isNullable($vardef)
    {
        if(empty($vardef['auto_increment']) && (empty($vardef['type']) || $vardef['type'] != 'id')
                    && (empty($vardef['dbType']) || $vardef['dbType'] != 'id')
					&& (empty($vardef['name']) || ($vardef['name'] != 'id' && $vardef['name'] != 'deleted'))
		) {
		    return true;
		}
		return false;
    }

    /**
     * Builds the SQL commands that repair a table structure
     *
     * @param  string $tablename
     * @param  array  $fielddefs
     * @param  array  $indices
     * @param  bool   $execute   optional, true if we want the queries executed instead of returned
     * @param  string $engine    optional, MySQL engine
     */
    public function repairTableParams($tablename, $fielddefs,  $indices, $execute = true, $engine = null)
    {
		//jc: had a bug when running the repair if the tablename is blank the repair will
		//fail when it tries to create a repair table
        if ($tablename == '' || empty($fielddefs))
            return '';

        //if the table does not exist create it and we are done
        $sql = "/* Table : $tablename */\n";
        if (!$this->tableExists($tablename)) {
            $createtablesql = $this->createTableSQLParams($tablename,$fielddefs,$indices,$engine);
            if($execute && $createtablesql){
                $this->createTableParams($tablename,$fielddefs,$indices,$engine);
            }

            $sql .= "/* MISSING TABLE: {$tablename} */\n";
            $sql .= $createtablesql . "\n";
            return $sql;
        }

        $compareFieldDefs = $this->get_columns($tablename);
        $compareIndices = $this->get_indices($tablename);

        $take_action = false;

        // do column comparisions
        $sql .=	"/*COLUMNS*/\n";
        foreach ($fielddefs as $value) {
            if (isset($value['source']) && $value['source'] != 'db')
                continue;

            $name = $value['name'];
            // add or fix the field defs per what the DB is expected to give us back
            $this->massageFieldDef($value,$tablename);

            $ignorerequired=false;

			//Do not track requiredness in the DB, auto_increment, ID,
			// and deleted fields are always required in the DB, so don't force those
            if ($this->isNullable($value)) {
			    $value['required'] = false;
			}
			//Should match the conditions in DBHelper::oneColumnSQLRep for DB required fields, type='id' fields will sometimes
			//come into this function as 'type' = 'char', 'dbType' = 'id' without required set in $value. Assume they are correct and leave them alone.
			else if (($name == 'id' || $value['type'] == 'id' || (isset($value['dbType']) && $value['dbType'] == 'id'))
                && (!isset($value['required']) && isset($compareFieldDefs[$name]['required'])))
			{
				$value['required'] = $compareFieldDefs[$name]['required'];
			}

            if ( !isset($compareFieldDefs[$name]) ) {
                // ok we need this field lets create it
                $sql .=	"/*MISSING IN DATABASE - $name -  ROW*/\n";
                $sql .= $this->addColumnSQL($tablename, $value) .  "\n";
                if ($execute)
                    $this->addColumn($tablename, $value);
                $take_action = true;
            } elseif ( !$this->compareVarDefs($compareFieldDefs[$name],$value)) {
                //fields are different lets alter it
                $sql .=	"/*MISMATCH WITH DATABASE - $name -  ROW ";
                foreach($compareFieldDefs[$name] as $rKey => $rValue) {
                    $sql .=	"[$rKey] => '$rValue'  ";
                }
                $sql .=	"*/\n";
                $sql .=	"/* VARDEF - $name -  ROW";
                foreach($value as $rKey => $rValue) {
                    $sql .=	"[$rKey] => '$rValue'  ";
                }
                $sql .=	"*/\n";

                //jc: oracle will complain if you try to execute a statement that sets a column to (not) null
                //when it is already (not) null
                if ( isset($value['isnull']) && isset($compareFieldDefs[$name]['isnull']) &&
                    $value['isnull'] === $compareFieldDefs[$name]['isnull']) {
                    unset($value['required']);
                    $ignorerequired=true;
                }

                //dwheeler: Once a column has been defined as null, we cannot try to force it back to !null
                if ((isset($value['required']) && ($value['required'] === true || $value['required'] == 'true' || $value['required'] === 1))
				    && (empty($compareFieldDefs[$name]['required']) || $compareFieldDefs[$name]['required'] != 'true'))
			    {
				    $ignorerequired = true;
			    }

                $sql .= $this->alterColumnSQL($tablename, $value,$ignorerequired) .  "\n";
                if($execute){
                    $this->alterColumn($tablename, $value, $ignorerequired);
                }
                $take_action = true;
            }
        }

        // do index comparisions
        $sql .=	"/* INDEXES */\n";
        $correctedIndexs = array();
        foreach ($indices as $value) {
            if (isset($value['source']) && $value['source'] != 'db')
                continue;

            $name = $value['name'];

			//Don't attempt to fix the same index twice in one pass;
			if (isset($correctedIndexs[$name]))
				continue;

            //don't bother checking primary nothing we can do about them
            if (isset($value['type']) && $value['type'] == 'primary')
                continue;

            //database helpers do not know how to handle full text indices
            if ($value['type']=='fulltext')
                continue;

            if ( in_array($value['type'],array('alternate_key','foreign')) )
                $value['type'] = 'index';

            if ( !isset($compareIndices[$name]) ) {
                // ok we need this field lets create it
                $sql .=	 "/*MISSING INDEX IN DATABASE - $name -{$value['type']}  ROW */\n";
                $sql .= $this->addIndexes($tablename,array($value), $execute) .  "\n";
                $take_action = true;
				$correctedIndexs[$name] = true;
            } elseif ( !$this->compareVarDefs($compareIndices[$name],$value) ) {
                // fields are different lets alter it
                $sql .=	"/*INDEX MISMATCH WITH DATABASE - $name -  ROW ";
                foreach ($compareIndices[$name] as $n1 => $t1) {
                    $sql .=	 "<$n1>";
                    if ( $n1 == 'fields' )
                        foreach($t1 as $rKey => $rValue)
                            $sql .= "[$rKey] => '$rValue'  ";
                    else
                        $sql .= " $t1 ";
                }
                $sql .=	"*/\n";
                $sql .=	"/* VARDEF - $name -  ROW";
                foreach ($value as $n1 => $t1) {
                    $sql .=	"<$n1>";
                    if ( $n1 == 'fields' )
                        foreach ($t1 as $rKey => $rValue)
                            $sql .=	"[$rKey] => '$rValue'  ";
                    else
                        $sql .= " $t1 ";
                }
                $sql .=	"*/\n";
                $sql .= $this->modifyIndexes($tablename,array($value), $execute) .  "\n";
                $take_action = true;
				$correctedIndexs[$name] = true;
            }
        }

        return ($take_action === true) ? $sql : '';
    }

    /**
     * Compares two vardefs
     *
     * @param  array  $fielddef1 This is from the database
     * @param  array  $fielddef2 This is from the vardef
     * @return bool   true if they match, false if they don't
     */
    public function compareVarDefs($fielddef1, $fielddef2)
    {
        foreach ( $fielddef1 as $key => $value ) {
            if ( $key == 'name' && ( strtolower($fielddef1[$key]) == strtolower($fielddef2[$key]) ) )
                continue;
            if ( isset($fielddef2[$key]) && $fielddef1[$key] == $fielddef2[$key] )
                continue;
            return false;
        }

        return true;
    }

    /**
     * Compare a field in two tables
     * @deprecated
     * @param  string $name   field name
     * @param  string $table1
     * @param  string $table2
     * @return array  array with keys 'msg','table1','table2'
     */
    public function compareFieldInTables($name, $table1, $table2)
    {
        $row1 = $this->describeField($name, $table1);
        $row2 = $this->describeField($name, $table2);
        $returnArray = array(
            'table1' => $row1,
            'table2' => $row2,
            'msg'    => 'error',
            );

        $ignore_filter = array('Key'=>1);
        if ($row1) {
            if (!$row2) {
                // Exists on table1 but not table2
                $returnArray['msg'] = 'not_exists_table2';
            }
            else {
                if (sizeof($row1) != sizeof($row2)) {
                    $returnArray['msg'] = 'no_match';
                }
                else {
                    $returnArray['msg'] = 'match';
                    foreach($row1 as $key => $value){
                        //ignore keys when checking we will check them when we do the index check
                        if( !isset($ignore_filter[$key]) && $row1[$key] !== $row2[$key]){
                            $returnArray['msg'] = 'no_match';
                        }
                    }
                }
            }
        }
        else {
            $returnArray['msg'] = 'not_exists_table1';
        }

        return $returnArray;
    }

    /**
     * Compare an index in two different tables
     * @deprecated
     * @param  string $name   index name
     * @param  string $table1
     * @param  string $table2
     * @return array  array with keys 'msg','table1','table2'
     */
    public function compareIndexInTables($name, $table1, $table2)
    {
        $row1 = $this->describeIndex($name, $table1);
        $row2 = $this->describeIndex($name, $table2);
        $returnArray = array(
            'table1' => $row1,
            'table2' => $row2,
            'msg'    => 'error',
            );
        $ignore_filter = array('Table'=>1, 'Seq_in_index'=>1,'Cardinality'=>1, 'Sub_part'=>1, 'Packed'=>1, 'Comment'=>1);

        if ($row1) {
            if (!$row2) {
                //Exists on table1 but not table2
                $returnArray['msg'] = 'not_exists_table2';
            }
            else {
                if (sizeof($row1) != sizeof($row2)) {
                    $returnArray['msg'] = 'no_match';
                }
                else {
                    $returnArray['msg'] = 'match';
                    foreach ($row1 as $fname => $fvalue) {
                        if (!isset($row2[$fname])) {
                            $returnArray['msg'] = 'no_match';
                        }
                        if(!isset($ignore_filter[$fname]) && $row1[$fname] != $row2[$fname]){
                            $returnArray['msg'] = 'no_match';
                        }
                    }
                }
            }
        } else {
            $returnArray['msg'] = 'not_exists_table1';
        }

        return $returnArray;
    }


    /**
	 * Creates an index identified by name on the given fields.
	 *
     * @param object $bean      SugarBean instance
     * @param array  $fieldDefs
     * @param string $name      index name
     * @param bool   $unique    optional, true if we want to create an unique index
	 */
    public function createIndex(SugarBean $bean, $fieldDefs, $name, $unique = true)
    {
        $sql = $this->createIndexSQL($bean, $fieldDefs, $name, $unique);
        $this->tableName = $tablename = $bean->getTableName();
        $msg = "Error creating index $name on table: $tablename:";
        return $this->query($sql,true,$msg);
    }

	/**
     * returns a SQL query that creates the indices as defined in metadata
     * @param  array  $indices Assoc array with index definitions from vardefs
     * @param  string $table Focus table
     * @return array  Array of SQL queries to generate indices
     */
	protected function getConstraintSql($indices, $table)
    {
        if (!$this->isFieldArray($indices))
            $indices = array($indices);

        $columns = array();

		foreach ($indices as $index) {
            if(!empty($index['db']) && $index['db'] != $this->dbType)
                continue;
            if (isset($index['source']) && $index['source'] != 'db')
               continue;

            $sql = $this->add_drop_constraint($table, $index);

            if(!empty($sql)) {
                $columns[] = $sql;
            }
		}

		return $columns;
	}

    /**
     * Adds a new indexes
     *
     * @param  string $tablename
     * @param  array  $indexes   indexes to add
     * @param  bool   $execute   true if we want to execute the returned sql statement
     * @return string SQL statement
     */
    public function addIndexes($tablename, $indexes, $execute = true)
    {
        $alters = $this->getConstraintSql($indexes,true,'ADD');
        if ($execute) {
            foreach($alters as $sql) {
                $this->query($sql, true, "Error adding index: ");
            }
        }
        if(!empty($alters)) {
            $sql = join(";\n", $alters).";\n";
        } else {
            $sql = '';
        }
        return $sql;
    }

    /**
     * Drops indexes
     *
     * @param  string $tablename
     * @param  array  $indexes   indexes to drop
     * @param  bool   $execute   true if we want to execute the returned sql statement
     * @return string SQL statement
     */
    public function dropIndexes($tablename, $indexes, $execute = true)
    {
        $sqls = array();
        foreach ($indexes as $index) {
            $name =$index['name'];
            $sqls[$name] = $this->add_drop_constraint($tablename,$index,true);
        }
        if (!empty($sqls) && $execute) {
            foreach($sqls as $name => $sql) {
                unset(self::$index_descriptions[$tablename][$name]);
                $this->query($sql);
            }
        }
        if(!empty($sqls)) {
            return join(";\n",$sqls).";";
        } else {
            return '';
        }
    }

    /**
     * Modifies indexes
     *
     * @param  string $tablename
     * @param  array  $indexes   indexes to modify
     * @param  bool   $execute   true if we want to execute the returned sql statement
     * @return string SQL statement
     */
    public function modifyIndexes($tablename, $indexes, $execute = true)
    {
        return $this->dropIndexes($tablename, $indexes, $execute)."\n".
            $this->addIndexes($tablename, $indexes, $execute);
    }

    /**
	 * Adds a column to table identified by field def.
	 *
	 * @param string $tablename
	 * @param array  $fieldDefs
	 */
    public function addColumn($tablename, $fieldDefs)
    {
        $this->tableName = $tablename;
        $sql = $this->addColumnSQL($tablename, $fieldDefs);
        if ($this->isFieldArray($fieldDefs)){

            foreach ($fieldDefs as $fieldDef)
                $columns[] = $fieldDef['name'];
            $columns = implode(",", $columns);
        }
        else {
            $columns = $fieldDefs['name'];
        }
        $msg = "Error adding column(s) $columns on table: $tablename:";
        return $this->query($sql,true,$msg);
    }

    /**
	 * Alters old column identified by oldFieldDef to new fieldDef.
	 *
	 * @param string $tablename
     * @param array  $newFieldDef
     * @param bool   $ignoreRequired optional, true if we are ignoring this being a required field
	 */
    public function alterColumn($tablename, $newFieldDef, $ignoreRequired = false)
    {
        $this->tableName = $tablename;
        $sql = $this->alterColumnSQL($tablename, $newFieldDef,$ignoreRequired);
        if ($this->isFieldArray($newFieldDef)){
            foreach ($newFieldDef as $fieldDef) {
                unset(self::$table_descriptions[$tablename][$fieldDef['name']]);
                $columns[] = $fieldDef['name'];
            }
            $columns = implode(",", $columns);
        }
        else {
            unset(self::$table_descriptions[$tablename][$newFieldDef['name']]);
            $columns = $newFieldDef['name'];
        }

        $msg = "Error altering column(s) $columns on table: $tablename:";
        return $this->query($sql,true,$msg);
    }

    /**
     * Drops the table associated with a bean
     *
     * @param object $bean SugarBean instance
     */
    public function dropTable(SugarBean $bean)
    {
        $this->dropTableName($bean->getTableName());
    }

    /**
     * Drops the table by name
     *
     * @param string $name SugarBean instance
     */
    public function dropTableName($name)
    {
        $sql = $this->dropTableNameSQL($name);
        $msg = "Error dropping table $name:";
        $this->query($sql,true,$msg);
    }

    /**
     * Deletes a column identified by fieldDef.
     *
     * @param string $name      SugarBean instance
     * @param array  $fieldDefs
     */
    public function deleteColumn(SugarBean $bean, $fieldDefs)
    {
        $this->tableName = $tablename = $bean->getTableName();
        $sql = $this->dropColumnSQL($tablename, $fieldDefs);
        $msg = "Error deleting column(s) on table: $tablename:";
        $this->query($sql,true,$msg);
    }

    /**
     * Generate a set of Insert statements based on the bean given
     *
     * @deprecated
     *
     * @param  object $bean         the bean from which table we will generate insert stmts
     * @param  string $select_query the query which will give us the set of objects we want to place into our insert statement
     * @param  int    $start        the first row to query
     * @param  int    $count        the number of rows to query
     * @param  string $table        the table to query from
     * @param  string $db_type      the client db type
     * @return string SQL insert statement
     */
	public function generateInsertSQL(
        SugarBean $bean,
        $select_query,
        $start,
        $count = -1,
        $table,
        $db_type,
        $is_related_query = false
        )
    {
        $GLOBALS['log']->info('call to DBManager::generateInsertSQL() is deprecated');
        global $sugar_config;

        $count_query = $bean->create_list_count_query($select_query);
		if(!empty($count_query))
		{
			// We have a count query.  Run it and get the results.
			$result = $this->query($count_query, true, "Error running count query for $this->object_name List: ");
			$assoc = $this->fetchByAssoc($result);
			if(!empty($assoc['c']))
			{
				$rows_found = $assoc['c'];
			}
		}
		if($count == -1){
			$count 	= $sugar_config['list_max_entries_per_page'];
		}
		$next_offset = $start + $count;

		$result = $this->limitQuery($select_query, $start, $count);
		$row_count = $this->getRowCount($result);
		// get basic insert
		$sql = "INSERT INTO ".$table;
		$custom_sql = "INSERT INTO ".$table."_cstm";

		// get field definitions
		$fields = $bean->getFieldDefinitions();
		$custom_fields = array();

		if($bean->hasCustomFields()){
			foreach ($fields as $fieldDef){
				if($fieldDef['source'] == 'custom_fields'){
					$custom_fields[$fieldDef['name']] = $fieldDef['name'];
				}
			}
			if(!empty($custom_fields)){
				$custom_fields['id_c'] = 'id_c';
				$id_field = array('name' => 'id_c', custom_type => 'id',);
				$fields[] = $id_field;
			}
		}

		// get column names and values
		$row_array = array();
		$columns = array();
		$cstm_row_array = array();
		$cstm_columns = array();
		$built_columns = false;
        //configure client helper
        $dbHelper = $this->configureHelper($db_type);
		while(($row = $this->fetchByAssoc($result)) != null)
		{
			$values = array();
			$cstm_values = array();
            if(!$is_related_query){
    			foreach ($fields as $fieldDef)
    			{
    				if(isset($fieldDef['source']) && $fieldDef['source'] != 'db' && $fieldDef['source'] != 'custom_fields') continue;
    				$val = $row[$fieldDef['name']];

    		   		//handle auto increment values here only need to do this on insert not create
               		if ($fieldDef['name'] == 'deleted'){
    		   			 $values['deleted'] = $val;
    		   			 if(!$built_columns){
               				$columns[] = 'deleted';
               			}
    		   		}
               		else
    		   		{
    		   			$type = $fieldDef['type'];
						if(!empty($fieldDef['custom_type'])){
							$type = $fieldDef['custom_type'];
						}
    		    		 // need to do some thing about types of values
						 if($db_type == 'mysql' && $val == '' && ($type == 'datetime' ||  $type == 'date' || $type == 'int' || $type == 'currency' || $type == 'decimal')){
							if(!empty($custom_fields[$fieldDef['name']]))
								$cstm_values[$fieldDef['name']] = 'null';
							else
						 		$values[$fieldDef['name']] = 'null';
						 }else{
    		     			 if(isset($type) && $type=='int') {
                             	if(!empty($custom_fields[$fieldDef['name']]))
                             		$cstm_values[$fieldDef['name']] = $GLOBALS['db']->quote(from_html($val));
    		     			 	else
                             		$values[$fieldDef['name']] = $GLOBALS['db']->quote(from_html($val));
                             } else {
                             	if(!empty($custom_fields[$fieldDef['name']]))
                             		$cstm_values[$fieldDef['name']] = "'".$GLOBALS['db']->quote(from_html($val))."'";
                             	else
                             		$values[$fieldDef['name']] = "'".$GLOBALS['db']->quote(from_html($val))."'";
                             }
						 }
    		     		if(!$built_columns){
               				if(!empty($custom_fields[$fieldDef['name']]))
								$cstm_columns[] = $fieldDef['name'];
							else
    		     				$columns[] = $fieldDef['name'];
               			}
    		   		}

    			}
            }else{
               foreach ($row as $key=>$val)
               {
               		if($key != 'orc_row'){
	                    $values[$key] = "'$val'";
	                    if(!$built_columns){
	                        $columns[] = $key;
	                    }
               		}
               }
            }
			$built_columns = true;
			if(!empty($values)){
				$row_array[] = $values;
			}
			if(!empty($cstm_values) && !empty($cstm_values['id_c']) && (strlen($cstm_values['id_c']) > 7)){
				$cstm_row_array[] = $cstm_values;
			}
		}

		//if (sizeof ($values) == 0) return ""; // no columns set

		// get the entire sql
		$sql .= "(".implode(",", $columns).") ";
		$sql .= "VALUES";
		for($i = 0; $i < count($row_array); $i++){
			$sql .= " (".implode(",", $row_array[$i]).")";
			if($i < (count($row_array) - 1)){
				$sql .= ", ";
			}
		}
		//custom
		// get the entire sql
		$custom_sql .= "(".implode(",", $cstm_columns).") ";
		$custom_sql .= "VALUES";

		for($i = 0; $i < count($cstm_row_array); $i++){
			$custom_sql .= " (".implode(",", $cstm_row_array[$i]).")";
			if($i < (count($cstm_row_array) - 1)){
				$custom_sql .= ", ";
			}
		}
		return array('data' => $sql, 'cstm_sql' => $custom_sql, 'result_count' => $row_count, 'total_count' => $rows_found, 'next_offset' => $next_offset);
	}

    /**
     * @deprecated
     * Disconnects all instances
     */
    public function disconnectAll()
    {
        DBManagerFactory::disconnectAll();
    }

    /**
     * This function sets the query threshold limit
     *
     * @param int $limit value of query threshold limit
     */
    public static function setQueryLimit($limit)
    {
		//reset the queryCount
		self::$queryCount = 0;
		self::$queryLimit = $limit;
    }

    /**
     * Returns the static queryCount value
     *
     * @return int value of the queryCount static variable
     */
    public static function getQueryCount()
    {
        return self::$queryCount;
    }


    /**
     * Resets the queryCount value to 0
     *
     */
    public static function resetQueryCount() {
    	self::$queryCount = 0;
    }

    /**
     * This function increments the global $sql_queries variable
     *
     * @param $sql The SQL statement being counted
     */
    public function countQuery()
    {
		if (self::$queryLimit != 0 && ++self::$queryCount > self::$queryLimit
            &&(empty($GLOBALS['current_user']) || !is_admin($GLOBALS['current_user']))) {
		   require_once('include/resource/ResourceManager.php');
		   $resourceManager = ResourceManager::getInstance();
		   $resourceManager->notifyObservers('ERR_QUERY_LIMIT');
		}
    }

    /**
     * Returns a string properly quoted for this database
     *
     * @param string $string
     */
    public function quote($string)
    {
        if(is_array($string)) {
            return $this->arrayQuote($string);
        }
        return from_html($string);
    }

    /**
     * Return string properly quoted with ''
     * @param string $string
     * @return string
     */
    public function quoted($string)
    {
        return "'".$this->quote($string)."'";
    }

    /**
     * Quote identifier (table/column name)
     * @param string $string
     */
    public function quoteIdentifier($string)
    {
        return $this->quoted($string);
    }

    /**
     * Quote the strings of the passed in array
     *
     * The array must only contain strings
     *
     * @param array $array
     * @param bool  $isLike
     */
    public function arrayQuote(array &$array)
    {
        foreach($array as &$val) {
            $val = $this->quote($val);
        }
        return $array;
    }
    /**
     * Frees out previous results
     *
     * @param resource $result optional, pass if you want to free a single result instead of all results
     */
    protected function freeResult($result = false)
    {
        if($result) {
            $this->freeDbResult($result);
        }
        if($this->lastResult) {
            $this->freeDbResult($this->lastResult);
            $this->lastResult = null;
        }
    }

    /**
     * Runs a query and returns a single row containing single value
     *
     * @param  string   $sql        SQL Statement to execute
     * @param  bool     $dieOnError True if we want to call die if the query returns errors
     * @param  string   $msg        Message to log if error occurs
     * @return array    single value from the query
     */
    public function getOne($sql, $dieOnError = false, $msg = '')
    {
        $GLOBALS['log']->info("Get One: |$sql|");
        $queryresult = $this->query($sql, $dieOnError, $msg);
        $this->checkError($msg.' Get One Failed:' . $sql, $dieOnError);
        if (!$queryresult) return false;
        $row = $this->fetchByAssoc($queryresult);
        if(!empty($row)) {
            return array_shift($row);
        }
        return false;
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
        $GLOBALS['log']->info("Fetch One: |$sql|");
        $this->checkConnection();
        $queryresult = $this->query($sql, $dieOnError, $msg);
        $this->checkError($msg.' Fetch One Failed:' . $sql, $dieOnError);

        if (!$queryresult) return false;

        $row = $this->fetchByAssoc($queryresult);
        if ( !$row ) return false;

        $this->freeResult($queryresult);
        return $row;
    }

    /**
     * Returns the number of rows returned by the result
     *
     * @param  resource $result
     * @return int
     */
    public function getRowCount($result)
    {
		return 0;
	}

    /**
     * Returns the number of rows affected by the last query
     *
     * @return int
     */
    public function getAffectedRowCount($result)
    {
        return 0;
    }

    /**
     * Get table description
     * @param string $tablename
     */
    public function getTableDescription($tablename, $reload = false)
    {
        if($reload || empty(self::$table_descriptions[$tablename])) {
            self::$table_descriptions[$tablename] = $this->get_columns($tablename);
        }
        return self::$table_descriptions[$tablename];
    }

    /**
     * Returns the field description for a given field in table
     *
     * @param  string $name
     * @param  string $tablename
     * @return array
     */
    protected function describeField($name, $tablename)
    {
        $table = $this->getTableDescription($tablename);
        if(!empty($table) && isset($table[$name]))
            return 	$table[$name];

        $table = $this->getTableDescription($tablename, true);

        if(isset($table[$name]))
           return $table[$name];

        return array();
    }

    /**
     * Returns the index description for a given index in table
     *
     * @param  string $name
     * @param  string $tablename
     * @return array
     */
    protected function describeIndex($name, $tablename)
    {
        if(isset(self::$index_descriptions[$tablename]) && isset(self::$index_descriptions[$tablename]) && isset(self::$index_descriptions[$tablename][$name])){
            return 	self::$index_descriptions[$tablename][$name];
        }

        self::$index_descriptions[$tablename] = $this->get_indices($tablename);

        if(isset(self::$index_descriptions[$tablename][$name])){
            return 	self::$index_descriptions[$tablename][$name];
        }

        return array();
    }

    /**
     * Truncates a string to a given length
     *
     * @param string $string
     * @param int    $len    length to trim to
     * @param string
     */
    public function truncate($string, $len)
    {
    	if ( is_numeric($len) && $len > 0)
        {
            $string = mb_substr($string,0,(int) $len, "UTF-8");
        }
        return $string;
    }
    /**
     * Use when you need to convert a database string to a different value; this function does it in a
     * database-backend aware way
     *
     * @param string $string database string to convert
     * @param string $type type of conversion to do
     * @param array  $additional_parameters optional, additional parameters to pass to the db function
     * @return string
     */
    public function convert($string, $type, array $additional_parameters = array())
    {
        /*
         * Supported conversions:
            today		return current date
            left		Take substring from the left
            date_format	Format date as string, supports %Y-%m-%d, %Y-%m, %Y
            datetime	Format date as standard-format datetime string
            ifnull		If var is null, use default value
            concat		Concatenate strings
            quarter		Quarter number of the date
            length		Length of string
            month		Month number of the date
            add_date	Add specified interval to a date
         */
        return $string;
    }

    /**
     * Returns the database string needed for concatinating multiple database strings together
     *
     * @param string $table table name of the database fields to concat
     * @param array $fields fields in the table to concat together
     * @return string
     */
    public function concat($table, array $fields, $space = ' ')
    {
        if(empty($fields)) return '';
        $elems = array();
        $space = $this->quoted($space);
        foreach ( $fields as $index => $field ) {
            if(!empty($elems)) $elems[] = $space;
            $elems[] = $this->convert("$table.$field", 'IFNULL', array("''"));
        }
        $first = array_shift($elems);
        return "LTRIM(RTRIM(".$this->convert($first, 'CONCAT', $elems)."))";
    }

    /**
     * Undoes database conversion
     *
     * @param string $string database string to convert
     * @param string $type type of conversion to do
     * @return string
     */
    public function fromConvert($string, $type)
    {
        return $string;
    }

	/**
     * Given a sql stmt attempt to parse it into the sql and the tokens. Then return the index of this prepared statement
     * Tokens can come in the following forms:
     * ? - a scalar which will be quoted
     * ! - a literal which will not be quoted
     * & - binary data to read from a file
     *
     * @param  string	$sql        The sql to parse
     * @return int index of the prepared statement to be used with execute
     */
    public function prepareQuery($sql){
    	//parse out the tokens
    	$tokens = preg_split('/((?<!\\\)[&?!])/', $sql, -1, PREG_SPLIT_DELIM_CAPTURE);

    	//maintain a count of the actual tokens for quick reference in execute
    	$count = 0;

    	$sqlStr = '';
	    foreach ($tokens as $key => $val) {
	        switch ($val) {
	            case '?' :
	            case '!' :
	            case '&' :
	            	$count++;
	            	$sqlStr .= '?';
	            	break;

	            default :
	            	//escape any special characters
	                $tokens[$key] = preg_replace('/\\\([&?!])/', "\\1", $val);
	                $sqlStr .= $tokens[$key];
	                break;
	        } // switch
	    } // foreach

	    $this->preparedTokens[] = array('tokens' => $tokens, 'tokenCount' => $count, 'sqlString' => $sqlStr);
	    end($this->preparedTokens);
	    return key($this->preparedTokens);
    }

    /**
     * Takes a prepared stmt index and the data to replace and creates the query and runs it.
     *
     * @param  int		$stmt       The index of the prepared statement from preparedTokens
     * @param  array    $data 		The array of data to replace the tokens with.
     * @return resource result set or false on error
     */
    public function executePreparedQuery($stmt, $data = array())
    {
    	if(!empty($this->preparedTokens[$stmt])){
    		if(!is_array($data)){
				$data = array($data);
			}

    		$pTokens = $this->preparedTokens[$stmt];

    		//ensure that the number of data elements matches the number of replacement tokens
    		//we found in prepare().
    		if(count($data) != $pTokens['tokenCount']){
    			//error the data count did not match the token count
    			return false;
    		}

    		$query = '';
    		$dataIndex = 0;
    		$tokens = $pTokens['tokens'];
    		foreach ($tokens as $val) {
            	switch ($val) {
            		case '?':
            			$query .= $this->quote($data[$dataIndex++]);
            			break;
            		case '&':
            			$filename = $data[$dataIndex++];
				        $query .= file_get_contents($filename);
            			break;
            		case '!':
            			$query .= $data[$dataIndex++];
            			break;
            		default:
            			$query .= $val;
            			break;
            	}//switch
    		}//foreach
    		return $this->query($query);
    	}else{
    		return false;
    	}
    }

    /**
     * Run both prepare and execute without the client having to run both individually.
     *
     * @param  string	$sql        The sql to parse
     * @param  array    $data 		The array of data to replace the tokens with.
     * @return resource result set or false on error
     */
    public function pQuery($sql, $data = array()){
    	$stmt = $this->prepareQuery($sql);
    	return $this->executePreparedQuery($stmt, $data);
    }

/********************** SQL FUNCTIONS ****************************/
    /**
	 * Generates sql for create table statement for a bean.
	 *
	 * NOTE: does not handle out-of-table constraints, use createConstraintSQL for that
	 * @param  object $bean SugarBean instance
	 * @return string SQL Create Table statement
	 */
	public function createTableSQL(SugarBean $bean)
    {
		$tablename = $bean->getTableName();
		$fieldDefs = $bean->getFieldDefinitions();
		$indices = $bean->getIndices();
		$sql = $this->createTableSQLParams($tablename, $fieldDefs, $indices);
	}

	/**
     * Generates SQL for insert statement.
     *
     * @param  object $bean SugarBean instance
     * @return string SQL Create Table statement
     */
    public function insertSQL(SugarBean $bean)
    {
		// get column names and values
		$values = array();
		foreach ($bean->getFieldDefinitions() as $field => $fieldDef)
		{
            if (isset($fieldDef['source']) && $fieldDef['source'] != 'db')  continue;
            //custom fields handle there save seperatley
            if(isset($bean->field_name_map) && !empty($bean->field_name_map[$field]['custom_type'])) continue;

            if(isset($bean->$field)) {
                // clean the incoming value..
                $val = from_html($bean->$field);
            } else {
                if(isset($fieldDef['default']) && strlen($fieldDef['default']) > 0) {
                    $val = $fieldDef['default'];
                } else {
                    $val = null;
                }
            }

            //handle auto increment values here only need to do this on insert not create
            // TODO: do we really need to do this?
            if (!empty($fieldDef['auto_increment'])) {
                $auto = $this->getAutoIncrementSQL($bean->getTableName(), $fieldDef['name']);
                if(!empty($auto)) {
                    $values[$field] = $auto;
                }
            } elseif ($fieldDef['name'] == 'deleted') {
                $values['deleted'] = (int)$val;
            } else {
                // need to do some thing about types of values
                $values[$field] = $this->massageValue($val, $fieldDef);
            }
		}

		if ( sizeof($values) == 0 )
            return ""; // no columns set

		// get the entire sql
		return "INSERT INTO ".$bean->getTableName()."
                    (".implode(",", array_keys($values)).")
                    VALUES (".implode(",", $values).")";
	}

	/**
     * Generates SQL for update statement.
     *
     * @param  object $bean SugarBean instance
     * @param  array  $where Optional, where conditions in an array
     * @return string SQL Create Table statement
     */
    public function updateSQL(SugarBean $bean, array $where = array())
    {
        $primaryField = $bean->getPrimaryFieldDefinition();
        $columns = array();

		// get column names and values
		foreach ($bean->getFieldDefinitions() as $field => $fieldDef) {
            if (isset($fieldDef['source']) && $fieldDef['source'] != 'db')  continue;
		    // Do not write out the id field on the update statement.
           // We are not allowed to change ids.
           if ($fieldDef['name'] == $primaryField['name']) continue;

           // If the field is an auto_increment field, then we shouldn't be setting it.  This was added
           // specially for Bugs and Cases which have a number associated with them.
           if (!empty($bean->field_name_map[$field]['auto_increment'])) continue;

           //custom fields handle their save seperatley
           if(isset($bean->field_name_map) && !empty($bean->field_name_map[$field]['custom_type']))  continue;

           if(isset($bean->$field)) {
               $val = from_html($bean->$field);
           } else {
                continue;
           }
		   if(!empty($fieldDef['type']) && $fieldDef['type'] == 'bool'){
               $val = $bean->getFieldValue($field);
		   }
           if(strlen($val) == 0 && isset($fieldDef['default']) && strlen($fieldDef['default']) > 0) {
               $val = $fieldDef['default'];
           }

		   $columns[] = "{$fieldDef['name']}=".$this->massageValue($val, $fieldDef);
		}

		if ( sizeof($columns) == 0 )
            return ""; // no columns set

        // build where clause
        $where = $this->getWhereClause($bean, $this->updateWhereArray($bean, $where));

        return "UPDATE ".$bean->getTableName()."
                    SET ".implode(",", $columns)."
                    $where AND deleted=0";
	}

    /**
     * This method returns a where array so that it has id entry if
     * where is not an array or is empty
     *
     * @param  object $bean SugarBean instance
     * @param  array  $where Optional, where conditions in an array
     * @return array
     */
    protected function updateWhereArray(SugarBean $bean, array $where = array())
    {
		if (count($where) == 0) {
            $fieldDef = $bean->getPrimaryFieldDefinition();
            $primaryColumn = $fieldDef['name'];

            $val = $bean->getFieldValue($fieldDef['name']);
            if ($val != FALSE){
                $where[$primaryColumn] = $val;
            }
        }

        return $where;
	}

    /**
     * Returns a where clause without the 'where' key word
     *
     * The clause returned does not have an 'and' at the beginning and the columns
     * are joined by 'and'.
     *
     * @param  string $table table name
     * @param  array  $whereArray Optional, where conditions in an array
     * @return string
     */
    protected function getColumnWhereClause($table, array $whereArray = array())
    {
        $where = array();
        foreach ($whereArray as $name => $val) {
            $op = "=";
            if (is_array($val)) {
                $op = "IN";
                $temp = array();
                foreach ($val as $tval){
                    $temp[] = $this->quoted($tval);
                }
                $val = implode(",", $temp);
                $val = "($val)";
            } else {
                $val = $this->quoted($val);
            }

            $where[] = " $table.$name $op $val";
        }

        if (!empty($where))
            return implode(" AND ", $where);

        return '';
    }

    /**
     * This method returns a complete where clause built from the
     * where values specified.
     *
     * @param  string $table table name
     * @param  array  $whereArray Optional, where conditions in an array
     * @return string
     */
	protected function getWhereClause(SugarBean $bean, array $whereArray=array())
	{
       return " WHERE " . $this->getColumnWhereClause($bean->getTableName(), $whereArray);
	}

    /**
     * Outputs a correct string for the sql statement according to value
     *
     * @param  mixed $val
     * @param  array $fieldDef field definition
     * @return mixed
     */
	public function massageValue($val, $fieldDef)
    {
//        if(is_null($val)) {
//            return "NULL";
//        }
//
        $type = $this->getFieldType($fieldDef);

        switch ($type) {
            case 'int':
            case 'uint':
            case 'ulong':
            case 'long':
            case 'short':
            case 'tinyint':
            case 'bool':
                return intval($val);
            case 'double':
            case 'float':
            case 'currency':
                return floatval($val);
                break;
		}

		if ( strlen($val) == 0 )
            return "''";

        return $this->quoted($val);
	}

    /**
     * Massages the field defintions to fill in anything else the DB backend may add
     *
     * @param  array  $fieldDef
     * @param  string $tablename
     * @return array
     */
    public function massageFieldDef(&$fieldDef, $tablename)
    {
        if ( !isset($fieldDef['dbType']) ) {
            if ( isset($fieldDef['dbtype']) )
                $fieldDef['dbType'] = $fieldDef['dbtype'];
            else
                $fieldDef['dbType'] = $fieldDef['type'];
        }
        $type = $this->getColumnType($fieldDef['dbType'],$fieldDef['name'],$tablename);
        $matches = array();
        preg_match_all('/(\w+)(?:\(([0-9]+,?[0-9]*)\)|)/i', $type, $matches);
        if ( isset($matches[1][0]) )
            $fieldDef['type'] = $matches[1][0];
        if ( isset($matches[2][0]) && empty($fieldDef['len']) )
            $fieldDef['len'] = $matches[2][0];
        if ( !empty($fieldDef['precision']) && is_numeric($fieldDef['precision']) && !strstr($fieldDef['len'],',') )
            $fieldDef['len'] .= ",{$fieldDef['precision']}";
        if (!empty($fieldDef['required']) || ($fieldDef['name'] == 'id' && !isset($fieldDef['required'])) ) {
            $fieldDef['required'] = 'true';
        }
    }

	/**
	 * Take an SQL statement and produce a list of fields used in that select
	 * @param string $selectStatement
	 * @return array
	 */
	public function getSelectFieldsFromQuery($selectStatement)
	{
		$selectStatement = trim($selectStatement);
		if (strtoupper(substr($selectStatement, 0, 6)) == "SELECT")
			$selectStatement = trim(substr($selectStatement, 6));

		//Due to sql functions existing in many selects, we can't use php explode
		$fields = array();
		$level = 0;
		$selectField = "";
		$strLen = strlen($selectStatement);
		for($i = 0; $i < $strLen; $i++)
		{
			$char = $selectStatement[$i];

			if ($char == "," && $level == 0)
			{
				$field = $this->getFieldNameFromSelect(trim($selectField));
				$fields[$field] = $selectField;
				$selectField = "";
			}
			else if ($char == "("){
				$level++;
				$selectField .= $char;
			}
			else if($char == ")"){
				$level--;
				$selectField .= $char;


			}else{
				$selectField .= $char;
			}

		}
		$fields[$this->getFieldNameFromSelect($selectField)] = $selectField;
		return $fields;
	}

	/**
	 * returns the field name used in a select
	 * @param String $string
	 */
	protected function getFieldNameFromSelect($string)
	{
	    if(strncasecmp($string, "DISTINCT ", 9) == 0) {
	        $string = substr($string, 9);
	    }
		if (stripos($string, " as ") !== false)
			//"as" used for an alias
			return trim(substr($string, strripos($string, " as ") + 4));
		else if (strrpos($string, " ") != 0)
			//Space used as a delimeter for an alias
			return trim(substr($string, strrpos($string, " ")));
		else if (strpos($string, ".") !== false)
			//No alias, but a table.field format was used
			return substr($string, strpos($string, ".") + 1);
		else
			//Give up and assume the whole thing is the field name
			return $string;
	}

    /**
     * Generates SQL for delete statement identified by id.
     *
     * @param  object $bean SugarBean instance
     * @param  array  $where where conditions in an array
     * @return string SQL Update Statement
     */
	public function deleteSQL(SugarBean $bean, array $where)
    {
        $where = $this->getWhereClause($bean, $this->updateWhereArray($bean, $where));
        return "UPDATE ".$bean->getTableName()." SET deleted=1 $where";
	}

    /**
     * Generates SQL for select statement for any bean identified by id.
     *
     * @param  object $bean SugarBean instance
     * @param  array  $where where conditions in an array
     * @return string SQL Select Statement
     */
	public function retrieveSQL(SugarBean $bean, array $where)
    {
        $where = $this->getWhereClause($bean, $this->updateWhereArray($bean, $where));
        return "SELECT * FROM ".$bean->getTableName()." $where AND deleted=0";
    }

    /**
     * This method implements a generic sql for a collection of beans.
     *
     * Currently, this function does not support outer joins.
     *
     * @param  array $bean value returned by get_class method as the keys and a bean as
     *      the value for that key. These beans will be joined in the sql by the key
     *      attribute of field defs.
     * @param  array $cols Optional, columns to be returned with the keys as names of bean
     *      as identified by get_class of bean. Values of this array is the array of fieldDefs
     *      to be returned for a bean. If an empty array is passed, all columns are selected.
     * @param  array $whereClause Optional, values with the keys as names of bean as identified
     *      by get_class of bean. Each value at the first level is an array of values for that
     *      bean identified by name of fields. If we want to pass multiple values for a name,
     *      pass it as an array. If where is not passed, all the rows will be returned.
     * @return string SQL Select Statement
     */
    public function retrieveViewSQL(array $beans, array $cols = array(), array $whereClause = array())
    {
        $relations = array(); // stores relations between tables as they are discovered
        $where = $select = array();
        foreach ($beans as $beanID => $bean) {
            $tableName = $bean->getTableName();
            $beanTables[$beanID] = $tableName;

            $table = $beanID;
            $tables[$table] = $tableName;
            $aliases[$tableName][] = $table;

            // build part of select for this table
            if (is_array($cols[$beanID]))
                foreach ($cols[$beanID] as $def) $select[] = $table.".".$def['name'];

            // build part of where clause
            if (is_array($whereClause[$beanID])){
                $where[] = $this->getColumnWhereClause($table, $whereClause[$beanID]);
            }
            // initialize so that it can be used properly in form clause generation
            $table_used_in_from[$table] = false;

            $indices = $bean->getIndices();
            foreach ($indices as $index){
                if ($index['type'] == 'foreign') {
                    $relationship[$table][] = array('foreignTable'=> $index['foreignTable']
                                                   ,'foreignColumn'=>$index['foreignField']
                                                   ,'localColumn'=> $index['fields']
                                                   );
                }
            }
            $where[] = " $table.deleted = 0";
        }

        // join these clauses
        $select = !empty($select) ? implode(",", $select) : "*";
        $where = implode(" AND ", $where);

        // generate the from clause. Use relations array to generate outer joins
        // all the rest of the tables will be used as a simple from
        // relations table define relations between table1 and table2 through column on table 1
        // table2 is assumed to joing through primaty key called id
        $separator = "";
        $from = ''; $table_used_in_from = array();
        foreach ($relations as $table1 => $rightsidearray){
            if ($table_used_in_from[$table1]) continue; // table has been joined

            $from .= $separator." ".$table1;
            $table_used_in_from[$table1] = true;
            foreach ($rightsidearray as $tablearray){
                $table2 = $tablearray['foreignTable']; // get foreign table
                $tableAlias = $aliases[$table2]; // get a list of aliases fo thtis table
                foreach ($tableAlias as $table2) {
                    //choose first alias that does not match
                    // we are doing this because of self joins.
                    // in case of self joins, the same table will bave many aliases.
                    if ($table2 != $table1) break;
                }

                $col = $tablearray['foreingColumn'];
                $name = $tablearray['localColumn'];
                $from .= " LEFT JOIN $table on ($table1.$name = $table2.$col)";
                $table_used_in_from[$table2] = true;
            }
            $separator = ",";
        }

        return "SELECT $select FROM $from WHERE $where";
    }

    /**
     * Generates SQL for create index statement for a bean.
     *
     * @param  object $bean SugarBean instance
     * @param  array  $fields fields used in the index
     * @param  string $name index name
     * @param  bool   $unique Optional, set to true if this is an unique index
     * @return string SQL Select Statement
     */
	public function createIndexSQL(SugarBean $bean, array $fields, $name, $unique = true)
    {
		$unique = ($unique) ? "unique" : "";
		$tablename = $bean->getTableName();
        $columns = array();
		// get column names
		foreach ($fields as $fieldDef)
            $columns[] = $fieldDef['name'];

        if (empty($columns))
            return "";

        $columns = implode(",", $columns);

        return "CREATE $unique INDEX $name ON $tablename ($columns)";
	}

    /**
     * Returns the type of the variable in the field
     *
     * @param  array $fieldDef
     * @return string
     */
    public function getFieldType($fieldDef)
    {
        // get the type for db type. if that is not set,
        // get it from type. This is done so that
        // we do not have change a lot of existing code
        // and add dbtype where type is being used for some special
        // purposes like referring to foreign table etc.
        if(!empty($fieldDef['dbType']))
            return  $fieldDef['dbType'];
        if(!empty($fieldDef['dbtype']))
            return  $fieldDef['dbtype'];
        if (!empty($fieldDef['type']))
            return  $fieldDef['type'];
        if (!empty($fieldDef['Type']))
            return  $fieldDef['Type'];
        if (!empty($fieldDef['data_type']))
            return  $fieldDef['data_type'];

        return null;
    }

    /**
     * Returns the defintion for a single column
     *
     * @param  array  $fieldDef
     * @param  bool   $ignoreRequired  Optional, true if we should ignor this being a required field
     * @param  string $table           Optional, table name
     * @param  bool   $return_as_array Optional, true if we should return the result as an array instead of sql
     * @return string or array if $return_as_array is true
     */
	protected function oneColumnSQLRep($fieldDef, $ignoreRequired = false, $table = '', $return_as_array = false)
    {
        $name = $fieldDef['name'];
        $type = $this->getFieldType($fieldDef);
        $colType = $this->getColumnType($type, $name, $table);

        if (( $colType == 'nvarchar'
				or $colType == 'nchar'
				or $colType == 'varchar'
				or $colType == 'char'
				or $colType == 'varchar2') ) {
            if( !empty($fieldDef['len']))
                $colType .= "(".$fieldDef['len'].")";
            else
                $colType .= "(255)";
        }
       if($colType == 'decimal' || $colType == 'float'){
	        if(!empty($fieldDef	['len'])){
	        	if(!empty($fieldDef['precision']) && is_numeric($fieldDef['precision']))
	        		if(strpos($fieldDef	['len'],',') === false){
	                    $colType .= "(".$fieldDef['len'].",".$fieldDef['precision'].")";
	        		}else{
	                    $colType .= "(".$fieldDef['len'].")";
	        		}
	        	else
	                    $colType .= "(".$fieldDef['len'].")";
	        }
       }


        if (isset($fieldDef['default']) && strlen($fieldDef['default']) > 0)
            $default = " DEFAULT ".$this->quoted($fieldDef['default']);
        elseif (!isset($default) && $type == 'bool')
            $default = " DEFAULT 0 ";
        elseif (!isset($default))
            $default = '';

        $auto_increment = '';
        if(!empty($fieldDef['auto_increment']) && $fieldDef['auto_increment'])
        	$auto_increment = $this->setAutoIncrement($table , $fieldDef['name']);

        $required = 'NULL';  // MySQL defaults to NULL, SQL Server defaults to NOT NULL -- must specify
        //Starting in 6.0, only ID and auto_increment fields will be NOT NULL in the DB.
        if ((empty($fieldDef['isnull'])  || strtolower($fieldDef['isnull']) == 'false') &&
		(!empty($auto_increment) || $name == 'id' || ($fieldDef['type'] == 'id' && isset($fieldDef['required']) && $fieldDef['required'])))
		{
            $required =  "NOT NULL";
        }
		if ($ignoreRequired)
            $required = "";

        if ( $return_as_array ) {
            return array(
                'name' => $name,
                'colType' => $colType,
                'default' => $default,
                'required' => $required,
                'auto_increment' => $auto_increment,
                'full' => "$name $colType $default $required $auto_increment",
                );
        } else {
	    	return "$name $colType $default $required $auto_increment";
        }
	}

    /**
     * Returns SQL defintions for all columns in a table
     *
     * @param  array  $fieldDefs
     * @param  bool   $ignoreRequired Optional, true if we should ignor this being a required field
     * @param  string $tablename      Optional, table name
     * @return string SQL column definitions
     */
	protected function columnSQLRep($fieldDefs, $ignoreRequired = false, $tablename)
    {
		$columns = array();

		if ($this->isFieldArray($fieldDefs)) {
			foreach ($fieldDefs as $fieldDef) {
				if(!isset($fieldDef['source']) || $fieldDef['source'] == 'db') {
					$columns[] = $this->oneColumnSQLRep($fieldDef,false, $tablename);
				}
			}
			$columns = implode(",", $columns);
		}
		else {
			$columns = $this->oneColumnSQLRep($fieldDefs,$ignoreRequired, $tablename);
		}

		return $columns;
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
		return "";
	}

	/**
     * Returns the sql for the next value in a sequence
     *
     * @param  string $table tablename
     * @param  string $field_name
     * @return string
     */
    public function getAutoIncrementSQL($table, $field_name)
    {
        return "";
    }

	/**
     * Either creates an auto increment through queries or returns sql for auto increment
     * that can be appended to the end of column defination (mysql)
     *
     * @param  string $table tablename
     * @param  string $field_name
     * @return string
     */
	protected function setAutoIncrement($table, $field_name)
    {
        $this->deleteAutoIncrement($table, $field_name);
        return "";
	}

    /**
     * Sets the next auto-increment value of a column to a specific value.
     *
     * @param  string $table tablename
     * @param  string $field_name
     */
    public function setAutoIncrementStart($table, $field_name, $start_value)
    {
        return "";
    }

	/**
     * Deletes an auto increment (for oracle not mysql)
     *
     * @param string $table tablename
     * @param string $field_name
     */
	public function deleteAutoIncrement($table, $field_name)
    {
        return;
	}

	/**
     * This method generates sql for adding a column to table identified by field def.
     *
     * @param  string $tablename
     * @param  array  $fieldDefs
     * @return string SQL statement
     */
	public function addColumnSQL($tablename, $fieldDefs)
    {
       return $this->changeColumnSQL($tablename, $fieldDefs, 'add');
	}

    /**
     * This method genrates sql for altering old column identified by oldFieldDef to new fieldDef.
     *
     * @param  string $tablename
     * @param  array  $newFieldDefs
     * @param  bool   $ignoreRequired Optional, true if we should ignor this being a required field
     * @return string SQL statement
     */
	public function alterColumnSQL($tablename, $newFieldDefs, $ignorerequired = false)
    {
        return $this->changeColumnSQL($tablename, $newFieldDefs, 'modify', $ignorerequired);
    }

    /**
     * Generates SQL for dropping a table.
     *
     * @param  object $bean Sugarbean instance
     * @return string SQL statement
     */
	public function dropTableSQL(SugarBean $bean)
    {
		return $this->dropTableNameSQL($bean->getTableName());
	}

	/**
     * Generates SQL for dropping a table.
     *
     * @param  string $name table name
     * @return string SQL statement
     */
	public function dropTableNameSQL($name)
    {
		return "DROP TABLE ".$name;
	}

    /**
     * Truncate table
     * @param  $name
     * @return string
     */
    public function truncateTableSQL($name)
    {
        return "TRUNCATE $name";
    }

    /**
     * This method generates sql that deletes a column identified by fieldDef.
     *
     * @param  object $bean      Sugarbean instance
     * @param  array  $fieldDefs
     * @return string SQL statement
     */
	public function deleteColumnSQL(SugarBean $bean, $fieldDefs)
    {
        return $this->dropColumnSQL($bean->getTableName(), $fieldDefs);
	}

    /**
     * This method generates sql that drops a column identified by fieldDef.
     * Designed to work like the other addColumnSQL() and alterColumnSQL() functions
     *
     * @param  string $tablename
     * @param  array  $fieldDefs
     * @return string SQL statement
     */
	public function dropColumnSQL($tablename, $fieldDefs)
    {
        return $this->changeColumnSQL($tablename, $fieldDefs, 'drop');
	}

    /*
     * Return a version of $proposed that can be used as a column name in any of our supported databases
     * Practically this means no longer than 25 characters as the smallest identifier length for our supported DBs is 30 chars for Oracle plus we add on at least four characters in some places (for indicies for example)
     * @param string $name Proposed name for the column
     * @param string $ensureUnique
     * @return string Valid column name trimmed to right length and with invalid characters removed
     */
    public function getValidDBName ($name, $ensureUnique = false, $type = 'column')
    {
        if(is_array($name)) {
            $result = array();
            foreach($name as $field) {
                $result[] = $this->getValidDBName($field, $ensureUnique, $type);
            }
        } else {
            // first strip any invalid characters - all but alphanumerics and -
            $name = preg_replace ( '/[^\w-]+/i', '', $name ) ;
            $len = strlen( $name ) ;
            $result = $name;
            $maxLen = empty($this->maxNameLengths[$type]) ? $this->maxNameLengths[$type]['column'] : $this->maxNameLengths[$type];
            if ($len <= $maxLen) {
                return strtolower($name);
            }
            if ($ensureUnique) {
                $md5str = md5($name);
                $tail = substr ( $name, -11) ;
                $temp = substr($md5str , strlen($md5str)-4 );
                $result = substr ( $name, 0, 10) . $temp . $tail ;
            } else {
                $result = substr ( $name, 0, 11) . substr ( $name, 11 - $maxLen);
            }

            return strtolower ( $result ) ;
        }
    }

    /**
     * Returns the valid type for a column given the type in fieldDef
     *
     * @param  string $type field type
     * @return string valid type for the given field
     */
    public function getColumnType($type)
    {
        return isset($this->type_map[$type])?$this->type_map[$type]:null;
    }

    /**
     * Checks to see if passed array is truely an array of defitions
     *
     * Such an array may have type as a key but it will point to an array
     * for a true array of definitions an to a col type for a definition only
     *
     * @param  mixed $defArray
     * @return bool
     */
    public function isFieldArray($defArray)
    {
        if ( !is_array($defArray) )
            return false;

        if ( isset($defArray['type']) ){
            // type key exists. May be an array of defs or a simple definition
            return is_array($defArray['type']); // type is not an array => definition else array
        }

        // type does not exist. Must be array of definitions
        return true;
    }

    /**
     * returns true if the type can be mapped to a valid column type
     *
     * @param  string $type
     * @return bool
     */
    protected function validColumnType($type)
    {
        $type = $this->getColumnType($type);
        return !empty($type);
    }

    /**
     * Generate query for audit table
     * @param SugarBean $bean
     * @param array $changes
     */
    protected function auditSQL(SugarBean $bean, $changes)
    {
		global $current_user;
		$sql = "INSERT INTO ".$bean->get_audit_table_name();
		//get field defs for the audit table.
		require('metadata/audit_templateMetaData.php');
		$fieldDefs = $dictionary['audit']['fields'];

		$values=array();
		$values['id'] = $this->massageValue(create_guid(), $fieldDefs['id']);
		$values['parent_id']= $this->massageValue($bean->id, $fieldDefs['parent_id']);
		$values['field_name']= $this->massageValue($changes['field_name'], $fieldDefs['field_name']);
		$values['data_type'] = $this->massageValue($changes['data_type'], $fieldDefs['data_type']);
		if ($changes['data_type']=='text') {
			$bean->fetched_row[$changes['field_name']]=$changes['after'];;
			$values['before_value_text'] = $this->massageValue($changes['before'], $fieldDefs['before_value_text']);
			$values['after_value_text'] = $this->massageValue($changes['after'], $fieldDefs['after_value_text']);
		} else {
			$bean->fetched_row[$changes['field_name']]=$changes['after'];;
			$values['before_value_string'] = $this->massageValue($changes['before'], $fieldDefs['before_value_string']);
			$values['after_value_string'] = $this->massageValue($changes['after'], $fieldDefs['after_value_string']);
		}
		$values['date_created'] = $this->massageValue(TimeDate::getInstance()->nowDb(), $fieldDefs['date_created'] );
		$values['created_by'] = $this->massageValue($current_user->id, $fieldDefs['created_by']);

		$sql .= "(".implode(",", array_keys($values)).") ";
		$sql .= "VALUES(".implode(",", $values).")";
		return $sql;
    }

    /**
     * Saves changes to module's audit table
     *
     * @param object $bean    Sugarbean instance
     * @param array  $changes changes
     * @see DBHelper::getDataChanges()
     */
    public function save_audit_records(SugarBean $bean, $changes)
	{
        return $this->query($this->auditSQL($bean, $changes));
	}

    /**
     * Uses the audit enabled fields array to find fields whose value has changed.
	 * The before and after values are stored in the bean.
     *
     * @param object $bean Sugarbean instance
     * @return array
     */
	public function getDataChanges(SugarBean &$bean)
    {
    	$changed_values=array();
		$audit_fields=$bean->getAuditEnabledFieldDefinitions();

		if (is_array($audit_fields) and count($audit_fields) > 0) {
			foreach ($audit_fields as $field=>$properties) {
				if (!empty($bean->fetched_row) && array_key_exists($field, $bean->fetched_row)) {
					$before_value=$bean->fetched_row[$field];
					$after_value=$bean->$field;
					if (isset($properties['type'])) {
						$field_type=$properties['type'];
					} else {
						if (isset($properties['dbType']))
							$field_type=$properties['dbType'];
						else if(isset($properties['data_type']))
							$field_type=$properties['data_type'];
						else
							$field_type=$properties['dbtype'];
					}

					//Because of bug #25078(sqlserver haven't 'date' type, trim extra "00:00:00" when insert into *_cstm table).
					// so when we read the audit datetime field from sqlserver, we have to replace the extra "00:00:00" again.
					if(!empty($field_type) && $field_type == 'date'){
						$before_value = $this->fromConvert($before_value , $field_type);
					}
					//if the type and values match, do nothing.
					if (!($this->_emptyValue($before_value,$field_type) && $this->_emptyValue($after_value,$field_type))) {
						if (trim($before_value) !== trim($after_value)) {
							if (!($this->_isTypeNumber($field_type) && (trim($before_value)+0) == (trim($after_value)+0))) {
								if (!($this->_isTypeBoolean($field_type) && ($this->_getBooleanValue($before_value)== $this->_getBooleanValue($after_value)))) {
									$changed_values[$field]=array('field_name'=>$field,
										'data_type'=>$field_type,
										'before'=>$before_value,
										'after'=>$after_value);
								}
							}
						}
					}
				}
			}
		}
		return $changed_values;
	}

	/**
     * Function returns true is full-text indexing is available in the connected database.
     *
     * Default value is false.
     *
     * @param  string $dbname
     * @return bool
     */
	protected function full_text_indexing_enabled($dbname = null)
	{
	    return $this->supports('fulltext');
	}

	public function full_text_indexing_installed()
	{
	    return false;
	}

	public function full_text_indexing_setup()
	{
	}

	/**
     * Quotes a string for storing in the database
     * @deprecated
     * Return value will be not surrounded by quotes
     *
     * @param  string $string
     * @return string
     */
    public function escape_quote($string)
    {
        return $this->quote($string);
    }

    /**
     * Renames an index definition
     *
     * @param  array  $old_definition
     * @param  array  $new_definition
     * @param  string $tablename
     * @return string SQL statement
     */
    public function rename_index($old_definition, $new_definition, $table_name)
    {
        return array($this->add_drop_constraint($table_name,$old_definition,true),
                $this->add_drop_constraint($table_name,$new_definition), false);
    }

    /**
     * Check if type is boolean
     * @param string $type
     */
    protected function _isTypeBoolean($type)
    {
        return 'bool' == $type;
    }

    /**
     * Get truth value for boolean type
     * Allows 'off' to mean false, along with all 'empty' values
     * @param mixed $val
     */
    protected function _getBooleanValue($val)
    {
    	//need to put the === sign here otherwise true == 'non empty string'
        if (empty($val) or $val==='off')
            return false;

        return true;
    }

    /**
     * Check if type is a number
     * @param string $type
     */
    protected function _isTypeNumber($type)
    {
        switch ($type) {
            case 'decimal':
            case 'decimal2':
            case 'int':
            case 'double':
            case 'float':
            case 'uint':
            case 'ulong':
            case 'long':
            case 'short':
                return true;
        }
        return false;
    }

    /**
     * return true if the value if empty
     */
    protected function _emptyValue($val, $type)
    {
        if (empty($val))
            return true;

        if($this->emptyValue($type) == $val) {
            return true;
        }
        switch ($type) {
            case 'decimal':
            case 'decimal2':
            case 'int':
            case 'double':
            case 'float':
            case 'uint':
            case 'ulong':
            case 'long':
            case 'short':
                return ($val == 0);
            case 'date':
                if ($val == '0000-00-00')
                    return true;
                if ($val == 'NULL')
                    return true;
                return false;
        }

        return false;
    }

    /**
     * Does this type represent text (i.e., non-varchar) value?
     * @param string $type
     */
    public function isTextType($type)
    {
        return false;
    }

    /**
     * Check if this DB supports certain capability
     * @param string $cap
     */
    public function supports($cap)
    {
        return !empty($this->capabilities[$cap]);
    }

    public function orderByEnum($order_by, $values, $order_dir)
    {
        $order_by_arr = array();
        foreach ($values as $key => $value) {
				array_push($order_by_arr, $order_by."=".$this->quoted($key)." $order_dir\n");
			}
		return implode(',', $order_by_arr);
    }

    /**
     * Return representation of an empty value depending on type
     * The value is fully quoted, converted, etc.
     * @param string $type
     */
    public function emptyValue($type)
    {
        if($this->_isTypeNumber($type)) {
            return 0;
        }
        if($type == "currency") {
            return 0;
        }
        return "''";
    }

    /**
     * List of available collation settings
     * @return string
     */
    public function getDefaultCollation()
    {
        return null;
    }

    /**
     * List of available collation settings
     * @return array
     */
    public function getCollationList()
    {
        return null;
    }

    /**
     * Returns the number of columns in a table
     *
     * @param  string $table_name
     * @return int
     */
    public function number_of_columns($table_name)
    {
        $table = $this->getTableDescription($table_name);
        return count($table);
    }

    /**
     * Return limit query based on given query
     * @param string $sql
     * @param int $start
     * @param int $count
     * @param bool $dieOnError
     * @param string $msg
     * @see DBManager::limitQuery()
     */
    public function limitQuerySql($sql, $start, $count, $dieOnError=false, $msg='')
    {
        return $this->limitQuery($sql,$start,$count,$dieOnError,$msg,false);
    }

    /**
     * Return current time in format fit for insertion into DB
     */
    public function now()
    {
        return $this->convert($this->quoted(TimeDate::getInstance()->nowDb()), "datetime");
    }

    /**
     * Generate fulltext query from set of terms
     * @param string $fields Field to search against
     * @param array $terms Search terms that may be or not be in the result
     * @param array $must_terms Search terms that have to be in the result
     * @param array $exclude_terms Search terms that have to be not in the result
     */
    public function getFulltextQuery($field, $terms, $must_terms = array(), $exclude_terms = array())
    {
        return "0=1"; // by default we don't have fulltext search
    }

    /**
     * Get database configuration information (DB-dependent)
     * @return array|null
     */
    public function getDbInfo()
    {
        return null;
    }

    /**
     * Check if connecting user has certain privilege
     * @param string $privilege
     */
    public function checkPrivilege($privilege)
    {
        switch($privilege) {
            case "CREATE TABLE":
                $this->query("CREATE TABLE temp (id varchar(36))");
                break;
            case "DROP TABLE":
                $sql = $this->dropTableNameSQL("temp");
                $this->query($sql);
                break;
            case "INSERT":
                $this->query("INSERT INTO temp (id) VALUES ('abcdef0123456789abcdef0123456789abcd')");
                break;
            case "UPDATE":
                $this->query("UPDATE temp SET id = '100000000000000000000000000000000000' WHERE id = 'abcdef0123456789abcdef0123456789abcd'");
                break;
            case 'SELECT':
                return $this->getOne('SELECT id FROM temp WHERE id=\'100000000000000000000000000000000000\'', false);
            case 'DELETE':
                $this->query("DELETE FROM temp WHERE id = '100000000000000000000000000000000000'");
                break;
            case "ADD COLUMN":
                $test = array("test" => array("name" => "test", "type" => "varchar", "len" => 50));
                $sql = 	$this->changeColumnSQL("temp", $test, "add");
                $this->query($sql);
                break;
            case "CHANGE COLUMN":
                $test = array("test" => array("name" => "test", "type" => "varchar", "len" => 100));
                $sql = 	$this->changeColumnSQL("temp", $test, "modify");
                $this->query($sql);
                break;
            case "DROP COLUMN":
                $test = array("test" => array("name" => "test", "type" => "varchar", "len" => 100));
                $sql = 	$this->changeColumnSQL("temp", $test, "drop");
                $this->query($sql);
                break;
            default:
                return false;
        }
        if($this->checkError()) {
    	    return false;
	    }
        return true;
    }

    /**
     * Check if the query is a select query
     * @param string $query
     */
	protected function isSelect($query)
	{
	    $query = trim($query);
		$select_check = strpos(strtolower($query), strtolower("SELECT"));
    	//Checks to see if there is union select which is valid
		$select_check2 = strpos(strtolower($query), strtolower("(SELECT"));
		if($select_check==0 || $select_check2==0){
			//Returning false means query is ok!
			return true;
		}
		return false;
	}

	/**
	 * Parse fulltext serahc query with mysql syntax:
	 *  terms quoted by ""
	 *  + means the term must be included
	 *  - means the term must be excluded
	 *  * or % at the end means wildcard
	 * @param string $query
	 * @return array of 3 elements - query terms, mandatory terms and excluded terms
	 */
	public function parseFulltextQuery($query)
	{
	    /* split on space or comma, double quotes with \ for escape */
        if(!preg_match_all('/([^" ,]+|".*?[^\\\\]")(,|\s)\s*/', $query, $m)) {
            return false;
        }
        $terms = $must_terms = $not_terms = array();
        foreach($m[1] as $item) {
            if($item[0] == '"') {
                $item = trim($item, '"');
            }
            if($item[0] == '+') {
                $must_terms[] = substr($item, 1);
                continue;
            }
            if($item[0] == '-') {
                $not_terms[] = substr($item, 1);
                continue;
            }
            $terms[] = $item;
        }
        return array($terms, $must_terms, $not_terms);
	}

    protected $standardQueries = array(
        'ALTER TABLE' => 'verifyAlterTable',
        'DROP TABLE' => 'verifyDropTable',
        'CREATE TABLE' => 'verifyCreateTable',
        'INSERT INTO' => 'verifyInsertInto',
        'UPDATE' => 'verifyUpdate',
        'DELETE FROM' => 'verifyDeleteFrom',
    );


    protected function extractTableName($query)
    {
       $query = preg_replace('/[^A-Za-z0-9_\s]/', "", $query);
       $query = trim(str_replace(array_keys($this->standardQueries), '', $query));

       $firstSpc = strpos($query, " ");
       $end = ($firstSpc > 0) ? $firstSpc : strlen($query);
       $table = substr($query, 0, $end);

       return $table;
    }

    /**
     * Verify SQl statement using per-DB verification function
     * provided the function exists
     * @param  $query string
     * @return string
     */
    public function verifySQLStatement($query, $skipTables)
    {
        $query = trim($query);
        foreach($this->standardQueries as $qstart => $check) {
            if(strncasecmp($qstart, $query, strlen($qstart)) == 0) {
                if(is_callable(array($this, $check))) {
                    $table = $this->extractTableName($query);
                    if(!in_array($table, $skipTables)) {
                        return call_user_func(array($this, $check), $table, $query);
                    } else {
                        $this->log->debug("Skipping table $table as blacklisted");
                    }
                } else {
                    $this->log->debug("No verification for $qstart on {$this->dbType}");
                }
                break;
            }
        }
        return "";
    }

    /**
     * Tests an CREATE TABLE query
     * @param string table The table name to get DDL
     * @param string query The query to test.
     * @return string Non-empty if error found
     */
    protected function verifyCreateTable($table, $query)
    {
    	$this->log->debug('verifying CREATE statement...');

		// rewrite DDL with _temp name
		$this->log->debug('testing query: ['.$query.']');
        $tempname = $table."__uw_temp";
		$tempTableQuery = str_replace("CREATE TABLE {$table}", "CREATE TABLE $tempname", $query);

        if(strpos($tempTableQuery, '__uw_temp') === false) {
            return 'Could not use a temp table to test query!';
        }

		$this->query($tempTableQuery, false, "Preflight Failed for: {$query}");

		$error = $this->lastError(); // empty on no-errors
		if(!empty($error)) {
            return $error;
		}

		// check if table exists
		$this->log->debug('testing for table: '.$table);
        if(!$this->tableExists($tempname)) {
            return "Failed to create temp table!";
		}

        $this->dropTableName($tempname);
        return '';
    }

    /**
     * Get DB driver name used for install/upgrade scripts
     * @return string
     */
    public function getScriptName()
    {
        return $this->dbType;
    }

    /**
     * Get tables like expression
     * @param $like string
     * @return array
     */
    public function tablesLike($like)
    {
        return false;
    }


    /**
     * Parses and runs queries
     *
     * @param  string   $sql        SQL Statement to execute
     * @param  bool     $dieOnError True if we want to call die if the query returns errors
     * @param  string   $msg        Message to log if error occurs
     * @param  bool     $suppress   Flag to suppress all error output unless in debug logging mode.
     * @return resource result set
     */
    abstract public function query($sql, $dieOnError = false, $msg = '', $suppress = false, $keepResult = false);

    /**
     * Runs a limit query: one where we specify where to start getting records and how many to get
     *
     * @param  string   $sql
     * @param  int      $start
     * @param  int      $count
     * @param  boolean  $dieOnError
     * @param  string   $msg
     * @return resource query result
     */
    abstract function limitQuery($sql, $start, $count, $dieOnError = false, $msg = '', $execute = true);


    /**
     * Free Database result
     * @param resource $dbResult
     */
    abstract protected function freeDbResult($dbResult);

    /**
     * Rename column in the DB
     * @param string $tablename
     * @param string $column
     * @param string $newname
     */
    abstract function renameColumnSQL($tablename, $column, $newname);

    /**
     * Returns definitions of all indies for passed table.
     *
     * return will is a multi-dimensional array that
     * categorizes the index definition by types, unique, primary and index.
     * <code>
     * <?php
     * array(
     *       'index1'=> array (
     *           'name'   => 'index1',
     *           'type'   => 'primary',
     *           'fields' => array('field1','field2')
     *           )
     *       )
     * ?>
     * </code>
     * This format is similar to how indicies are defined in vardef file.
     *
     * @param  string $tablename
     * @return array
     */
    abstract public function get_indices($tablename);

    /**
     * Returns definitions of all indies for passed table.
     *
     * return will is a multi-dimensional array that
     * categorizes the index definition by types, unique, primary and index.
     * <code>
     * <?php
     * array(
     *       'field1'=> array (
     *           'name'   => 'field1',
     *           'type'   => 'varchar',
     *           'len' => '200'
     *           )
     *       )
     * ?>
     * </code>
     * This format is similar to how indicies are defined in vardef file.
     *
     * @param  string $tablename
     * @return array
     */
    abstract public function get_columns($tablename);

    /**
     * Generates alter constraint statement given a table name and vardef definition.
     *
     * Supports both adding and droping a constraint.
     *
     * @param  string $table     tablename
     * @param  array  $defintion field definition
     * @param  bool   $drop      true if we are dropping the constraint, false if we are adding it
     * @return string SQL statement
     */
    abstract public function add_drop_constraint($table, $definition, $drop = false);

    /**
     * Returns the description of fields based on the result
     *
     * @param  resource $result
     * @param  boolean  $make_lower_case
     * @return array field array
     */
    abstract public function getFieldsArray($result, $make_lower_case = false);

    /**
     * Returns an array of tables for this database
     *
     * @return	$tables		an array of with table names
     * @return	false		if no tables found
     */
    abstract public function getTablesArray();

    /**
     * Return's the version of the database
     *
     * @return string
     */
    abstract public function version();

    /**
     * Checks if a table with the name $tableName exists
     * and returns true if it does or false otherwise
     *
     * @param  string $tableName
     * @return bool
     */
    abstract public function tableExists($tableName);

    /**
     * Fetches the next row in the query result into an associative array
     *
     * @param  resource $result
     * @param  int      $rowNum optional, specify a certain row to return
     * @param  bool     $encode optional, true if we want html encode the resulting array
     * @return array    returns false if there are no more rows available to fetch
     */
    abstract public function fetchByAssoc($result, $rowNum = -1, $encode = true);

    /**
     * Connects to the database backend
     *
     * Takes in the database settings and opens a database connection based on those
     * will open either a persistent or non-persistent connection.
     * If a persistent connection is desired but not available it will defualt to non-persistent
     *
     * configOptions must include
     * db_host_name - server ip
     * db_user_name - database user name
     * db_password - database password
     *
     * @param array   $configOptions
     * @param boolean $dieOnError
     */
    abstract public function connect(array $configOptions = null, $dieOnError = false);

    /**
	 * Generates sql for create table statement for a bean.
	 *
	 * @param  string $tablename
	 * @param  array  $fieldDefs
     * @param  array  $indices
     * @param  string $engine
     * @return string SQL Create Table statement
	 */
	abstract public function createTableSQLParams($tablename, $fieldDefs, $indices);

	/**
     * Generates the SQL for changing columns
     *
     * @param string $tablename
     * @param array  $fieldDefs
     * @param string $action
     * @param bool   $ignoreRequired Optional, true if we should ignor this being a required field
     * @return string
	 */
	abstract protected function changeColumnSQL($tablename, $fieldDefs, $action, $ignoreRequired = false);

    /**
     * Disconnects from the database
     *
     * Also handles any cleanup needed
     */
    abstract public function disconnect();

    /**
     * Get last database error
     * @return string
     */
    abstract public function lastError();

    /**
     * Check if this query is valid
     * Validates only SELECT queries
     * @return bool
     */
    abstract public function validateQuery($query);

    /**
     * Check if certain database exists
     * @param string $dbname
     */
    abstract public function dbExists($dbname);

    /**
     * Create a database
     * @param string $dbname
     */
    abstract public function createDatabase($dbname);

    /**
     * Drop a database
     * @param string $dbname
     */
    abstract public function dropDatabase($dbname);

    /**
     * Check if certain DB user exists
     * @param string $username
     */
    abstract public function userExists($username);

    /**
     * Create DB user
     * @param string $database_name
     * @param string $host_name
     * @param string $user
     * @param string $password
     */
    abstract public function createDbUser($database_name, $host_name, $user, $password);
}