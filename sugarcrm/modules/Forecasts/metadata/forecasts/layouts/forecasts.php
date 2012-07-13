<?php
$viewdefs['Forecasts']['forecasts']['layout']['forecasts'] = array(
    'type' => 'forecasts',
    'components' => array(
        0 => array(
            'view' => 'forecastsFilter',
            'model' => array(
                'name' => 'Filters'
            )
        ),
        1 => array(
            'view' => 'chartOptions',
            'model' => array(
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
				'name' => 'Progress'
			)
        ),
        5 => array(
            'view' => 'forecastsCommitted',
            'collection' => array(
                'name' => 'Committed'
            ),
        ),

        6 => array(
            'view' => 'forecastsWorksheet',

            'contextCollection' => array(
                'module' => 'ForecastWorksheets',
                'name' => 'Worksheets'
            ),
        ),

        7 => array(
            'view' => 'forecastsWorksheetManager',

            'contextCollection' => array(
                'module' => 'ForecastManagerWorksheets',
                'name' => 'WorksheetManager'
            ),

        ),
        
        8 => array(
            'view' => 'forecastsSubnav',
        )
    )
);