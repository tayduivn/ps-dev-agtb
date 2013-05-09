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
$viewdefs['ForecastManagerWorksheets']['base']['view']['list'] = array(
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'LBL_PANEL_1',
            'fields' =>
            array(
                array(
                    'name' => 'name',
                    'type' => 'userLink',
                    'label' => 'LBL_NAME',
                    'link' => true,
                    'route' =>
                    array(
                        'recordID' => 'user_id'
                    ),
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'related_fields' => array(
                        'user_id',
                        'isManager',
                        'show_history_log',
                        'draft'
                    )
                ),
                array(
                    'name' => 'quota',
                    'type' => 'currency',
                    'label' => 'LBL_QUOTA',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'convertToBase' => true,
                    'related_fields' => array(
                        'base_rate',
                        'currency_id'
                    ),
                    'align' => 'right',
                    'click_to_edit' => true,
                ),
                array(
                    'name' => 'likely_case',
                    'type' => 'currency',
                    'label' => 'LBL_LIKELY_CASE',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'convertToBase' => true,
                    'related_fields' => array(
                        'base_rate',
                        'currency_id'
                    ),
                    'align' => 'right'
                ),
                array(
                    'name' => 'likely_case_adjusted',
                    'type' => 'currency',
                    'label' => 'LBL_LIKELY_CASE_VALUE',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'convertToBase' => true,
                    'related_fields' => array(
                        'base_rate',
                        'currency_id'
                    ),
                    'align' => 'right',
                    'click_to_edit' => true,
                ),
                array(
                    'name' => 'best_case',
                    'type' => 'currency',
                    'label' => 'LBL_BEST_CASE',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'convertToBase' => true,
                    'related_fields' => array(
                        'base_rate',
                        'currency_id'
                    ),
                    'align' => 'right'
                ),
                array(
                    'name' => 'best_case_adjusted',
                    'type' => 'currency',
                    'label' => 'LBL_BEST_CASE_VALUE',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'convertToBase' => true,
                    'related_fields' => array(
                        'base_rate',
                        'currency_id'
                    ),
                    'align' => 'right',
                    'click_to_edit' => true,
                ),
                array(
                    'name' => 'worst_case',
                    'type' => 'currency',
                    'label' => 'LBL_WORST_CASE',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'convertToBase' => true,
                    'related_fields' => array(
                        'base_rate',
                        'currency_id'
                    ),
                    'align' => 'right'
                ),
                array(
                    'name' => 'worst_case_adjusted',
                    'type' => 'currency',
                    'label' => 'LBL_WORST_CASE_VALUE',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'convertToBase' => true,
                    'related_fields' => array(
                        'base_rate',
                        'currency_id'
                    ),
                    'align' => 'right',
                    'click_to_edit' => true,
                )
            ),
        ),
    ),
);
