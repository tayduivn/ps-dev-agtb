<?php

$viewdefs['base']['view']['countrychart'] = array(
    'dashlets' => array(
        array(
            'name' => 'LBL_DASHLET_COUNTRY_CHART_NAME',
            'description' => 'LBL_DASHLET_COUNTRY_CHART_DESCRIPTION',
            'config' => array(
            ),
            'preview' => array(
            ),
            'filter' => array(
                'module' => array(
                    'Accounts',
                ),
                'view' => 'records'
            )
        ),
        array(
            'name' => 'LBL_DASHLET_COUNTRY_CHART_NAME',
            'description' => 'LBL_DASHLET_COUNTRY_CHART_DESCRIPTION',
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
