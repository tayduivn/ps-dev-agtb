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
 * $Id: FilterSubPanelView.php 45763 2009-04-01 19:16:18Z majed $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/




require_once('include/JSON.php');

global $app_strings;
//we don't want the parent module's string file, but rather the string file specifc to this subpanel
global $current_language;
$current_module_strings = return_module_language($current_language, 'WorkFlowTriggerShells');

global $currentModule;
global $focus;
global $action;

// focus_triggers_list is the means of passing data to a SubPanelView.
global $focus_trigger_filters_list;

$button  = "<form  action='index.php' method='post' name='form_trigger_filter' id='form_trigger_filter'>\n";
$button .= "<input type='hidden' name='module' value='WorkFlowTriggerShells'>\n";
$button .= "<input type='hidden' name='workflow_id' value='$focus->id'>\n<input type='hidden' name='trigger_name' value='$focus->name'>\n";
$button .= "<input type='hidden' name='return_module' value='".$currentModule."'>\n";
$button .= "<input type='hidden' name='return_action' value='".$action."'>\n";
$button .= "<input type='hidden' name='return_id' value='".$focus->id."'>\n";
$button .= "<input type='hidden' name='action'>\n";

$primary_present = false;

	$button .= "<input title='".$current_module_strings['LBL_NEW_FILTER_BUTTON_TITLE']."' accessKey='".$current_module_strings['LBL_NEW_FILTER_BUTTON_KEY']."' class='button' type='button' name='New' value='  ".$current_module_strings['LBL_NEW_FILTER_BUTTON_LABEL']."'";
	$button .= "LANGUAGE=javascript onclick='window.open(\"index.php?module=WorkFlowTriggerShells&action=CreateStep1&sugar_body_only=true&form=ComponentView&workflow_id=$focus->id&frame_type=Secondary\",\"new\",\"width=400,height=500,resizable=1,scrollbars=1\");'";


$button .= "</form>\n";
$ListView = new ListView();
$header_text = '';
		
	$ListView->initNewXTemplate( 'modules/WorkFlowTriggerShells/FilterSubPanelView.html',$current_module_strings);
$ListView->xTemplateAssign("WORKFLOW_ID", $focus->id);
$ListView->xTemplateAssign("RETURN_URL", "&return_module=".$currentModule."&return_action=DetailView&return_id={$_REQUEST['record']}");
$ListView->xTemplateAssign("EDIT_INLINE_PNG",  SugarThemeRegistry::current()->getImage/*ALTFIXED*/('edit_inline','align="absmiddle" border="0"',null,null,'.gif',$app_strings['LNK_EDIT']));
$ListView->xTemplateAssign("DELETE_INLINE_PNG",  SugarThemeRegistry::current()->getImage/*ALTFIXED*/('delete_inline','align="absmiddle" border="0"',null,null,'.gif',$app_strings['LNK_REMOVE']));
$ListView->setHeaderTitle($current_module_strings['LBL_TRIGGER_FILTER_TITLE'] . $header_text);
$ListView->setHeaderText($button);
$ListView->processListView($focus_trigger_filters_list, "main", "TRIGGER");
?>
