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

$viewdefs['Products']['base']['view']['record'] = array(
    'buttons' => array(
        array(
            'type' => 'button',
            'name' => 'cancel_button',
            'label' => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
            'showOn' => 'edit',
        ),
        array(
            'type' => 'rowaction',
            'event' => 'button:save_button:click',
            'name' => 'save_button',
            'label' => 'LBL_SAVE_BUTTON_LABEL',
            'css_class' => 'btn btn-primary',
            'showOn' => 'edit',
            'acl_action' => 'edit',
        ),
        array(
            'type' => 'actiondropdown',
            'name' => 'main_dropdown',
            'primary' => true,
            'showOn' => 'view',
            'buttons' => array(
                array(
                    'type' => 'rowaction',
                    'event' => 'button:edit_button:click',
                    'name' => 'edit_button',
                    'label' => 'LBL_EDIT_BUTTON_LABEL',
                    'primary' => true,
                    'acl_action' => 'edit',
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:delete_button:click',
                    'name' => 'delete_button',
                    'label' => 'LBL_DELETE_BUTTON_LABEL',
                    'acl_action' => 'delete',
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:duplicate_button:click',
                    'name' => 'duplicate_button',
                    'label' => 'LBL_DUPLICATE_BUTTON_LABEL',
                    'acl_action' => 'create',
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:convert_to_quote:click',
                    'name' => 'convert_to_quote_button',
                    'label' => 'LBL_CONVERT_TO_QUOTE',
                    'acl_action' => 'view',
                ),
            ),
        ),
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
    'panels' => array(
        array(
            'name' => 'panel_header',
            'header' => true,
            'fields' => array(
                array(
                    'name' => 'name',
                    'required' => true,
                ),
                array(
                    'type' => 'follow',
                    'readonly' => true,
                ),
            ),
        ),
        array(
            'name' => 'panel_body',
            'columns' => 2,
            'labels' => true,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                'opportunity_name',
                array(
                    'name' => 'account_name',
                    'readonly' => true,
                ),
                'sales_stage',
                'probability',
                'commit_stage',
                'sales_status',
                array(
                    'name' => 'date_closed',
                    'required' => true,
                ),
                'product_template_name',
                array(
                    'name' => 'category_name',
                    'label' => 'LBL_CATEGORY'
                ),
                'quantity',
                array(
                    'name' => 'discount_price',
                    'type' => 'currency',
                    'convertToBase' => true,
                    'showTransactionalAmount' => true
                ),
                array(
                    'name' => 'discount_amount',
                    'type' => 'currency',
                    'related_fields' => array(
                        'discount_amount',
                        'currency_id',
                        'base_rate',
                    ),
                    'convertToBase' => true,
                    'showTransactionalAmount' => true,
                    'currency_field' => 'currency_id',
                    'base_rate_field' => 'base_rate',
                ),
                array(
                    'name' => 'product_line_item_amount',
                    'type' => 'currency',
                    'label' => 'LBL_CALCULATED_LINE_ITEM_AMOUNT',
                    'readonly' => true,
                    'convertToBase' => true,
                    'showTransactionalAmount' => true,
                ),
                array(
                    'name' => 'likely_case',
                    'required' => true,
                    'type' => 'currency',
                    'related_fields' => array(
                        'likely_case',
                        'currency_id',
                        'base_rate',
                    ),
                    'convertToBase' => true,
                    'showTransactionalAmount' => true,
                    'currency_field' => 'currency_id',
                    'base_rate_field' => 'base_rate',
                ),
                array(
                    'name' => 'quote_name',
                    'label' => 'LBL_ASSOCIATED_QUOTE',
                    'related_fields' => array('quote_id'),
                    // this is a hack to get the quote_id field loaded
                    'readonly' => true,
                    'bwcLink' => true,
                ),
            ),
        ),
        array(
            'name' => 'panel_hidden',
            'hide' => true,
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                array(
                    'name' => 'best_case',
                    'type' => 'currency',
                    'related_fields' => array(
                        'best_case',
                        'currency_id',
                        'base_rate',
                    ),
                    'currency_field' => 'currency_id',
                    'base_rate_field' => 'base_rate',
                ),
                array(
                    'name' => 'worst_case',
                    'type' => 'currency',
                    'related_fields' => array(
                        'worst_case',
                        'currency_id',
                        'base_rate',
                    ),
                    'currency_field' => 'currency_id',
                    'base_rate_field' => 'base_rate',
                ),
                'next_step',
                'product_type',
                'lead_source',
                'campaign_name',
                'assigned_user_name',
                //BEGIN SUGARCRM flav=pro ONLY
                'team_name',
                //END SUGARCRM flav=pro ONLY
                array(
                    'name' => 'description',
                    'span' => 12,
                ),
                array(
                    'name' => 'list_price',
                    'readonly' => true,
                ),
                array(
                    'name' => 'tax_class',
                    'readonly' => true,
                ),
                array(
                    'name' => 'cost_price',
                    'readonly' => true,
                ),
            ),
        ),
    ),
);
