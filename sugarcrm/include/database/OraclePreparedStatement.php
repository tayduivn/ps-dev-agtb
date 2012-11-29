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

    // Oracle type defs

    //     SQLT_BFILEE or OCI_B_BFILE - for BFILEs;
    //     SQLT_CFILEE or OCI_B_CFILEE - for CFILEs;
    //     SQLT_CLOB   or OCI_B_CLOB - for CLOBs;
    //     SQLT_BLOB   or OCI_B_BLOB - for BLOBs;
    //     SQLT_RDD    or OCI_B_ROWID - for ROWIDs;
    //     SQLT_NTY    or OCI_B_NTY - for named datatypes;
    //     SQLT_INT    or OCI_B_INT - for integers;
    //     SQLT_CHR - for VARCHARs;
    //     SQLT_BIN    or OCI_B_BIN - for RAW columns;
    //     SQLT_LNG - for LONG columns;
    //     SQLT_LBI - for LONG RAW columns;
    //     SQLT_RSET - for cursors created with oci_new_cursor().



    public $ps_type_map = array(
        'int'           => SQLT_INT,
        'double'        => SQLT_INT,
        'float'         => SQLT_FLT,
        'uint'          => SQLT_INT,
        'ulong'         => SQLT_LNG,
        'long'          => SQLT_LNG,
        'short'         => SQLT_INT,
        'varchar'       => SQLT_CHR,
        'text'          => OCI_B_CLOB,
        'longtext'      => OCI_B_CLOB,
        'date'          => SQLT_CHR,
        'enum'          => SQLT_CHR,
        'relate'        => SQLT_CHR,
        'multienum'     => OCI_B_CLOB,
        'html'          => OCI_B_CLOB,
        'longhtml'      => OCI_B_CLOB,
        'datetime'      => SQLT_CHR,
        'datetimecombo' => SQLT_CHR,
        'time'          => SQLT_CHR,
        'bool'          => SQLT_BIN,
        'tinyint'       => SQLT_INT,
        'char'          => SQLT_CHR,
        'blob'          => OCI_B_BLOB,
        'longblob'      => OCI_B_BLOB,
        'currency'      => SQLT_NUM,
        'decimal'       => SQLT_NUM,
        'decimal2'      => SQLT_NUM,
        'id'            => SQLT_CHR,
        'url'           => SQLT_CHR,
        'encrypt'       => SQLT_CHR,
        'file'          => SQLT_CHR,
        'decimal_tpl'   => SQLT_CHR,
    );



  public function preparePreparedStatement($sql, array $data, array $fieldDefs = array() ){


      echo "\n\n---------------------------------------------\n";

      echo "==> OraclePreparedStatement.preparePreparedStatement: entry  sqlText: >$sql <  \ndata:\n" ;
      var_dump($data);
      echo "OraclePreparedStatement.preparePreparedStatement: fileddefs:\n";
      var_dump($fieldDefs);


      // Convert ? into :var in prepared statements
      if (!empty($fieldDefs) or (!is_array($fieldDefs))) {
         //for ($i=0; $i<count($data); $i++) {
         //    $bindVar = $fieldDefs[$i];
         //    $data
         //}

         $cleanedSql = "";
         $fields = array();
         $dataTypes = array();
         $i = 0;
         $nextParam = strpos( $sql, "?" );
         if ($nextParam == 0 )
             $cleanedSql = $sql;
         else {     // parse the sql string looking for params
             while ($nextParam > 0 ) {
                 $name = "p$i"; // we don't always get fielddefs so we make up our own instead of using $fieldDefs[$i]['name'];
                 $type = $fieldDefs[$i]['type'];
                 $dataType = $this->ps_type_map["$type"];
                 echo "Processing param $i Name: $name   type:$type   dataType: $dataType\n" ;
                 $cleanedSql .= substr( $sql, 0, $nextParam ) . ":$name";
                 echo "cleanedSql: $cleanedSql\n";

                 // insert the fieldDef and type
                 $fields[] = $name;
                 $dataTypes[] = $dataType;

                 //echo "sugarDataType:\n";
                 //var_dump($sugarDataType);
                 //if ( $sugarDataType === "" ) //no type, default to varchar
                 //    $dataTypes[] = SQLT_CHR;
                 //else
                 //    $dataTypes[] = $sugarDataType;
                 $sql = substr($sql, $nextParam+1); // strip off the ?
                 echo "remaining sql is: $sql\n";
                 $nextParam = strpos( $sql, "?" ); // look for another param
                 echo "another nextParam is at $nextParam\n";
                 $i++;
              }
          }

          // add the remaining sql
          $cleanedSql .= $sql;

          echo "finished building sql: $cleanedSql \n";
          var_dump($dataTypes);

      }
      else {
         $errorMsg ="ERROR Prepared Statements without field definitions not yet supported.";
         echo "$errorMsg \n";
         return $errorMsg;
      }

      $sqlText = $cleanedSql;
      echo "\n\n\npreparePreparedStatement: oci_parse call for oracle converted sqlText: >$sqlText <  \n" ;


      // do the parse
      echo "\n\n\nOraclePreparedStatment.preparePreparedStatement: oci_parse call for oracle converted sqlText: >$sqlText <  \n" ;
      if (!($this->stmt = oci_parse($this->dblink, $sqlText))) {
          echo "preparePreparedStatement: Prepare Failed! \n";
          return "Prepare failed: (" . $this->dblink->errno . ") " . $this->dblink->error;
      }

      // bind the array elements
      $num_args = count($data);
      echo "OraclePreparedStatment.preparePreparedStatement: binding $num_args arguments \n";
      $this->bound_vars = $bound = array_fill(0, $num_args, null);
      $types = "";
      for($i=0; $i<$num_args;$i++) {
          $bound[$i] =& $this->bound_vars[$i];
          $dataTypes[$i] = SQLT_CHR;
          $fieldName = "bound[" . $i . "]";
          echo "binding $fields[$i] to $fieldName, Type: $dataTypes[$i]  \n";
          oci_bind_by_name($this->stmt, $fields[$i], $fieldName, $dataTypes[$i]);   // $bound[$i]
      }

      return $this;
  }




   public function executePreparedStatement($data){

      echo "--------------------------------------------------\n";
      echo "==> OraclePreparedStatment.executePreparedStatement: entry    data is:\n";
      var_dump($data);

      // transfer the data from the input array to the bound array
      for($i=0; $i<count($data);$i++) {

	  //strip quotation marks
          $dataElement = array_shift($data);
          if (substr($dataElement, 0, 1) =="'" )
	      $dataElement = substr($dataElement,1);
	  $len = strlen($dataElement);
	  if (substr($dataElement, $len-1, 1) =="'" )
	      $dataElement = substr($dataElement,0, $len-1);

          $this->bound_vars[$i] = $dataElement;
      }


      if (!($res = oci_execute($this->stmt, OCI_DEFAULT))) {
          return "Execute Prepared Statement failed: (" . $dblink->errno . ") " . $dblink->error;
      }
      oci_commit($this->database);
      return $res;
   }

}
