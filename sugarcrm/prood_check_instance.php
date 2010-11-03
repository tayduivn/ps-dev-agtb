<?php

if(isset($_REQUEST['iname']) && !empty($_REQUEST['iname'])){
	$iname = $_REQUEST['iname'];
}
else {
	$iname = "";
}
if(isset($_REQUEST['uemail']) && !empty($_REQUEST['uemail'])) {
	$uemail = $_REQUEST['uemail'];
}
else {
$uemail = "";
}

if(isset($iname) && !empty($iname)) {
	$od_url = "https://ionapi.sugarcrm.com/check.php?qt=qne&a=".$iname;

	$ch = curl_init($od_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	$content = curl_exec($ch);
	curl_close($ch);

	//if $content = 0 then check sugarshop DB for any duplicate instance names
	$ion_content = $content;
	if($content < 1) {
		$sugarshop_url = 'https://'.$_SERVER['HTTP_HOST'].'/sugarshop/sugarshop_check_instance.php?iname='.$iname;
		$content = file_get_contents($sugarshop_url);
		if($content === FALSE) {
			$content = $ion_content;
		}
	}
	printf("%d", $content);
	die();
}

if(isset($uemail) && !empty($uemail)) {
	$eval_url = "https://ionapi.sugarcrm.com/check.php?qt=qen&a=".$uemail;

	$ch = curl_init($eval_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	$content = curl_exec($ch);
	curl_close($ch);

	echo $content;
	die();
}
?>
