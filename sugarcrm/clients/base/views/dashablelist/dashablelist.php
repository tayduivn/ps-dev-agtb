<?php

$viewdefs['base']['view']['dashablelist'] = array(
    'dashlets' => array(
        array(
            'name' => 'LBL_DASHLET_LISTVIEW_NAME',
            'description' => 'LBL_DASHLET_LISTVIEW_DESCRIPTION',
            'config' => array(
                'module' => 'Accounts',
                'display_columns' => array(
                    'name',
                    'phone_office',
                    'billing_address_country',
                ),
                'my_items' => '1',
            ),
            'preview' => array(
                'module' => 'Accounts',
                'display_columns' => array(
                    'name',
                    'phone_office',
                    'billing_address_country',
                ),
                'my_items' => '1',
            ),
        ),
        array(
            'name' => 'LBL_DASHLET_LISTVIEW_NAME',
            'description' => 'LBL_DASHLET_LISTVIEW_DESCRIPTION',
            'config' => array(
                'module' => 'Bugs',
                'display_columns' => array(
                    'bug_number',
                    'name',
                    'status',
                    'priority',
                ),
                'my_items' => '1',
                'status' => 'Assigned',
            ),
            'preview' => array(
                'module' => 'Bugs',
                'display_columns' => array(
                    'bug_number',
                    'name',
                    'status',
                    'priority',
                ),
                'my_items' => '1',
                'status' => 'Assigned',
            ),
        ),
        array(
            'name' => 'LBL_DASHLET_LISTVIEW_NAME',
            'description' => 'LBL_DASHLET_LISTVIEW_DESCRIPTION',
            'config' => array(
                'module' => 'Opportunities',
                'display_columns' => array(
                    'name',
                    'account_name',
                    'amount',
                    'date_closed',
                ),
                'my_items' => '1',
            ),
            'preview' => array(
                'module' => 'Opportunities',
                'display_columns' => array(
                    'name',
                    'account_name',
                    'amount',
                    'date_closed',
                ),
                'my_items' => '1',
            ),
        ),
        array(
            'name' => 'LBL_DASHLET_LISTVIEW_NAME',
            'description' => 'LBL_DASHLET_LISTVIEW_DESCRIPTION',
            'config' => array(
                'module' => 'Contacts',
                'display_columns' => array(
                    'name',
                    'title',
                    'phone_work',
                    'date_entered',
                    'assigned_user_name',
                ),
                'my_items' => '1',
            ),
            'preview' => array(
                'module' => 'Contacts',
                'display_columns' => array(
                    'name',
                    'title',
                    'phone_work',
                    'date_entered',
                    'assigned_user_name',
                ),
                'my_items' => '1',
            ),
        ),
        array(
            'name' => 'LBL_DASHLET_LISTVIEW_NAME',
            'description' => 'LBL_DASHLET_LISTVIEW_DESCRIPTION',
            'config' => array(
                'module' => 'Leads',
                'display_columns' => array(
                    'name',
                    'phone_work',
                    'email',
                ),
                'my_items' => '1',
            ),
            'preview' => array(
                'module' => 'Leads',
                'display_columns' => array(
                    'name',
                    'phone_work',
                    'email',
                ),
                'my_items' => '1',
            ),
        ),
    ),
    'dashlet_config_panels' => array(
        array(
            'name' => 'panel_body',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                array(
                    'name' => 'display_rows',
                    'label' => 'Display Rows',
                    'type' => 'enum',
                    'options' => array(
                        5 => 5,
                        10 => 10,
                        15 => 15,
                        20 => 20,
                    ),
                ),
                array(
                    'name' => 'auto_refresh',
                    'label' => 'Auto Refresh',
                    'type' => 'enum',
                    'options' => array(
                        0 => "None",
                        1 => "Every 1 Minutes",
                        5 => "Every 5 Minutes",
                        10 => "Every 10 Minutes",
                        15 => "Every 15 Minutes",
                        30 => "Every 30 Minutes",
                    ),
                ),
            ),
        ),
        array(
            'name' => 'panel_module_metadata',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                array(
                    'name' => 'display_columns',
                    'label' => 'Columns',
                    'type' => 'enum',
                    'isMultiSelect' => true,
                    'span' => 12,
                    'hasBlank' => true,
                ),
                array(
                    'name' => 'my_items',
                    'label' => 'Only My Items',
                    'type' => 'enum',
                    'options' => array(
                        '' => '',
                        '1' => 'Yes',
                        '0' => 'No',
                    ),
                ),
                array(
                    'name' => 'favorites',
                    'label' => 'Only Favorite Items',
                    'type' => 'enum',
                    'options' => array(
                        '' => '',
                        '1' => 'Yes',
                        '0' => 'No',
                    ),
                ),
            ),
        ),
    ),
);
