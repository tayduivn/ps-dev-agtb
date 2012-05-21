<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

// $Id: performSetup.php,v 1.62 2006/06/20 01:51:45 eddy Exp $
// This file will load the configuration settings from session data,
// write to the config file, and execute any necessary database steps.

if( !isset( $install_script ) || !$install_script ) {
	die($mod_strings['ERR_NO_DIRECT_SCRIPT']);
}
set_time_limit(90);
// flush after each output so the user can see the progress in real-time
ob_implicit_flush();
require_once('include/utils.php');
require_once('include/modules.php');
require_once('include/utils/file_utils.php');
$setup_site_url						= $_SESSION['setup_site_url'];
$parent_setup_site_url              = $_SESSION['parent_setup_site_url'];
$setup_site_portal_username         = $_SESSION['setup_site_portal_username'];
$setup_site_portal_password			= $_SESSION['setup_site_portal_password'];
$parsed_url							= parse_url($setup_site_url);
$setup_site_host_name				= $parsed_url['host'];
$setup_site_log_file				= 'sugarcrm.log';  // may be an option later
$setup_site_session_path			= isset($_SESSION['setup_site_custom_session_path']) ? $_SESSION['setup_site_session_path'] : '';
$setup_site_log_dir					= isset($_SESSION['setup_site_custom_log_dir']) ? $_SESSION['setup_site_log_dir'] : '';
$setup_site_guid					= (isset($_SESSION['setup_site_specify_guid']) && $_SESSION['setup_site_specify_guid'] != '') ? $_SESSION['setup_site_guid'] : '';
$cache_dir							= 'cache/';
$render_table_open					= "<table cellspacing='0' cellpadding='0' border='0' bgcolor='#dddddd' align='center' style='padding:0px 5px 0px 5px;border-left:1px solid #000000;border-right:1px solid #000000'><tr><td colspan='2' width='588'>\n";
$render_table_close					= "</td></tr></table>\n";
$line_entry_format					= "&nbsp&nbsp&nbsp&nbsp&nbsp<b>";
$line_exit_format					= "... &nbsp&nbsp</b>";
$bottle								= array();

$out =<<<EOQ
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<title>{$mod_strings['LBL_WIZARD_TITLE']} {$next_step}</title>
	<link rel="stylesheet" href="install/install.css" type="text/css" />
	<script type="text/javascript" src="install/installCommon.js"></script>
</head>
<body onload="javascript:document.getElementById('defaultFocus').focus();">
<table cellspacing="0" cellpadding="0" border="0" align="center" class="shell" style="height=15px;margin-bottom:0px;border-bottom:0px">
<tr>
	<th width="400">{$mod_strings['LBL_STEP']} {$next_step}: {$mod_strings['LBL_PERFORM_TITLE']}</th>
	<th width="200" height="30" style="text-align: right;"><a href="http://www.sugarcrm.com" target="_blank">
	<IMG src="include/images/sugarcrm_login_65.png" alt="SugarCRM" border="0"></a></th>
</tr>
<tr>
	<td colspan="2" width="600">
EOQ;
echo $out;

$bottle = handleSugarConfig();
handleLog4Php();
handleHtaccess();


// load up the config_override.php file.
// This is used to provide default user settings
if( is_file("config_override.php") ){
	require_once("config_override.php");
}

$startTime			= microtime();
$focus				= 0;
$processed_tables	= array(); // for keeping track of the tables we have worked on
$empty				= '';
$new_tables			= 1; // is there ever a scenario where we DON'T create the admin user?
$new_config			= 1;
$new_report			= 1;



	
///////////////////////////////////////////////////////////////////////////
////	FINALIZE LANG PACK INSTALL
	if(isset($_SESSION['INSTALLED_LANG_PACKS']) && is_array($_SESSION['INSTALLED_LANG_PACKS']) && !empty($_SESSION['INSTALLED_LANG_PACKS'])) {
		foreach($_SESSION['INSTALLED_LANG_PACKS'] as $langZip) {
			$lang = getSugarConfigLanguageArray($langZip);
			if(!empty($lang)) {
				$exLang = explode('::', $lang);
				if(is_array($exLang) && count($exLang) == 3) {
					$q = "INSERT INTO upgrade_history(id, filename, md5sum, type, status, version, date_entered)
							VALUES(
								'".create_guid()."',
								'{$langZip}',
								'".md5_file($langZip)."',
								'langpack',
								'installed',
								'{$exLang[3]}',
								'".date('Y-m-d H:i:s')."')";
					$db->query($q);
				} 
			}
		}
	}
		
///////////////////////////////////////////////////////////////////////////////
////	SETUP DONE
$memoryUsed = '';
if(function_exists('memory_get_usage')) {
	$memoryUsed = $mod_strings['LBL_PERFORM_OUTRO_5'].memory_get_usage().$mod_strings['LBL_PERFORM_OUTRO_6'];
}

$errTcpip = '';
$fp = @fsockopen("www.sugarcrm.com", 80, $errno, $errstr, 3);
if (!$fp) {
	$errTcpip = "<p>{$mod_strings['ERR_PERFORM_NO_TCPIP']}</p>";
}
if ($fp) {
	@fclose($fp);
	$fpResult =<<<FP
	 <form action="install.php" method="post" name="form" id="form">
	 <input type="hidden" name="current_step" value="{$next_step}">
	 <table cellspacing="0" cellpadding="0" border="0" class="stdTable">
		<tr>
		 <td><input class="button" type="button" onclick="window.open('http://www.sugarcrm.com/forums/');" value="{$mod_strings['LBL_HELP']}" /></td>
		 <td>
			<input class="button" type="button" name="goto" value="{$mod_strings['LBL_BACK']}" onclick="document.getElementById('form').submit();" />
			<input type="hidden" name="goto" value="Back" />
		 </td>
		 <td><input class="button" type="submit" name="goto" value="{$mod_strings['LBL_NEXT']}" id="defaultFocus"/></td>
		</tr>
	 </table>
	 </form>
FP;
} else {
		$fpResult =<<<FP
	 <table cellspacing="0" cellpadding="0" border="0" class="stdTable">
		<tr>
		 <td><input class="button" type="button" onclick="showHelp(4);" value="{$mod_strings['LBL_HELP']}" /></td>
		 <td>
			<form action="install.php" method="post" name="form" id="form">
				<input type="hidden" name="current_step" value="4">
				<input class="button" type="button" name="goto" value="{$mod_strings['LBL_BACK']}" />
	            <input type="hidden" name="goto" value="{$mod_strings['LBL_BACK']}" />
			</form>
		 </td>
		 <td>
			<form action="index.php" method="post" name="formFinish" id="formFinish">
				<input type="hidden" name="default_user_name" value="admin" />
				<input class="button" type="submit" name="next" value="{$mod_strings['LBL_PERFORM_FINISH']}" id="defaultFocus"/>
			</form>
		 </td>
		</tr>
	 </table>
FP;
}

if( count( $bottle ) > 0 ){
	foreach( $bottle as $bottle_message ){
		$bottleMsg .= "{$bottle_message}\n";
	}
} else {
	$bottleMsg = $mod_strings['LBL_PERFORM_SUCCESS'];
}


$out =<<<EOQ
<br><p><b>{$mod_strings['LBL_PERFORM_OUTRO_1']} {$setup_sugar_version} {$mod_strings['LBL_PERFORM_OUTRO_2']}</b></p>
{$memoryUsed}
{$errTcpip}
	</td>
</tr>
<tr>
<td align="right" colspan="2" style="border-bottom:1px solid #000000">
<hr>
<table cellspacing="0" cellpadding="0" border="0" class="stdTable">
<tr>
<td>
{$fpResult}
</td>
</tr>
</table>
</td>
</tr>
</table>
<br>
</body>
</html>
<!--
<bottle>{$bottleMsg}</bottle>
-->
EOQ;

echo $out;

?>