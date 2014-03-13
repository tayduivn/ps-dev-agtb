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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
$viewdefs['RevenueLineItems']['base']['view']['selection-list'] = array(
    'panels' => array(
        array(
            'name' => 'panel_header',
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'name',
                    'link' => true,
                    'label' => 'LBL_LIST_NAME',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'opportunity_name',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'account_name',
                    'readonly' => true,
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
                    'default' => false,
                ),
                array(
                    'name' => 'date_closed',
                    'enabled' => true,
                    'default' => false,
                ),
                array(
                    'name' => 'commit_stage',
                    'enabled' => true,
                    'default' => false,
                ),
                array(
                    'name' => 'product_template_name',
                    'enabled' => true,
                    'default' => false,
                ),
                array(
                    'name' => 'category_name',
                    'enabled' => true,
                    'default' => false,
                ),
                array(
                    'name' => 'quantity',
                    'enabled' => true,
                    'default' => false,
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
                    'default' => false,
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
                    'default' => false,
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
                    'default' => false,
                ),
                array(
                    'name' => 'quote_name',
                    'label' => 'LBL_ASSOCIATED_QUOTE',
                    'related_fields' => array('quote_id'),
                    'readonly' => true,
                    'enabled' => true,
                    'default' => false,
                ),
                array(
                    'name' => 'assigned_user_name',
                    'enabled' => true,
                    'default' => false,
                ),
            ),
        ),
    ),
);
