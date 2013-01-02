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



 /* to summarize what we are trying to do in Oracle:

    $stmt = oci_parse($conn,"INSERT INTO testPreparedStatement (id, col1, col2) VALUES(:p1, :p2, :p3)");
    $bound = array();
    oci_bind_by_name($stmt, ":p1", $bound[1], -1, $dataTypes[1]);
    oci_bind_by_name($stmt, ":p2", $bound[2], -1, $dataTypes[2]);
    oci_bind_by_name($stmt, ":p3", $bound[3], -1, $dataTypes[3]);
    oci_execute($stmt);

  The stages it goes through are:

    DBManager.insertParams
        Table:      testPreparedStatement
        Field_defs: { ['id']   => { ['name'] => 'id',   ['type'] => 'id',      ['required']=>true },
                    { ['col1'] => { ['name'] => 'col1', ['type'] => 'varchar', ['len'] => '100' },
                    { ['col2'] => { ['name'] => 'col2', ['type'] => 'varchar', ['len'] => '100' },
        Data:       { ['id'] => 3, ['col1'] => "col1 data for id 3", ['col2'] => "col2 data for id 3" }
        Field_map:  null

    PreparedStatement.__construct
	SQL:   INSERT INTO testPreparedStatement (id,col1,col2) VALUES (?id,?varchar,?varchar)
        Data:  { ["id"]=> "'3'", ["col1"]=> "'col1 data for id 3'", ["col2"]=> "'col2 data for id 3'" }

    OraclePreparedStatement.preparePreparedStatement
	SQL:   INSERT INTO testPreparedStatement (id,col1,col2) VALUES (?,?,?)
        Data:  { ["id"]=> "'3'", ["col1"]=> "'col1 data for id 3'", ["col2"]=> "'col2 data for id 3'" }
	Types: { [0]=> {["type"]=>"id"}, [1]=>{["type"]=>"varchar"}, [2]=>{["type"]=>"varchar"}

        $stmt = oci_parse($conn,"INSERT INTO testPreparedStatement (id, col1, col2) VALUES(:p1, :p2, :p3)");
        $bound = array();
        oci_bind_by_name($stmt, ":p1", $bound[1], -1, $dataTypes[1]);
        oci_bind_by_name($stmt, ":p2", $bound[2], -1, $dataTypes[2]);
        oci_bind_by_name($stmt, ":p3", $bound[3], -1, $dataTypes[3]);

        Data: $bound = { [1]=> "3", [2]=> "col1 data for id 3", [3]=> "col2 data for id 3" }
        oci_execute($stmt);
 */

require_once 'include/database/PreparedStatement.php';

class OraclePreparedStatement extends PreparedStatement
{

    /**
     * Place to bind query vars to
     * @var array
     */
    protected $bound_vars = array();


    /*
     * Maps column datatypes to MySQL bind variable types
     *
     * Oracle type defs
     *
     *     SQLT_BFILEE or OCI_B_BFILE    - for BFILEs;
     *     SQLT_CFILEE or OCI_B_CFILEE   - for CFILEs;
     *     SQLT_CLOB   or OCI_B_CLOB     - for CLOBs;
     *     SQLT_BLOB   or OCI_B_BLOB     - for BLOBs;
     *     SQLT_RDD    or OCI_B_ROWID    - for ROWIDs;
     *     SQLT_NTY    or OCI_B_NTY      - for named datatypes;
     *     SQLT_INT    or OCI_B_INT      - for integers;
     *     SQLT_CHR                      - for VARCHARs, Converts the PHP parameter to a string type and binds as a string.;
     *     SQLT_LVC                      - Used with oci_bind_array_by_name() to bind arrays of LONG VARCHAR.
     *     SQLT_STR                      - Used with oci_bind_array_by_name() to bind arrays of STRING.
     *     SQLT_BIN    or OCI_B_BIN      - for RAW columns;
     *     SQLT_LNG                      - for LONG columns;
     *     SQLT_ODT                      - for LONG columns;
     *     SQLT_LBI                      - for LONG RAW columns;
     *     SQLT_RSET   or OCI_B_CURSOR   - for cursors created with oci_new_cursor(). Used with oci_bind_by_name() when binding cursors,
     *                                     previously allocated with oci_new_descriptor().
     *     SQLT_NUM    or OCI_B_NUM      - Converts the PHP parameter to a 'C' long type, and binds to that value.
     *                                     Used with oci_bind_array_by_name() to bind arrays of NUMBER.
     *     SQLT_FLT                      - Used with oci_bind_array_by_name() to bind arrays of FLOAT.
     *                    OCI_B_CURSOR   -
     *     SQLT_AFC                      - Used with oci_bind_array_by_name() to bind arrays of CHAR.
     *     SQLT_AVC                      - Used with oci_bind_array_by_name() to bind arrays of VARCHAR2.
     *     SQLT_VCS                      - Used with oci_bind_array_by_name() to bind arrays of VARCHAR.
     */
    protected $ps_type_map = array(
        // Oracle DataType => PHP Bind Variable   Used by Sugar Data Types

        // char types
        'char'             =>  SQLT_CHR,    // char
        'varchar2'         =>  SQLT_CHR,    // varchar, relate, url
        'varchar2(36)'     =>  SQLT_CHR,    // id
        'varchar2(255)'    =>  SQLT_CHR,    // enum, encrypt, file
        'clob'             =>  SQLT_CLOB,   // text, longtext, multienum, html, longhtml
        'blob'             =>  SQLT_BLOB,   // blob, longblob

        // floating point types
        'number(20,2)'     =>  SQLT_FLT,    // decimal
        'number(26,6)'     =>  SQLT_FLT,    // currency
        'number(30,6)'     =>  SQLT_FLT,    // float, decimal2
        'number(38,10)'    =>  SQLT_FLT,    // double
        'number(%d, %d)'   =>  SQLT_FLT,    // decimal_tpl

        // integer types
        'number'           =>  SQLT_INT,    // int
        'number(1)'        =>  SQLT_INT,    // bool
        'number(3)'        =>  SQLT_INT,    // tinyint, short
        'number(15)'       =>  SQLT_INT,    // uint
        'number(38)'       =>  SQLT_INT,    // ulong
        'number(38)'       =>  SQLT_INT,    // long

        // date time types
        'date'             =>  SQLT_CHR,    // date
        'date'             =>  SQLT_CHR,    // datetime, datetimecombo, time
    );


  public function preparePreparedStatement($sqlText, array $fieldDefs,  $msg = '' ){


      $this->lastsql = $sqlText;
      $GLOBALS['log']->info('QueryPrepare:' . $sqlText);

      // Convert ? into :var in prepared statements
      if (!empty($fieldDefs) or (!is_array($fieldDefs))) {

         $cleanedSql = "";
         $fields = array();
         $dataTypes = array();
         $i = 0;
         $nextParam = strpos( $sqlText, "?" );
         if ($nextParam == 0 )
             $cleanedSql = $sqlText;
         else {     // parse the sql string looking for params
             while ($nextParam > 0 ) {
                 $name = "p$i"; // we don't always get fielddefs so we make up our own instead of using $fieldDefs[$i]['name'];
                 $thisType = trim($fieldDefs[$i]["type"]);
                 $mappedType = $this->DBM->type_map[$thisType];

                 $dataType = $this->ps_type_map[$mappedType ];
                 $cleanedSql .= substr( $sqlText, 0, $nextParam ) . ":$name";

                 // insert the fieldDef and type
                 $fields[] = $name;
                 $dataTypes[] = $dataType;

                 $sqlText = substr($sqlText, $nextParam+1); // strip off the ?
                 $nextParam = strpos( $sqlText, "?" ); // look for another param
                 $i++;
              }
          }

          // add the remaining sql
          $cleanedSql .= $sqlText;

      }
      else {
         $this->log->error("ERROR Prepared Statements without field definitions not yet supported.");
         return false;
      }

      $sqlText = $cleanedSql;

      // do the prepare
      if (!($this->stmt = oci_parse($this->dblink, $sqlText))) {
          $this->log->error("preparePreparedStatement: Prepare failed: $msg for sql: $sqlText (" . $this->dblink->errno . ") " . $this->dblink->error);
          return false;
      }

      // bind the array elements
      $num_args = count($fieldDefs);

      $this->bound_vars = array();
      $this->bound_vars = array_fill(0, $num_args, null);

      foreach($this->bound_vars as $statement_bind => $variable_bind) {
          $oraBvName = ":p" . $statement_bind;
			if (!empty($fieldDefs[$statement_bind]["len"]))
				$len = $fieldDefs[$statement_bind]["len"];
			else
				$len = 5000;

          if(!oci_bind_by_name($this->stmt, $oraBvName, $this->bound_vars[$statement_bind], $len, $dataTypes[$statement_bind])) {
              $this->log->error("preparePreparedStatement: Bind failed: $msg for sql: $sqlText for param $bindvars[$statement_bind] "
                                . " as type $dataTypes[$statement_bind]  lenL $len" . $this->dblink->errno . " " . $this->dblink->error);
      }
      }

      $this->DBM->checkError(" QueryPrepare Failed: $msg for sql: $sqlText ::");

      return $this;
  }




   public function executePreparedStatement(array $data,  $msg = ''){

      //parent::countQuery($this->sqlText);
      $GLOBALS['log']->info('Query:' . $this->sqlText);
      $this->DBM->query_time = microtime(true);

      // transfer the data from the input array to the bound array
      for($i=0; $i<count($data);$i++) {
          $this->bound_vars[$i] = array_shift($data);
      }

      $res = oci_execute($this->stmt, OCI_DEFAULT);

      $this->DBM->query_time = microtime(true) - $this->DBM->query_time;
      $GLOBALS['log']->info('Query Execution Time:'.$this->DBM->query_time);

      if (!$res) {
          $this->log->error("Query Failed: $this->sqlText");           $this->stmt = false; // Making sure we don't use the statement resource for error reporting
      }
      else {

          if($this->DBM->dump_slow_queries($this->sqlText)) {
              $this->DBM->track_slow_queries($this->sqlText);
          }
      }
      $this->DBM->checkError($msg.' Query Failed:' . $this->sqlText);


      return $this->stmt;
   }

    public function preparedStatementFetch( $msg = '' ) {

        //return = oci_fetch_assoc($this->stmt);

        $row = oci_fetch_array($this->stmt, OCI_ASSOC|OCI_RETURN_NULLS|OCI_RETURN_LOBS);
        if ( !$row )
            return false;
        if (!$this->DBM->checkError("Fetch error", false, $this->stmt)) {
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
}
