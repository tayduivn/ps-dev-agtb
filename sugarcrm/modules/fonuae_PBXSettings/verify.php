<?php
/**
 * Verify PBX login credentials
 * Felix Nilam - 03/11/2010
 */
 
$username = $_REQUEST['username'];
$pass = $_REQUEST['pass'];

require('modules/fonuae_PBXSettings/salt.php');
$hash = md5(md5($click2call_salt).$pass);
$verify_url = "https://cp.fonality.com/SugarUAE/auth.cgi?username=" . urlencode($username) ."&hash=" . $hash . "&rand=" . time();

$ch = curl_init($verify_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$ret = curl_exec($ch);
curl_close($ch);

if($ret != '0'){
	echo $ret;
} else {
	echo '0';
}
?>
