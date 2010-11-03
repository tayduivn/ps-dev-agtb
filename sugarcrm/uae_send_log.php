<?php
/**
 * Send the UAE log to Fonality
 * Author: Felix Nilam
 * Date: 03/04/2010
 */
 
define('sugarEntry', true);
require_once('include/entryPoint.php');

$errors = array();
$files_to_copy = array(
	'click2call_log' => 'UAE/log/uae_click2call.log', 
	'callassistant_log' => 'UAE/log/uae_callassistant.log',
	'callassistant_config' => 'fonality/include/InboundCall/inbound_call_config.php',
	'click2call_config' => 'fonality/include/normalizePhone/default_dial_code.php'
);

$found = 0;
$uae_create_dir_url = "https://cp.fonality.com/SugarUAE/log.cgi";

// get the server_id
$server_id = $_REQUEST['server_id'];

$vars = array();
$vars['server_id'] = $server_id;
$found = 0;
foreach($files_to_copy as $key => $file){
	$vars[$key] = file_get_contents($file);
	if(file_exists($file)){
		$found = 1;
	}
}
$ch = curl_init($uae_create_dir_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$ret = curl_exec($ch);
curl_close($ch);

if(!preg_match('/success/', $ret)){
	$errors[] = $ret;
}

if(!$found){
	echo "<span style='color:red'>There are no log files</span>";
}
else if(empty($errors)){
	echo "<span style='color:green'>Log files sent successfully</span>";
} else {
	echo "<span style='color:red'>".implode("<br/>", $errors)."</span>";
}
?>