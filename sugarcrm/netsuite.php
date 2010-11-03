<?php

define('sugarEntry', true);
require_once('include/entryPoint.php');

require_once('custom/si_custom_files/MoofCartHelper.php');
if(isset($_REQUEST['password']) && $_REQUEST['password'] == MoofCartHelper::$netsuiteAccessCode) {
	unset($_REQUEST['password']);
	$gearman_worker = $_REQUEST['type'];
	unset($_REQUEST['type']);
	$gc = new GearmanClient();

    $server = MoofCartHelper::getGearmanServers();

	// add the servers here!
	$gc->addServers($server);
	$gc->doBackground($gearman_worker, serialize($_REQUEST));

    die();
}

die();

