<?php

$viewdefs['Opportunities']['base']['filter']['default'] = array(
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
        'amount' => array(),
        'best_case' => array(),
        'worst_case' => array(),
        'next_step' => array(),
        'probability' => array(),
        'lead_source' => array(),
        'opportunity_type' => array(),
        'sales_stage' => array(),
        'date_entered' => array(),
        'date_modified' => array(),
        'date_closed' => array(),
        'assigned_user_id' => array(),
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
