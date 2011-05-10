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
/*********************************************************************************
 * $Id: Login.php,v 1.68 2006/06/06 17:58:53 majed Exp $
 * Description: TODO:  To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
 
require_once('include/Sugar_Smarty.php');

//we don't want the parent module's string file, but rather the string file specifc to this subpanel
global $current_language, $theme;
$current_module_strings = return_module_language($current_language, 'Users');

$ss = new Sugar_Smarty();

// Retrieve username and password from the session if possible.
if(isset($_SESSION["login_user_name"])) {
	if (isset($_REQUEST['default_user_name'])) {
		$login_user_name = $_REQUEST['default_user_name'];
    }
	else {
		$login_user_name = $_SESSION['login_user_name'];
    }
} else {
	if(isset($_REQUEST['default_user_name'])) {
		$login_user_name = $_REQUEST['default_user_name'];
    } elseif(isset($_REQUEST['ck_login_id_20'])) {
		$login_user_name = get_assigned_user_name($_REQUEST['ck_login_id_20']);
	} else {
		$login_user_name = $sugar_config['default_user_name'];
	}
	$_SESSION['login_user_name'] = $login_user_name;
}

$current_module_strings['VLD_ERROR'] = $GLOBALS['app_strings']["\x4c\x4f\x47\x49\x4e\x5f\x4c\x4f\x47\x4f\x5f\x45\x52\x52\x4f\x52"];


// Retrieve username and password from the session if possible.
if(isset($_SESSION["login_password"])) {
	$login_password = $_SESSION['login_password'];
} else {
	$login_password = $sugar_config['default_password'];
	$_SESSION['login_password'] = $login_password;
}

if(isset($_SESSION["login_error"])) {
	$login_error = $_SESSION['login_error'];
}
else {
    $login_error = null;
}

if(!empty($_REQUEST['sessiontimeout'])) {
    $ss->assign('sessionTimeout', true);
}

$ss->assign('login_error', $login_error);
$ss->assign('theme', $theme);
$ss->assign('login_user_name', $login_user_name);
$ss->assign('login_password', $login_password);
$ss->assign('current_module_strings', $current_module_strings);
$ss->assign('app_strings', $app_strings);
$ss->assign('sugar_version', $sugar_version);
echo $ss->fetch('modules/Users/Login.tpl');

?>