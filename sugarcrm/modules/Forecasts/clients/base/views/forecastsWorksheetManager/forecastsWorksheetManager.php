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
                    'default' => true,
                    'enabled' => true,
                ),

                array(
                    'name' => 'amount',
                    'type' => 'currency',
                    'label' => 'LBL_AMOUNT',
                    'default' => true,
                    'enabled' => false,
                    'convertToBase' => true,
                ),

                array(
                    'name' => 'quota',
                    'type' => 'editableCurrency',
                    'label' => 'LBL_QUOTA',
                    'default' => true,
                    'enabled' => true,
                    'convertToBase'=> true,
                ),

                array(
                    'name' => 'likely_case',
                    'type' => 'currency',
                    'label' => 'LBL_LIKELY_CASE',
                    'default' => true,
                    'enabled' => true,
                    'convertToBase'=> true,
                ),

                array(
                    'name' => 'likely_adjusted',
                    'type' => 'editableCurrency',
                    'label' => 'LBL_LIKELY_CASE_VALUE',
                    'default' => true,
                    'enabled' => true,
                    'convertToBase'=> true,
               ),

                array(
                    'name' => 'best_case',
                    'type' => 'currency',
                    'label' => 'LBL_BEST_CASE',
                    'default' => true,
                    'enabled' => true,
                    'convertToBase'=> true,
                ),

                array(
                    'name' => 'best_adjusted',
                    'type' => 'editableCurrency',
                    'label' => 'LBL_BEST_CASE_VALUE',
                    'default' => true,
                    'enabled' => true,
                    'convertToBase'=> true,
                ),

                array(
                    'name' => 'worst_case',
                    'type' => 'currency',
                    'label' => 'LBL_WORST_CASE',
                    'default' => true,
                    'enabled' => true,
                    'convertToBase'=> true,
                ),

                array(
                    'name' => 'worst_adjusted',
                    'type' => 'editableCurrency',
                    'label' => 'LBL_WORST_CASE_VALUE',
                    'default' => true,
                    'enabled' => true,
                    'convertToBase'=> true,
                ),

                array(
                    'name' => 'user_history_log',
                    'type' => 'historyLog',
                    'label' => '',
                    'default' => true,
                    'enabled' => true,
               ),
            ),
        ),
    ),
);
