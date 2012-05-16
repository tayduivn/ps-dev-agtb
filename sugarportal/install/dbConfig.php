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

// $Id: dbConfig.php,v 1.15 2006/06/06 17:57:53 majed Exp $

if( !isset( $install_script ) || !$install_script ){
	die($mod_strings['ERR_NO_DIRECT_SCRIPT']);
}

$web_root = $_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
$web_root = str_replace("/install.php", "", $web_root);
$web_root = "http://$web_root";
$current_dir = str_replace('\install', "", dirname(__FILE__));
$current_dir = str_replace('/install', "", $current_dir);
$current_dir = trim($current_dir);
$setup_db_conn_test_result = -1;

if(!isset($_SESSION['dbConfig_submitted']) || !$_SESSION['dbConfig_submitted']){
	if( is_readable('config.php') ){
		 require_once("config.php");
	}

	// set the form's php var to the loaded config's var else default to sane settings
	$_SESSION['setup_db_host_name']					= empty($sugar_config['dbconfig']['db_host_name'])		? 'localhost'	: $sugar_config['dbconfig']['db_host_name'];
	$_SESSION['setup_db_database_name']				= empty($sugar_config['dbconfig']['db_name'])			? 'sugarcrm'	: $sugar_config['dbconfig']['db_name'];
	$_SESSION['setup_db_sugarsales_user']				= empty($sugar_config['dbconfig']['db_user_name'])		? 'sugarcrm'	: $sugar_config['dbconfig']['db_user_name'];
	$_SESSION['setup_db_sugarsales_password']			= empty($sugar_config['dbconfig']['db_password'])		? ''			: $sugar_config['dbconfig']['db_password'];
	$_SESSION['setup_db_sugarsales_password_retype']	= empty($sugar_config['dbconfig']['db_password'])		? ''			: $sugar_config['dbconfig']['db_password'];
	$_SESSION['setup_db_use_mb_demo_data']				= false;
	$_SESSION['setup_db_create_database']				= false;
	$_SESSION['setup_db_drop_tables']					= false;
	$_SESSION['setup_db_pop_demo_data']				= false;
	$_SESSION['setup_db_username_is_privileged']		= true;
	$_SESSION['setup_db_admin_user_name']				= 'root';
	$_SESSION['setup_db_admin_password']				= '';
	$_SESSION['setup_db_create_sugarsales_user']		= false;
}

$validationErr = '';
if( isset($validation_errors) ){
	if( count($validation_errors) > 0 ){
		 $validationErr  = '<div id="errorMsgs">';
		 $validationErr .= "<p>{$mod_strings['ERR_DBCONF_VALIDATION']}</p>";
		 $validationErr .= '<ul>';

		 foreach( $validation_errors as $error ){
				$validationErr .= '<li>' . $error . '</li>';
		 }
		 $validationErr .= '</ul>';
		 $validationErr .= '</div>';
	}
}


// DB split 
$oci8sid = '';
$oci8name = '/>';
$createDbCheckbox = '';
$createDb = (isset($_SESSION['setup_db_create_database']) && !empty($_SESSION['setup_db_create_database'])) ? 'checked="checked"' : '';
$dropCreate = (isset($_SESSION['setup_db_drop_tables']) && !empty($_SESSION['setup_db_drop_tables'])) ? 'checked="checked"' : ''; 
$demoData = (isset($_SESSION['setup_db_pop_demo_data']) && !empty($_SESSION['setup_db_pop_demo_data'])) ? 'checked="checked"' : '';
$mbDemoData = (isset($_SESSION['setup_db_use_mb_demo_data']) && $_SESSION['setup_db_use_mb_demo_data'] == 'yes') ? 'checked="checked"' : '';


if($_SESSION['setup_db_type'] == 'oci8') {

} else {
	$dbSplit1 = '
	<tr>
		 <td><span class="required">*</span></td>
		 <td nowrap><b>'.$mod_strings['LBL_DBCONF_HOST_NAME'].'</b></td>
		 <td align="left">
			<input type="text" name="setup_db_host_name" id="defaultFocus" value="'.$_SESSION['setup_db_host_name'].'" />
		</td>
	</tr>';
	
	$dbSplit2 = '<tr><td></td>
		<td nowrap><b>'.$mod_strings['LBL_DBCONF_PRIV_USER_2'].'</b></td>
		<td align="left"><input type="checkbox" class="checkbox" name="setup_db_username_is_privileged" value="yes" onclick="toggleUsernameIsPrivileged();"';
	if(isset($_SESSION['setup_db_username_is_privileged']) && !empty($_SESSION['setup_db_username_is_privileged'])) {
		$dbSplit2 .= 'checked="checked"';
	}
	$dbSplit2 .=	' /></td></tr><tbody id="privileged_user_info"><tr><td><span class="required">*</span></td><td width="50%">
					<b>'.$mod_strings['LBL_DBCONF_PRIV_USER'].'</b><br><em>'.$mod_strings['LBL_DBCONF_PRIV_USER_DIRECTIONS'].'</em></td>
						<td align="left"><input type="text" name="setup_db_admin_user_name" value="'.$_SESSION['setup_db_admin_user_name'].'" /></td></tr>
						<tr><td></td><td nowrap><b>'.$mod_strings['LBL_DBCONF_PRIV_PASS'].'</b></td><td align="left"><input type="password" name="setup_db_admin_password" value="'.$_SESSION['setup_db_admin_password'].'" /></td></tr></tbody>';
	
	$dbUser = '<input type="checkbox" class="checkbox" name="setup_db_create_sugarsales_user" onclick="togglePasswordRetypeRequired();" value="yes"';
	if(isset($_SESSION['setup_db_create_sugarsales_user']) && !empty($_SESSION['setup_db_create_sugarsales_user'])) {
		$dbUser .= 'checked="checked"';
	}
	$dbUser .= " /> <b>{$mod_strings['LBL_DBCONF_CREATE_USER']}</b>";
	
	$createDbCheckbox = "<input type='checkbox' class='checkbox' name='setup_db_create_database' onclick='toggleDropTables();' value='yes' {$createDb} /> <b>{$mod_strings['LBL_DBCONF_CREATE_DB']}</b>";
}




///////////////////////////////////////////////////////////////////////////////
////	BEGIN PAGE OUTPUT

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
	<script type="text/javascript" src="install/dbConfig.js"></script>
	<script type="text/javascript">
		function showMb() {
			var mb1 = document.getElementById("mbTd1");
			var mb2 = document.getElementById("mbTd2");
			
			if(document.getElementById("demoData").checked == true) {
				mb1.style.display = "";
				mb2.style.display = "";
			} else {
				mb1.style.display = "none";
				mb2.style.display = "none";
			}
		}
	</script>
</head>
EOQ;
$out .= '<body onload="';
if(!isset($_SESSION['oc_install']) || $_SESSION['oc_install'] == false){
    $out .= 'showMb();';   
}
$out .= 'toggleDropTables();togglePasswordRetypeRequired();toggleUsernameIsPrivileged();document.getElementById(\'defaultFocus\').focus();">';

$out2 =<<<EOQ2
<form action="install.php" method="post" name="setConfig" id="form">
<table cellspacing="0" cellpadding="0" border="0" align="center" class="shell">
<tr>
	<th width="400">{$mod_strings['LBL_STEP']} {$next_step}: {$mod_strings['LBL_DBCONF_TITLE']}</th>
	<th width="200" height="30" style="text-align: right;"><a href="http://www.sugarcrm.com" target="_blank">
		<IMG src="include/images/sugarcrm_login_65.png" alt="SugarCRM" border="0"></a></th>
</tr>
<tr>
	<td colspan="2" width="600">
		 <p>{$mod_strings['LBL_DBCONF_INSTRUCTIONS']}</p>
		 {$validationErr}
<div class="required">{$mod_strings['LBL_REQUIRED']}</div>
<table width="100%" cellpadding="0" cellpadding="0" border="0" class="StyleDottedHr">
<tr><th colspan="3" align="left">{$mod_strings['LBL_DBCONF_TITLE']}</td></tr>

{$dbSplit1}

<tr><td><span class="required">*</span></td>
	<td nowrap><b>{$mod_strings['LBL_DBCONF_DB_NAME']} {$oci8sid}</b></td>
	<td nowrap align="left">
		 <input type="text" name="setup_db_database_name" maxlength="64" value="{$_SESSION['setup_db_database_name']}"
		 {$oci8name}
		 {$createDbCheckbox}
	</td>
</tr>
<tr>
	<td><span class="required">*</span></td>
	<td nowrap><b>{$mod_strings['LBL_DBCONF_DB_USER']}</b></td>
	<td nowrap align="left">
		 <input type="text" name="setup_db_sugarsales_user" maxlength="16" value="{$_SESSION['setup_db_sugarsales_user']}" />
		 {$dbUser}
	</td>
</tr>
<tr>
	<td></td>
	<td nowrap><b>{$mod_strings['LBL_DBCONF_DB_PASSWORD']}</b></td>
	<td nowrap align="left"><input type="password" name="setup_db_sugarsales_password" value="{$_SESSION['setup_db_sugarsales_password']}" /></td></tr>
<tbody id="password_retype_required">
<tr><td></td>
	<td nowrap><b>{$mod_strings['LBL_DBCONF_DB_PASSWORD2']}</b></td>
	<td nowrap align="left">
		<input type="password" name="setup_db_sugarsales_password_retype" value="{$_SESSION['setup_db_sugarsales_password_retype']}" /></td></tr>
</tbody>
<tr>
	<td></td>
	<td nowrap><b>{$mod_strings['LBL_DBCONF_DB_DROP_CREATE']}</b><br>
	<i>{$mod_strings['LBL_DBCONF_DB_DROP_CREATE_WARN']}</i></td>
	<td nowrap align="left"><input type="checkbox" class="checkbox" name="setup_db_drop_tables" value="yes" {$dropCreate} /></td></tr>
EOQ2;
$out3 =<<<EOQ3
<tr>
	<td></td>
	<td nowrap><b>{$mod_strings['LBL_DBCONF_DEMO_DATA']}</b></td>
	<td nowrap align="left">
		<input type="checkbox" class="checkbox" id="demoData" name="setup_db_pop_demo_data" value="yes" {$demoData} onClick="showMb();" />
	</td>
</tr>
<tr>
	<td></td>
	<td nowrap id="mbTd1" style="display:none;">
		<b>{$mod_strings['LBL_DBCONF_MB_DEMO_DATA']}</b><br>
	</td>
	<td nowrap id="mbTd2" align="left">
		<input type="checkbox" class="checkbox" id="mbCheckbox" name="setup_db_use_mb_demo_data" value="yes" {$mbDemoData} />
	</td>
</tr>
EOQ3;
$out4 =<<<EOQ4
	{$dbSplit2}
</table>
</td>
</tr>
<tr>
<td align="right" colspan="2">
<hr>
	 <input type="hidden" name="current_step" value="{$next_step}">
	 <table cellspacing="0" cellpadding="0" border="0" class="stdTable">
		<tr>
			<td><input class="button" type="button" onclick="window.open('http://www.sugarcrm.com/forums/');" value="{$mod_strings['LBL_HELP']}" /></td>
			<td>
				<input class="button" type="button" name="goto" value="{$mod_strings['LBL_BACK']}" onclick="document.getElementById('form').submit();" />
	            <input type="hidden" name="goto" value="{$mod_strings['LBL_BACK']}" />
			</td>
			<td>
				<input class="button" type="submit" name="goto" id="defaultFocus" value="{$mod_strings['LBL_NEXT']}" />
			</td>
		</tr>
	 </table>
</td>
</tr>
</table>
</form>
<br>
</body>
</html>
EOQ4;

echo $out.$out2;
if(!isset($_SESSION['oc_install']) || $_SESSION['oc_install'] == false){
    echo $out3;
}
echo $out4;


////	END PAGE OUTPUT
///////////////////////////////////////////////////////////////////////////////

?>