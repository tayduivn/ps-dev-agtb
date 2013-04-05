<?php

$viewdefs['base']['layout']['footer'] = array(
    'components' => array(
        'type' => 'simple',
        array(
            'view' => 'reminders'
        ),
        array(
            'view' => 'footer-actions',
        ),
        array(
            'view' => 'footer-intro',
        )
    ),

);