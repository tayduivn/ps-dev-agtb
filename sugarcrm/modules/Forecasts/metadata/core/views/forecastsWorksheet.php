<?php
$viewdefs['Forecasts']['core']['view']['forecastsWorksheet'] = array(
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'LBL_PANEL_1',
            'fields' =>
            array(
                array(
                    'name' => 'forecast',
                    'label' => 'LBL_FORECAST',
                    'default' => true,
                    'enabled' => true,
                ),

                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                    'default' => true,
                    'enabled' => true,
                ),

                array(
                    'name' => 'date_closed',
                    'label' => 'LBL_DATE_CLOSED',
                    'default' => true,
                    'enabled' => true,
                ),

                array(
                    'name' => 'sales_stage',
                    'label' => 'LBL_SALES_STAGE',
                    'options' => 'sales_stage_dom',
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true
                ),

                array(
                    'name' => 'probability',
                    'label' => 'LBL_PROBABILITY',
                    'default' => true,
                    'enabled' => true,
                ),

                array(
                    'name' => 'amount',
                    'label' => 'LBL_AMOUNT',
                    'default' => true,
                    'enabled' => true
                ),

                array(
                    'name' => 'best_case_worksheet',
                    'label' => 'LBL_BEST_CASE',
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true
                ),

                array(
                    'name' => 'likely_case_worksheet',
                    'label' => 'LBL_LIKELY_CASE',
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true
                ),
            ),
        ),
    ),
);
