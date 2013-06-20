<?php

$viewdefs['Cases']['base']['filter']['default'] = array(
    'default_filter' => 'all_records',
    'fields' => array(
        'name' => array(),
        'account_name_related' => array(
            'dbFields' => array(
                'accounts.name',
            ),
            'type' => 'text',
            'vname' => 'LBL_ACCOUNT_NAME',
        ),
        'status' => array(),
        'priority' => array(),
        'case_number' => array(),
        'date_entered' => array(),
        'date_modified' => array(),
        'assigned_user_id' => array(),
        'assigned_user_name' => array(),
        '$owner' => array(
            'options' => 'filter_predefined_dom',
            'type' => 'bool',
            'vname' => 'LBL_CURRENT_USER_FILTER',
        ),
        '$favorite' => array(
            'options' => 'filter_predefined_dom',
            'type' => 'bool',
            'vname' => 'LBL_FAVORITES_FILTER',
        ),
    ),
);
