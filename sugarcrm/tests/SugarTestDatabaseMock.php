<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

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
        $this->queries = array();
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

        if (!isset($response)) {
            $GLOBALS['log']->fatal("SugarTestDatabaseMock came across a query it wasn't expecting: $sql");
            $this->rows = array();
            return false;
        } else {
            if (isset($this->queries[$responseKey]['runCount'])) {
                $this->queries[$responseKey]['runCount']++;
            } else {
                $this->queries[$responseKey]['runCount'] = 1;
            }
            // if response has rows, return them
            if (isset($response['rows'])) {
                $this->rows = $response['rows'];
                return $response['rows'];
            }
            return true;
        }
        
    }

    public function getOne($sql, $dieOnError = false, $msg = '')
    {
        $response = $this->query($sql, $dieOnError, $msg);
        return isset($response[0]) ? array_shift($response[0]) : false;
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
