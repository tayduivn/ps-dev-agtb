<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');


$module_name = 'pmse_Emails_Templates';
$viewdefs[$module_name]['mobile']['layout']['list'] = array(
    'type' => 'list',
    'components' =>
    array(
        0 =>
        array(
            'view' => 'list',
        )
    ),
);