<?php
$viewdefs['Forecasts']['base']['view']['forecastsWorksheetManager'] = array(
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'LBL_PANEL_1',
            'fields' =>
            array(

                array(
                    'name' => 'name',
                    'type' => 'userLink',
                    'label' => 'LBL_NAME',
                    'link' => true,
                    'route' =>
                    array(
                        'recordID'=>'user_id'
                    ),
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                ),

                array(
                    'name' => 'amount',
                    'type' => 'currency',
                    'label' => 'LBL_AMOUNT',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => false,
                    'convertToBase' => true,
                ),

                array(
                    'name' => 'quota',
                    'type' => 'currency',
                    'label' => 'LBL_QUOTA',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true,
                    'convertToBase'=> true,
                ),

                array(
                    'name' => 'likely_case',
                    'type' => 'currency',
                    'label' => 'LBL_LIKELY_CASE',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'convertToBase'=> true,
                ),

                array(
                    'name' => 'likely_adjusted',
                    'type' => 'currency',
                    'label' => 'LBL_LIKELY_CASE_VALUE',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true,
                    'convertToBase'=> true,
               ),

                array(
                    'name' => 'best_case',
                    'type' => 'currency',
                    'label' => 'LBL_BEST_CASE',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'convertToBase'=> true,
                ),

                array(
                    'name' => 'best_adjusted',
                    'type' => 'currency',
                    'label' => 'LBL_BEST_CASE_VALUE',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true,
                    'convertToBase'=> true,
                ),

                array(
                    'name' => 'worst_case',
                    'type' => 'currency',
                    'label' => 'LBL_WORST_CASE',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'convertToBase'=> true,
                ),

                array(
                    'name' => 'worst_adjusted',
                    'type' => 'currency',
                    'label' => 'LBL_WORST_CASE_VALUE',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true,
                    'convertToBase'=> true,
                ),

                array(
                    'name' => 'user_history_log',
                    'type' => 'historyLog',
                    'label' => '',
                    'sortable' => false,
                    'default' => true,
                    'enabled' => true,
               ),
            ),
        ),
    ),
);
