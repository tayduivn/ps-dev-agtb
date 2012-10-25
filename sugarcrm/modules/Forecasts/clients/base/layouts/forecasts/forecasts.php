<?php
$viewdefs['Forecasts']['base']['layout']['forecasts'] = array(
    'type' => 'forecasts',
    'components' => array(
        array(
            'view' => 'testView',
        ),
        array(
            'view' => 'forecastsChart',
        ),
        array(
            'view' => 'forecastsProgress',
        ),
        array(
            'view' => 'forecastsWorksheet',
            'contextCollection' => array(
                'module' => 'ForecastWorksheets',
                'name' => 'Worksheet'
            ),
        ),
        array(
            'view' => 'forecastsWorksheetTotals'
        ),
        array(
            'view' => 'forecastsWorksheetManager',
            'contextCollection' => array(
                'module' => 'ForecastManagerWorksheets',
                'name' => 'WorksheetManager'
            ),
        ),
        array(
            'view' => 'forecastsWorksheetManagerTotals'
        ),
        array(
            'layout' => array(
                'type' => 'modal',
                'showEvent' => 'modal:forecastsWizardConfig:open',
            ),
        ),
        array(
            'layout' => array(
                'type' => 'modal',
                'showEvent' => 'modal:forecastsTabbedConfig:open',
            ),
        ),
        array(
            'view' => 'forecastsTitle',
        ),
        array(
            'view' => 'forecastsTree',
        ),
        array(
            'view' => 'forecastsCommitButtons',
        ),
        array(
            'layout' => 'forecastsInfo'
        ),
    ),
);