<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

if (!is_admin($current_user)) sugar_die("Unauthorized access to administration.");

unset($_POST['reassignbtn']);

$reassignArray = array();
foreach($_POST as $table => $value){
	$firstColons = strpos($table, "::");
	$reassignArray[substr($table, 0, $firstColons)][substr($table, $firstColons + 2)] = $value;
}

/*
echo "<PRE>";
print_r($reassignArray);
die();
*/

echo "<h3>Updating tables with null assigned_user_id values</h3>\n";

foreach($reassignArray as $table => $valArray){
	
	if(empty($valArray['assigned_user_id'])){
		echo "No user selected for the $table table. No update will run for this.<BR>";
		continue;
	}
	
	echo "update $table set assigned_user_id = '{$valArray['assigned_user_id']}' where assigned_user_id is null<BR>"; 
	//mysql_query("update $table set assigned_user_id = '{$valArray['assigned_user_id']}' where assigned_user_id is null");
	//$affected = mysql_affected_rows();
	//echo "update $table set assigned_user_id = '{$valArray['assigned_user_id']}' where assigned_user_id is null<BR>";
	//echo "&nbsp;&nbsp;&nbsp;&nbsp;$affected rows updated<BR>"; 
}

?>
