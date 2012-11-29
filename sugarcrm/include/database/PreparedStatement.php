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

/**
 * Base prepared statement database implementation
 * @api
 */

abstract class PreparedStatement{


    protected $DBM = null;

    /**
     * Actual concrete DB resource link, will be used by child classes
     * @var mixed
     */
    protected $dblink;

    protected $sqlText = null;

    protected $fieldDefs = array();

    protected $sqlTextHash = null;

    protected $sqlPrepareCount = null;

    protected $sqlExecuteCount = null;

    protected $statementHandle = null;

    protected $preparedStatementHndl = null;



    /**
     * Create Prepared Statement object from sql in the form of "INSERT INTO testPreparedStatement(id) VALUES(?int, ?varchar)"
     */
    public function __construct($DBM, $sql, array $data, array $fieldDefs = array() ){
        $this->timedate = TimeDate::getInstance();
        $this->log = $GLOBALS['log'];
        $this->dblink = $DBM->getDatabase();

echo "=========================================\n";
echo "==> Prepared Statement.__construct: start. sqlText: $sql\n";
echo "\nData\n";
        var_dump($data);
echo "\nFieldDefs\n";
        var_dump($fieldDefs);
echo "\n";

        if (empty($DBM))    {
          $msg = "ERROR Database object missing";
          echo "$msg\n";
          return $msg;
        }

        if (empty($sql))    {
            $msg = "ERROR Prepared SQL text is missing";
            echo "$msg\n";
            return $msg;
        }

        //if (empty($fieldDefs) || !is_array($fieldDefs))    {
        //    return "ERROR field definitions are missing";
        //}


        $this->sqlText = $sql;

        // Build fieldDefs array and replace ?SugarDataType placeholders with a single ?placeholder
        $cleanedSql = "";
        $nextParam = strpos( $sql, "?" );
//echo "initial nextParam is at $nextParam\n";
        if ($nextParam == 0 )
            $cleanedSql = $sql;
        else {     // parse the sql string looking for params
           $row = 0;
           while ($nextParam > 0 ) {
//              echo "Processing a param. row=$row\n" ;
              $cleanedSql .= substr( $sql, 0, $nextParam + 1);  // we want the ?
//              echo "cleanedSql: $cleanedSql\n";

              $sql = substr( $sql, $nextParam + 1);   // strip leading chars
//              echo "remaining sql for sugarDataType is: $sql\n";

              // scan for termination of SugarDataType
               $sugarDataType = "";
              for ($i=0; ($i < strlen($sql)) and (strpos(",) ", substr($sql, $i, 1)) === false); $i++){
//                 echo "testing >" . substr($sql, $i, 1) . "< Result was " . strpos(",) ", substr($sql, $i, 1)) . "\n";
//                 if ( (strpos(",) ", substr($sql, $i, 1)) == false ))
                 if (strpos(",) ", substr($sql, $i, 1)) == false) {
                 $sugarDataType .=  substr($sql, $i, 1);
              }
              }
//              echo "i is $i  sugarDataType is $sugarDataType \n";
              // insert the fieldDef
//               echo "sugarDataType:\n";
               var_dump($sugarDataType);
              if ( $sugarDataType === "" ) //no type, default to varchar
                  $fieldDefs[$row]['type'] = 'varchar';
              else
                  $fieldDefs[$row]['type'] = $sugarDataType;
              $sql = substr($sql, $i); // strip off the SugarDataType
//              echo "remaining sql is: $sql\n";
              $nextParam = strpos( $sql, "?" ); // look for another param
//              echo "another nextParam is at $nextParam\n";
              $row++;


           }
        }

        // add the remaining sql
        $cleanedSql .= $sql;

        echo "finished building fieldDefs\n";
        var_dump($fieldDefs);
        echo "cleaned sql: $cleanedSql \n";

        echo "Preparing the statement...\n";
        //Prepare the statement in the database                  $DBM
        $preparedStatementHndl = $this->preparePreparedStatement($cleanedSql, $data, $fieldDefs );
        if (empty($preparedStatementHndl))
            return "preparing statement failed";
    }

    public function executeStatement(array $data){

        $this->executePreparedStatement($data);

    }

}
