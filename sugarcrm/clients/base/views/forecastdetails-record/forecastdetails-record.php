<?php
//FILE SUGARCRM flav=ent ONLY
$viewdefs['base']['view']['forecastdetails-record'] = array(
    'dashlets' => array(
        array(
            'label' => 'LBL_DASHLET_FORECASTS_DETAILS',
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
