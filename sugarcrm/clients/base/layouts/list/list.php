<?php
$viewdefs['base']['layout']['list'] = array(
    'type' => 'fluid',
    'components' =>
    array(
        0 =>
        array(
            'view' => 'list-top',
        ),
        1 =>
        array(
            'view' => 'filter',
        ),
        2 =>
        array(
            'view' => 'quickcreate-list',
        ),
        3 =>
        array(
            'view' => 'list-bottom',
        ),
        array(
            'layout' => array(
                'type' => 'modal',
                'showEvent' => 'modal:quickcreate:open',
            ),
        ),

    ),
);
