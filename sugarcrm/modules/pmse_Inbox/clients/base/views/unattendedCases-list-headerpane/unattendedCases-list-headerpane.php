<?php
$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['view']['unattendedCases-list-headerpane'] = array(
    'template' => 'headerpane',
    'title' => 'LBL_PMSE_TITLE_UNATTENDED_CASES',
    'buttons' => array(
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
);