<?php

if(!defined('sugarEntry')) die("Not a valid entry point");

$allowedUsers = array(
	'lori',
	'kneilsen',
	'sadek',
);

if(!in_array($GLOBALS['current_user']->user_name, $allowedUsers)){
	sugar_die('You do not have access to this page. Please contact <a href="mailto:internalsystems@sugarcrm.com">Internal Systems</a> if you think you should have access.');
}

$header_display = 'Support Release Notifications';
echo "<h2>$header_display</h2><BR>";

require_once('modules/Releases/Release.php');
$seedRelease = new Release();
$releases = $seedRelease->get_releases(TRUE, "Active");

// Form to select the timeframe
if(!isset($_REQUEST['release'])){
$output =<<<EOQ
<form method=post action="{$_SERVER['REQUEST_URI']}" name=supportnotifications>
<table border="0" cellpadding="0" cellspacing="0" width="80%">
	<tr>
	<td width="60%" class="tabDetailViewDL">
	Please select the release you would like to notify for:
	</td>
	<td width="40%" class="tabDetailViewDF">
	<select name=release id=release>
EOQ;
$output .= get_select_options_with_id ($releases, '');
$output .=<<<EOQ
	</select>
	</td>
	</tr>
</table>
<BR>
<input type=submit value="Query Release">
</form>
EOQ;
	echo $output;
	return;
}
else if(!isset($_REQUEST['gooo'])){

require_once('custom/si_custom_files/SupportReleaseNotifier.php');
$srn = new SupportReleaseNotifier();
$results = $srn->queryRelease($_REQUEST['release']);
	
$output =<<<EOQ
<form method=post action="{$_SERVER['REQUEST_URI']}" name=supportnotifications>
<input type=hidden name=release value="{$_REQUEST['release']}">
<table border="0" cellpadding="0" cellspacing="0" width="80%">
	<tr>
	<td width="100%" class="tabDetailViewDF">
	The following cases will have emails sent to all the associated contacts.
	</td>
	</tr>
EOQ;
require_once('modules/Cases/Case.php');
foreach($results as $case_id => $bug_array){
	$case = new aCase();
	$case->disable_row_level_security;
	$case->retrieve($case_id);
	$output .= "<tr><td width=\"100%\" class=\"tabDetailViewDF\"><a href=\"index.php?module=Cases&action=DetailView&record={$case->id}\">{$case->name}</a></td></tr>";
}
$output .=<<<EOQ
</table>
<BR>
Send Test Only:
<input type=checkbox name=sendtest><BR>
Send To:
<input name=testemail><BR>
<input type=submit name=gooo value="Send Emails">
</form>
EOQ;
	echo $output;
	return;
}
else{
	$send_test = false;
	if(isset($_REQUEST['sendtest'])){
		$send_test = true;
	}
	
	$test_email = '';
	if($send_test && !empty($_REQUEST['testemail'])){
		$test_email = $_REQUEST['testemail'];
	}
	
	require_once('custom/si_custom_files/SupportReleaseNotifier.php');
	$srn = new SupportReleaseNotifier();
	$results = $srn->queryRelease($_REQUEST['release'], true, $test_email);
	
	echo "Notification sent!";
}

?>
