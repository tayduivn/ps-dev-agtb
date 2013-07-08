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
    'tree' => array(
        array(
            'type' => 'reportingUsers',
            'acl_action' => 'is_manager'
        )
    ),
    'buttons' => array(
        array(
            'name' => 'save_draft_button',
            'events' => array(
                'click' => 'function(e){
                    this.view.context.trigger("button:save_draft_button:click");
                    }'
            ),
            'type' => 'button',
            'label' => 'LBL_SAVE_DRAFT',
            'acl_action' => 'current_user',
        ),
        array(
            'type' => 'actiondropdown',
            'name' => 'main_dropdown',
            'primary' => true,
            'buttons' => array(
                array(
                    'name' => 'commit_button',
                    'type' => 'button',
                    'label' => 'LBL_QC_COMMIT_BUTTON',
                    'events' => array(
                        'click' => 'function(e){
                            this.view.context.trigger("button:commit_button:click");
                        }'
                    ),
                    'css_class' => 'btn-primary',
                    'icon' => 'icon-upload',
                    'acl_action' => 'current_user',
                    'primary' => true
                ),
                array(
                    'name' => 'assign_quota',
                    'type' => 'assignquota',
                    'label' => 'LBL_ASSIGN_QUOTA_BUTTON',
                    'events' => array(
                        'click' => 'function(e){
                            this.view.context.trigger("button:assign_quota:click");
                        }'
                    ),
                    'acl_action' => 'manager_current_user',
                ),
                array(
                    'name' => 'export_button',
                    'type' => 'rowaction',
                    'label' => 'LBL_EXPORT_CSV',
                    'event' => 'button:export_button:click',
                ),
                array(
                    'name' => 'settings_button',
                    'type' => 'rowaction',
                    'label' => 'LBL_FORECAST_SETTINGS',
                    'events' => array(
                        'click' => 'function(e){
                            this.view.context.trigger("button:settings_button:click");
                        }'
                    ),
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
