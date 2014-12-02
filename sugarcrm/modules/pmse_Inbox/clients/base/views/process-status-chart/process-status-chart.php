<?php
$viewdefs['pmse_Inbox']['base']['view']['process-status-chart'] = array(
    'dashlets' => array(
        array(
            'label' => 'LBL_PMSE_PROCESS_STATUS_CHART_NAME',
            'description' => 'LBL_PMSE_PROCESS_STATUS_CHART_DESCRIPTION',
            'filter' => array(
                'module' => array(
                    'Home',
                    'pmse_Project',
                ),
                'view' => 'records'
            ),
            'config' => array(),
            'preview' => array(),
        ),
    ),
);