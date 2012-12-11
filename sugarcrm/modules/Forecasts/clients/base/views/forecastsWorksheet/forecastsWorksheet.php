<?php
$viewdefs['Forecasts']['base']['view']['forecastsWorksheet'] = array(
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'LBL_PANEL_1',
            'fields' =>
            array(
                array(
                    'name' => 'commit_stage',
                    'type' => 'enum',
                    'options' => 'commit_stage_dom',
                    'searchBarThreshold' => 5,
                    'label' => 'LBL_FORECAST',
                    'default' => true,
                    'enabled' => true,
                ),
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                    'link' => true,
                    'route' =>
                    array(
                        'module'=>'Opportunities',
                        'action'=>'DetailView',
                        'recordID'=>'id'
                    ),
                    'default' => true,
                    'enabled' => true,
                    'type' => 'recordLink'
                ),

                array(
                    'name' => 'date_closed',
                    'label' => 'LBL_DATE_CLOSED',
                    'default' => true,
                    'enabled' => true,
                    'type' => 'date',
                    'view' => 'default',					
                ),

                array(
                    'name' => 'sales_stage',
                    'label' => 'LBL_SALES_STAGE',
                    'default' => true,
                    'enabled' => true,
                ),

                array(
                    'name' => 'probability',
                    'label' => 'LBL_OW_PROBABILITY',
                    'type' => 'editableInt',
                    'default' => true,
                    'enabled' => true,
                    'maxValue' => 100,
                    'minValue' => 0,
                    //'clickToEdit' => true
                ),

                array(
                    'name' => 'likely_case',
                    'label' => 'LBL_LIKELY_CASE',
                    'type' => 'currency',
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true,
                    'convertToBase'=> true,
                    'showTransactionalAmount'=>true,
                ),

                array(
                    'name' => 'best_case',
                    'label' => 'LBL_BEST_CASE',
                    'type' => 'currency',
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true,
                    'convertToBase'=> true,
                    'showTransactionalAmount'=>true,
                ),

                array(
                    'name' => 'worst_case',
                    'type' => 'currency',
                    'label' => 'LBL_WORST_CASE',
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true,
                    'convertToBase'=> true,
                    'showTransactionalAmount'=>true,
                ),
            ),
        ),
    ),
);
