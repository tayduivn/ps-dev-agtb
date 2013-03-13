<?php

$viewdefs['base']['view']['opportunity-metrics'] = array(
    'dashlets' => array(
        array(
            'name' => 'LBL_DASHLET_OPPORTUNITY_NAME',
            'description' => 'LBL_DASHLET_OPPORTUNITY_DESCRIPTION',
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
