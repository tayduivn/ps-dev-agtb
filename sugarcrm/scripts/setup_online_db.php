<?php
// sets up the online db for dev/prod

chdir('..');
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');


if(stristr($_SERVER['PWD'], 'stage.sugarinternal.sugarondemand') || stristr($_SERVER['PWD'], 'dev.sugarinternal')) {
	define('IS_PROD',false);
}
else{
	define('IS_PROD', true);
}

if(IS_PROD) {
	$online_db = mysql_connect('online-comdb2', 'sugarcrm_com', '08yag81g9Ag91');
	$db_name = 'sugarcrm_com';
}
else{
	$online_db = mysql_connect('online-comdb1.sjc.sugarcrm.pvt','stage_home','sgrmmo');
	$db_name = 'stage_home';
}

$moof_prefix = 'moofcart_';
$drupal_prefix = 'drupal_';
$pardot_prefix = 'pardot_';
$sugaru_prefix = 'sugaru_';
$wp_prefix = 'wp_';
$xcart_prefix = 'xcart_';
$vb_prefix = 'vb3new_';

mysql_select_db($db_name, $online_db);

?>
