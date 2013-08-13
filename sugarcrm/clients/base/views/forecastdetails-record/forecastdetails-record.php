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
);
