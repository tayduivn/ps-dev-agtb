<?php
$viewdefs['Forecasts']['forecasts']['view']['forecastsWorksheetManager'] = array(
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'LBL_PANEL_1',
            'fields' =>
            array(
               
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                    'link' => true,
                    'route' =>
                    array(
                        'module'=>'Users',
                        'action'=>'DetailView',
                        'recordID'=>'primaryid'
                    ),
                    'default' => true,
                    'enabled' => true,
                ),

                array(
                    'name' => 'amount',
                    'label' => 'LBL_AMOUNT',
                    'default' => true,
                    'enabled' => true,
                ),

                array(
                    'name' => 'quota',
                    'label' => 'LBL_QUOTA',
                    'default' => true,
                    'enabled' => true,
                ),
                
                array(
                    'name' => 'best_case',
                    'label' => 'LBL_BEST_CASE',
                    'default' => true,
                    'enabled' => true
                ),

                array(
                    'name' => 'best_adjusted',
                    'label' => 'LBL_BEST_CASE_VALUE',
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true
                ),

                array(
                    'name' => 'likely_case',
                    'label' => 'LBL_LIKELY_CASE',
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true
                ),
                array(
                    'name' => 'likely_adjusted',
                    'label' => 'LBL_LIKELY_CASE_VALUE',
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true
                ),
            ),
        ),
    ),
);
