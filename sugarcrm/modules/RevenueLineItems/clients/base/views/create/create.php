<?php
//FILE SUGARCRM flav=pro ONLY
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$viewdefs['RevenueLineItems']['base']['view']['create'] = array(
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
            'fields' => array(
                //BEGIN SUGARCRM flav=ent ONLY
                array(
                    'name' => 'opportunity_name',
                    'required' => true
                ),
                //END SUGARCRM flav=ent ONLY
                array(
                    'name' => 'account_name',
                    'readonly' => true
                ),
                //BEGIN SUGARCRM flav=ent ONLY
                'sales_stage',
                'probability',
                //END SUGARCRM flav=ent ONLY
                'sales_status',
                //BEGIN SUGARCRM flav=ent ONLY
                array(
                    'name' => 'date_closed',
                    'required' => true
                ),
                array(
                    'name' => 'commit_stage',
                    'span' => 6
                ),
                //END SUGARCRM flav=ent ONLY
                array(
                    'name' => 'spacer',  // we need this for when forecasts is not setup and we also need to remove the spacer
                    'span' => 6,
                    'readonly' => true
                ),
                'product_template_name',
                array(
                    'name' => 'category_name',
                    'type' => 'productCategoriesRelate',
                    'label' => 'LBL_CATEGORY',
                    'readonly' => true
                ),
                'quantity',
                array(
                    'name' => 'discount_price',
                    'type' => 'currency',
                    'readonly' => true,
                    'related_fields' => array(
                        'discount_price',
                        'currency_id',
                        'base_rate',
                    ),
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
                //BEGIN SUGARCRM flav=ent ONLY
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
                    'currency_field' => 'currency_id',
                    'base_rate_field' => 'base_rate',
                )
                //END SUGARCRM flav=ent ONLY
            ),
        ),
        array(
            'name' => 'panel_hidden',
            'hide' => true,
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                //BEGIN SUGARCRM flav=ent ONLY
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
                //END SUGARCRM flav=ent ONLY
                'next_step',
                'product_type',
                'lead_source',
                'campaign_name',
                'assigned_user_name',
                //BEGIN SUGARCRM flav=pro ONLY
                array(
                    'type' => 'teamset',
                    'name' => 'team_name',
                ),
                //END SUGARCRM flav=pro ONLY
                array(
                    'name' => 'description',
                    'span' => 12
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
                    'currency_field' => 'currency_id',
                    'base_rate_field' => 'base_rate',
                ),
                array(
                    'name' => 'quote_name',
                    'label' => 'LBL_ASSOCIATED_QUOTE',
                    'related_fields' => array('quote_id'),  // this is a hack to get the quote_id field loaded
                    'readonly' => true,
                    'bwcLink' => true
                ),
            )
        )
    ),
);
