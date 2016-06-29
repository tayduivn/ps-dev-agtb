<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
$viewdefs['Emails']['base']['view']['create'] = array(
    'template' => 'record',
    'buttons' => array(
        array(
            'type' => 'button',
            'name' => 'cancel_button',
            'label' => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
            'events' => array(
                'click' => 'button:cancel_button:click',
            ),
        ),
        array(
            'name' => 'draft_button',
            'type' => 'button',
            'label' => 'LBL_SAVE_AS_DRAFT_BUTTON_LABEL',
            'events' => array(
                'click' => 'button:draft_button:click',
            ),
        ),
        array(
            'name' => 'send_button',
            'type' => 'button',
            'label' => 'LBL_SEND_BUTTON_LABEL',
            'primary' => true,
            'events' => array(
                'click' => 'button:send_button:click',
            ),
        ),
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
    'panels' => array(
        array(
            'name' => 'panel_body',
            'label' => 'LBL_PANEL_2',
            'columns' => 1,
            'labels' => true,
            'labelsOnTop' => false,
            'placeholders' => true,
            'fields' => array(
                array(
                    'name' => 'outbound_email_id',
                    'label' => 'LBL_FROM',
                    'span' => 12,
                    'css_class' => 'inherit-width',
                    'searchBarThreshold' => -1,
                ),
                array(
                    'name' => 'to',
                    'type' => 'email-recipients',
                    'label' => 'LBL_TO_ADDRS',
                    'span' => 12,
                    'fields' => array('name', 'email_address_used'),
                ),
                array(
                    'name' => 'cc',
                    'type' => 'email-recipients',
                    'label' => 'LBL_CC',
                    'span' => 12,
                    'fields' => array('name', 'email_address_used'),
                ),
                array(
                    'name' => 'bcc',
                    'type' => 'email-recipients',
                    'label' => 'LBL_BCC',
                    'span' => 12,
                    'fields' => array('name', 'email_address_used'),
                ),
                array(
                    'name' => 'name',
                    'label' => 'LBL_SUBJECT',
                    'span' => 12,
                    'related_fields' => array('state'),
                ),
                array(
                    'name' => 'description_html',
                    'type' => 'htmleditable_tinymce',
                    'dismiss_label' => true,
                    'span' => 12,
                    'tinyConfig' => array(
                        'toolbar' => 'code | bold italic underline strikethrough | bullist numlist | ' .
                            'alignleft aligncenter alignright alignjustify | forecolor backcolor | ' .
                            'fontsizeselect | formatselect | fontselect | sugarattachment sugarsignature sugartemplate',
                    ),
                ),
                array(
                    'name' => 'attachments',
                    'type' => 'email-attachments',
                    'dismiss_label' => true,
                ),
            ),
        ),
        array(
            'name' => 'panel_hidden',
            'hide' => true,
            'columns' => 1,
            'labelsOnTop' => false,
            'placeholders' => true,
            'fields' => array(
                array(
                    'type' => 'teamset',
                    'name' => 'team_name',
                    'span' => 12,
                ),
                array(
                    'name' => 'parent_name',
                    'span' => 12,
                ),
                array(
                    'name' => 'tag',
                    'span' => '12',
                ),
            ),
        ),
    ),
);
