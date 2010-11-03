<?php
/*
** @author: (unknown)
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: (unknown)
** Description: updates "Subscription Expiration" field in Accounts, based on Subscriptions linked to each Account 
** Wiki customization page: http://internalwiki.sjc.sugarcrm.pvt/index.php/ProcessAccountSubscriptionExp.php
*/

$si_dir = "/var/www/sugarinternal/sugarinternal.sugarondemand.com";
$log_dir = '/var/www/sugarinternal/logs/';
$config   = "$si_dir/config.php";

require($config);
$server = $sugar_config['dbconfig']['db_host_name'];
$user   = $sugar_config['dbconfig']['db_user_name'];
$pass   = $sugar_config['dbconfig']['db_password'];
$name   = $sugar_config['dbconfig']['db_name'];

$connection = mysql_pconnect($server, $user, $pass);
if(!$connection){
        die("Could not connect to jaguar database.");
}
mysql_select_db($name);

$where = "account_id is not null and account_id != '' and status = 'enabled' and name not like '%eval%'";

$query = 
"select count(*) count, account_id, distgroups.name name, ".
	"min(expiration_date) min_exp, max(expiration_date) max_exp, subscriptions.id subscription_id ".
"from subscriptions inner join subscriptions_distgroups on subscriptions.id = subscriptions_distgroups.subscription_id ".
                   "inner join distgroups on distgroups.id = subscriptions_distgroups.distgroup_id ".
"where $where ".
"group by account_id";

$res = mysql_query($query);

$valArray = array();

$where = "status = 'enabled' and name not like '%eval%'";
while($row = mysql_fetch_assoc($res)){
	if($row['min_exp'] != $row['max_exp']){
		$tmp['account_id'] = $row['account_id'];
		
		$inquery =
		"select account_id, distgroups.name name, expiration_date ".
		"from subscriptions inner join subscriptions_distgroups on subscriptions.id = subscriptions_distgroups.subscription_id ".
				   "inner join distgroups on distgroups.id = subscriptions_distgroups.distgroup_id ".
		"where $where and account_id = '{$row['account_id']}' ".
		"order by expiration_date asc ".
		"";
		$inres = mysql_query($inquery);
		
		$exp = $row['max_exp'];
		while($inrow = mysql_fetch_assoc($inres)){
			if($inrow['name'] != 'SugarNetwork'){
				$exp = $inrow['expiration_date'];
				if($inrow['name'] == "SugarPartner"){
					//echo "Did search, used $exp instead of {$row['max_exp']} for expiration for account {$row['account_id']}\n";
					//echo "SugarPartner - break\n";
					break;
				}
			}
		}
		$tmp['expiration_date'] = $exp;
		//echo "Did search, used $exp instead of {$row['max_exp']} for expiration for account {$row['account_id']}\n";
		$valArray[] = $tmp;
	}
	else{
		$tmp['account_id'] = $row['account_id'];
		$tmp['expiration_date'] = $row['max_exp'];
		$valArray[] = $tmp;
	}
}

$counter = 0;
foreach($valArray as $arr){
	//echo "EXECUTED :: update accounts_cstm set subscription_expiration_c = '{$arr['expiration_date']}' where id_c = '{$arr['account_id']}'\n";
	mysql_query("update accounts_cstm set subscription_expiration_c = '{$arr['expiration_date']}' where id_c = '{$arr['account_id']}'");
	if($affected = mysql_affected_rows() > 0)
		$counter += $affected;
}

//echo "\n$counter accounts updated\n";

$fp = fopen("$log_dir/AccountSubsExp.log", 'a');
fwrite($fp, "[".date("Y-m-d H:i:s")."] $counter accounts updated\n");
fclose($fp);

?>
