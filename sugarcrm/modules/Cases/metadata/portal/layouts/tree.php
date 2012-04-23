<?php
$viewdefs['Cases']['portal']['layout']['tree'] = array(
    'type' => 'row',
    'components' =>
    array(
        0 => array(
            'view' => 'header',
        ),
        1 => array(
            'layout' => array(
                'type' => 'fluid',
                'components' => array(
                    array(
                        "size" => 2,
                        "view" => "tree",
                    ),
                    array(
                        "size" => 10,
                        "view" => "grid",
                    )
                )
            )
        ),
    ),
);