<?php
$viewdefs['Forecasts']['forecasts']['layout']['forecasts'] = array(
    'type' => 'forecasts',
    'components' => array(
        0 => array(
            'view' => 'forecastsFilter',
            'model' => array(
                'module' => 'Forecasts',
                'name' => 'Filters'
            )
        ),
        1 => array(
            'view' => 'chartOptions',
            'model' => array(
                'module' => 'Forecasts',
                'name' => 'ChartOptions'
            )
        ),
        2 => array(
            'view' => 'tree',
        ),
        3 => array(
            'view' => 'chart',
        ),
        4 => array(
            'view' => 'progress',
			'model' => array(
				'module' => 'Forecasts',
				'name' => 'Progress'
			)
        ),
        5 => array(
            'view' => 'forecastsCommitted',
            'collection' => array(
                'module' => 'Forecasts',
                'name' => 'Committed'
            ),
        ),
        6 => array(
            'view' => 'forecastsWorksheet',
            'collection' => array(
                'module' => 'Forecasts',
                'name' => 'Worksheet',
            ),
        ),
        /*
        7 => array(
            'view' => 'forecastsWorksheetManager',
            'collection' => array(
                'module' => 'Forecasts',
                'name' => 'WorksheetManager',
            ),
        ),
        */
        8 => array(
            'view' => 'forecastsSubnav',
        )
    )
);