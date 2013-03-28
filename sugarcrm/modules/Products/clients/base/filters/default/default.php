<?php

$viewdefs['Products']['base']['filter']['default'] = array(
    'default_filter' => 'all_records',
    'fields' => array(
        'name' => array(),
        'contact_name_related' => array(
            'dbFields' => array(
                'contact_link.first_name',
                'contact_link.last_name',
            ),
            'type' => 'text',
            'vname' => 'LBL_CONTACT_NAME',
        ),
        'status' => array(),
        'type_id' => array(),
        'category_id' => array(),
        'manufacturer_id' => array(),
        'mft_part_num' => array(),
        'vendor_part_num' => array(),
        'tax_class'=> array(),
        'support_term'=> array(),
        'date_entered' => array(),
        'date_modified' => array(),
        '$favorite' => array(
            'options' => 'filter_predefined_dom',
            'type' => 'bool',
            'vname' => 'LBL_FAVORITES_FILTER',
        ),
    ),
);
