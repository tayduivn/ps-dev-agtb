<?php
$viewdefs['Forecasts']['core']['layout']['left'] = array(
    'type' => 'rows',
    'components' =>
        array(
            0 => array(
                'view' => 'filter',
            ),
            1 => array(
                'view' => 'chartOptions',
            ),
            2 => array(
                'layout' =>
                    array(
                        'type' => 'simple',
                        'components' => array(
                            0 => array(
                                'view' => 'tree',
                                'context' => array(
                                    'module' => 'Users'
                                )
                            )
                        )
                    )
            ),
        ),
    );