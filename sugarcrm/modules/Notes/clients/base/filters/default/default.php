<?php

$viewdefs['Notes']['base']['filter']['default'] = array(
    'default_filter' => 'all_records',
    'filters' => array(
        array(
            'id' => 'my_notes',
            'name' => translate('LBL_MY_NOTES_DASHLETNAME', 'Notes'),
            'filter_definition' => array(
                '$creator' => '',
            ),
            'editable' => false
        ),
    )
);
