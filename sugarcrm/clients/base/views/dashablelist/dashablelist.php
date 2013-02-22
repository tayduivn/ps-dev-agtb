<?php

$viewdefs['base']['view']['dashablelist'] = array(
    'dashlets' => array(
        array(
            'name' => 'My Accounts',
            'description' => 'Listing of your accounts',
            'config' => array(
                'module' => 'Accounts',
                'display_columns' => array('name','phone_office'),
                'my_items' => '1',
            ),
            'preview' => array(
                'module' => 'Accounts',
                'display_columns' => array('name','phone_office'),
                'my_items' => '1',
            )
        ),
        array(
            'name' => 'My Assigned Bugs',
            'description' => 'Bugs assigned to you',
            'config' => array(
                'module' => 'Bugs',
                'display_columns' => array(
                    'bug_number', 'name', 'status', 'priority'
                ),
                'my_items' => '1',
            ),
            'preview' => array(
                'module' => 'Bugs',
                'display_columns' => array(
                    'bug_number', 'name', 'status', 'priority'
                ),
                'my_items' => '1',
            )
        ),
        array(
            'name' => 'My Closed Opportunies',
            'description' => 'Opportunies that you have closed',
            'config' => array(
                'module' => 'Opportunities',
                'display_columns' => array(
                    'name', 'account_name', 'amount'
                ),
                'my_items' => '1',
            ),
            'preview' => array(
                'module' => 'Opportunities',
                'display_columns' => array(
                    'name', 'account_name', 'amount'
                ),
                'my_items' => '1',
            ),
        )
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
                        20 => 20
                    )
                ),
                array(
                    'name' => 'auto_refresh',
                    'label' => 'Auto Refresh',
                    'type' => 'enum',
                    'options' => array(
                        -1 => "None",
                        1 => "Every 1 Minutes",
                        5 => "Every 5 Minutes",
                        10 => "Every 10 Minutes",
                        15 => "Every 15 Minutes",
                        30 => "Every 30 Minutes",
                    )
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
    )
);
