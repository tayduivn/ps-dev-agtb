<?php
$viewdefs['Opportunities']['core']['layout']['list'] = array(
    'type' => 'simple',
    'components' =>
    array(
        1 => array(
            'layout' => array(
                'type' => 'fluid',
                'components' => array(
                    array(
                        "size" => 2,
                        "layout" => array(
                            'type' => 'rows',
                            'components' => array(
                                array(
                                    'view' => 'tree',
                                )
                            )
                        )
                    )
                )
            )
        ),
    ),
);