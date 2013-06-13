<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

$viewdefs['Forecasts']['base']['view']['forecastsConfigWorksheetColumns'] = array(
    'panels' => array(
        array(
            'label' => 'LBL_FORECASTS_CONFIG_BREADCRUMB_RANGES',
            'fields' => array(
                // Default enabled columns
                array(
                    'name' => 'name',
                    'label' => 'LBL_REVENUE_LINE_ITEM_NAME',
                ),
                array(
                    'name' => 'account_name',
                    'label' => 'LBL_OW_ACCOUNTNAME',
                ),
                array(
                    'name' => 'date_closed',
                    'label' => 'LBL_DATE_CLOSED',
                ),
                array(
                    'name' => 'product_template_name',
                    'label' => 'LBL_PRODUCT',
                    'label_module' => 'Products'
                ),
                array(
                    'name' => 'likely_case',
                    'label' => 'LBL_CALCULATED_LINE_ITEM_AMOUNT',
                    'label_module' => 'Products'
                ),
                array(
                    'name' => 'sales_stage',
                    'label' => 'LBL_SALES_STAGE',
                ),
                array(
                    'name' => 'probability',
                    'label' => 'LBL_OW_PROBABILITY',
                ),
                array(
                    'name' => 'best_case',
                    'label' => 'LB_FS_BEST_CASE',
                ),
                array(
                    'name' => 'worst_case',
                    'label' => 'LB_FS_WORST_CASE',
                ),
                array(
                    'name' => 'opportunity_name',
                    'label' => 'LBL_OPPORTUNITY',
                    'label_module' => 'Products'
                ),

                // Non-default-enabled columns
                array(
                    'name' => 'list_price',
                    'label' => 'LBL_LIST_PRICE',
                    'label_module' => 'Products'
                ),
                array(
                    'name' => 'cost_price',
                    'label' => 'LBL_COST_PRICE',
                    'label_module' => 'Products'
                ),
                array(
                    'name' => 'discount_price',
                    'label' => 'LBL_DISCOUNT_PRICE',
                    'label_module' => 'Products'
                ),
                array(
                    'name' => 'discount_amount',
                    'label' => 'LBL_DISCOUNT_TOTAL',
                    'label_module' => 'Products'
                ),
                array(
                    'name' => 'quantity',
                    'label' => 'LBL_LIST_QUANTITY',
                    'label_module' => 'Products'
                ),
                array(
                    'name' => 'product_template_id',
                    'label' => 'LBL_PRODUCT_TEMPLATE',
                    'label_module' => 'Products'
                ),
                array(
                    'name' => 'sales_status',
                    'label' => 'LBL_SALES_STATUS',
                    'label_module' => 'Opportunities'
                ),
                array(
                    'name' => 'commit_stage',
                    'label' => 'LBL_COMMIT_STAGE',
                ),
                array(
                    'name' => 'product_type',
                    'label' => 'LBL_PRODUCT',
                    'label_module' => 'Products'
                ),
                array(
                    'name' => 'lead_source',
                    'label' => 'LBL_LEAD_SOURCE',
                    'label_module' => 'Contacts'
                ),
                array(
                    'name' => 'campaign_name',
                    'label' => 'LBL_TOP_CAMPAIGNS_NAME',
                    'label_module' => 'Campaigns'
                ),
                array(
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                ),
                array(
                    'name' => 'team_name',
                    'label' => 'LBL_NAME',
                    'label_module' => 'Teams'
                ),
                array(
                    'name' => 'next_step',
                    'label' => 'LBL_NEXT_STEP',
                    'label_module' => 'Products'
                ),
                array(
                    'name' => 'description',
                    'label' => 'LBL_DESCRIPTION',
                    'label_module' => 'Products'
                ),
            )
        )
    )
);
