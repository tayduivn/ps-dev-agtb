<?php
$viewdefs['Cases']['core']['layout']['tree'] = array(
    'type' => 'simple',
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
                        "layout" => array(
                            'type' => 'rows',
                            'components' => array(
                                array(
                                    'view' => 'tree',
                                ),
                                array(
                                    'view' => 'test',
                                )
                            )
                        )
                    ),
                    array(
                        "size" => 10,
                        "view" => "grid",
                        "listeners" => array(
                            'treeview:node_select' => "do what ever here"
                        )
                    )
                )
            )
        ),
    ),
);