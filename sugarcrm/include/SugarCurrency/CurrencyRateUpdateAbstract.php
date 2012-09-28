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
 * CurrencyRateUpdate
 *
 * A class for updating currency rates on specified database table columns
 *
 * @author Monte Ohrt <mohrt@sugarcrm.com>
 */
abstract class CurrencyRateUpdateAbstract
{
    /*
     * rate type, currently only 'base' is supported
     */
    protected $type = 'base';

    /*
     * if excluded, this module will not update its currencies
     */
    protected $exclude = false;

    /*
     * Table definitions, define each rate column of each table
     * of the form array('table'=>'col','table2'=>'col',...)
     */
    protected $tableDefinitions = array(
        'base' => array()
    );

    /**
     * constructor
     *
     * @access public
     * @param  string $type Optional if empty, base currency is assumed
     */
    public function __construct( $type = 'base' ) {
        $this->setType($type);
    }

    public function run() {
        if(!$this->exlude) {
            // module excluded, silent exit
            return true;
        }
        if(empty($this->tableDefinitions[$this->type])) {
            // no definitions, we are done
            return true;
        }
        $db = DBManagerFactory::getInstance();
        if(empty($db)) {
            $GLOBALS['log']->error('CurrencyRateUpdate: unable to load database manager.');
            return false;
        }
        $tables = $db->getTablesArray();
        foreach($this->tableDefinitions[$this->type] as $table=>$column) {
            // make sure the table and column exist
            if(empty($tables[$table])) {
                $GLOBALS['log']->error("CurrencyRateUpdate: unknown table: {$table}.");
                return false;
            }
            $columns = $db->get_columns($table);
            if(empty($columns[$column]))
            {
                $GLOBALS['log']->error("CurrencyRateUpdate: unknown table column: {$table},{$column}.");
                return false;
            }
            if(empty($columns['currency_id']))
            {
                $GLOBALS['log']->error("CurrencyRateUpdate: table {$table} must have currency_id column.");
                return false;
            }
            // setup SQL statement
            $sql = sprintf("UPDATE currencies c, `%s` t SET t.%s = c.%s WHERE c.currency_id = t.currency_id", $table, $column, $column);
            // execute
            $result = $db->query($sql, true, "CurrencyRateUpdate query failed: {$query}");
            return !empty($result);
        }
        return true;
    }

    /*
     * setters/getters
     */

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getTableDefinitions()
    {
        return $this->tableDefinitions;
    }

    public function setTableDefinitions($tableDefinitions)
    {
        $this->tableDefinitions = $tableDefinitions;
    }

    public function getExclude()
    {
        return $this->exclude;
    }

    public function setExclude($exclude)
    {
        if(!is_bool($exclude)) {
            return false;
        }
        $this->exclude = $exclude;
    }
}