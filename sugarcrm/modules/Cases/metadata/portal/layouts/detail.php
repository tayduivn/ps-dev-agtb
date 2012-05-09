<?php
$viewdefs['Cases']['portal']['layout']['detail'] = array(
    'type' => 'columns',
    'components' =>
    array(
        0 => array(
            'layout' =>
            array(
                'type' => 'leftside',
                'components' =>
                array(
                    0 => array(
                        'view' => 'detail',
                    ),
                    1 => array(
                        'view' => 'activity',
                        'context' => array(
                            'link' => 'notes',
                        ),
                    ),
                ),
            ),
        ),
    ),
);