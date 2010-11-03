<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$foundone = false;
$leadnumber = $_POST['leadnumber'];
$contactnumber = $_POST['contactnumber'];
$accountnumber = $_POST['accountnumber'];
$usernumber = $_POST['usernumber'];

if(isset($_POST['leadexec']) && $_POST['leadexec'] == "on"){
	$foundone = true;
	echo "Executing clean up for the emails_leads relationships.<BR>\n";
}
else{
	echo "<u>Not</u> executing clean up for the emails_leads relationships.<BR>\n";
	unset($leadnumber);
}

if(isset($_POST['contactexec']) && $_POST['contactexec'] == "on"){
	$foundone = true;
	echo "Executing clean up for the emails_contacts relationships.<BR>\n";
}
else{
	echo "<u>Not</u> executing clean up for the emails_contacts relationships.<BR>\n";
	unset($contactnumber);
}


if(isset($_POST['accountexec']) && $_POST['accountexec'] == "on"){
	$foundone = true;
	echo "Executing clean up for the emails_accounts relationships.<BR>\n";
}
else{
	echo "<u>Not</u> executing clean up for the emails_accounts relationships.<BR>\n";
	unset($accountnumber);
}


if(isset($_POST['userexec']) && $_POST['userexec'] == "on"){
	$foundone = true;
	echo "Executing clean up for the emails_users relationships.<BR>\n";
}
else{
	echo "<u>Not</u> executing clean up for the emails_users relationships.<BR>\n";
	unset($usernumber);
}


if(!$foundone){
	echo "You did not select any relationships to clean up. Exiting script.";
	return;
}

echo "<BR>\n";

if(isset($leadnumber)){
	echo "<h3>Calculating all emails that are associated with more than $leadnumber leads</h3>";
	$res = mysql_query("select count(*) count, email_id from emails_leads group by email_id having count > $leadnumber");
	$idarr = array();
	$totalcount = 0;
	while($row = mysql_fetch_assoc($res)){
		$idarr[$row['email_id'].$row['count']] = $row['email_id'];
		$totalcount += $row['count'];
	}
	foreach($idarr as $count => $id){
		echo "delete from emails_leads where email_id = '$id';<BR>";
		mysql_query("delete from emails_leads where email_id = '$id'");
		echo "&nbsp;&nbsp;&nbsp;&nbsp;Completed: ".mysql_affected_rows()." deleted<BR>";
	}
	echo "<b>Deleted a total of $totalcount emails_leads relationships.</b><BR>";
}

if(isset($contactnumber)){
	echo "<h3>Calculating all emails that are associated with more than $contactnumber contacts</h3>";
	$res = mysql_query("select count(*) count, email_id from emails_contacts group by email_id having count > $contactnumber");
	$idarr = array();
	$totalcount = 0;
	while($row = mysql_fetch_assoc($res)){
		$idarr[$row['email_id'].$row['count']] = $row['email_id'];
		$totalcount += $row['count'];
	}
	foreach($idarr as $count => $id){
		echo "delete from emails_contacts where email_id = '$id';<BR>";
		mysql_query("delete from emails_contacts where email_id = '$id'");
		echo "&nbsp;&nbsp;&nbsp;&nbsp;Completed: ".mysql_affected_rows()." deleted<BR>";
	}
	echo "<b>Deleted a total of $totalcount emails_contacts relationships.</b><BR>";
}

if(isset($accountnumber)){
	echo "<h3>Calculating all emails that are associated with more than $accountnumber accounts</h3>";
	$res = mysql_query("select count(*) count, email_id from emails_accounts group by email_id having count > $accountnumber");
	$idarr = array();
	$totalcount = 0;
	while($row = mysql_fetch_assoc($res)){
		$idarr[$row['email_id'].$row['count']] = $row['email_id'];
		$totalcount += $row['count'];
	}
	foreach($idarr as $count => $id){
		echo "delete from emails_accounts where email_id = '$id';<BR>";
		mysql_query("delete from emails_accounts where email_id = '$id'");
		echo "&nbsp;&nbsp;&nbsp;&nbsp;Completed: ".mysql_affected_rows()." deleted<BR>";
	}
	echo "<b>Deleted a total of $totalcount emails_accounts relationships.</b><BR>";
}

if(isset($usernumber)){
	echo "<h3>Calculating all emails that are associated with more than $usernumber users</h3>";
	$res = mysql_query("select count(*) count, email_id from emails_users group by email_id having count > $usernumber");
	$idarr = array();
	$totalcount = 0;
	while($row = mysql_fetch_assoc($res)){
		$totalcount += $row['count'];
		$idarr[$row['email_id'].$row['count']] = $row['email_id'];
	}
	foreach($idarr as $count => $id){
		echo "delete from emails_users where email_id = '$id';<BR>";
		mysql_query("delete from emails_users where email_id = '$id'");
		echo "&nbsp;&nbsp;&nbsp;&nbsp;Completed: ".mysql_affected_rows()." deleted<BR>";
	}
	echo "<b>Deleted a total of $totalcount emails_users relationships.</b><BR>";
}

echo "<BR>Script complete\n";

?>
