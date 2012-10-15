<?php
$viewdefs['base']['layout']['list'] = array(
    'type' => 'fluid',
    'components' => array(
        array(
            'view' => 'list-top',
        ),
        array(
            'view' => 'filter',
        ),
        array(
            'view' => 'list',
        ),
        array(
            'view' => 'list-bottom',
        )
    ),
);
