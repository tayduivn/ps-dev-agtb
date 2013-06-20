<?php

$viewdefs['Tasks']['base']['filter']['default'] = array(
    'default_filter' => 'assigned_to_me',
    'fields' => array(
        'name' => array(),
        'contact_name_related' => array(
            'dbFields' => array(
                'contacts.first_name',
                'contacts.last_name',
            ),
            'type' => 'text',
            'vname' => 'LBL_CONTACT_NAME',
        ),
        'status' => array(),
        'date_entered' => array(),
        'date_modified' => array(),
        'date_start' => array(),
        'date_due' => array(),
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
