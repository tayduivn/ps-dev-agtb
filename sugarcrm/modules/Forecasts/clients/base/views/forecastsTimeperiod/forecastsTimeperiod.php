<?php
$viewdefs['Forecasts']['base']['view']['forecastsTimeperiod'] = array(
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'LBL_PANEL_1',
            'fields' =>
            array(
                array(
                    'name' => 'timeperiod',
                    'label' => 'LBL_TIMEPERIOD_NAME',
                    'type' => 'enum',
                    'view' => 'forecastsTimeperiod',
                    // options are set dynamically in the view
                    'default' => true,
                    'enabled' => true,
                ),
            ),
        ),
    ),
);