<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


/*********************************************************************************

 * Description: This file handles the Data base functionality for prepared Statements
 * It acts as the prepared statement abstraction layer for the application.
 *
 * All the functions in this class will work with any bean which implements the meta interface.
 * The passed bean is passed to helper class which uses these functions to generate correct sql.
 *
 * The meta interface has the following functions:
 */
require_once 'include/database/PreparedStatement.php';

class SqlsrvPreparedStatement extends PreparedStatement
{

    /**
     * Place to bind query vars to
     * @var array
     *
     * Special handling is required for the following types which are functions not constants
     *     binary(byte count)
     *     char(char count)
     *     decimal (precision,scale)
     *     nchar(char count)
     *     numeric(precision, scale)
     *     nvarchar(char count)
     *     varbinary(byte count)
     */
    protected $bound_vars = array();

    /* SQLSRV types:

        SQLSRV_SQLTYPE_BIGINT (integer)
        SQLSRV_SQLTYPE_BINARY (integer)
        SQLSRV_SQLTYPE_BIT (integer)
        SQLSRV_SQLTYPE_CHAR (integer)
        SQLSRV_SQLTYPE_DATE (integer)
        SQLSRV_SQLTYPE_DATETIME (integer)
        SQLSRV_SQLTYPE_DATETIME2 (integer)
        SQLSRV_SQLTYPE_DATETIMEOFFSET (integer)
        SQLSRV_SQLTYPE_DECIMAL (integer)
        SQLSRV_SQLTYPE_FLOAT (integer)
        SQLSRV_SQLTYPE_IMAGE (integer)
        SQLSRV_SQLTYPE_INT (integer)
        SQLSRV_SQLTYPE_MONEY (integer)
        SQLSRV_SQLTYPE_NCHAR (integer)
        SQLSRV_SQLTYPE_NUMERIC (integer)
        SQLSRV_SQLTYPE_NVARCHAR (integer)
        SQLSRV_SQLTYPE_NVARCHAR('max') (integer)
        SQLSRV_SQLTYPE_NTEXT (integer)
        SQLSRV_SQLTYPE_REAL (integer)
        SQLSRV_SQLTYPE_SMALLDATETIME (integer)
        SQLSRV_SQLTYPE_SMALLINT (integer)
        SQLSRV_SQLTYPE_SMALLMONEY (integer)
        SQLSRV_SQLTYPE_TEXT (integer)
        SQLSRV_SQLTYPE_TIME (integer)
        SQLSRV_SQLTYPE_TIMESTAMP (integer)
        SQLSRV_SQLTYPE_TINYINT (integer)
        SQLSRV_SQLTYPE_UNIQUEIDENTIFIER (integer)
        SQLSRV_SQLTYPE_UDT (integer)
        SQLSRV_SQLTYPE_VARBINARY (integer)
        SQLSRV_SQLTYPE_VARBINARY('max') (integer)
        SQLSRV_SQLTYPE_VARCHAR (integer)
        SQLSRV_SQLTYPE_VARCHAR('max') (integer)
        SQLSRV_SQLTYPE_XML (integer)

    */

    public $ps_type_map = array(
        'int'           =>  SQLSRV_SQLTYPE_INT,
        'double'        =>  SQLSRV_SQLTYPE_FLOAT,
        'float'         =>  SQLSRV_SQLTYPE_FLOAT,
        'uint'          =>  SQLSRV_SQLTYPE_INT,
        'ulong'         =>  SQLSRV_SQLTYPE_INT,
        'long'          =>  SQLSRV_SQLTYPE_INT,
        'short'         =>  SQLSRV_SQLTYPE_INT,
        'varchar'       =>  'SQLSRV_SQLTYPE_CHAR',
        'text'          =>  SQLSRV_SQLTYPE_TEXT,
        'longtext'      =>  SQLSRV_SQLTYPE_TEXT,
        'date'          =>  SQLSRV_SQLTYPE_DATE,
        'enum'          =>  'SQLSRV_SQLTYPE_CHAR',
        'relate'        =>  'SQLSRV_SQLTYPE_CHAR',
        'multienum'     =>  'SQLSRV_SQLTYPE_CHAR',
        'html'          =>  'SQLSRV_SQLTYPE_CHAR',
        'longhtml'      =>  'SQLSRV_SQLTYPE_CHAR',
        'datetime'      =>  SQLSRV_SQLTYPE_DATETIME,
        'datetimecombo' =>  SQLSRV_SQLTYPE_DATE,
        'time'          =>  SQLSRV_SQLTYPE_TIME,
        'bool'          =>  SQLSRV_SQLTYPE_BIT,
        'tinyint'       =>  SQLSRV_SQLTYPE_TINYINT,
        'char'          =>  'SQLSRV_SQLTYPE_CHAR',
        'blob'          =>  'SQLSRV_SQLTYPE_BINARY',
        'longblob'      =>  'SQLSRV_SQLTYPE_BINARY',
        'currency'      =>  SQLSRV_SQLTYPE_MONEY,
        'decimal'       =>  'SQLSRV_SQLTYPE_DECIMAL',
        'decimal2'      =>  'SQLSRV_SQLTYPE_DECIMAL',
        'id'            =>  'SQLSRV_SQLTYPE_CHAR',
        'url'           =>  'SQLSRV_SQLTYPE_CHAR',
        'encrypt'       =>  'SQLSRV_SQLTYPE_CHAR',
        'file'          =>  'SQLSRV_SQLTYPE_CHAR',
        'decimal_tpl'   =>  'SQLSRV_SQLTYPE_CHAR',

    );



  public function preparePreparedStatementOldSqlsrv($sqlText, array $fieldDefs, $msg = '' ){

      $this->lastsql = $sqlText;
      $GLOBALS['log']->info('QueryPrepare:' . $sqlText);

	  $keylessData = array();
	  foreach($data as &$dataElement) {
          $keylessData[] = $dataElement;
	  }

      if (!($this->stmt = sqlsrv_prepare($this->dblink, $sqlText, $keylessData))) {
          $this->log->error("Prepare failed: $msg for sql: $sqlText (" . $this->dblink->errno . ") " . $this->dblink->error);
          return false;
      }

      /*
      $num_args = $this->stmt->param_count;
      echo "preparePreparedStatement: num_args from prepare: $num_args \n";
      $this->bound_vars = $bound = array_fill(0, $num_args, null);
      $types = "";
      for($i=0; $i<$num_args;$i++) {
          $types .= $this->ps_type_map[ $fieldDefs[$i] ];
          $bound[$i] =& $this->bound_vars[$i];
      }
      echo "types: >$types<\n";
      array_unshift($bound, $types);

      echo "Binding the data: types then vars\n";
      var_dump($bound);
      // Pre-bind the internal data array to    $this->bound_vars
      call_user_func_array(array($this->stmt, "bind_param"), $bound);
      */

      $this->checkError(" QueryPrepare Failed: $msg for sql: $sqlText ::");

      return $this;
  }



    public function preparePreparedStatement($sqlText,  array $fieldDefs, $msg = '' ){

        $this->lastsql = $sqlText;
        $GLOBALS['log']->info('QueryPrepare:' . $sqlText);

        $num_args = count($fieldDefs);
        $this->bound_vars = array_fill(0, $num_args, null);
        $params = array();
        for($i=0; $i<$num_args;$i++) {
            $dbType = trim($fieldDefs[$i]["type"]);
            $sqlsrvType = $this->ps_type_map[ $dbType ];  // SugarType->type_map->ps_type_map

			if (!empty($fieldDefs[$i]["len"]))
				$len = $fieldDefs[$i]["len"];
			else
				$len = 5000;

			switch ($sqlsrvType) {
				case 'SQLSRV_SQLTYPE_BINARY':    // byte count
				    $sqlsrvType = SQLSRV_SQLTYPE_BINARY($len);
					break;
			    case 'SQLSRV_SQLTYPE_DECIMAL':   // precision, scale
				    $sqlsrvType = SQLSRV_SQLTYPE_DECIMAL($len,6);
					break;
				case 'SQLSRV_SQLTYPE_CHAR':      // char count
				    $sqlsrvType = SQLSRV_SQLTYPE_CHAR($len);
					break;
				case 'SQLSRV_SQLTYPE_NCHAR':     // char count
				    $sqlsrvType = SQLSRV_SQLTYPE_NCHAR($len);
					break;
				case 'SQLSRV_SQLTYPE_NUMERIC':   // precision, scale
				    $sqlsrvType = SQLSRV_SQLTYPE_NUMERIC($len,6);
					break;
				case 'SQLSRV_SQLTYPE_NVARCHAR':  // char count
				    $sqlsrvType = SQLSRV_SQLTYPE_NVARCHAR($len);
					break;
				case 'SQLSRV_SQLTYPE_VARBINARY': // byte count
				    $sqlsrvType = SQLSRV_SQLTYPE_VARBINARY($len);
					break;

        }

            $params[] = array( &$this->bound_vars[$i], SQLSRV_PARAM_IN, null, $sqlsrvType );
        }

        if (!($this->stmt = sqlsrv_prepare($this->dblink, $sqlText, $params))) {
            $this->log->error("Prepare failed: $msg for sql: $sqlText (" . $this->dblink->errno . ") " . $this->dblink->error);
            return false;
        }

        $this->DBM->checkError(" QueryPrepare Failed: $msg for sql: $sqlText");

        return $this;
    }




   public function executePreparedStatementOldSqlsrv(array $data, $msg = ''){

       parent::countQuery($this->sqlText);
       $GLOBALS['log']->info('Query:' . $this->sqlText);

       if ($this->stmt->param_count != count($data) )
           return "incorrect number of elements. Expected " . $this->stmt->param_count . " but got " . count($data);

       $this->query_time = microtime(true);

       /*
      if ($this->stmt->param_count != count($data) )
          return "incorrect number of elements. Expected " . $this->stmt->param_count . " but got " . count($data);

      // transfer the data from the input array to the bound array
      for($i=0; $i<count($data);$i++) {
         $this->bound_vars[$i] = $data[$i];
      }
      */

      $res = sqlsrv_execute($this->stmt);


      $this->query_time = microtime(true) - $this->query_time;
      $GLOBALS['log']->info('Query Execution Time:'.$this->query_time);

      if (!$res) {
          $this->log->error("Query Failed: $this->sqlText");
          $this->stmt = false; // Making sure we don't use the statement resource for error reporting
      }
      else {

          if($this->dump_slow_queries($this->sqlText)) {
              $this->track_slow_queries($this->sqlText);
   }
      }
      $this->DBM->checkError($msg.' Query Failed:' . $this->sqlText . '::', $dieOnError);

      return $this->stmt;
   }





    public function executePreparedStatement(array $data, $msg = ''){

        //parent::countQuery($this->sqlText);
        $GLOBALS['log']->info('Query:' . $this->sqlText);

        //if ($this->stmt->param_count != count($data) )
        //    return "incorrect number of elements. Expected " . $this->stmt->param_count . " but got " . count($data);

        $param_count = count($data);
        $this->query_time = microtime(true);

        for($i=0; $i<$param_count;$i++) {
            $this->bound_vars[$i] = array_shift($data);
        }

        $res = sqlsrv_execute($this->stmt);

        $this->query_time = microtime(true) - $this->query_time;
        $GLOBALS['log']->info('Query Execution Time:'.$this->query_time);

        if (!$res) {
            $this->log->error("Query Failed: $this->sqlText");
            $this->stmt = false; // Making sure we don't use the statement resource for error reporting
        }
        else {

            if($this->DBM->dump_slow_queries($this->sqlText)) {
                $this->DBM->track_slow_queries($this->sqlText);
            }
        }
        $this->DBM->checkError($msg.' Query Failed:' . $this->sqlText . '::');

        return $this->stmt;
    }

    public function preparedStatementFetch( $msg = '' ) {

        //return sqlsrv_fetch_object($this->stmt);
        //return sqlsrv_fetch_array($this->stmt);

	    $row = sqlsrv_fetch_array($this->stmt);
        if ( !$row )
            return false;
        $temp = $row;
        $row = array();
        foreach ($temp as $key => $val) {
            // make the column keys as lower case. Trim the val returned
            $row[strtolower($key)] = trim($val);
        }

        return $row;

	}

}
