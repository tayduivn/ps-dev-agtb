<?php
//FILE SUGARCRM flav!=sales ONLY
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
 * $Id: ConfigureTabs.php 54176 2010-02-01 23:07:34Z dwheeler $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/







require_once('modules/Administration/Forms.php');

global $mod_strings;
global $app_list_strings;
global $app_strings;
global $current_user;

if (!is_admin($current_user)) sugar_die("Unauthorized access to administration.");


$title = get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_CONFIGURE_TABS'].":", true);

global $theme, $currentModule, $app_list_strings, $app_strings;



$GLOBALS['log']->info("Administration ConfigureTabs view");
require_once("modules/MySettings/TabController.php");
$controller = new TabController();
$tabs = $controller->get_tabs_system();

$enabled= array();
foreach ($tabs[0] as $key=>$value)
{
    $enabled[] = array("module" => $key, 'label' => translate($key));
}
$disabled = array();
foreach ($tabs[1] as $key=>$value)
{
	$disabled[] = array("module" => $key, 'label' => translate($key));
}

$user_can_edit = $controller->get_users_can_edit();
$this->ss->assign('APP', $GLOBALS['app_strings']);
$this->ss->assign('MOD', $GLOBALS['mod_strings']);
$this->ss->assign('title',  $title);
$this->ss->assign('user_can_edit',  $user_can_edit);
$this->ss->assign('enabled_tabs', json_encode($enabled));
$this->ss->assign('disabled_tabs', json_encode($disabled));
$this->ss->assign('description',  $mod_strings['LBL_CONFIG_TABS']);

echo $this->ss->fetch('modules/Administration/ConfigureTabs.tpl');	

?>
