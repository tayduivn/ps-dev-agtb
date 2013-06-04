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
        'name' => 'product_template_name',
        'required' => true,
    ),
    array(
        'name' => 'spacer',  // we need this for when forecasts is not setup and we also need to remove the spacer
        'span' => 6,
        'readonly' => true
    ),
    'account_name',
    'status',
    'quantity',
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
    ),
    array(
        'name' => 'discount_price',
        'type' => 'currency',
        'related_fields' => array(
            'discount_price',
            'currency_id',
            'base_rate',
        ),
        'convertToBase' => true,
        'showTransactionalAmount' => true,
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
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
        'name' => 'discount_rate_percent',
        'readonly' => true,
    ),
);

$fieldsHidden = array(
    'serial_number',
    'contact_name',
    'asset_number',
    'date_purchased',
    array(
        'name' => 'book_value',
        'type' => 'currency',
        'related_fields' => array(
            'book_value',
            'currency_id',
            'base_rate',
        ),
        'convertToBase' => true,
        'showTransactionalAmount' => true,
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
    ),
    'date_support_starts',
    'book_value_date',
    'date_support_expires',
    'website',
    'tax_class',
    'manufacturer_name',
    'weight',
    'mft_part_num',
    array(
        'name' => 'category_name',
        'type' => 'productCategoriesRelate',
        'label' => 'LBL_CATEGORY',
        'readonly' => true
    ),
    'vendor_part_num',
    'product_type',
    array(
        'name' => 'description',
        'span' => 12,
    ),
    'support_name',
    'support_contact',
    'support_description',
    'support_term',
    'date_entered',
    'date_modified',
);
//END SUGARCRM flav=pro && flav!=ent ONLY

//BEGIN SUGARCRM flav=ent ONLY
// ENT/ULT only fields
$fields = array(
    array(
        'name' => 'opportunity_name',
        'required' => true
    ),
    array(
        'name' => 'account_name',
        'readonly' => true,
    ),
    'sales_stage',
    'probability',
    array(
        'name' => 'date_closed',
        'required' => true,
    ),
    array(
        'name' => 'dc_spacer',  // we need this for when forecasts is not setup and we also need to remove the spacer
        'span' => 6,
        'readonly' => true
    ),
    array(
        'name' => 'commit_stage',
        'span' => 6
    ),
    array(
        'name' => 'cs_spacer',  // we need this for when forecasts is not setup and we also need to remove the spacer
        'span' => 6,
        'readonly' => true
    ),
    'product_template_name',
    array(
        'name' => 'category_name',
        'type' => 'relate',
        'label' => 'LBL_CATEGORY',
    ),
    'quantity',
    array(
        'name' => 'discount_price',
        'type' => 'currency',
        'related_fields' => array(
            'discount_price',
            'currency_id',
            'base_rate',
        ),
        'convertToBase' => true,
        'showTransactionalAmount' => true,
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
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
        'name' => 'total_amount',
        'type' => 'currency',
        'label' => 'LBL_CALCULATED_LINE_ITEM_AMOUNT',
        'readonly' => true,
        'related_fields' => array(
            'total_amount',
            'currency_id',
            'base_rate',
        ),
        'convertToBase' => true,
        'showTransactionalAmount' => true,
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
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
);

$fieldsHidden = array(
    array(
        'name' => 'best_case',
        'type' => 'currency',
        'related_fields' => array(
            'best_case',
            'currency_id',
            'base_rate',
        ),
        'convertToBase' => true,
        'showTransactionalAmount' => true,
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
        'convertToBase' => true,
        'showTransactionalAmount' => true,
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
    ),
    'next_step',
    'product_type',
    'lead_source',
    'campaign_name',
    'assigned_user_name',
    'team_name',
    array(
        'name' => 'description',
        'span' => 12,
    ),
    array(
        'name' => 'list_price',
        'readonly' => true,
        'type' => 'currency',
        'related_fields' => array(
            'list_price',
            'currency_id',
            'base_rate',
        ),
        'convertToBase' => true,
        'showTransactionalAmount' => true,
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
    ),
    'tax_class',
    array(
        'name' => 'cost_price',
        'readonly' => true,
        'type' => 'currency',
        'related_fields' => array(
            'cost_price',
            'currency_id',
            'base_rate',
        ),
        'convertToBase' => true,
        'showTransactionalAmount' => true,
        'currency_field' => 'currency_id',
        'base_rate_field' => 'base_rate',
    ),
);
//END SUGARCRM flav=ent ONLY

$viewdefs['Products']['base']['view']['create-actions'] = array(
    'type' => 'record',
    'buttons' => array(
        array(
            'name'    => 'cancel_button',
            'type'    => 'button',
            'label'   => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
        ),
        array(
            'name'    => 'restore_button',
            'type'    => 'button',
            'label'   => 'LBL_RESTORE',
            'css_class' => 'btn-invisible btn-link',
            'showOn' => 'select',
        ),
        array(
            'type' => 'actiondropdown',
            'name' => 'main_dropdown',
            'primary' => true,
            'buttons' => array(
                array(
                    'type' => 'rowaction',
                    'name' => 'save_button',
                    'label' => 'LBL_SAVE_BUTTON_LABEL',
                ),
                array(
                    'type' => 'rowaction',
                    'name' => 'save_view_button',
                    'label' => 'LBL_SAVE_AND_VIEW',
                    'showOn' => 'create',
                ),
                array(
                    'type' => 'rowaction',
                    'name' => 'save_create_button',
                    'label' => 'LBL_SAVE_AND_CREATE_ANOTHER',
                    'showOn' => 'create',
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
                    'label' => 'LBL_MODULE_NAME_SINGULAR'
                ),
            )
        ),
        array(
            'name' => 'panel_body',
            'columns' => 2,
            'labels' => true,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => $fields
        ),
        array(
            'name' => 'panel_hidden',
            'hide' => true,
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => $fieldsHidden
        )
    ),
);
