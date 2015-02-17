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
$vardefs = array(
    'fields' => array(
        'currency_id' => array(
            'name' => 'currency_id',
            'dbType' => 'id',
            'vname' => 'LBL_CURRENCY_ID',
            'type' => 'currency_id',
            'function' => 'getCurrencies', // This is needed for BWC modules
            'function_bean' => 'Currencies', // This is needed for BWC modules
            'required' => false,
            'reportable' => false,
            'default' => '-99'
        ),
        'base_rate' => array(
            'name' => 'base_rate',
            'vname' => 'LBL_CURRENCY_RATE',
            'type' => 'decimal',
            'len' => '26,6'
        ),
        'currency_name' => array(
            'name' => 'currency_name',
            'rname' => 'name',
            'id_name' => 'currency_id',
            'vname' => 'LBL_CURRENCY_NAME',
            'type' => 'relate',
            'link' => 'currencies',
            'isnull' => true,
            'table' => 'currencies',
            'module' => 'Currencies',
            'source' => 'non-db',
            'studio' => false,
            'duplicate_merge' => 'disabled',
            'function' => 'getCurrencyDropDown',  // This is needed for BWC modules
            'massupdate' => false
        ),
        'currency_symbol' => array(
            'name' => 'currency_symbol',
            'rname' => 'symbol',
            'id_name' => 'currency_id',
            'vname' => 'LBL_CURRENCY_SYMBOL',
            'type' => 'relate',
            'link' => 'currencies',
            'isnull' => true,
            'table' => 'currencies',
            'module' => 'Currencies',
            'source' => 'non-db',
            'studio' => false,
            'duplicate_merge' => 'disabled',
            'massupdate' => false
        ),
        'currencies' => array(
            'name' => 'currencies',
            'type' => 'link',
            'relationship' => strtolower($module) . '_currencies',
            'source' => 'non-db',
            'vname' => 'LBL_CURRENCIES'
        )
    ),
    'relationships' => array(
        strtolower($module) . '_currencies' => array(
            'lhs_module' => $module,
            'lhs_table' => strtolower($table_name),
            'lhs_key' => 'currency_id',
            'rhs_module' => 'Currencies',
            'rhs_table' => 'currencies',
            'rhs_key' => 'id',
            'relationship_type' => 'one-to-many'
        )
    )
);
