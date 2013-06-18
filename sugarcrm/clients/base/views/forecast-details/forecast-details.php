<?php

$viewdefs['base']['view']['forecast-details'] = array(
    'dashlets' => array(
        array(
            'name' => 'LBL_DASHLET_FORECASTS_DETAILS',
            'description' => 'LBL_DASHLET_FORECASTS_DETAILS_DESC',
            'config' => array(
            ),
            'preview' => array(
            ),
            'filter' => array(
                'module' => array(
                    'Home',
                    'Accounts',
                    'Forecasts',
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
