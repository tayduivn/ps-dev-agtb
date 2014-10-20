<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['layout']['detail'] = array(
    'type' => 'detail',
    'components' => array(
        array(
            'view' => 'subnavdetail',
        ),
        array(
            'view' => 'detail',
        ),
    ),
);