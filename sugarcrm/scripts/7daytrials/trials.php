<?php
chdir('../../');
define('sugarEntry', true);
require_once('include/entryPoint.php');

require_once('modules/Opportunities/Opportunity.php');
require_once('scripts/7daytrials/trial_config.php');

if (!isset($_GET['opportunity_id']) || empty($_GET['opportunity_id'])) {
	echo "No Opportunity specified.  Please go back and try again.";
	die();
}

$opp = new Opportunity();
$opp->disable_row_level_security = TRUE;
$opp->retrieve($_GET['opportunity_id']);

if (empty($opp->id) || ($opp->deleted == 1)) {
	echo "Invalid Opportunity specified.  Please go back and try again.";
	die();
}

if (empty($opp->trial_name_c)) {
	echo "This Opportunity has no trial associated with it.  Please go back and try again.";
	die();
}

if ($opp->trial_extended_c == 1) {
	echo "This trial has already been extended.";
	die();
}

$td = new TimeDate();

$db_date = $td->to_db_date($opp->trial_expiration_c);
$new_date = date('Y-m-d', strtotime("{$db_date} +9 days"));
$opp->trial_expiration_c = $td->to_display_date($new_date, FALSE);

$trial_parts = parse_url($opp->trial_name_c);
$trial_name = str_replace('/', '', $trial_parts['path']);

$trial_db = mysql_connect($trial['db_host'], $trial['db_user'], $trial['db_pass']);
mysql_select_db($trial['db_name'], $trial_db);

$res = mysql_query("UPDATE trials SET expiration_date = expiration_date + INTERVAL 9 DAY WHERE trial_name = '{$trial_name}'", $trial_db) or die(mysql_error());

if (mysql_affected_rows($trial_db) != 1) {
	echo "An error occured when extending this trial.  Please go back and try again.";
	die();
}

mysql_close($trial_db);

$opp->trial_extended_c = 1;
$opp->save();

header("Location: {$sugar_config['site_url']}/index.php?module=Opportunities&action=DetailView&record={$_GET['opportunity_id']}");
