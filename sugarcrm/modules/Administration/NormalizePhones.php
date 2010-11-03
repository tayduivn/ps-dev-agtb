<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

global $current_user;
if(!is_admin($current_user)){
	sugar_die("Unauthorized Access");
}

require_once('fonality/include/normalizePhone/Normalize.php');
?>
