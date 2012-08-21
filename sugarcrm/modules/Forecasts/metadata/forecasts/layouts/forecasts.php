<?php
$viewdefs['Forecasts']['forecasts']['layout']['forecasts'] = array(
    'type' => 'forecasts',
    'components' => array(
        0 => array(
            'view' => 'filter',
        ),
        1 => array(
            'view' => 'forecastsChartOptions',
        ),
        2 => array(
            'view' => 'tree',
        ),
        3 => array(
            'view' => 'forecastsChart',
        ),
        4 => array(
            'view' => 'progress',
			'model' => array(
				'name' => 'Progress',
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
                'name' => 'Worksheet'
            ),
        ),

        7 => array(
            'view' => 'forecastSchedule',
            'contextCollection' => array(
                'module' => 'ForecastSchedule',
                'name' => 'ForecastSchedule',
            )
        ),

        8 => array(
            'view' => 'forecastsWorksheetManager',

            'contextCollection' => array(
                'module' => 'ForecastManagerWorksheets',
                'name' => 'WorksheetManager'
            ),

        ),
        
        9 => array(
            'view' => 'forecastsSubnav',
        ),

        10 => array(
            'view' => 'timeframes',
            'model' => array(
                'name' => 'Timeframes'
            )
        ),
        11 => array(
            'view' => 'forecastsCommitButtons',
        ),
    )
);