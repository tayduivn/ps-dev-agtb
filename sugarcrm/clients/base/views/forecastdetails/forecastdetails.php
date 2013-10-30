<?php

$viewdefs['base']['view']['forecastdetails'] = array(
    'dashlets' => array(
        array(
            'label' => 'LBL_DASHLET_FORECAST_NAME',
            'description' => 'LBL_DASHLET_FORECASTS_DETAILS_DESC',
            'config' => array(
            ),
            'preview' => array(
            ),
            'filter' => array(
                'module' => array(
                    'Home',
                    'Forecasts',
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
            'type' => 'timeperiod',
        ),
    )
);
