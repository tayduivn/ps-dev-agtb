<?php

$viewdefs['base']['view']['forecastdetails-record'] = array(
    'dashlets' => array(
        array(
            'name' => 'LBL_DASHLET_FORECASTS_DETAILS',
            'description' => 'LBL_DASHLET_FORECASTS_DETAILS_DESC',
            'config' => array(
                'module' => 'Forecasts',
            ),
            'preview' => array(
            ),
            'filter' => array(
                'module' => array(
                    'Opportunities',
                    'RevenueLineItems',
                ),
                'view' => array(
                    'record'
                )
            ),
        ),
    ),
    'panels' => array(
        array(
            'name' => 'panel_body',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                'selected_time_period'
            ),
        ),
    ),
);
