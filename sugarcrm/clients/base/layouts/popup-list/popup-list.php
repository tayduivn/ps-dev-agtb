<?php
$viewdefs['base']['layout']['popup-list'] = array(
    'type' => 'fluid',
    'components' =>
    array(
        array(
            'view' => 'filter',
        ),
        array(
            'view' => 'popup-list',
        ),
        array(
            'view' => 'list-bottom',
        ),
        array(
            'view' => 'popup'
        ),
    ),
);
