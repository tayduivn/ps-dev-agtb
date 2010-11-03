<?php
/*
** @author: Sadek Baroudi
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: (can't find original ITRequest number)
** Description: links SugarInstallations to Accounts, based on a matching Subscription (License key) 
** Wiki customization page: http://internalwiki.sjc.sugarcrm.pvt/index.php/SugarInstallationsLinkToAccounts.php
*/

$cwd = getcwd();
$log_dir = '/var/www/sugarinternal/logs';
$log_file = "$log_dir/installations_linking.log";
$last_run_file = "$log_dir/installations_linking_last_run.log";
$last_run = '2000-01-01';
if(file_exists($last_run_file)){
	$last_run = file_get_contents($last_run_file);
}

chdir('..');
define('sugarEntry', true);
require('include/entryPoint.php');

$subs_accounts_query = "select subscription_id, account_id from subscriptions where account_id is not null and account_id != '' and deleted = 0 and date_modified > '$last_run'";
$res = $GLOBALS['db']->query($subs_accounts_query);

chdir($cwd);
$lr_fp = fopen($last_run_file, 'w');
fwrite($lr_fp, gmdate('Y-m-d H:i:s'));
fclose($lr_fp);
$log_fp = fopen($log_file, 'a');
chdir('..');
while($row = $GLOBALS['db']->fetchByAssoc($res)){
	$si_query = "select id from sugar_installations where license_key = '{$row['subscription_id']}' and (account_id is null or account_id = '')";
	$si_res = $GLOBALS['db']->query($si_query);
	while($si_row = $GLOBALS['db']->fetchByAssoc($si_res)){
		//echo "Updating sugar installation {$si_row['id']} setting account to {$row['account_id']}\n";
		$update = "update sugar_installations set account_id = '{$row['account_id']}' where id = {$si_row['id']}";
		fwrite($log_fp, '"'.date('Y-m-d H:i:s').'"'.",\"$update\"\n");
		$GLOBALS['db']->query($update);
	}
}
chdir($cwd);
fclose($log_fp);
