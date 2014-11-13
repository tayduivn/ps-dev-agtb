<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$module_name = 'pmse_Project';
$listViewDefs[$module_name] = array(
	'NAME' => array(
		'width' => '32', 
		'label' => 'LBL_NAME', 
		'default' => true,
        'link' => true),         
	'TEAM_NAME' => array(
		'width' => '9', 
		'label' => 'LBL_TEAM',
        'default' => false),
	'ASSIGNED_USER_NAME' => array(
		'width' => '9', 
		'label' => 'LBL_ASSIGNED_TO_NAME',
		'module' => 'Employees',
        'id' => 'ASSIGNED_USER_ID',
        'default' => true),
	
);
