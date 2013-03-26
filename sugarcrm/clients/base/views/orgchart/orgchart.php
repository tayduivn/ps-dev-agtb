<?php

$viewdefs['base']['view']['orgchart'] = array(
    'dashlets' => array(
        array(
            'name' => 'LBL_ORG_CHART',
            'description' => 'LBL_ORG_CHART_DESC',
            'config' => array(
            ),
            'preview' => array(
            ),
            'filter' => array(
                'module' => array(
                    'Leads',
                ),
                'view' => 'records'
            )
        ),
        array(
            'name' => 'LBL_ORG_CHART',
            'description' => 'LBL_ORG_CHART_DESC',
            'config' => array(
            ),
            'preview' => array(
            ),
            'filter' => array(
                'module' => array(
                    'Home',
                ),
                'view' => 'record'
            )
        ),
    ),
);
