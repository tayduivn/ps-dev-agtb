<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$viewdefs['pmse_Inbox']['base']['view']['list-headerpane'] = array(
    'template' => 'headerpane',
    'title' => 'My Cases',//'LBL_MODULE_NAME',
    'buttons' => array(
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
);
