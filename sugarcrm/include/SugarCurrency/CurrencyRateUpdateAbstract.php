<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
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

/**
 * CurrencyRateUpdateAbstract
 *
 * A class for updating currency rates on specified database table columns
 * when a currency conversion rate is updated by the administrator.
 *
 * Each module that has currency fields must supply a
 * modules/[ModuleName]/jobs/CurrencyRateUpdate.php file that
 * extends this class and defines the tables/columns that should be updated,
 * and manage any special cases as well, such as when rates should not be updated.
 *
 */
abstract class CurrencyRateUpdateAbstract
{
    /*
     * database handle
     */
    protected $db;

    /*
     * if excluded, this module will not update its currencies
     */
    protected $exclude = false;

    /*
     * Rate column definitions, define each column of each table
     *
     * example:
     *
     * array(
     *   'tableFoo'=>array('base_rate'),
     *   'tableBar'=>array('base_rate')
     * ));
     */
    protected $rateColumnDefinitions = array();

    /*
     * automatic updating of usdollar fields
     */
    protected $updateUsDollar = true;

    /*
    * Us Dollar column definitions, define each column of each table
    *
    * format is tablename=>array(amount_field=>amount_usdollar_field)
    *
    * example:
    *
    * array(
    *   'tableFoo'=>array('amount'=>'amount_usdollar','foo'=>'foo_usdollar'),
    *   'tableBar'=>array('foo'=>'foo_usdollar')
    * ));
    */
    protected $usDollarColumnDefinitions = array();

    /**
     * constructor
     *
     * @access protected
     */
    protected function __construct() {}

    /**
     * run
     *
     * run the job to process the rate fields
     *
     * @access public
     * @param  object    $data   data object
     * @return boolean   true on success
     */
    public function run($data) {
        $currencyId = !empty($data->currencyId) ? $data->currencyId : null;
        if(empty($currencyId)) {
            return false;
        }
        if($this->exclude) {
            // module excluded, silent exit
            return true;
        }
        if(empty($this->rateColumnDefinitions)) {
            // no definitions, we are done
            return true;
        }
        $this->db = DBManagerFactory::getInstance();
        if(empty($this->db)) {
            $GLOBALS['log']->error('CurrencyRateUpdate: unable to load database manager.');
            return false;
        }
        $dbTables = $this->db->getTablesArray();
        // loop each defined table and update each rate column according to the currency id
        foreach($this->rateColumnDefinitions as $tableName=>$tableColumns) {
            // make sure table exists
            if(!in_array($tableName,$dbTables)) {
                $GLOBALS['log']->error("CurrencyRateUpdate: unknown table: {$tableName}.");
                return false;
            }
            $columns = $this->db->get_columns($tableName);
            foreach($tableColumns as $columnName) {
                // make sure column exists
                if(empty($columns[$columnName]))
                {
                    $GLOBALS['log']->error("CurrencyRateUpdate: unknown table column: {$columnName} on table {$tableName}.");
                    return false;
                }
                if(empty($columns['currency_id']))
                {
                    $GLOBALS['log']->error("CurrencyRateUpdate: table {$tableName} must have currency_id column.");
                    return false;
                }
                if(!$result = $this->doCustomProcess($tableName, $columnName, $currencyId)) {
                    // if no custom processing required, we do the standard update
                    $result = $this->updateRate($tableName, $columnName, $currencyId);
                }
                if (empty($result)) {
                    return false;
                }
            }
        }
        if($this->updateUsDollar) {
            if(!$this->updateUsDollarColumns($currencyId)) {
                return false;
            }
        }
        return true;
    }

    /**
     * doCustomProcess
     *
     * Override this method in your extended class
     * to do specific tests and actions.
     *
     * @access protected
     * @param  string $table
     * @param  string $column
     * @param  string $currencyId
     * @return boolean true if custom processing was done
     */
    protected function doCustomProcess($table, $column, $currencyId) {
        return false;
    }

    /**
     * updateRate
     *
     * execute the standard sql query for updating rates.
     * to use a specific query, override doCustomProcess()
     * in your extended class and make your own.
     *
     * @access protected
     * @param  string $table
     * @param  string $column
     * @param  string $currencyId
     * @return Object database result object
     */
    protected function updateRate($table, $column, $currencyId) {
        // setup SQL statement
        $query = sprintf("UPDATE currencies c, %s t SET t.%s = c.conversion_rate WHERE c.id = '%s' and c.id = t.currency_id",
            $table,
            $column,
            $currencyId
        );
        // execute
        return $this->db->query($query, true, "CurrencyRateUpdate query failed: {$query}");
    }

    /**
     * updateUsDollarColumns
     *
     * automatically update *_usdollar fields for backward compatibility
     * with modules that still use this field. The *_usdollar fields use
     * the base_rate field for the rate calculations.
     *
     * @access protected
     * @param  string    $currencyId
     * @return boolean true on success
     */
    protected function updateUsDollarColumns($currencyId) {
        // loop through all the tables
        foreach($this->usDollarColumnDefinitions as $tableName=>$tableDefs) {
            $columns = $this->db->get_columns($tableName);
            if(empty($columns)) {
                continue;
            }
            foreach($tableDefs as $amountColumn=>$usDollarColumn) {
                if(!in_array($columns, $amountColumn) || !in_array($columns, $usDollarColumn) || !in_array($columns, 'base_rate')) {
                    continue;
                }
                // setup SQL statement
                $query = sprintf("UPDATE %s t SET t.%s = t.base_rate*t.%s where t.currency_id = '%s'",
                    $tableName,
                    $usDollarColumn,
                    $amountColumn,
                    $currencyId
                );
                // execute
                $result = $this->db->query($query, true, "CurrencyRateUpdate query failed: {$query}");
                if(empty($result)) {
                    return false;
                }
            }
        }
        return true;
    }


    /*
     * setters/getters
     */

    protected function getRateColumnDefinitions($table)
    {
        return $this->rateColumnDefinitions[$table];
    }

    protected function addRateColumnDefinition($table, $column)
    {
        if(!is_array($this->rateColumnDefinitions[$table])) {
            $this->rateColumnDefinitions[$table] = array();
        }
        if(in_array($column, $this->rateColumnDefinitions[$table])) {
            return true;
        }
        $this->rateColumnDefinitions[$table][] = $column;
        return true;
    }

    protected function removeRateColumnDefinition($table, $column) {
        if(!is_array($this->rateColumnDefinitions[$table])) {
            $this->rateColumnDefinitions[$table] = array();
        }
        if(!in_array($column, $this->rateColumnDefinitions[$table])) {
            return true;
        }
        // remove element from array
        array_filter($this->rateColumnDefinitions[$table], function($a) use($column) {
            return $a !== $column;
        });
        return true;
    }

    protected function getExclude()
    {
        return $this->exclude;
    }

    protected function setExclude($exclude)
    {
        if(!is_bool($exclude)) {
            return false;
        }
        $this->exclude = $exclude;
    }

    protected function getUsDollarColumnDefinitions($table)
    {
        return $this->usDollarColumnDefinitions[$table];
    }

    protected function addUsDollarColumnDefinition($table, $amountColumn, $usDollarColumn)
    {
        if(!is_array($this->usDollarColumnDefinitions[$table])) {
            return false;
        }
        $this->usDollarColumnDefinitions[$table][$amountColumn] = $usDollarColumn;
        return true;
    }

    protected function removeUsDollarColumnDefinition($table, $amountColumn) {
        if(!is_array($this->usDollarColumnDefinitions[$table])) {
            return false;
        }
        unset($this->usDollarColumnDefinitions[$table][$amountColumn]);
        return true;
    }
}