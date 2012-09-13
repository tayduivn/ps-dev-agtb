<?php
$viewdefs['Forecasts']['base']['view']['forecastsCommitted'] = array(
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
                array(
                    'name' => 'id',
                    'label' => 'LBL_ID',
                    'default' => true,
                    'enabled' => true,
                ),
                array(
                    'name' => 'date_entered',
                    'label' => 'LBL_DATE_ENTERED',
                    'default' => true,
                    'enabled' => true,
                ),

                array(
                    'name' => 'best_case',
                    'label' => 'LBL_BEST_CASE',
                    'default' => true,
                    'enabled' => true,
                ),

                array(
                    'name' => 'likely_case',
                    'label' => 'LBL_LIKELY_CASE',
                    'default' => true,
                    'enabled' => true,
                ),

                array(
                    'name' => 'worst_case',
                    'label' => 'LBL_WORST_CASE',
                    'default' => true,
                    'enabled' => true,
                ),
            ),
        ),
    ),
);