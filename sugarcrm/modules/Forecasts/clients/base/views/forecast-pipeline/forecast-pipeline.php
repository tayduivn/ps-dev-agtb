<?php

$viewdefs['Forecasts']['base']['view']['forecast-pipeline'] = array(
    'dashlets' => array(
        array(
            'name' => 'Forecast Pipeline Chart',
            'description' => 'Displays current pipeline chart.',
            'config' => array(
                'module' => 'Forecasts'
            ),
            'preview' => array(
                'module' => 'Forecasts'
            ),
            'filter' => array(
                'module' => array(
                    'Home',
                    'Accounts',
                    'Opportunities',
                    'RevenueLineItems'
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
