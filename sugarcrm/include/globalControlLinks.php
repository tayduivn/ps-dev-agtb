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
 * $Id: globalControlLinks.php 52976 2009-12-03 21:10:31Z jmertic $
 * Description:  controls which link show up in the upper right hand corner of the app
 ********************************************************************************/

global $app_strings, $current_user;
global $sugar_config, $sugar_version, $sugar_flavor, $server_unique_key, $current_language, $action;

 if(!isset($global_control_links)){
 	$global_control_links = array();
	$sub_menu = array();
 }
if(isset( $sugar_config['disc_client']) && $sugar_config['disc_client']){
	require_once('modules/Sync/headermenu.php');
}

//BEGIN SUGARCRM flav!=com ONLY
if(SugarThemeRegistry::current()->name != 'Classic')
//BEGIN SUGARCRM flav=sales ONLY
if(!is_admin($GLOBALS['current_user']))
//END SUGARCRM flav=sales ONLY
$global_control_links['profile'] = array(
'linkinfo' => array($app_strings['LBL_PROFILE'] => 'index.php?module=Users&action=EditView&record='.$GLOBALS['current_user']->id),
'submenu' => ''
);
//END SUGARCRM flav!=com ONLY

$global_control_links['employees'] = array(
'linkinfo' => array($app_strings['LBL_EMPLOYEES']=> 'index.php?module=Employees&action=index&query=true'),
'submenu' => ''
);
if (
        is_admin($current_user)
		//BEGIN SUGARCRM flav=pro ONLY
		|| $current_user->isDeveloperForAnyModule()
		//END SUGARCRM flav=pro ONLY

        ) $global_control_links['admin'] = array(

'linkinfo' => array($app_strings['LBL_ADMIN'] => 'index.php?module=Administration&action=index'),
'submenu' => ''
);
//BEGIN SUGARCRM flav=sales ONLY
if ($current_user->user_type == "UserAdministrator")
$global_control_links['admin'] = array(
'linkinfo' => array($app_strings['LBL_USER_ADMIN'] => 'index.php?module=Users&action=index'),
'submenu' => ''
);
//END SUGARCRM flav=sales ONLY
//BEGIN SUGARCRM flav!=sales ONLY
$global_control_links['training'] = array(
'linkinfo' => array($app_strings['LBL_TRAINING'] => 'javascript:void(window.open(\'http://support.sugarcrm.com\'))'),
'submenu' => ''
 );
//END SUGARCRM flav!=sales ONLY

/* no longer goes in the menubar - now implemented in the bottom bar.
$global_control_links['help'] = array(
    'linkinfo' => array($app_strings['LNK_HELP'] => ' javascript:void window.open(\'index.php?module=Administration&action=SupportPortal&view=documentation&version='.$sugar_version.'&edition='.$sugar_flavor.'&lang='.$current_language.'&help_module='.$GLOBALS['module'].'&help_action='.$action.'&key='.$server_unique_key.'\')'),
    'submenu' => ''
 );
*/

$global_control_links['users'] = array(
'linkinfo' => array($app_strings['LBL_LOGOUT'] => 'index.php?module=Users&action=Logout'),
'submenu' => ''
);

$global_control_links['about'] = array('linkinfo' => array($app_strings['LNK_ABOUT'] => 'index.php?module=Home&action=About'),
'submenu' => ''
);

if (sugar_is_file('custom/include/globalControlLinks.php')) {
    include('custom/include/globalControlLinks.php');
}
if (sugar_is_file('custom/application/Ext/GlobalLinks/links.ext.php')) {
    include('custom/application/Ext/GlobalLinks/links.ext.php');
}
?>
