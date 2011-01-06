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
* $Id: MysqliManager.php 16822 2006-09-26 17:37:32Z ajay $
* Description: This file handles the Data base functionality for the application.
* It acts as the DB abstraction layer for the application. It depends on helper classes
* which generate the necessary SQL. This sql is then passed to PEAR DB classes.
* The helper class is chosen in DBManagerFactory, which is driven by 'db_type' in 'dbconfig' under config.php.
*
* All the functions in this class will work with any bean which implements the meta interface.
* The passed bean is passed to helper class which uses these functions to generate correct sql.
*
* The meta interface has the following functions:
* getTableName()                Returns table name of the object.
* getFieldDefinitions()         Returns a collection of field definitions in order.
* getFieldDefintion(name)       Return field definition for the field.
* getFieldValue(name)           Returns the value of the field identified by name.
*                               If the field is not set, the function will return boolean FALSE.
* getPrimaryFieldDefinition()   Returns the field definition for primary key
*
* The field definition is an array with the following keys:
*
* name      This represents name of the field. This is a required field.
* type      This represents type of the field. This is a required field and valid values are:
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

require_once('include/database/MysqlManager.php');

class MysqliManager extends MysqlManager
{
    /**
     * @see DBManager::$dbType
     */
    public $dbType = 'mysql';

    /**
     * @see DBManager::$backendFunctions
     */
    protected $backendFunctions = array(
        'free_result'        => 'mysqli_free_result',
        'close'              => 'mysqli_close',
        'row_count'          => 'mysqli_num_rows',
        'affected_row_count' => 'mysqli_affected_rows',
        );

    /**
     * @see DBManager::checkError()
     */
    public function checkError(
        $msg = '',
        $dieOnError = false
        )
    {
        if (DBManager::checkError($msg, $dieOnError))
            return true;

        $userMsg = inDeveloperMode()?"$msg: ":"";

        if (mysqli_errno($this->getDatabase())){
            if($this->dieOnError || $dieOnError){
                $GLOBALS['log']->fatal("$msg: MySQL error ".mysqli_errno($this->database).": ".mysqli_error($this->database));
                sugar_die ($userMsg.$GLOBALS['app_strings']['ERR_DB_FAIL']);
            }
            else{
                $this->last_error = $userMsg."MySQL error ".mysqli_errno($this->database).": ".mysqli_error($this->database);
                $GLOBALS['log']->error("$msg: MySQL error ".mysqli_errno($this->database).": ".mysqli_error($this->database));

            }
            return true;
        }
        return false;
    }

    /**
     * @see MysqlManager::query()
     */
    public function query(
        $sql,
        $dieOnError = false,
        $msg = '',
        $suppress = false,
        $autofree = false
        )
    {
        static $queryMD5 = array();
		//BEGIN SUGARCRM flav=pro ONLY
        $this->addDistinctClause($sql);
		//END SUGARCRM flav=pro ONLY

        parent::countQuery($sql);
        $GLOBALS['log']->info('Query:' . $sql);
        $this->checkConnection();
        //$this->freeResult();
        $this->query_time = microtime(true);
        $this->lastsql = $sql;
        if ($suppress==true){
            //BEGIN SUGARCRM flav=ent ONLY
            //suppress flag is when you are using CSQL and make a bad query.
            //We don't want any php errors to appear
            $orig_level = error_reporting();
            error_reporting(0);
            $result = mysqli_query($this->database,$sql);
            error_reporting($orig_level);
            //END SUGARCRM flav=ent ONLY
        }
        else {
            $result = mysqli_query($this->database,$sql);
        }
        $md5 = md5($sql);

        if (empty($queryMD5[$md5]))
        	$queryMD5[$md5] = true;

        $this->lastmysqlrow = -1;
        $this->query_time = microtime(true) - $this->query_time;
        $GLOBALS['log']->info('Query Execution Time:'.$this->query_time);

        //BEGIN SUGARCRM flav=pro ONLY
        if($this->dump_slow_queries($sql)) {
		   $this->track_slow_queries($sql);
		}
		//END SUGARCRM flav=pro ONLY

		$this->checkError($msg.' Query Failed: ' . $sql, $dieOnError);
        if($autofree)
            $this->lastResult[] =& $result;

        return $result;
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

        if (!isset($result) || empty($result))
            return 0;

        $i = 0;
        while ($i < mysqli_num_fields($result)) {
            $meta = mysqli_fetch_field_direct($result, $i);
            if (!$meta)
                return 0;

            if($make_lower_case == true)
                $meta->name = strtolower($meta->name);

            $field_array[] = $meta->name;

            $i++;
        }

        return $field_array;
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

        if ($result && $rowNum > -1) {
            if ($this->getRowCount($result) > $rowNum)
                mysqli_data_seek($result, $rowNum);
            $this->lastmysqlrow = $rowNum;
        }

        $row = mysqli_fetch_assoc($result);

        if ($encode && $this->encode && is_array($row))
            return array_map('to_html', $row);

        return $row;
    }

    /**
     * @see DBManager::quote()
     */
    public function quote(
        $string,
        $isLike = true
        )
    {
        return mysqli_escape_string($this->getDatabase(),DBManager::quote($string));
    }

    /**
     * @see DBManager::quoteForEmail()
     */
    public function quoteForEmail(
        $string,
        $isLike = true
        )
    {
        return mysqli_escape_string($this->getDatabase(),$string);
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

        if (is_null($configOptions))
            $configOptions = $sugar_config['dbconfig'];

        if(!isset($this->database)) {

   	        //mysqli connector has a separate parameter for port.. We need to separate it out from the host name
			$dbhost=$configOptions['db_host_name'];
	        $dbport=null;
	        $pos=strpos($configOptions['db_host_name'],':');
	        if ($pos !== false) {
	        	$dbhost=substr($configOptions['db_host_name'],0,$pos);
	        	$dbport=substr($configOptions['db_host_name'],$pos+1);
	        }

        	$this->database = mysqli_connect($dbhost,$configOptions['db_user_name'],$configOptions['db_password'],$configOptions['db_name'],$dbport);
        	if(empty($this->database)) {
        	    $GLOBALS['log']->fatal("Could not connect to DB server ".$dbhost." as ".$configOptions['db_user_name'].". port " .$dbport . ": " . mysqli_connect_error());
                sugar_die($GLOBALS['app_strings']['ERR_NO_DB']);
        	}
        }
        if(!@mysqli_select_db($this->database,$configOptions['db_name'])) {
            $GLOBALS['log']->fatal( "Unable to select database {$configOptions['db_name']}: " . mysqli_connect_error());
            sugar_die($GLOBALS['app_strings']['ERR_NO_DB']);
        }

        // cn: using direct calls to prevent this from spamming the Logs
        mysqli_query($this->database,"SET CHARACTER SET utf8"); // no quotes around "[charset]"
        mysqli_query($this->database,"SET NAMES 'utf8'");

        if($this->checkError('Could Not Connect', $dieOnError))
            $GLOBALS['log']->info("connected to db");
    }
}

?>
