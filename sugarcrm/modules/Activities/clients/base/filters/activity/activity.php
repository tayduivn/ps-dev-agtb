<?php

// Hack for ActivityStream. MetadataManager does not supporrt submodules yet.

$viewdefs['Activities']['base']['filter']['activity'] = array(
    'create'               => false,
    'quicksearch_field'    => array(),
    'quicksearch_priority' => 2,
    'filters'              => array(
        array(
            'id'                => 'all_records',
            'name'              => 'LBL_LISTVIEW_FILTER_ALL',
            'filter_definition' => array(),
            'editable'          => false
        ),
        array(
            'id'                => 'messages_for_create',
            'name'              => 'Messages for Create',
            'filter_definition' => array(
                '$or' => array(
                    array('activity_type' => 'create'),
                    array('activity_type' => 'attach'),
                ),
            ),
            'editable'          => false
        ),
        array(
            'id'                => 'messages_for_update',
            'name'              => 'Messages for Update',
            'filter_definition' => array(
                'activity_type' => 'update',
            ),
            'editable'          => false
        ),
        array(
            'id'                => 'messages_for_link',
            'name'              => 'Messages for link',
            'filter_definition' => array(
                'activity_type' => 'link',
            ),
            'editable'          => false
        ),
        array(
            'id'                => 'messages_for_unlink',
            'name'              => 'Messages for unlink',
            'filter_definition' => array(
                'activity_type' => 'unlink',
            ),
            'editable'          => false
        ),
        array(
            'id'                => 'messages_for_post',
            'name'              => 'Messages for post',
            'filter_definition' => array(
                'activity_type' => 'post',
            ),
            'editable'          => false
        ),
    ),
);
