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

require_once('include/SugarCurrency/CurrencyRateUpdateAbstract.php');

    /**
     * OpportunitiesCurrencyRateUpdate
     *
     * A class for updating currency rates on specified database table columns
     * when a currency conversion rate is updated by the administrator.
     *
     */
class OpportunitiesCurrencyRateUpdate extends CurrencyRateUpdateAbstract
{
    /**
     * constructor
     *
     * @access public
     */
    public function __construct() {
        // set rate field definitions
        $this->addRateColumnDefinition('opportunities','base_rate');
        // set usdollar field definitions
        $this->addUsDollarColumnDefinition('opportunities','amount','amount_usdollar');
    }

    /**
     * doCustomUpdateRate
     *
     * Return true to skip updates for this module.
     * Return false to do default update of base_rate column.
     * To custom processing, do here and return true.
     *
     * @access protected
     * @param  string $table
     * @param  string $column
     * @param  string $currencyId
     * @return boolean true if custom processing was done
     */
    public function doCustomUpdateRate($table, $column, $currencyId) {
        // setup SQL statement
        $query = sprintf("UPDATE currencies c, %s t SET t.%s = c.conversion_rate
        WHERE t.sales_stage NOT LIKE 'Closed%%'
        AND c.id = '%s' and c.id = t.currency_id",
            $table,
            $column,
            $currencyId
        );
        // execute
        $this->db->query($query, true, string_format($GLOBALS['app_strings']['ERR_DB_QUERY'],array('OpportunitiesCurrencyRateUpdate',$query)));
        return true;
    }

    /**
     * doCustomUpdateUsDollarRate
     *
     * Return true to skip updates for this module.
     * Return false to do default update of amount * base_rate = usdollar
     * To custom processing, do here and return true.
     *
     * @access protected
     * @param  string    $tableName
     * @param  string    $usDollarColumn
     * @param  string    $amountColumn
     * @param  string    $currencyId
     * @return boolean true if custom processing was done
     */
    protected function doCustomUpdateUsDollarRate($tableName, $usDollarColumn, $amountColumn, $currencyId) {
        // setup SQL statement
        $query = sprintf("UPDATE %s t SET t.%s = t.base_rate * t.%s
            WHERE t.sales_stage NOT LIKE 'Closed%%'
            AND t.currency_id = '%s'",
            $tableName,
            $usDollarColumn,
            $amountColumn,
            $currencyId
        );
        // execute
        $result = $this->db->query($query, true, string_format($GLOBALS['app_strings']['ERR_DB_QUERY'],array('OpportunitiesCurrencyRateUpdate',$query)));
        if(empty($result)) {
            return false;
        }
        return true;
    }

}