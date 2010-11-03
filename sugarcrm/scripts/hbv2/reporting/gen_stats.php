<?php
/*
** @author: Julian Ostrow
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: (unknown)
** Description: generates a CSV file from the data contained in Sugar Internal's 'sugar_hb_stats' table
** Wiki customization page: http://internalwiki.sjc.sugarcrm.pvt/index.php/Hbv2/reporting/gen_stats.php
*/

function get_flavors($str) {
	$ret = array(
		'OS' => 0,
		'PRO' => 0,
		'ENT' => 0
	);

	$pieces = explode(" ", $str);
	foreach ($pieces as $piece) {
		$smaller = explode(":", $piece);

		if (in_array($smaller[0], array_keys($ret))) {
			$ret[$smaller[0]] = $smaller[1];
		}
	}

	return $ret;
}

require_once("../core.php");

$db = mysql_connect($db_host, $db_user, $db_pass);
mysql_select_db($db_name, $db);

$res = mysql_query("SELECT * FROM sugar_hb_stats ORDER BY month");

echo "day,new_silent_systems,total_systems,total_live_systems_OS,total_live_systems_PRO,total_live_systems_ENT,";
echo "total_active_systems_OS,total_active_systems_PRO,total_active_systems_ENT,total_users_active_systems,";
echo "total_silent_systems_OS,total_silent_systems_PRO,total_silent_systems_ENT\n";

$old_silent = 274054;
while ($row = mysql_fetch_assoc($res)) {
	$data = array();

	$live = get_flavors($row['total_live_systems']);
	$active = get_flavors($row['total_active_systems']);
	$silent = get_flavors($row['total_silent_systems']);

	$data['day'] = $row['month'];
	$data['new_silent_systems'] = array_sum($silent) - $old_silent;
	$data['total_systems'] = $row['total_systems'];

	$data['total_live_systems_OS'] = $live['OS'];
	$data['total_live_systems_PRO'] = $live['PRO'];
	$data['total_live_systems_ENT'] = $live['ENT'];

	$data['total_active_systems_OS'] = $active['OS'];
	$data['total_active_systems_PRO'] = $active['PRO'];
	$data['total_active_systems_ENT'] = $active['ENT'];

	$data['total_users_active_systems'] = $row['total_users_active_systems'];

	$data['total_silent_systems_OS'] = $silent['OS'];
	$data['total_silent_systems_PRO'] = $silent['PRO'];
	$data['total_silent_systems_ENT'] = $silent['ENT'];

	$old_silent = array_sum($silent);

	echo implode(",", $data) . "\n";
}
?>
