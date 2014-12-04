<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$viewdefs['pmse_Inbox']['base']['view']['list-headerpane'] = array(
    'template' => 'headerpane',
    'title' => 'LBL_PMSE_MY_PROCESSES',
    'buttons' => array(
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
);
