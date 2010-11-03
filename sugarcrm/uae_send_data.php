<?php
/**
 * Send the stats to Fonality
 * Author: Felix Nilam
 * Date: 03/22/2010
 */
 
define('sugarEntry', true);
require_once('include/entryPoint.php');
require_once('UAE/common/utils.php');

$db = DBManagerFactory::getInstance();

// get the data
$data = array();
$query = "SELECT * FROM fonality_stats where user != 'license'";
$res = $db->query($query);

$vars = array();
$i = 1;
while($row = $db->fetchByAssoc($res)){
	foreach($row as $key => $val){
		$vars[$key.$i] = $val;
	}
	$i++;
}

$vars['total'] = $i - 1;
$uae_data_url = "http://uae.fonality.com/Sugar/log.php";

$ch = curl_init($uae_data_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$ret = curl_exec($ch);
curl_close($ch);
?>