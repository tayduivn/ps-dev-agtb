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

class MysqliPreparedStatement extends PreparedStatement
{

    /**
     * Place to bind query vars to
     * @var array
     */
    protected $bound_vars = array();

    /**
     * Place to bind query output vars to
     * @var array
     */
    protected $output_vars = array();

    /*
     * Maps MySQL column datatypes to MySQL bind variable types
     *
     * Possible types are:
     *   b - blob
     *   d - double
     *   i - integer
     *   s - string
     *
     */
    protected $ps_type_map = array(
        // Sugar DataType      PHP Bind Variable data type

        // char types
        'char'             => 's', // char
        'char(36)'         => 's', // id
        'varchar'          => 's', // varchar, enum, relate, url, encrypt, file
        'text'             => 's', // text, multienum, html,
        'longtext'         => 's', // longtext, longhtml
        'blob'             => 'b', // blob
        'longblob'         => 'b', // longblob

        // floating point types
        'double'           => 'd', // double
        'float'            => 'd', // float
        'decimal(26,6)'    => 'd', // currency
        'decimal'          => 'd', // decimal, decimal2
        'decimal(%d, %d)'  => 'd', // decimal_tpl

        // integer types
        'bool'             => 'i', // bool
        'tinyint'          => 'i', // tinyint
        'smallint'         => 'i', // short
        'int'              => 'i', // int
        'int unsigned'     => 'i', // uint
        'bigint'           => 'i', // long
        'bigint unsigned'  => 'i', // ulong

        // date time types
        'time'             => 's', // time
        'date'             => 's', // date
        'datetime'         => 's', // datetime, datetimecombo

    );



  /**
   * Tracks slow queries in the tracker database table
   *
   * @param resource $dblink   database resource to use
   * @param string   $sqlText  the sql statement to prepare
   * @param array    $data     1D array of data to match the positional params
   * @param array    fieldDefs field definitions
   *
   */
  public function preparePreparedStatement($sqlText,  array $fieldDefs, $msg = '' ){

      $this->lastsql = $sqlText;
      $GLOBALS['log']->info('QueryPrepare:' . $sqlText);

      if (!($this->stmt = $this->dblink->prepare($sqlText))) {
          $this->log->error("Prepare failed: $msg for sql: $sqlText (" . $this->dblink->errno . ") " . $this->dblink->error);
          return false;
      }
      $num_args = $this->stmt->param_count;
      $this->bound_vars = $bound = array_fill(0, $num_args, null);
      $types = "";
      for($i=0; $i<$num_args;$i++) {
          $thisType = trim($fieldDefs[$i]["type"]);
          $mappedType = $this->DBM->type_map[$thisType];
          $types .= $this->ps_type_map[ $mappedType ];  // SugarType->type_map->ps_type_map
          $bound[$i] =& $this->bound_vars[$i];
      }
      array_unshift($bound, $types);    // puts $types in front of the data elements

      call_user_func_array(array($this->stmt, "bind_param"), $bound);

      $this->DBM->checkError(" QueryPrepare Failed: $msg for sql: $sqlText ::");

      return $this;
  }




   public function executePreparedStatement(array $data, $msg = ''){

      //parent::countQuery($this->sqlText);
      $GLOBALS['log']->info('Query:' . $this->sqlText);

      if ($this->stmt->param_count != count($data) )
          return "incorrect number of elements. Expected " . $this->stmt->param_count . " but got " . count($data);

      $this->query_time = microtime(true);

      for($i=0; $i<$this->stmt->param_count;$i++) {
         $this->bound_vars[$i] = array_shift($data);
      }

      $this->preparedStatementResult = null;
      $res = $this->stmt->execute();

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

       // first time, create an array of column names from the returned data set
       if (empty($this->preparedStatementResult)) {

          $fieldCount = $this->stmt->field_count;

          $returnVars = array();

          $statement='';
          $this->preparedStatmentResult = $this->stmt->result_metadata();
          if (is_object($this->preparedStatmentResult))  {
              $fields = $this->preparedStatmentResult->fetch_fields();
              foreach($fields as $field) {
                  $returnVars[]['name'] = $field->name;
                  if(empty($statement)){
                      $statement.="\$out_vars['".$field->name."']";
                  }else{
                      $statement.=", \$out_vars['".$field->name."']";
                  }
              }
              $statement="\$this->stmt->bind_result($statement);";

          }

           $out_vars = array(); //array_fill(0, $fieldCount, null);
           eval($statement);

       }


       // Get the next results
       $this->stmt->fetch();

       return $out_vars;
   }
}
