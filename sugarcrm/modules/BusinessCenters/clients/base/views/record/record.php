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

$viewdefs['BusinessCenters']['base']['view']['record'] = [
    'buttons' => [
        [
            'type' => 'button',
            'name' => 'cancel_button',
            'label' => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
            'showOn' => 'edit',
            'events' => [
                'click' => 'button:cancel_button:click',
            ],
        ],
        [
            'type' => 'rowaction',
            'event' => 'button:save_button:click',
            'name' => 'save_button',
            'label' => 'LBL_SAVE_BUTTON_LABEL',
            'css_class' => 'btn btn-primary',
            'showOn' => 'edit',
            'acl_action' => 'edit',
        ],
        [
            'type' => 'actiondropdown',
            'name' => 'main_dropdown',
            'primary' => true,
            'showOn' => 'view',
            'buttons' => [
                [
                    'type' => 'rowaction',
                    'event' => 'button:edit_button:click',
                    'name' => 'edit_button',
                    'label' => 'LBL_EDIT_BUTTON_LABEL',
                    'acl_action' => 'edit',
                ],
                [
                    'type' => 'shareaction',
                    'name' => 'share',
                    'label' => 'LBL_RECORD_SHARE_BUTTON',
                    'acl_action' => 'view',
                ],
                [
                    'type' => 'divider',
                ],
                [
                    'type' => 'rowaction',
                    'event' => 'button:find_duplicates_button:click',
                    'name' => 'find_duplicates_button',
                    'label' => 'LBL_DUP_MERGE',
                    'acl_action' => 'edit',
                ],
                [
                    'type' => 'rowaction',
                    'event' => 'button:duplicate_button:click',
                    'name' => 'duplicate_button',
                    'label' => 'LBL_DUPLICATE_BUTTON_LABEL',
                    'acl_module' => 'BusinessCenters',
                    'acl_action' => 'create',
                ],
                [
                    'type' => 'rowaction',
                    'event' => 'button:audit_button:click',
                    'name' => 'audit_button',
                    'label' => 'LNK_VIEW_CHANGE_LOG',
                    'acl_action' => 'view',
                ],
                [
                    'type' => 'divider',
                ],
                [
                    'type' => 'rowaction',
                    'event' => 'button:delete_button:click',
                    'name' => 'delete_button',
                    'label' => 'LBL_DELETE_BUTTON_LABEL',
                    'acl_action' => 'delete',
                ],
            ],
        ],
        [
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ],
    ],
    'panels' => [
        [
            'name' => 'panel_header',
            'label' => 'LBL_PANEL_HEADER',
            'header' => true,
            'fields' => [
                [
                    'name' => 'picture',
                    'type' => 'avatar',
                    'size' => 'large',
                    'dismiss_label' => true,
                    'readonly' => true,
                ],
                [
                    'name' => 'name',
                ],
                [
                    'name' => 'favorite',
                    'label' => 'LBL_FAVORITE',
                    'type' => 'favorite',
                    'dismiss_label' => true,
                ],
            ],
        ],
        [
            'name' => 'panel_body',
            'label' => 'LBL_RECORD_BODY',
            'columns' => 3,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => [
                [
                    'name' => 'timezone',
                    'span' => 12,
                ],
                [
                    'name' => 'address',
                    'type' => 'fieldset',
                    'css_class' => 'address',
                    'label' => 'LBL_ADDRESS',
                    'fields' => [
                        [
                            'name' => 'address_street',
                            'css_class' => 'address_street',
                            'placeholder' => 'LBL_ADDRESS_STREET',
                        ],
                        [
                            'name' => 'address_city',
                            'css_class' => 'address_city',
                            'placeholder' => 'LBL_ADDRESS_CITY',
                        ],
                        [
                            'name' => 'address_state',
                            'css_class' => 'address_state',
                            'placeholder' => 'LBL_ADDRESS_STATE',
                        ],
                        [
                            'name' => 'address_postalcode',
                            'css_class' => 'address_zip',
                            'placeholder' => 'LBL_ADDRESS_POSTALCODE',
                        ],
                        [
                            'name' => 'address_country',
                            'css_class' => 'address_country',
                            'placeholder' => 'LBL_ADDRESS_COUNTRY',
                        ],
                    ],
                ],
                'team_name',
                'assigned_user_name',
                [
                    'name' => 'tag',
                    'span' => 12,
                ],
                [
                    'name' => 'date_entered_by',
                    'readonly' => true,
                    'inline' => true,
                    'type' => 'fieldset',
                    'label' => 'LBL_DATE_ENTERED',
                    'fields' => [
                        [
                            'name' => 'date_entered',
                        ],
                        [
                            'type' => 'label',
                            'default_value' => 'LBL_BY',
                        ],
                        [
                            'name' => 'created_by_name',
                        ],
                    ],
                ],
                [
                    'name' => 'date_modified_by',
                    'readonly' => true,
                    'inline' => true,
                    'type' => 'fieldset',
                    'label' => 'LBL_DATE_MODIFIED',
                    'fields' => [
                        [
                            'name' => 'date_modified',
                        ],
                        [
                            'type' => 'label',
                            'default_value' => 'LBL_BY',
                        ],
                        [
                            'name' => 'modified_by_name',
                        ],
                    ],
                ],
            ],
        ],
        [
            //'newTab' => false,
            'panelDefault' => 'expanded',
            'name' => 'business_hours',
            'label' => 'LBL_RECORD_BUSINESS_HOURS_PANEL_HEADER',
            'columns' => 2,
            'labelsOnTop' => 1,
            'placeholders' => 1,
            'fields' => [
                // Sunday
                'is_open_sunday',
                '',
                'sunday_open_hour',
                'sunday_open_minutes',
                'sunday_closed_hour',
                'sunday_closed_minutes',
                // Monday
                'is_open_monday',
                '',
                'monday_open_hour',
                'monday_open_minutes',
                'monday_closed_hour',
                'monday_closed_minutes',
                // Tuesday
                'is_open_tuesday',
                '',
                'tuesday_open_hour',
                'tuesday_open_minutes',
                'tuesday_closed_hour',
                'tuesday_closed_minutes',
                // Wednesday
                'is_open_wednesday',
                '',
                'wednesday_open_hour',
                'wednesday_open_minutes',
                'wednesday_closed_hour',
                'wednesday_closed_minutes',
                // Thursday
                'is_open_thursday',
                '',
                'thursday_open_hour',
                'thursday_open_minutes',
                'thursday_closed_hour',
                'thursday_closed_minutes',
                // Friday
                'is_open_friday',
                '',
                'friday_open_hour',
                'friday_open_minutes',
                'friday_closed_hour',
                'friday_closed_minutes',
                // Saturday
                'is_open_saturday',
                '',
                'saturday_open_hour',
                'saturday_open_minutes',
                'saturday_closed_hour',
                'saturday_closed_minutes',
            ],
        ],
    ],
];
