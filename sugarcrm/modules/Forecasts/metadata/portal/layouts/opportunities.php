<?php
$viewdefs['Forecasts']['portal']['layout']['opportunities'] = array(
    'type' => 'rows',
    'components' =>
        array(
            //First component is the
            array(
                'layout' => array(
                    'type' => 'rows',
                    'components' => array(
                        array(
                            "view" => "navigation",
                        )
                    )
                )
            ),
            array(
                'layout' => array(
                    'type' => 'columns',
                    'components' => array(
                        array(
                            "view" => "filter",
                        ),
                        array(
                            "view" => "list",
                        )
                    )
                )
            ),
        ),
);