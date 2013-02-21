<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once 'include/database/DBManagerFactory.php';

class SugarTestDatabaseMock extends DBManager
{
    public $oldInstances = null;

    public $queries = array();
    public $rows = array();

    public function setUp()
    {
        $this->oldInstances = DBManagerFactory::$instances;
        DBManagerFactory::$instances = array();
        DBManagerFactory::$instances[''] = $this;
    }

    public function tearDown()
    {
        DBManagerFactory::$instances = $this->oldInstances;
    }


    public function query($sql, $dieOnError = false, $msg = '', $suppress = false, $keepResult = false)
    {
        $sql = preg_replace('/\s\s+/',' ',$sql);
        $matches = array();
        foreach ( $this->queries as $responseKey => $possibleResponse ) {
            if ( preg_match($possibleResponse['match'],$sql,$matches) ) {
                $response = $possibleResponse;
                break;
            }
        }

        if ( !isset($response) ) {
            $GLOBALS['log']->fatal("SugarTestDatabaseMock came across a query it wasn't expecting: $sql");
            $this->rows = array();
            return false;
        } else {
            if ( isset($this->queries[$responseKey]['runCount']) ) {
                $this->queries[$responseKey]['runCount']++;
            }
            else {
                $this->queries[$responseKey]['runCount'] = 1;
            }
            $this->rows = $response['rows'];
            return $response['rows'];
        }
        
    }

    public function limitQuery($sql, $start, $count, $dieOnError = false, $msg = '', $execute = true)
    {
        return $this->query($sql." LIMIT ${start},${count}");
    }

    public function fetchRow($result)
    {
        if ( count($this->rows) < 1 ) {
            return;
        } else {
            return array_pop($this->rows);
        }
    }

    /*
     * Everything from here on out is just so we are a DBManager, just stubs
     */

    protected function freeDbResult($dbResult) {}
    public function quote($string) {return addslashes($string);}
    public function convert($string, $type, array $additional_parameters = array()) {return $string;}
    public function fromConvert($string, $type) {return $string;}
    public function renameColumnSQL($tablename, $column, $newname) {}
    public function get_indices($tablename) {return array();}
    public function get_columns($tablename) {return array();}
    public function add_drop_constraint($table, $definition, $drop = false) {}
    public function getFieldsArray($result, $make_lower_case = false) {}
    public function getTablesArray() {}
    public function version() {}
    public function tableExists($tableName) {}
    public function connect(array $configOptions = null, $dieOnError = false) {}
    public function createTableSQLParams($tablename, $fieldDefs, $indices) {}
    protected function changeColumnSQL($tablename, $fieldDefs, $action, $ignoreRequired = false) {}
    public function disconnect() {}
    public function lastDbError() {}
    public function validateQuery($query) { return true; }
    public function valid() { return true; }
    public function dbExists($dbname) { return true; }
    public function tablesLike($like) {}
    public function createDatabase($dbname) {}
    public function dropDatabase($dbname) {}
    public function getDbInfo() {}
    public function userExists($username) { return true; }
    public function createDbUser($database_name, $host_name, $user, $password) {}
    public function full_text_indexing_installed() { return true; }
    public function getFulltextQuery($field, $terms, $must_terms = array(), $exclude_terms = array()) {}
    public function installConfig() {}
    public function getFromDummyTable() {}
    public function getGuidSQL() {}
}
