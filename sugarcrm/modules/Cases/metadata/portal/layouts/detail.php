<?php
$viewdefs['Cases']['portal']['layout']['detail'] = array(
    'type' => 'rows',
    'components' =>
    array(
        0 =>
        array(
            'view' => 'detail',
        ),
        1 =>
        array(
            'view' => 'activity',
        ),
        4 =>
        array(
            'view' => 'list',
            'context' => array(
                'link' => 'notes',
            ),
        ),
        3 =>
        array(
            'view' => 'subpanel',
        ),
    ),
);