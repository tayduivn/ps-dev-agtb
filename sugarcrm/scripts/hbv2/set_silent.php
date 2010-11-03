<?php
/*
** @author: Julian Ostrow
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: (unknown)
** Description: sets SugarInstallations status to 'Silent' if their last touch (last archived heartbeat) was 30+ days ago 
** Wiki customization page: http://internalwiki.sjc.sugarcrm.pvt/index.php/Hbv2/Set_silent.php
*/

require_once("core.php");

$db = mysql_connect($db_host, $db_user, $db_pass) or handle_error(mysql_error(), TRUE);
mysql_select_db($db_name, $db) or handle_error(mysql_error(), TRUE);

mysql_query("UPDATE sugar_installations SET status = '" . STATUS_SILENT . "'
	WHERE last_touch <= CURRENT_TIMESTAMP() - INTERVAL 30 DAY AND status != '" . STATUS_SILENT . "'") or handle_error(mysql_error(), TRUE);

handle_runtime("Set " . mysql_affected_rows($db) . " installations to SILENT in " . get_runtime());
?>
