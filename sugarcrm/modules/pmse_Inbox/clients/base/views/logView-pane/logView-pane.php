<?php

$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['view']['logView-pane'] = array(
    'panels' => array(
        array(
            'fields' => array(
                array(
                    'name' => 'composeLogViewPane',
                    'type' => 'textarea',
                    'rows'=>'10',
                    'cols'=>'15',
                    'view' => 'edit',
                )
            ),
        ),
    )
);