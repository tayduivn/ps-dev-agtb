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
 * $Id: header.php,v 1.64 2006/06/06 17:58:55 majed Exp $
 * Description:  Contains a variety of utility functions used to display UI
 * components such as form headers and footers.  Intended to be modified on a per
 * theme basis.
 ********************************************************************************/

require_once('include/Sugar_Smarty.php');
require_once('include/utils.php');
require_once('include/globalControlLinks.php');

global $currentModule;
global $moduleList;
global $theme;
global $app_list_strings;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";
require_once($theme_path.'layout_utils.php');
require($theme_path.'config.php');

global $app_strings;
$default_charset = $sugar_config['default_charset'];
$module_path="modules/".$currentModule."/";

$shortcuts = load_menu($module_path);

$ss = new Sugar_Smarty();
$ss->assign('APP', $app_strings);
$ss->assign("THEME", $theme);
$ss->assign("IMAGE_PATH", $image_path);
$ss->assign("CURRENT_MODULE", $currentModule);
$ss->assign('GCL', $global_control_links);
$ss->assign("CURRENT_TAB_CLASS", "currentTab");
$ss->assign("OTHER_TAB_CLASS", "otherTab");
$ss->assign("CURRENT_USER", empty($_SESSION['contact_user_name']) ? '' : $_SESSION['contact_user_name']);
$ss->assign('SHORTCUTS', $shortcuts);

$tabs = array();
foreach($moduleList as $module) {
    $tabs[$module] = $app_list_strings['moduleList'][$module];
}

$ss->assign('TABS', $tabs);
echo $ss->fetch($theme_path . 'header.tpl');

?>
