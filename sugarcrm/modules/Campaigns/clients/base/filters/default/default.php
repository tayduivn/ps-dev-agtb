<?php
$viewdefs['Campaigns']['base']['filter']['default'] = array(
    'default_filter' => 'all_records',
    'fields' => array(
        'name' => array(),
        'status' => array(),
        'campaign_type' => array(),
        'start_date' => array(),
        'end_date' => array(),
        'date_entered' => array(),
        'date_modified' => array(),
        '$owner' => array(
            'options' => 'filter_predefined_dom',
            'type' => 'bool',
            'vname' => 'LBL_CURRENT_USER_FILTER',
        ),
        'assigned_user_id' => array(),
        '$favorite' => array(
            'options' => 'filter_predefined_dom',
            'type' => 'bool',
            'vname' => 'LBL_FAVORITES_FILTER',
        ),
    ),
);
