<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
$dictionary['ForecastWorksheet'] = array(
    'table' => 'forecast_worksheets',
    'studio' => false,
    'acls' => array('SugarACLForecastWorksheets' => true, 'SugarACLStatic' => true),
    'fields' => array(
        'parent_id' =>
        array(
            'name' => 'parent_id',
            'vname' => 'LBL_PARENT_ACCOUNT_ID',
            'type' => 'id',
            'group'=>'parent_name',
            'required' => false,
            'reportable' => false,
            'audited' => false,
            'comment' => 'Account ID of the parent of this account',
            'studio' => false
        ),
        'parent_type' =>
        array(
            'name' => 'parent_type',
            'vname' => 'LBL_PARENT_TYPE',
            'type' => 'parent_type',
            'dbType' => 'varchar',
            'group' => 'parent_name',
            'options' => 'parent_type_display',
            'len' => '255',
            'comment' => 'Sugar module the Worksheet is associated with',
            'studio' => false
        ),
        'parent_name' =>
        array(
            'name' => 'parent_name',
            'parent_type' => 'record_type_display',
            'type_name' => 'parent_type',
            'id_name' => 'parent_id',
            'vname' => 'LBL_LIST_RELATED_TO',
            'type' => 'parent',
            'group' => 'parent_name',
            'source' => 'non-db',
            'options' => 'parent_type_display',
            'studio' => true,
        ),
        'account_name' =>
        array(
            'name' => 'account_name',
            'id_name' => 'account_id',
            'vname' => 'LBL_ACCOUNT_NAME',
            'type' => 'varchar',
            'len' => '255',
            'studio' => false
        ),
        'account_id' =>
        array(
            'name' => 'account_id',
            'vname' => 'LBL_ACCOUNT_ID',
            'type' => 'id',
            'audited' => false,
            'studio' => false
        ),
        'sales_status' => array(
            'name' => 'sales_status',
            'vname' => 'LBL_SALES_STATUS',
            'type' => 'enum',
            'options' => 'sales_status_dom',
            'len' => '255',
            'audited' => true,
        ),
        'likely_case' =>
        array(
            'name' => 'likely_case',
            'vname' => 'LBL_LIKELY_CASE',
            'dbType' => 'currency',
            'type' => 'currency',
            'len' => '26,6',
            'validation' => array('type' => 'range', 'min' => 0),
            'audited' => false,
            'studio' => false
        ),
        'best_case' =>
        array(
            'name' => 'best_case',
            'vname' => 'LBL_BEST_CASE',
            'dbType' => 'currency',
            'type' => 'currency',
            'len' => '26,6',
            'validation' => array('type' => 'range', 'min' => 0),
            'audited' => false,
            'studio' => false
        ),
        'worst_case' =>
        array(
            'name' => 'worst_case',
            'vname' => 'LBL_WORST_CASE',
            'dbType' => 'currency',
            'type' => 'currency',
            'len' => '26,6',
            'validation' => array('type' => 'range', 'min' => 0),
            'audited' => false,
            'studio' => false
        ),
        'base_rate' =>
        array(
            'name' => 'base_rate',
            'vname' => 'LBL_BASE_RATE',
            'type' => 'double',
            'required' => true,
            'studio' => false
        ),
        'currency_id' =>
        array(
            'name' => 'currency_id',
            'type' => 'id',
            'group' => 'currency_id',
            'vname' => 'LBL_CURRENCY',
            'function' => array('name' => 'getCurrencyDropDown', 'returns' => 'html'),
            'reportable' => false,
            'comment' => 'Currency used for display purposes',
            'studio' => false
        ),
        'currency_name' =>
        array(
            'name' => 'currency_name',
            'rname' => 'name',
            'id_name' => 'currency_id',
            'vname' => 'LBL_CURRENCY_NAME',
            'type' => 'relate',
            'isnull' => 'true',
            'table' => 'currencies',
            'module' => 'Currencies',
            'source' => 'non-db',
            'function' => array('name' => 'getCurrencyNameDropDown', 'returns' => 'html'),
            'studio' => false,
            'duplicate_merge' => 'disabled',
        ),
        'currency_symbol' =>
        array(
            'name' => 'currency_symbol',
            'rname' => 'symbol',
            'id_name' => 'currency_id',
            'vname' => 'LBL_CURRENCY_SYMBOL',
            'type' => 'relate',
            'isnull' => 'true',
            'table' => 'currencies',
            'module' => 'Currencies',
            'source' => 'non-db',
            'function' => array('name' => 'getCurrencySymbolDropDown', 'returns' => 'html'),
            'studio' => false,
            'duplicate_merge' => 'disabled',
        ),
        'date_closed' =>
        array(
            'name' => 'date_closed',
            'vname' => 'LBL_DATE_CLOSED',
            'type' => 'date',
            'audited' => false,
            'comment' => 'Expected or actual date the oppportunity will close',
            'importable' => 'required',
            'required' => true,
            'enable_range_search' => true,
            'options' => 'date_range_search_dom',
            'studio' => false
        ),
        'date_closed_timestamp' =>
        array(
            'name' => 'date_closed_timestamp',
            'vname' => 'LBL_DATE_CLOSED_TIMESTAMP',
            'type' => 'int',
            'studio' => false
        ),
        'sales_stage' =>
        array(
            'name' => 'sales_stage',
            'vname' => 'LBL_SALES_STAGE',
            'type' => 'enum',
            'options' => 'sales_stage_dom',
            'len' => '255',
            'audited' => false,
            'comment' => 'Indication of progression towards closure',
            'merge_filter' => 'enabled',
            'importable' => 'required',
            'required' => true,
            'studio' => false
        ),
        'probability' =>
        array(
            'name' => 'probability',
            'vname' => 'LBL_PROBABILITY',
            'type' => 'int',
            'dbType' => 'double',
            'audited' => false,
            'comment' => 'The probability of closure',
            'validation' => array('type' => 'range', 'min' => 0, 'max' => 100),
            'merge_filter' => 'enabled',
            'studio' => false
        ),
        'commit_stage' =>
        array(
            'name' => 'commit_stage',
            'vname' => 'LBL_COMMIT_STAGE',
            'type' => 'enum',
            'len' => '50',
            'comment' => 'Forecast commit ranges: Include, Likely, Omit etc.',
            'studio' => false
        ),
        'draft' =>
        array(
            'name' => 'draft',
            'vname' => 'LBL_DRAFT',
            'default' => 0,
            'type' => 'int',
            'comment' => 'Is A Draft Version',
            'studio' => false
        ),
        'parent_deleted' =>
        array(
            'name' => 'parent_deleted',
            'default' => 0,
            'type' => 'int',
            'comment' => 'Is Parent Deleted',
            'studio' => false,
            'source' => 'non-db'
        ),
        'opportunity' =>
        array(
            'name' => 'opportunity',
            'type' => 'link',
            'relationship' => 'opportunity_worksheets',
            'source' => 'non-db',
            'vname' => 'LBL_OPPORTUNITY',
        ),
        'product' =>
        array(
            'name' => 'product',
            'type' => 'link',
            'relationship' => 'products_worksheets',
            'source' => 'non-db',
            'vname' => 'LBL_PRODUCT',
        )
    ),
    'indices' => array(
        array('name' => 'idx_worksheets_parent', 'type' => 'index', 'fields' => array('parent_id', 'parent_type')),
        array(
            'name' => 'idx_worksheets_assigned_del',
            'type' => 'index',
            'fields' => array('deleted', 'assigned_user_id')
        ),
        array(
            'name' => 'idx_worksheets_assigned_del_time_draft',
            'type' => 'index',
            'fields' => array('assigned_user_id', 'date_closed_timestamp', 'draft', 'deleted')
        ),
    ),
);

VardefManager::createVardef(
    'ForecastWorksheets',
    'ForecastWorksheet',
    array(
        'default',
        'assignable',
//BEGIN SUGARCRM flav=pro ONLY
        'team_security',
//END SUGARCRM flav=pro ONLY
    )
);
