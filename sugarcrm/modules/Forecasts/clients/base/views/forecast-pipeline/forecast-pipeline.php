<?php

$viewdefs['Forecasts']['base']['view']['forecast-pipeline'] = array(
    'dashlets' => array(
        array(
            'label' => 'LBL_DASHLET_PIPELINE_CHART_NAME',
            'description' => 'LBL_DASHLET_PIPELINE_CHART_DESC',
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
            'type' => 'timeperiod',
            'dropdown_class' => 'topline-timeperiod-dropdown',
        ),
    )
);
