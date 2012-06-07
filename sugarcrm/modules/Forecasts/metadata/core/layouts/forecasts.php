<?php
$viewdefs['Forecasts']['core']['layout']['forecasts'] = array(
    'type' => 'forecasts',
    'components' =>
    array(
        0 => array(
            'view' => 'forecastsFilter',
        ),
        1 => array(
            'view' => 'chartOptions',
        ),
        2 => array(
            'view' => 'tree',
        ),
        3 => array(
            'view' => 'chart',
        ),
        4 => array(
            'view' => 'progress',
        ),
        5 => array(
            'view' => 'changeLog',
        ),
        6 => array(
            'view' => 'grid',
        ),
        7 => array(
            'view' => 'forecastsSubnav',
        )
    )
);