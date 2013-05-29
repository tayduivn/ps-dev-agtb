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

$viewdefs['RevenueLineItems']['base']['view']['list'] = array(
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
                    'default' => true
                ),
                'opportunity_name',
                array(
                    'name' => 'account_name',
                    'readonly' => true
                ),
                'probability',
                'date_closed',
                'commit_stage',
                'product_template_name',
                'quantity',
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
                ),
                'assigned_user_name'
            ),

        ),
    ),
);
