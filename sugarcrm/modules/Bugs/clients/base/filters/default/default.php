<?php

$viewdefs['Bugs']['base']['filter']['default'] = array(
    'quicksearch_field' => array('name', 'bug_number'),
    'quicksearch_priority' => 2,
    'default_filter' => 'all_records',
    'fields' => array(
        'name' => array(),
        'status' => array(),
        'priority' => array(),
        'found_in_release' => array(),
        'fixed_in_release' => array(),
        'resolution' => array(),
        'bug_number' => array(),
        'date_entered' => array(),
        'date_modified' => array(),
        'assigned_user_id' => array(),
        'assigned_user_name' => array(),
        '$owner' => array(
            'predefined_filter' => true,
            'vname' => 'LBL_CURRENT_USER_FILTER',
        ),
        '$favorite' => array(
            'predefined_filter' => true,
            'vname' => 'LBL_FAVORITES_FILTER',
        ),
    ),
);
