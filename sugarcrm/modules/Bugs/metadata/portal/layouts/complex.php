<?php
$viewdefs['Bugs']['portal']['layout']['complex'] = array(
    'type' => 'columns',
    'components' =>
        array(
            //First component is the
            array(
                'layout' => array(
                    'type' => 'simple',
                    'components' => array(
                        array(
                            "view" => "example",
                        ),
                        array(
                            "view" => "fromserver",
                        )
                    )
                )
            ),
            array(
                'view' => 'fromserver',
            ),
        ),
);