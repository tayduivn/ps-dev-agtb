<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: SubcalcView.php 45763 2009-04-01 19:16:18Z majed $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/




require_once('modules/QueryBuilder/QueryFilter.php');


global $app_strings;
//we don't want the parent module's string file, but rather the string file specifc to this subpanel
global $current_language;
$current_module_strings = return_module_language($current_language, 'QueryBuilder');

global $currentModule;
global $theme;
global $focus;
global $action;




global $seed_object;
global $calc_object;
global $sugar_version, $sugar_config;

//Setup filter object
$filter_object = new QueryFilter();

global $current_user;
$header_text = '';
if(is_admin($current_user) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){
		$header_text = "&nbsp;<a href='index.php?action=index&module=DynamicLayout&from_action=SubPanelView&from_module=Leads&record=". $_REQUEST['record']."'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0' alt='Edit Layout' align='bottom'")."</a>";
}

//This needs to be changed.  It is here because I have to use to_pdf=true.  The index recognizes
//only popup, vs Columnpopup as a popup.
echo getVersionedScript('include/javascript/sugar_3.js');


$xtpl=new XTemplate ('modules/QueryBuilder/SubcalcView.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);
$xtpl->assign("GRIDLINE", $gridline);
$xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);
$xtpl->assign("RECORD", $focus->id);
$xtpl->assign('EDIT_INLINE', SugarThemeRegistry::current()->getImage('edit_inline','align="absmiddle" alt="'.$app_strings['LNK_EDIT'].'" border="0"'));
$xtpl->assign('MOVE_INLINE', SugarThemeRegistry::current()->getImage('arrow','align="absmiddle" alt="Order" border="0"'));
$xtpl->assign('DELETE_INLINE_PNG', SugarThemeRegistry::current()->getImage("delete_inline", 'align="absmiddle" alt="'. $app_strings['LNK_DELETE'] . '" border="0"'));

//Sub-Calc Form (Math)
	//core form elements
	$subcalc_form =		"<input type=\"hidden\" name=\"module\" value=\"QueryBuilder\"> \n";
	$subcalc_form .=		"<input type=\"hidden\" name=\"record\" value=\"{$seed_object->id}\"> \n";
	$subcalc_form .=		"<input type=\"hidden\" name=\"action\" value=\"SubCalcSave\"> \n";
	$subcalc_form .=		"<input type=\"hidden\" name=\"return_module\" value=\"QueryBuilder\"> \n";
	$subcalc_form .=		"<input type=\"hidden\" name=\"return_id\" value=\"{$seed_object->id}\"> \n";
	$subcalc_form .=		"<input type=\"hidden\" name=\"return_action\" value=\"ColumnPopup\"> \n";
	$subcalc_form .=		"<input type=\"hidden\" name=\"component\" value=\"Column\"> \n";


	//Passing IDs
	$subcalc_form .=		"<input type=\"hidden\" name=\"column_record\" value=\"{$focus->id}\"> \n";
	$subcalc_form .=		"<input type=\"hidden\" name=\"calc_id\" value=\"{$calc_object->id}\"> \n";
	$subcalc_form .=		"<input type=\"hidden\" name=\"parent_id\" value=\"{$calc_object->id}\"> \n";

	//field modified onclick save
	$subcalc_form .=		"<input type=\"hidden\" name=\"left_field\" > \n";
	$subcalc_form .=		"<input type=\"hidden\" name=\"right_field\" > \n";

	//visual for popup
	$subcalc_form .=		"<input type=\"hidden\" name=\"to_pdf\" value=\"true\"> \n";


 	$xtpl->assign("SUBCALC_FORM", $subcalc_form);


//Retrieve the total calculation to display
	//function call to retrieve the total calculation line
	//Set true, because we want to see the display labels and not the database field information

	$total_calculation = $calc_object->get_total_subcalc_start(true);
	$total_calculation = $calc_object->calc_type."( ".$total_calculation." )";

	echo "<h3> Total Calculation: </h3>".$total_calculation."<BR>";





//Add/Edit Filter Area
//Retrieve the filters that is being edited


	if(!empty($_REQUEST['filter_id'])){
		$filter_object->retrieve($_REQUEST['filter_id']);
		$is_edit = true;
	}

		$calc_select_array = $seed_object->get_relationship_modules($seed_object->base_module);

	$xtpl->assign("LEFT_MODULE", get_select_options_with_id($calc_select_array,$filter_object->left_module));
	$xtpl->assign("RIGHT_MODULE", get_select_options_with_id($calc_select_array,$filter_object->right_module));

	$xtpl->assign("LEFT_FIELD", $filter_object->left_field);
	$xtpl->assign("RIGHT_FIELD", $filter_object->right_field);

	$xtpl->assign("LEFT_TYPE", get_select_options_with_id($app_list_strings['query_calc_leftright_type_dom'],$filter_object->left_type));
	$xtpl->assign("RIGHT_TYPE", get_select_options_with_id($app_list_strings['query_calc_leftright_type_dom'],$filter_object->right_type));


	$xtpl->assign("LEFT_VALUE", $filter_object->left_value);
	$xtpl->assign("RIGHT_VALUE", $filter_object->right_value);

	$filter_group_array = $filter_object->get_filter_group_array();

	if(!empty($filter_object->parent_filter_id)){
		$glued_parent_filter_id = $filter_object->glue_parent_filter_id();
	} else {
		$glued_parent_filter_id ="";
	}

	$xtpl->assign("PARENT_FILTER_OPTIONS", get_select_options_with_id($filter_group_array, $glued_parent_filter_id));




	$xtpl->assign("OPERATOR", get_select_options_with_id($app_list_strings['query_calc_oper_dom'],$filter_object->operator));
if ($filter_object->calc_enclosed == 'on') $xtpl->assign("CALC_ENCLOSED", "checked");
	$xtpl->assign("LIST_ORDER", $filter_object->list_order);

if(!empty($is_edit) && $is_edit==true){
	echo "<BR><input title=\"New [Alt+N]\" accesskey=\"N\" class=\"button\" onclick=\"return set_new_calc();\" name=\"New\" value=\"New\" type=\"submit\">";
	echo "<h3> Edit Calculation Part: </h3>";
	//$xtpl->assign("ADD_CALC_BUTTON", $add_calc_button);


	$xtpl->assign("FILTER_ID", $filter_object->id);
} else {
	echo "<h3> Add Calculation Part: </h3>";
}
	if(empty($filter_object->list_order)) $filter_object->list_order = "0";
	$xtpl->assign("LIST_ORDER", $filter_object->list_order);


	$xtpl->parse("edit");
	$xtpl->out("edit");


//Get list of sub-calculations

	$where = "parent_id='".$calc_object->id."'";
	$button = "";

	$ListView = new ListView();
	$ListView->setXTemplate($xtpl);
	$ListView->setHeaderTitle("Calculation Parts List");
	$ListView->show_export_button = false;
	$ListView->setHeaderText($button);


	//set query_id and column_id
	$ListView->xTemplateAssign("QUERY_ID", $seed_object->id);
	$ListView->xTemplateAssign("COLUMN_ID", $focus->id);

	$del_image_link = SugarThemeRegistry::current()->getImage('delete_inline','align="absmiddle" alt="'.$app_strings['LNK_DELETE'].'" border="0"');
	$ListView->xTemplateAssign("DELETE_INLINE_PNG", $del_image_link);

	$ListView->setQuery($where, "", "list_order", "SUB_CALC");
	$ListView->setModStrings($mod_strings);
	$ListView->processListView($filter_object, "main", "SUB_CALC");









?>
