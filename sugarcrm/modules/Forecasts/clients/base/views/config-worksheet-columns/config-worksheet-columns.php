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
//BEGIN SUGARCRM flav=pro && flav!=ent ONLY
// PRO/CORP only fields
$fields = array(
    // Default enabled columns
    array(
        'name' => 'commit_stage',
        'label' => 'LBL_FORECASTS_CONFIG_TITLE_RANGES',
        'locked' => true
    ),
    array(
        'name' => 'parent_name',
        'label' => 'LBL_NAME',
        'label_module' => 'Opportunities',
        'locked' => true
    ),
    array(
        'name' => 'account_name',
        'label' => 'LBL_LIST_ACCOUNT_NAME',
        'label_module' => 'RevenueLineItems'
    ),
    array(
        'name' => 'date_closed',
        'label' => 'LBL_DATE_CLOSED',
        'label_module' => 'RevenueLineItems'
    ),
    array(
        'name' => 'sales_stage',
        'label' => 'LBL_SALES_STAGE',
        'label_module' => 'Products'
    ),
    array(
        'name' => 'probability',
        'label' => 'LBL_OW_PROBABILITY',
    ),
    array(
        'name' => 'worst_case',
        'label' => 'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_WORST',
        'locked' => true
    ),
    array(
        'name' => 'likely_case',
        'label' => 'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_LIKELY',
        'locked' => true
    ),
    array(
        'name' => 'best_case',
        'label' => 'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_BEST',
        'locked' => true
    ),

    // Non-default-enabled columns
    array(
        'name' => 'product_type',
        'label' => 'LBL_TYPE',
        'label_module' => 'RevenueLineItems'
    ),
    array(
        'name' => 'lead_source',
        'label' => 'LBL_LEAD_SOURCE',
        'label_module' => 'Contacts'
    ),
    array(
        'name' => 'campaign_name',
        'label' => 'LBL_CAMPAIGN'
    ),
    array(
        'name' => 'assigned_user_name',
        'label' => 'LBL_ASSIGNED_TO_NAME',
    ),
    array(
        'name' => 'team_name',
        'label' => 'LBL_TEAMS'
    ),
    array(
        'name' => 'next_step',
        'label' => 'LBL_NEXT_STEP',
        'label_module' => 'RevenueLineItems'
    ),
    array(
        'name' => 'description',
        'label' => 'LBL_DESCRIPTION',
        'label_module' => 'RevenueLineItems'
    ),
);

//END SUGARCRM flav=pro && flav!=ent ONLY

//BEGIN SUGARCRM flav=ent ONLY
// ENT/ULT only fields
$fields = array(
    // Default enabled columns
    array(
        'name' => 'commit_stage',
        'label' => 'LBL_FORECASTS_CONFIG_TITLE_RANGES',
        'locked' => true
    ),
    array(
        'name' => 'parent_name',
        'label' => 'LBL_LIST_NAME',
        'label_module' => 'RevenueLineItems',
        'locked' => true
    ),
    array(
        'name' => 'opportunity_name',
        'label' => 'LBL_OPPORTUNITY_NAME',
        'label_module' => 'Opportunities'
    ),
    array(
        'name' => 'account_name',
        'label' => 'LBL_LIST_ACCOUNT_NAME',
        'label_module' => 'RevenueLineItems'
    ),
    array(
        'name' => 'date_closed',
        'label' => 'LBL_DATE_CLOSED',
        'label_module' => 'RevenueLineItems'
    ),
    array(
        'name' => 'product_template_name',
        'label' => 'LBL_PRODUCT',
        'label_module' => 'RevenueLineItems'
    ),
    array(
        'name' => 'sales_stage',
        'label' => 'LBL_SALES_STAGE',
        'label_module' => 'RevenueLineItems'
    ),
    array(
        'name' => 'probability',
        'label' => 'LBL_OW_PROBABILITY',
    ),
    array(
        'name' => 'worst_case',
        'label' => 'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_WORST',
        'locked' => true
    ),
    array(
        'name' => 'likely_case',
        'label' => 'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_LIKELY',
        'locked' => true
    ),
    array(
        'name' => 'best_case',
        'label' => 'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_BEST',
        'locked' => true
    ),

    // Non-default-enabled columns
    array(
        'name' => 'list_price',
        'label' => 'LBL_LIST_PRICE',
        'label_module' => 'RevenueLineItems'
    ),
    array(
        'name' => 'cost_price',
        'label' => 'LBL_COST_PRICE',
    ),
    array(
        'name' => 'discount_price',
        'label' => 'LBL_DISCOUNT_PRICE',
        'label_module' => 'RevenueLineItems'
    ),
    array(
        'name' => 'discount_amount',
        'label' => 'LBL_TOTAL_DISCOUNT_AMOUNT',
    ),
    array(
        'name' => 'quantity',
        'label' => 'LBL_LIST_QUANTITY',
        'label_module' => 'RevenueLineItems'
    ),
    array(
        'name' => 'category_name',
        'label' => 'LBL_CATEGORY',
    ),
    array(
        'name' => 'total_amount',
        'label' => 'LBL_CALCULATED_LINE_ITEM_AMOUNT',
        'label_module' => 'RevenueLineItems'
    ),
    array(
        'name' => 'product_type',
        'label' => 'LBL_TYPE',
        'label_module' => 'RevenueLineItems'
    ),
    array(
        'name' => 'lead_source',
        'label' => 'LBL_LEAD_SOURCE',
        'label_module' => 'Contacts'
    ),
    array(
        'name' => 'campaign_name',
        'label' => 'LBL_CAMPAIGN'
    ),
    array(
        'name' => 'assigned_user_name',
        'label' => 'LBL_ASSIGNED_TO_NAME',
    ),
    array(
        'name' => 'team_name',
        'label' => 'LBL_TEAMS'
    ),
    array(
        'name' => 'next_step',
        'label' => 'LBL_NEXT_STEP',
        'label_module' => 'RevenueLineItems'
    ),
    array(
        'name' => 'description',
        'label' => 'LBL_DESCRIPTION',
        'label_module' => 'RevenueLineItems'
    ),
);
//END SUGARCRM flav=ent ONLY

$viewdefs['Forecasts']['base']['view']['config-worksheet-columns'] = array(
    'label' => 'LBL_FORECASTS_CONFIG_TITLE_WORKSHEET_COLUMNS',
    'panels' => array(
        array(
            'fields' => $fields,
        )
    )
);
