<?php
$viewdefs['Forecasts']['portal']['layout']['list'] = array(
    'type' => 'simple',
    'components' =>
    array(
        1 => array(
            'layout' => array(
                'type' => 'fluid',
                'components' => array(
                    array(
                        'context' => array(
                            'module'=>'Users',
                        ),
                        'view' => 'tree'
                    ),
                    array(
                        'context' => array(
                            'module'=>'Opportunities',
                        ),
                        'view' => 'grid'
                    )
                )
            )
        ),
    ),
);