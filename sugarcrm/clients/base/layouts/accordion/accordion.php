<?php
$viewdefs['base']['layout']['accordion'] = array(
    'type' => 'fluid',
    'components' =>
    array(
        0 =>
        array(
            'view' => 'accordion-top',
        ),
        1 =>
        array(
            'layout' => 'accordion-panels',
        ),
        2 =>
        array(
            'view' => 'accordion-bottom',
        ),
    ),
);