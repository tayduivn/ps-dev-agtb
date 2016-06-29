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
                array(
                    'name' => 'from',
                    'type' => 'from',
                    'label' => 'LBL_FROM',
                    'readonly' => true,
                    'fields' => array(
                        'name',
                        'email_address_used',
                        'email',
                    ),
                ),
                array(
                    'name' => 'date_sent',
                    'readonly' => true,
                ),
                array(
                    'name' => 'to',
                    'type' => 'email-recipients',
                    'label' => 'LBL_TO',
                    'readonly' => true,
                    'fields' => array(
                        'name',
                        'email_address_used',
                        'email',
                    ),
                    'span' => 12,
                ),
                array(
                    'name' => 'description_html',
                    'type' => 'htmleditable_tinymce',
                    'dismiss_label' => true,
                    'readonly' => true,
                    'span' => 12,
                ),
                array(
                    'name' => 'attachments',
                    'type' => 'email-attachments',
                    'label' => 'LBL_ATTACHMENTS',
                    'readonly' => true,
                    'span' => 12,
                ),
            ),
        ),
        array(
            'name' => 'panel_hidden',
            'label' => 'LBL_RECORD_SHOWMORE',
            'hide' => true,
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                array(
                    'name' => 'cc',
                    'type' => 'email-recipients',
                    'label' => 'LBL_CC',
                    'readonly' => true,
                    'fields' => array(
                        'name',
                        'email_address_used',
                        'email',
                    ),
                ),
                array(
                    'name' => 'bcc',
                    'type' => 'email-recipients',
                    'label' => 'LBL_BCC',
                    'readonly' => true,
                    'fields' => array(
                        'name',
                        'email_address_used',
                        'email',
                    ),
                ),
                array(
                    'name' => 'assigned_user_name',
                    'readonly' => true,
                ),
                array(
                    'name' => 'parent_name',
                    'readonly' => true,
                ),
                array(
                    'name' => 'team_name',
                    'readonly' => true,
                ),
                array(
                    'name' => 'tag',
                    'readonly' => true,
                    'span' => 12,
                ),
            ),
        ),
    ),
);
