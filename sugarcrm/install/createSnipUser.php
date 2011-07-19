<?php
installLog("creating new user for Snip");

<<<<<<< HEAD
$snip_user = new User();
$snip_user->save ();

$snip_user->first_name = 'Snip';
$snip_user->last_name = 'User';
$snip_user->user_name = 'SnipUser';
$snip_user->title = null;
$snip_user->is_admin = false;
$snip_user->reports_to = null;
$snip_user->reports_to_name = null;
$snip_user->email = 'default@localhost.com';
$snip_user->status = 'Active';
$snip_user->employee_status = 'Active';
$snip_user->user_hash = strtolower(md5(random_password()));
$snip_user->save ();

function random_password () {
	$randompass='';
	$length=rand(10,20);

	for ($i=0;$i<$length;$i++)
		$randompass.=chr(rand(33,96)); //random character. ascii values 33-96 are 0-9, A-Z and symbols like !@#$

	return $randompass;
}
=======
require_once 'modules/SNIP/SugarSNIP.php';
$snip = SugarSNIP::getInstance();
$snip->getSnipUser();
>>>>>>> 58edcfe7cf12a9472b35ada95f0746c341fa3015

?>