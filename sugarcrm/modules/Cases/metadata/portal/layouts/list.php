<?php
$viewdefs['Cases']['portal']['layout']['list'] = array(
    'type' => 'simple',
    'components' =>
    array(
        0 =>
        array(
            'view' => 'list',
        ),
        1 =>
        array(
            'context'=>array(
                'module'=>'Bugs',
            ),
            'view' => 'list',
        ),
    ),
);