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

if( !isset( $install_script ) || !$install_script ){
    die($mod_strings['ERR_NO_DIRECT_SCRIPT']);
}

$setup_db_type = $_SESSION['setup_db_type'];
$errs = '';
if(isset($validation_errors)) {
	if(count($validation_errors) > 0) {
		$errs  = '<div id="errorMsgs">';
		$errs .= "<p>{$mod_strings['LBL_SYSOPTS_ERRS_TITLE']}</p>";
		$errs .= '<ul>';

		foreach($validation_errors as $error) {
			$errs .= '<li>' . $error . '</li>';
		}

		$errs .= '</ul>';
		$errs .= '</div>';
	}
}

$mysql = '';
$oci8 = '';
$mssql = '';
if($setup_db_type == "oci8") $oci8 = 'checked="checked"';
elseif($setup_db_type == 'mssql') $mssql = 'checked="checked"';
else $mysql = 'checked="checked"';

$out=<<<EOQ
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
<body onload="document.getElementById('defaultFocus').focus();">
<form action="install.php" method="post" name="systemOptions" id="form">

<table cellspacing="0" cellpadding="0" border="0" align="center" class="shell">
<tr>
    <th width="400">{$mod_strings['LBL_STEP']} {$next_step}: {$mod_strings['LBL_SYSOPTS_CONFIG']}</th>
	<th width="200" height="30" style="text-align: right;">
		<a href="http://www.sugarcrm.com" target="_blank">
		<IMG src="include/images/sugarcrm_login_65.png" alt="SugarCRM" border="0"></a></th>
</tr>
<tr>
   <td colspan="2" width="600">	
      <p>{$mod_strings['LBL_SYSOPTS_1']}</p>
		{$errs}

<div class="required">{$mod_strings['LBL_REQUIRED']}</div>
<table width="100%" cellpadding="0" cellpadding="0" border="0" class="StyleDottedHr">
<tr><th colspan="3" align="left">{$mod_strings['LBL_SYSOPTS_DB']}</td></tr>
<tr>
    <td><span class="required">*</span></td>
    <td nowrap><b>{$mod_strings['LBL_SYSOPTS_DB_TYPE']}</b><br><em>{$mod_strings['LBL_SYSOPTS_DB_DIRECTIONS']}</em></td>
    <td align="left">
        <input type="radio" class="checkbox" name="setup_db_type" id="defaultFocus" value="mysql" {$mysql} />{$mod_strings['LBL_MYSQL']}<BR>
		<input type="radio" class="checkbox" name="setup_db_type" id="defaultFocus" value="mssql" {$mssql} />{$mod_strings['LBL_MSSQL']}<BR>
        <input type="radio" class="checkbox" name="setup_db_type" id="defaultFocus" value="oci8" {$oci8} />{$mod_strings['LBL_ORACLE']}<BR>
    </td>
</tr>
</table>
</td>
</tr>
<tr>
<td align="right" colspan="2">
<hr>
     <input type="hidden" name="current_step" value=" $next_step ">
     <table cellspacing="0" cellpadding="0" border="0" class="stdTable">
       <tr>
         <td><input class="button" type="button" onclick="window.open('http://www.sugarcrm.com/forums/');" value="{$mod_strings['LBL_HELP']}" /></td>
         <td>
            <input class="button" type="button" value="{$mod_strings['LBL_BACK']}" onclick="document.getElementById('form').submit();" />
            <input type="hidden" name="goto" value="{$mod_strings['LBL_BACK']}" />
         </td>
         <td><input class="button" type="submit" name="goto" value="{$mod_strings['LBL_NEXT']}" /></td>
       </tr>
     </table>
</td>
</tr>
</table>
</form>
</body>
</html>
EOQ;
echo $out;
?>