<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */


require_once('include/SugarCurrency/CurrencyRateUpdateAbstract.php');

/**
 * QuotesCurrencyRateUpdate
 *
 * A class for updating currency rates on specified database table columns
 * when a currency conversion rate is updated by the administrator.
 *
 */
class QuotesCurrencyRateUpdate extends CurrencyRateUpdateAbstract
{
    /**
     * constructor
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
        // set rate field definitions
        $this->addRateColumnDefinition('quotes','base_rate');
        // set usdollar field definitions
        $this->addUsDollarColumnDefinition('quotes','subtotal','subtotal_usdollar');
        $this->addUsDollarColumnDefinition('quotes','shipping','shipping_usdollar');
        $this->addUsDollarColumnDefinition('quotes','deal_tot','deal_tot_usdollar');
        $this->addUsDollarColumnDefinition('quotes','new_sub','new_sub_usdollar');
        $this->addUsDollarColumnDefinition('quotes','tax','tax_usdollar');
        $this->addUsDollarColumnDefinition('quotes','total','total_usdollar');
    }

    /**
     * doCustomUpdateRate
     *
     * Return true to skip updates for this module.
     * Return false to do default update of base_rate column.
     * To custom processing, do here and return true.
     *
     * @access public
     * @param  string $table
     * @param  string $column
     * @param  string $currencyId
     * @return boolean true if custom processing was done
     */
    public function doCustomUpdateRate($table, $column, $currencyId)
    {
        // get the conversion rate
        $rate = $this->db->getOne(sprintf("SELECT conversion_rate FROM currencies WHERE id = '%s'", $currencyId));

        // setup SQL statement
        $query = sprintf("UPDATE %s SET %s = '%s'
        WHERE quote_stage NOT LIKE ('%%Closed%%')
        AND currency_id = '%s'",
            $table,
            $column,
            $rate,
            $currencyId
        );
        // execute
        $result = $this->db->query(
            $query,
            true,
            string_format(
                $GLOBALS['app_strings']['ERR_DB_QUERY'],
                array('QuotesCurrencyRateUpdate',$query
                )
            )
        );
        return !empty($result);
    }

    /**
     * doCustomUpdateUsDollarRate
     *
     * Return true to skip updates for this module.
     * Return false to do default update of amount * base_rate = usdollar
     * To custom processing, do here and return true.
     *
     * @access public
     * @param  string    $tableName
     * @param  string    $usDollarColumn
     * @param  string    $amountColumn
     * @param  string    $currencyId
     * @return boolean true if custom processing was done
     */
    public function doCustomUpdateUsDollarRate($tableName, $usDollarColumn, $amountColumn, $currencyId)
    {
        // setup SQL statement
        $query = sprintf("UPDATE %s SET %s = %s / base_rate
            WHERE quote_stage NOT LIKE ('%%Closed%%')
            AND currency_id = '%s'",
            $tableName,
            $usDollarColumn,
            $amountColumn,
            $currencyId
        );
        // execute
        $result = $this->db->query(
            $query,
            true,
            string_format(
                $GLOBALS['app_strings']['ERR_DB_QUERY'],
                array('QuotesCurrencyRateUpdate', $query)
            )
        );
        return !empty($result);
    }

}
