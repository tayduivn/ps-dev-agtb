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

$viewdefs['Shifts']['base']['view']['record'] = [
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
            'label' => 'LBL_RECORD_HEADER',
            'header' => true,
            'fields' => [
                [
                    'name' => 'picture',
                    'type' => 'avatar',
                    'width' => 42,
                    'height' => 42,
                    'dismiss_label' => true,
                    'readonly' => true,
                ],
                'name',
                [
                    'name' => 'favorite',
                    'label' => 'LBL_FAVORITE',
                    'type' => 'favorite',
                    'readonly' => true,
                    'dismiss_label' => true,
                ],
            ],
        ],
        [
            'name' => 'panel_body',
            'label' => 'LBL_RECORD_BODY',
            'columns' => 2,
            'placeholders' => true,
            'fields' => [
                [
                    'name' => 'timezone',
                    'span' => 12,
                ],
            ],
        ],
        [
            'name' => 'shift_hours',
            'columns' => 3,
            'placeholders' => true,
            'label' => 'LBL_RECORD_BODY',
            'fields' => [
                [
                    'name' => 'is_open_sunday',
                    'type' => 'bool',
                    'label' => 'LBL_SUNDAY_HOURS',
                ],
                [
                    'name' => 'sunday_open',
                    'type' => 'timeselect',
                    'fields' => [
                        'sunday_open_hour',
                        'sunday_open_minutes',
                    ],
                    'label' => 'LBL_SUNDAY_OPEN_TIME',
                    'default_times' => [
                        'hour' => 12,
                        'minute' => 0,
                    ],
                ],
                [
                    'name' => 'sunday_close',
                    'type' => 'timeselect',
                    'fields' => [
                        'sunday_close_hour',
                        'sunday_close_minutes',
                    ],
                    'label' => 'LBL_SUNDAY_CLOSE_TIME',
                    'default_times' => [
                        'hour' => 12,
                        'minute' => 0,
                    ],
                ],
                // Monday
                [
                    'name' => 'is_open_monday',
                    'type' => 'bool',
                    'label' => 'LBL_MONDAY_HOURS',
                ],
                [
                    'name' => 'monday_open',
                    'type' => 'timeselect',
                    'fields' => [
                        'monday_open_hour',
                        'monday_open_minutes',
                    ],
                    'label' => 'LBL_MONDAY_OPEN_TIME',
                    'default_times' => [
                        'hour' => 8,
                        'minute' => 0,
                    ],
                ],
                [
                    'name' => 'monday_close',
                    'type' => 'timeselect',
                    'fields' => [
                        'monday_close_hour',
                        'monday_close_minutes',
                    ],
                    'label' => 'LBL_MONDAY_CLOSE_TIME',
                    'default_times' => [
                        'hour' => 17,
                        'minute' => 0,
                    ],
                ],
                // Tuesday
                [
                    'name' => 'is_open_tuesday',
                    'type' => 'bool',
                    'label' => 'LBL_TUESDAY_HOURS',
                ],
                [
                    'name' => 'tuesday_open',
                    'type' => 'timeselect',
                    'fields' => [
                        'tuesday_open_hour',
                        'tuesday_open_minutes',
                    ],
                    'label' => 'LBL_TUESDAY_OPEN_TIME',
                    'default_times' => [
                        'hour' => 8,
                        'minute' => 0,
                    ],
                ],
                [
                    'name' => 'tuesday_close',
                    'type' => 'timeselect',
                    'fields' => [
                        'tuesday_close_hour',
                        'tuesday_close_minutes',
                    ],
                    'label' => 'LBL_TUESDAY_CLOSE_TIME',
                    'default_times' => [
                        'hour' => 17,
                        'minute' => 0,
                    ],
                ],
                // Wednesday
                [
                    'name' => 'is_open_wednesday',
                    'type' => 'bool',
                    'label' => 'LBL_WEDNESDAY_HOURS',
                ],
                [
                    'name' => 'wednesday_open',
                    'type' => 'timeselect',
                    'fields' => [
                        'wednesday_open_hour',
                        'wednesday_open_minutes',
                    ],
                    'label' => 'LBL_WEDNESDAY_OPEN_TIME',
                    'default_times' => [
                        'hour' => 8,
                        'minute' => 0,
                    ],
                ],
                [
                    'name' => 'wednesday_close',
                    'type' => 'timeselect',
                    'fields' => [
                        'wednesday_close_hour',
                        'wednesday_close_minutes',
                    ],
                    'label' => 'LBL_WEDNESDAY_CLOSE_TIME',
                    'default_times' => [
                        'hour' => 17,
                        'minute' => 0,
                    ],
                ],
                // Thursday
                [
                    'name' => 'is_open_thursday',
                    'type' => 'bool',
                    'label' => 'LBL_THURSDAY_HOURS',
                ],
                [
                    'name' => 'thursday_open',
                    'type' => 'timeselect',
                    'fields' => [
                        'thursday_open_hour',
                        'thursday_open_minutes',
                    ],
                    'label' => 'LBL_THURSDAY_OPEN_TIME',
                    'default_times' => [
                        'hour' => 8,
                        'minute' => 0,
                    ],
                ],
                [
                    'name' => 'thursday_close',
                    'type' => 'timeselect',
                    'fields' => [
                        'thursday_close_hour',
                        'thursday_close_minutes',
                    ],
                    'label' => 'LBL_THURSDAY_CLOSE_TIME',
                    'default_times' => [
                        'hour' => 17,
                        'minute' => 0,
                    ],
                ],
                // Friday
                [
                    'name' => 'is_open_friday',
                    'type' => 'bool',
                    'label' => 'LBL_FRIDAY_HOURS',
                ],
                [
                    'name' => 'friday_open',
                    'type' => 'timeselect',
                    'fields' => [
                        'friday_open_hour',
                        'friday_open_minutes',
                    ],
                    'label' => 'LBL_FRIDAY_OPEN_TIME',
                    'default_times' => [
                        'hour' => 8,
                        'minute' => 0,
                    ],
                ],
                [
                    'name' => 'friday_close',
                    'type' => 'timeselect',
                    'fields' => [
                        'friday_close_hour',
                        'friday_close_minutes',
                    ],
                    'label' => 'LBL_FRIDAY_CLOSE_TIME',
                    'default_times' => [
                        'hour' => 17,
                        'minute' => 0,
                    ],
                ],
                // Saturday
                [
                    'name' => 'is_open_saturday',
                    'type' => 'bool',
                    'label' => 'LBL_SATURDAY_HOURS',
                ],
                [
                    'name' => 'saturday_open',
                    'type' => 'timeselect',
                    'fields' => [
                        'saturday_open_hour',
                        'saturday_open_minutes',
                    ],
                    'label' => 'LBL_SATURDAY_OPEN_TIME',
                    'default_times' => [
                        'hour' => 12,
                        'minute' => 0,
                    ],
                ],
                [
                    'name' => 'saturday_close',
                    'type' => 'timeselect',
                    'fields' => [
                        'saturday_close_hour',
                        'saturday_close_minutes',
                    ],
                    'label' => 'LBL_SATURDAY_CLOSE_TIME',
                    'default_times' => [
                        'hour' => 12,
                        'minute' => 0,
                    ],
                ],
            ],
        ],
        [
            'name' => 'panel_hidden',
            'label' => 'LBL_SHOW_MORE',
            'hide' => true,
            'columns' => 2,
            'placeholders' => true,
            'fields' => [
                'team_name',
                'assigned_user_name',
                [
                    'name' => 'tag',
                    'span' => 12,
                ],
                [
                    'name' => 'description',
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
    ],
];
