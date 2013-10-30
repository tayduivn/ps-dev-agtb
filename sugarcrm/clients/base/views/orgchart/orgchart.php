<?php

$viewdefs['base']['view']['orgchart'] = array(
    'dashlets' => array(
        array(
            'label' => 'LBL_ORG_CHART',
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
            'label' => 'LBL_ORG_CHART',
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
