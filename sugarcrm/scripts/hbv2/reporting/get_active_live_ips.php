<?php
/*
** @author: Julian Ostrow
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: (unknown)
** Description: displays a list of distinct soap_client_ip values from the SugarInstallations module, where the SugarInstallation status is 'Live' or 'Active'
** Wiki customization page: http://internalwiki.sjc.sugarcrm.pvt/index.php/Hbv2/reporting/get_active_live_ips.php
*/

require_once('../core.php');

$db = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name, $db) or die(mysql_error());

$res = mysql_query("SELECT DISTINCT soap_client_ip FROM sugar_installations WHERE status IN ('A', 'L')");
while ($row = mysql_fetch_assoc($res)) {
	echo $row['soap_client_ip'] . "\n";
}
?>
