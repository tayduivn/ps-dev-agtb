<?php
$viewdefs['base']['view']['casessummary'] = array(
    'dashlets' => array(
        array(
            'name' => 'LBL_CASE_SUMMARY_CHART',
            'description' => 'LBL_CASE_SUMMARY_CHART_DESC',
            'config' => array(),
            'preview' => array(),
            'filter' => array(
                'module' => array(
                    'Accounts',
                ),
                'view' => 'record',
            )
        ),
    ),
);
