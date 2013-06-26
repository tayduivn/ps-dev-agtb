<?php
//FILE SUGARCRM flav=pro ONLY
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

//BEGIN SUGARCRM flav=pro && flav!=ent ONLY
// PRO/CORP only fields
$fields = array(
    array(
        'name' => 'name',
        'link' => true,
        'label' => 'LBL_LIST_NAME',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'account_name',
        'sortable' => false,
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'status',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'quantity',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'discount_price',
        'type' => 'currency',
        'related_fields' => array(
            'currency_id',
            'base_rate',
        ),
        'convertToBase' => true,
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'list_price',
        'type' => 'currency',
        'related_fields' => array(
            'currency_id',
            'base_rate',
        ),
        'convertToBase' => true,
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'cost_price',
        'type' => 'currency',
        'related_fields' => array(
            'currency_id',
            'base_rate',
        ),
        'convertToBase' => true,
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'date_entered',
        'enabled' => true,
        'default' => true,
    ),
);
//END SUGARCRM flav=pro && flav!=ent ONLY

//BEGIN SUGARCRM flav=ent ONLY
// ENT/ULT only fields
$fields = array(
    array(
        'name' => 'name',
        'link' => true,
        'label' => 'LBL_LIST_NAME',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'opportunity_name',
        'sortable' => false,
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'account_name',
        'readonly' => true,
        'sortable' => false,
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'sales_stage',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'probability',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'date_closed',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'commit_stage',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'product_template_name',
        'sortable' => false,
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'category_name',
        'sortable' => false,
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'quantity',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'likely_case',
        'required' => true,
        'type' => 'currency',
        'related_fields' => array(
            'currency_id',
            'base_rate',
        ),
        'convertToBase' => true,
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'best_case',
        'required' => true,
        'type' => 'currency',
        'related_fields' => array(
            'currency_id',
            'base_rate',
        ),
        'convertToBase' => true,
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'worst_case',
        'required' => true,
        'type' => 'currency',
        'related_fields' => array(
            'currency_id',
            'base_rate',
        ),
        'convertToBase' => true,
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
        'enabled' => true,
        'default' => true,
    ),
    array(
        'name' => 'assigned_user_name',
        'sortable' => false,
        'enabled' => true,
        'default' => true,
    )
);
//END SUGARCRM flav=ent ONLY

$viewdefs['Products']['base']['view']['list'] = array(
    'panels' => array(
        array(
            'name' => 'panel_header',
            'label' => 'LBL_PANEL_1',
            'fields' => $fields
        ),
    ),
);
