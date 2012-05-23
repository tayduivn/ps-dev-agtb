<?php
$viewdefs['Forecasts']['core']['layout']['main'] = array(
    'type' => 'rows',
    'components' => array(
        0 => array(
            'layout' => array(
                'type' => 'columns',
                'components' =>
                    array(
                        0 => array(
                            'view' => 'chart',
                        ),
                        1 => array(
                            'layout' => array(
                                'type' => 'rows',
                                'components' => array(
                                    0 => array(
                                        'view' => 'progress',
                                    ),
                                    1 => array(
                                        'view' => 'changeLog',
                                    ),
                                )
                            )
                        )
                    )
            )
        ),
        1 => array(
            'layout' => array(
                'type' => 'simple',
                'components' => array(
                    0 => array(
                        'view' => 'grid',
                        'context' => array(
                            'module' => 'Opportunities'
                        )
                    )
                )
            )
        )
    )
);