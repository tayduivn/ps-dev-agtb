<?php

$viewdefs['Documents']['base']['filter']['default'] = array(
    'default_filter' => 'all_records',
    'quicksearch_field' => array('document_name'),
    'quicksearch_priority' => 2,
    'filters' => array(
        array(
            'id' => 'created_by_me',
            'name' => 'LBL_CREATED_BY_ME',
            'filter_definition' => array(
                '$creator' => '',
            ),
            'editable' => false
        ),
    )
);
