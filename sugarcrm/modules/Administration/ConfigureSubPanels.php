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
 * $Id: ConfigureSubPanels.php 45763 2009-04-01 19:16:18Z majed $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('modules/Administration/Forms.php');
require_once ('include/SubPanel/SubPanelDefinitions.php') ;
require_once("modules/MySettings/TabController.php");

global $mod_strings;
global $app_list_strings;
global $app_strings;
global $current_user;

$mod_list_strings_key_to_lower = array_change_key_case($app_list_strings['moduleList']);

if (!is_admin($current_user)) sugar_die("Unauthorized access to administration.");

	//////////////////  Processing Save
	//if coming from save, iterate through array and save into user preferences
		$panels_to_show = '';
		$panels_to_hide = '';

	if(isset($_REQUEST['Save_or_Cancel']) && $_REQUEST['Save_or_Cancel']=='save'){
	    if(isset($_REQUEST['disabled_panels'])) 
			$panels_to_hide = $_REQUEST['disabled_panels'];
		//turn list  into array
		$hidpanels_arr = explode(',',$panels_to_hide);
		$hidpanels_arr = TabController::get_key_array($hidpanels_arr);
		//save list of subpanels to hide
		SubPanelDefinitions::set_hidden_subpanels($hidpanels_arr);
		echo "true";
	} else 
	{
	//////////////////  Processing UI
	//create title for form
	$title = get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_CONFIGURE_SUBPANELS'].":", true);
	
	//get list of all subpanels and panels to hide 
	$panels_arr = SubPanelDefinitions::get_all_subpanels();
	$hidpanels_arr = SubPanelDefinitions::get_hidden_subpanels();

	if(!$hidpanels_arr || !is_array($hidpanels_arr)) $hidpanels_arr = array();

	//create array of subpanels to show, used to create Drag and Drop widget
	$enabled = array();
	foreach ($panels_arr as $key)
	{
		if(empty($key)) continue;
		$key = strtolower($key);
		$enabled[] =  array("module" => $key, "label" => $mod_list_strings_key_to_lower[$key]);
	}
	
	//now create array of panels to hide for use in Drag and Drop widget
	$disabled = array();
	foreach ($hidpanels_arr as $key)
	{
		if(empty($key)) continue;
		$key = strtolower($key);
		$disabled[] =  array("module" => $key, "label" => $mod_list_strings_key_to_lower[$key]);
	}


	
		$this->ss->assign('title',  $title);
		$this->ss->assign('description', $mod_strings['LBL_CONFIG_SUBPANELS']);
        $this->ss->assign('enabled_panels', json_encode($enabled));
        $this->ss->assign('disabled_panels', json_encode($disabled));
        $this->ss->assign('mod', $mod_strings);
        $this->ss->assign('APP', $app_strings);

        //echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_CONFIG_SUBPANELS'], true);
        echo $this->ss->fetch('modules/Administration/ConfigureSubPanelsForm.tpl');	
	}

?>
