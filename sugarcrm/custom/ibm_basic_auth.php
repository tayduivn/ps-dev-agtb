<?php
// jostrow

if (!isset($_SERVER['PHP_AUTH_USER'])) {
	$rand = rand(100000, 999999);

	header("WWW-Authenticate: Basic realm=\"SugarCRM{$rand}\"");
	header('HTTP/1.0 401 Unauthorized');

	die();
}
else {
	$user_name = $_SERVER['PHP_AUTH_USER'];
	$user_password = $_SERVER['PHP_AUTH_PW'];

	if (!isset($post_login_nav)) {
		$post_login_nav = '';
	}

	header("Location: index.php?module=Users&action=Authenticate&user_name={$user_name}&user_password={$user_password}&login_language=en_us{$post_login_nav}");

	die();
}
