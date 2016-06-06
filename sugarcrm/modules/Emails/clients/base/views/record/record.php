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
$viewdefs['Emails']['base']['view']['record'] = array(
    'buttons' => array(
        array(
            'type' => 'actiondropdown',
            'name' => 'main_dropdown',
            'primary' => true,
            'showOn' => 'view',
            'buttons' => array(
                array(
                    'name' => 'reply_button',
                    'type' => 'rowaction',
                    'event' => 'button:reply_button:click',
                    'label' => 'LBL_BUTTON_REPLY',
                    'acl_action' => 'view',
                ),
                array(
                    'name' => 'forward_button',
                    'type' => 'rowaction',
                    'event' => 'button:forward_button:click',
                    'label' => 'LBL_BUTTON_FORWARD',
                    'acl_action' => 'view',
                ),
                array(
                    'name' => 'delete_button',
                    'type' => 'rowaction',
                    'event' => 'button:delete_button:click',
                    'label' => 'LBL_DELETE_BUTTON',
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
                    'name' => 'picture',
                    'type' => 'avatar',
                    'size' => 'large',
                    'dismiss_label' => true,
                    'readonly' => true,
                ),
                'name',
                array(
                    'name' => 'favorite',
                    'label' => 'LBL_FAVORITE',
                    'type' => 'favorite',
                    'dismiss_label' => true,
                ),
            ),
        ),
        array(
            'name' => 'panel_body',
            'label' => 'LBL_RECORD_BODY',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                'from_addr_name',
                'assigned_user_name',
                'to_addrs_names',
                'team_name',
                'cc_addrs_names',
                'date_sent',
                'bcc_addrs_names',
                'parent_name',
                array(
                    'name' => 'description_html',
                    'type' => 'htmleditable_tinymce',
                    'dismiss_label' => true,
                    'span' => 12,
                ),
                array(
                    'name' => 'description',
                    'span' => 12,
                ),
            ),
        ),
    ),
);
