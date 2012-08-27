<?php
$viewdefs['Forecasts']['base']['layout']['forecasts'] = array(
    'type' => 'forecasts',
    'components' => array(
        array(
            'view' => 'forecastsFilter',
        ),
        array(
            'view' => 'forecastsChartOptions',
        ),
        array(
            'view' => 'forecastsTree',
        ),
        array(
            'view' => 'forecastsChart',
        ),
        array(
            'view' => 'forecastsProgress',
        ),
        array(
            'view' => 'forecastsCommitted',
            'collection' => array(
                'name' => 'Committed'
            ),
        ),
        array(
            'view' => 'forecastsWorksheet',

            'contextCollection' => array(
                'module' => 'ForecastWorksheets',
                'name' => 'Worksheet'
            ),
        ),
        array(
            'view' => 'forecastSchedule',
            'contextCollection' => array(
                'module' => 'ForecastSchedule',
                'name' => 'ForecastSchedule',
            )
        ),
        array(
            'view' => 'forecastsWorksheetManager',

            'contextCollection' => array(
                'module' => 'ForecastManagerWorksheets',
                'name' => 'WorksheetManager'
            ),

        ),
        array(
            'view' => 'forecastsSubnav',
        ),
        array(
            'view' => 'forecastsCommitButtons',
        ),
        array(
            'view' => 'forecastsConfigTimeperiods',
        ),
        array(
            'view' => 'forecastsConfigCategories',
        ),
        array(
            'view' => 'forecastsConfigRange',
        ),
    )
);