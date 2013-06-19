<?php
$viewdefs['CampaignLog']['base']['filter']['basic'] = array(
    'create'               => false,
    'quicksearch_field'    => array('name'),
    'quicksearch_priority' => 1,
    'filters'              => array(
        array(
            'id'                => 'all_records',
            'name'              => 'LBL_LISTVIEW_FILTER_ALL',
            'filter_definition' => array(),
            'editable'          => false
        ),
    ),
);
