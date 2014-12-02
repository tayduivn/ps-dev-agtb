<?php
$viewdefs['pmse_Inbox']['base']['view']['process-users-chart'] = array(
    'dashlets' => array(
        array(
            'label' => 'LBL_PMSE_PROCESS_USERS_CHART_NAME',
            'description' => 'LBL_PMSE_PROCESS_USERS_CHART_DESCRIPTION',
            'filter' => array(
                'module' => array(
                    'Home',
                    'pmse_Project',
                ),
                'view' => array(
                    'records',
                ),
            ),
            'config' => array(
                'isRecord' => '0',
            ),
            'preview' => array(
                'isRecord' => '0',
            ),
        ),
        array(
            'label' => 'LBL_PMSE_PROCESS_USERS_CHART_NAME_RECORD',
            'description' => 'LBL_PMSE_PROCESS_USERS_CHART_DESCRIPTION',
            'filter' => array(
                'module' => array(
                    'pmse_Project',
                ),
                'view' => array(
                    'record',
                ),
            ),
            'config' => array(
                'isRecord' => '1',
            ),
            'preview' => array(
                'isRecord' => '1',
            ),
        ),
    ),
    'processes_selector' => array(
        array(
            'name' => 'processes_selector',
            'label' => 'Process Selector',
            'type' => 'enum',
            'options' => array(),
        ),
    ),
    'config' => array(
        'fields' => array(
            array(
                'name' => 'isRecord',
                'label' => 'isRecord',
                'desc' => 'LBL_DNB_PRIM_NAME_DESC',
                'type' => 'text'
            ),
        ),
    ),
);