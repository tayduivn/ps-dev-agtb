<?php

$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['view']['config-log'] = array(
    'panels' => array(
        array(
            'fields' => array(
                array(
                    'name' => 'comboLogConfig',
                    'type' => 'enum',
                    'options' => array(
                        'emergency'=>'EMERGENCY',
                        'alert'=>'ALERT',
                        'critical'=>'CRITICAL',
                        'error'=>'ERROR',
                        'warning'=>'WARNING',
                        'notice'=>'NOTICE',
                        'info'=>'INFO',
                        'debug'=>'DEBUG'
                    ),
//                    'event' => 'change:logSelect',
                    'view' => 'edit',
                )
            ),
        ),
    )
);