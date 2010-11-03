<?php
chdir('../..');
define('sugarEntry', true);

require_once('include/entryPoint.php');

$accounts = array();

$badacc_res = $GLOBALS['db']->query("SELECT id, date_modified FROM accounts WHERE date_entered = '0000-00-00 00:00:00' AND deleted = 0");
while ($badacc_row = $GLOBALS['db']->fetchByAssoc($badacc_res)) {

	if ($badacc_row['date_modified'] != '0000-00-00 00:00:00') {
		$accounts[$badacc_row['id']]['date_modified'] = strtotime($badacc_row['date_modified']);
	}

	$tracker_res = $GLOBALS['db']->query("SELECT * FROM tracker WHERE item_id = '{$badacc_row['id']}' AND action = 'save' ORDER BY id LIMIT 1") or die(mysql_error());
	if ($GLOBALS['db']->getRowCount($tracker_res) == 0) {
		// nothing
	}
	else {
		$tracker_row = $GLOBALS['db']->fetchByAssoc($tracker_res);
		$accounts[$badacc_row['id']]['tracker'] = strtotime($tracker_row['date_modified']);
	}

	$audit_res = $GLOBALS['db']->query("SELECT date_created FROM accounts_audit WHERE parent_id = '{$badacc_row['id']}' ORDER BY date_created LIMIT 1");
	if ($GLOBALS['db']->getRowCount($audit_res) == 0) {
		// nothing
	}
	else {
		$audit_row = $GLOBALS['db']->fetchByAssoc($audit_res);
		$accounts[$badacc_row['id']]['audit'] = strtotime($audit_row['date_created']);
	}
}

foreach ($accounts as $id => $acc) {
	if (empty($acc)) {
		echo "{$id} has nothing!\n";
	}
	else {
		asort($acc);

		$keys = array_keys($acc);
		$earliest = array_shift($acc);
		$newdate = date('Y-m-d H:i:s', $earliest);

		$GLOBALS['db']->query("UPDATE accounts SET date_entered = '{$newdate}' WHERE id = '{$id}'");

		echo "{$id} updated to {$newdate} ({$keys[0]})\n";
	}
}

