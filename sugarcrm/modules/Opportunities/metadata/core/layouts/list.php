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
                    ),
                    array(
                        "size" => 10,
                        "view" => "grid",
                        'listeners' => array(
                            'treeview:node_select' => 'filterGridById'
                        )
                    )
                )
            )
        ),
    ),
);