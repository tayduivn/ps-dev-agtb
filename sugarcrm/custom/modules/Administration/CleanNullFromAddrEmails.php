<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

if(!isset($_GET['leadnumber']) || !isset($_GET['contactnumber']) || !isset($_GET['accountnumber']) || !isset($_GET['usernumber']))
{
	if(!isset($_GET['leadnumber'])){
		echo "Please select the number of leads duplicate relationships you'd like to check for by passing it as &leadnumber=X in the get string<BR>";
	}

	if(!isset($_GET['contactnumber'])){
		echo "Please select the number of contacts duplicate relationships you'd like to check for by passing it as &contactnumber=X in the get string<BR>";
	}

	if(!isset($_GET['accountnumber'])){
		echo "Please select the number of accounts duplicate relationships you'd like to check for by passing it as &accountnumber=X in the get string<BR>";
	}

	if(!isset($_GET['usernumber'])){
		echo "Please select the number of users duplicate relationships you'd like to check for by passing it as &usernumber=X in the get string<BR>";
	}
	
	return;
}

$leadnumber = $_GET['leadnumber'];
$contactnumber = $_GET['contactnumber'];
$accountnumber = $_GET['accountnumber'];
$usernumber = $_GET['usernumber'];

echo "NOTE: The script that just ran is <u>NOT</u> deleting the relationships, only listing them. Click Execute Clean Up below to clean up the relationships<BR>\n";
echo "<form method=post action=index.php?module=Administration&action=CleanNullFromAddrEmailsExec>\n";
echo "<input type=hidden name=leadnumber value=".$_GET['leadnumber'].">\n";
echo "<input type=hidden name=contactnumber value=".$_GET['contactnumber'].">\n";
echo "<input type=hidden name=accountnumber value=".$_GET['accountnumber'].">\n";
echo "<input type=hidden name=usernumber value=".$_GET['usernumber'].">\n";
echo "Execute for Leads: <input type=checkbox name=leadexec><BR>\n";
echo "Execute for Contacts: <input type=checkbox name=contactexec><BR>\n";
echo "Execute for Accounts: <input type=checkbox name=accountexec><BR>\n";
echo "Execute for Users: <input type=checkbox name=userexec><BR>\n";
echo "<input type=submit class=btn name=exec_btn value=\"Execute Clean Up\"><BR><BR>\n";

echo "<h3>Calculating all emails that are associated with more than $leadnumber leads</h3>";
$res = mysql_query("select count(*) count, email_id from emails_leads group by email_id having count > $leadnumber");
$idarr = array();
$totalcount = 0;
while($row = mysql_fetch_assoc($res)){
	$idarr[$row['email_id'].$row['count']] = $row['email_id'];
	$totalcount += $row['count'];
}
foreach($idarr as $count => $id)
{
	echo "delete from emails_leads where email_id = '$id'; (".substr($count, 36)." rows)<BR>";
}
echo "<b>Found a total of $totalcount emails_leads relationships that can be cleaned up</b><BR>";

echo "<h3>Calculating all emails that are associated with more than $contactnumber contacts</h3>";
$res = mysql_query("select count(*) count, email_id from emails_contacts group by email_id having count > $contactnumber");
$idarr = array();
$totalcount = 0;
while($row = mysql_fetch_assoc($res)){
	$idarr[$row['email_id'].$row['count']] = $row['email_id'];
	$totalcount += $row['count'];
}
foreach($idarr as $count => $id)
{
	echo "delete from emails_contacts where email_id = '$id'; (".substr($count, 36)." rows)<BR>";
}
echo "<b>Found a total of $totalcount emails_contacts relationships that can be cleaned up</b><BR>";

echo "<h3>Calculating all emails that are associated with more than $accountnumber accounts</h3>";
$res = mysql_query("select count(*) count, email_id from emails_accounts group by email_id having count > $accountnumber");
$idarr = array();
$totalcount = 0;
while($row = mysql_fetch_assoc($res)){
	$idarr[$row['email_id'].$row['count']] = $row['email_id'];
	$totalcount += $row['count'];
}
foreach($idarr as $count => $id)
{
	echo "delete from emails_accounts where email_id = '$id'; (".substr($count, 36)." rows)<BR>";
}
echo "<b>Found a total of $totalcount emails_accounts relationships that can be cleaned up</b><BR>";

echo "<h3>Calculating all emails that are associated with more than $usernumber users</h3>";
$res = mysql_query("select count(*) count, email_id from emails_users group by email_id having count > $usernumber");
$idarr = array();
$totalcount = 0;
while($row = mysql_fetch_assoc($res)){
	$totalcount += $row['count'];
	$idarr[$row['email_id'].$row['count']] = $row['email_id'];
}
foreach($idarr as $count => $id)
{
	echo "delete from emails_users where email_id = '$id'; (".substr($count, 36)." rows)<BR>";
}
echo "<b>Found a total of $totalcount emails_users relationships that can be cleaned up</b><BR>";

/*
foreach($idarr as $id)
{
	mysql_query("delete from emails_accounts where email_id = '$id'");
	echo "delete from emails_accounts where email_id = '$id' -> deleted ".mysql_affected_rows()." rows<BR>";
	mysql_query("delete from emails_contacts where email_id = '$id'");
	echo "delete from emails_contacts where email_id = '$id' -> deleted ".mysql_affected_rows()." rows<BR>";
	mysql_query("delete from emails_leads where email_id = '$id'");
	echo "delete from emails_leads where email_id = '$id' -> deleted ".mysql_affected_rows()." rows<BR>";
	mysql_query("delete from emails_users where email_id = '$id'");
	echo "delete from emails_users where email_id = '$id' -> deleted ".mysql_affected_rows()." rows<BR>";
}
*/
?>
