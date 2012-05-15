<?php
$viewdefs['Cases']['portal']['layout']['grid'] = array(
    'type' => 'simple',
    'components' =>
    array(
        0 => array(
            'context'=>array(
                'module'=>'Cases',
            ),
            'view' => 'grid',
        ),
        1 =>
        array(
            'context'=>array(
                'module'=>'Opportunities',
            ),
            'view' => 'grid',
        ),
    ),
);
