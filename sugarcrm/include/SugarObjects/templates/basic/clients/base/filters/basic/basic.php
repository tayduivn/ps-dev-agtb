<?php

$viewdefs['<module_name>']['base']['filter']['basic'] = array(
    'create' => true,
    'quicksearch_field' => array('name'),
    'filters' => array(
        array(
            'id' => 'all_records',
            'name' => translate('LBL_LISTVIEW_ALL') . ' <module_name>',
            'filter_definition' => array(
                'filter' => array(),
            ),
            'editable' => false
        ),
        array(
            'id' => 'assigned_to_me',
            'name' => 'LBL_ASSIGNED_TO_ME',
            'filter_definition' => array(
                'filter' => array(
                    array(
                        '$owner' => '',
                    ),
                ),
            ),
            'editable' => false
        ),
        array(
            'id' => 'favorites',
            'name' => 'LBL_FAVORITES',
            'filter_definition' => array(
                'filter' => array(
                    array(
                        '$favorite' => '',
                    ),
                ),
            ),
            'editable' => false
        ),
        array(
            'id' => 'recently_viewed',
            'name' => 'LBL_RECENTLY_VIEWED',
            'filter_definition' => array(
                'filter' => array(
                    array(
                        '$tracker' => '-7 DAY',
                    ),
                ),
            ),
            'editable' => false
        ),
        array(
            'id' => 'assigned_to_me',
            'name' => 'LBL_NEW_RECORDS',
            'filter_definition' => array(
                'filter' => array(
                    array(
                        'date_entered' => array(
                            '$fromDays' => '-7',
                        ),
                    ),
                ),
            ),
            'editable' => false
        ),
    ),
);
