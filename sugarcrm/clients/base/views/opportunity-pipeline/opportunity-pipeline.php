<?php

$viewdefs['base']['view']['opportunity-pipeline'] = array(
    'dashlets' => array(
        array(
            'name' => 'Opportunity Pipeline Chart',
            'description' => 'Displays current pipeline chart.',
            'config' => array(
            ),
            'preview' => array(
            ),
            'filter' => array(
                'module' => array(
                    'Home',
                ),
                'view' => 'record'
            )
        ),
    ),
    'timeperiod' => array(
        array(
            'name' => 'selectedTimePeriod',
            'label' => 'TimePeriod',
            'type' => 'enum',
        ),
    )
);
