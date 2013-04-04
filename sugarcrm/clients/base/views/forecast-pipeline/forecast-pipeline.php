<?php

$viewdefs['base']['view']['forecast-pipeline'] = array(
    'dashlets' => array(
        array(
            'name' => 'Forecast Pipeline Chart',
            'description' => 'Displays current pipeline chart.',
            'config' => array(
            ),
            'preview' => array(
            ),
            'filter' => array(
                'module' => array(
                    'Home',
                    'Accounts',
                    'Opportunities',
                    'Products'
                ),
                'view' => array(
                    'record',
                    'records'
                )
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
