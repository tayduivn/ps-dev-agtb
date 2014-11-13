<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$module_name = 'pmse_Emails_Templates';
$viewdefs[$module_name]['mobile']['layout']['edit'] = array(
    'type' => 'edit',
    'components' =>
    array(
        0 =>
        array(
            'view' => 'edit',
        )
    ),
);