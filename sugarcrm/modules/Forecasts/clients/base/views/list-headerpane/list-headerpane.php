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

$viewdefs['Forecasts']['base']['view']['list-headerpane'] = array(
    'type' => 'headerpane',
    'tree' => array(
        array(
            'type' => 'reportingUsers',
            'acl_action' => 'is_manager'
        )
    ),
    'buttons' => array(
        array(
            'name' => 'draft_button',
            'event' => 'button:draft_button:click',
            'type' => 'button',
            'label' => 'LBL_SAVE_DRAFT',
            'acl_action' => 'current_user',
            'css_class' => 'disabled'
        ),
        array(
            'name' => 'commit_button',
            'type' => 'button',
            'event' => 'button:commit_button:click',
            'label' => 'LBL_QC_COMMIT_BUTTON',
            'css_class' => 'btn-primary disabled',
            'icon' => 'icon-upload',
            'acl_action' => 'current_user'
        ),
        array(
            'type' => 'actiondropdown',
            'name' => 'main_dropdown',
            'primary' => false,
            'buttons' => array(
                array(
                    'type' => 'rowaction',
                    'event' => 'button:export_button:click',
                    'name' => 'export_button',
                    'label' => 'LBL_EXPORT_CSV',
                    'primary' => true
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:settings_button:click',
                    'name' => 'settings_button',
                    'label' => 'LBL_FORECAST_SETTINGS',
                    'acl_action' => 'admin'
                ),
                array(
                    'type' => 'rowaction',
                    'event' => 'button:print_button:click',
                    'name' => 'print_button',
                    'label' => 'LBL_PRINT',
                ),
            ),
        ),
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
);
