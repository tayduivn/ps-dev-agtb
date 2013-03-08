<?php

$viewdefs['base']['view']['opportunity-metrics'] = array(
    'dashlets' => array(
        array(
            'name' => 'Opportunity Metrics',
            'description' => 'Opportunity Metrics for Related Account',
            'filter' => array(
                'module' => array(
                    'Accounts'
                ),
                'view' => 'record'
            ),
            'config' => array(

            ),
            'preview' => array(

            )
        ),
    ),
);
