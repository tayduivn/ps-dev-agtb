<?php

chdir(dirname(__FILE__));
require_once('pardotApi.class.php');


chdir('../..');
define('sugarEntry', true);


require_once('include/entryPoint.php');
require_once('modules/Touchpoints/Touchpoint.php');
require_once('modules/Users/User.php');

$tp_full_query =
"
select touchpoints.email1, touchpoints.id
from touchpoints inner join touchpoints_cstm on touchpoints.id = touchpoints_cstm.id_c
                 inner join leadaccounts on touchpoints.new_leadaccount_id = leadaccounts.id and leadaccounts.deleted = 0
                 inner join opportunities on leadaccounts.opportunity_id = opportunities.id and opportunities.deleted = 0
where touchpoints.scrubbed != 0 and opportunities.sales_stage not in ('Closed Won', 'Sales Op Closed', 'Finance Closed', 'Closed Lost') and touchpoints_cstm.prospect_id_c is null
group by touchpoints.email1
";

$tp_fq_res = $GLOBALS['db']->query($tp_full_query);
$touchpoint_pardot_scan = array();
while($tp_fq_row = $GLOBALS['db']->fetchByAssoc($tp_fq_res)){
	$tp_in_query = "select count(*) count from touchpoints inner join touchpoints_cstm on touchpoints.id = touchpoints_cstm.id_c ".
					"where touchpoints_cstm.prospect_id_c is not null and touchpoints.email1 = '{$tp_fq_row['email1']}'";
	$tp_in_res = $GLOBALS['db']->query($tp_in_query);
	$tp_in_row = $GLOBALS['db']->fetchByAssoc($tp_in_res);
	if($tp_in_row){
		if($tp_in_row['count'] == 0){
			$touchpoint_pardot_scan[$tp_fq_row['id']] = $tp_fq_row['email1'];
		}
	}
}

global $app_list_strings;
$app_list_strings = return_app_list_strings_language('en_us');

$current_user = new User();
$user_id = $current_user->retrieve_user_id('admin');
$current_user->retrieve($user_id);
		  
$pardot = pardotApi::magic();

$error = 0;

$touchpoints_to_update = array();
foreach($touchpoint_pardot_scan as $tp_id => $tp_email){
	$GLOBALS['db']->query("select 1"); // make sure we don't lose the database connection
    $prospect = $pardot->getProspectByEmail($tp_email);
    
    if($prospect) {
		$update_tp = "update touchpoints_cstm set prospect_id_c = '{$prospect->id}' where id_c = '{$tp_id}'";
		echo "Starting for: tp {$tp_id} :: pardot_id {$prospect->id} :: at ".date('Y-m-d H:i:s')." PST\n";
		$GLOBALS['db']->query($update_tp);
		
		$touchpoints_to_update[$tp_id] = $prospect->id;
	}
	else{
		echo "Failed to retrieve pardot object from email {$tp_email} for touchpoint {$tp_id}\n";
	}
}

foreach($touchpoints_to_update as $tp_id => $prospect_id){
	$GLOBALS['db']->query("select 1"); // to make sure we don't lose a connection to the database server
	
	$output = array();
	$return = null;
	$command = 'php scripts/pardot/syncProspectActivities.php '
		. escapeshellarg($tp_id);
	echo $command."\n";
	exec($command, $output, $return);
	if ($return) {
		echo "Error updating {$prospect_id}\n";
		$error = 1;
	}
	if ($output) {
		echo join("\n", $output);
	}
}
