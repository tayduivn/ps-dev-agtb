<?php

$viewdefs['Forecasts']['forecasts']['view']['forecastSchedule'] = array(
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'LBL_PANEL_1',
            'fields' =>
            array(
                array(
                    'name' => 'include_expected',
                    'type' => 'bool',
                    'label' => 'LBL_INCLUDE_EXPECTED',
                    'default' => true,
                    'enabled' => true
                ),
                array(
                    'name' => 'expected_amount',
                    'label' => 'LBL_EXPECTED_AMOUNT',
                    'type' => 'int',
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true
                ),
                array(
                    'name' => 'expected_best_case',
                    'label' => 'LBL_EXPECTED_BEST_CASE',
                    'type' => 'int',
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true
                ),
                array(
                    'name' => 'expected_likely_case',
                    'label' => 'LBL_EXPECTED_LIKELY_CASE',
                    'type' => 'int',
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true
                ),
            ),
        ),
    ),
);
