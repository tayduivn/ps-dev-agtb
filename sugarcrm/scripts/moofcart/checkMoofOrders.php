<?php
// ITR20168 :: jbartek -> process missed orders

chdir('../..');
define('sugarEntry', true);
require_once('include/entryPoint.php');
require_once('custom/si_custom_files/MoofCartHelper.php');
require_once('scripts/setup_online_db.php');

global $current_user;
$current_user = new User();
$current_user->getSystemUser();
// get orders that were placed in the last x minutes
$minutes = 10;

$query = "SELECT * FROM {$moof_prefix}orderworkload WHERE placed > DATE_SUB(NOW(), INTERVAL {$minutes} MINUTE)";

logIt(__FILE__ . ":: running query: {$query}");

$response = mysql_query($query,$online_db);

while($row = mysql_fetch_assoc($response)) {
	$order_id = (int) $row['order_id'];
	if($order_id <= 0) {
		logIt(__FILE__ . ":: Invalid Orders::order_id for {$order_id}");
		continue;
	}
	$checkSI = MoofCartHelper::isOrderInSI($order_id);
	if($checkSI === false) {
		MoofCartHelper::processMoofWorkload($row['order_workload']);
		logIt(__FILE__ . ":: Sent Orders::order_id for {$order_id}");
	}

	logIt(__FILE__ . ":: Skipped Orders::order_id for {$order_id}");
}

function logIt($msg) {
	$GLOBALS['log']->info($msg);
	return true;
}
?>
