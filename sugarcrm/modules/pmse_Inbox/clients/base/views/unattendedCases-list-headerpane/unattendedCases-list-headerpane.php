<?php
$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['view']['unattendedCases-list-headerpane'] = array(
    'template' => 'headerpane',
    'title' => 'LBL_UNATTENDED_CASES_TITLE',
    'buttons' => array(
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
);