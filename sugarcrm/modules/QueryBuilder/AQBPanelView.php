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
 * $Id: AQBPanelView.php 45763 2009-04-01 19:16:18Z majed $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/




require_once('modules/QueryBuilder/QueryColumn.php');
require_once('modules/QueryBuilder/QueryGroupBy.php');


global $app_strings;
//we don't want the parent module's string file, but rather the string file specifc to this subpanel
global $current_language;
$current_module_strings = return_module_language($current_language, 'QueryBuilder');

global $currentModule;
global $theme;
global $focus;
global $action;




// focus_list is the means of passing data to a SubPanelView.
global $focus_list;

global $current_user;
$header_text = '';
if(is_admin($current_user) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){	
		$header_text = "&nbsp;<a href='index.php?action=index&module=DynamicLayout&from_action=SubPanelView&from_module=Leads&record=". $_REQUEST['record']."'>".SugarThemeRegistry::current()->getImage/*ALTFIXED*/("EditLayout","border='0' align='bottom'",null,null,'.gif',$mod_strings['LBL_EDITLAYOUT'])."</a>";
}


$aqb_sub_xtpl=new XTemplate ('modules/QueryBuilder/AQBPanelView.html');
$aqb_sub_xtpl->assign("MOD", $mod_strings);
$aqb_sub_xtpl->assign("APP", $app_strings);
$aqb_sub_xtpl->assign("GRIDLINE", $gridline);
$aqb_sub_xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);
$aqb_sub_xtpl->assign("RECORD", $focus->id);
$aqb_sub_xtpl->assign('EDIT_INLINE', SugarThemeRegistry::current()->getImage/*ALTFIXED*/('edit_inline','align="absmiddle" border="0"',null,null,'.gif',$app_strings['LNK_EDIT']));
$aqb_sub_xtpl->assign('MOVE_INLINE', SugarThemeRegistry::current()->getImage/*ALTFIXED*/('arrow','align="absmiddle" border="0"',null,null,'.gif',$mod_strings['LBL_ORDER']));
$aqb_sub_xtpl->assign('DELETE_INLINE_PNG', SugarThemeRegistry::current()->getImage/*ALTFIXED*/("delete_inline", 'align="absmiddle" border="0"',null,null,'.gif',$app_strings['LNK_DELETE']));


//Column Form
	//core form elements	
	$column_form =		"<input type=\"hidden\" name=\"module\" value=\"QueryBuilder\"> \n";
	$column_form .=		"<input type=\"hidden\" name=\"record\" value=\"{$focus->id}\"> \n";
	$column_form .=		"<input type=\"hidden\" name=\"action\"> \n";
	$column_form .=		"<input type=\"hidden\" name=\"return_module\" value=\"QueryBuilder\"> \n";
	$column_form .=		"<input type=\"hidden\" name=\"return_id\" value=\"{$focus->id}\"> \n";
	$column_form .=		"<input type=\"hidden\" name=\"return_action\" value=\"DetailView\"> \n";
	$column_form .=		"<input type=\"hidden\" name=\"component\" value=\"Column\"> \n";
	
	//column elements
	$column_form .=		"<input type=\"hidden\" name=\"column_record\"> \n";
	$column_form .=		"<input type=\"hidden\" name=\"column_name\"> \n";
	$column_form .=		"<input type=\"hidden\" name=\"column_module\"> \n";
	$column_form .=		"<input type=\"hidden\" name=\"parent_id\" value=\"{$focus->id}\"> \n";
	$column_form .=		"<input type=\"hidden\" name=\"column_type\"> \n";

	//list order elements
	$column_form .=		"<input type=\"hidden\" name=\"change_order\"> \n";
	$column_form .=		"<input type=\"hidden\" name=\"magnitude\"> \n";
	$column_form .=		"<input type=\"hidden\" name=\"direction\"> \n";
	
	
	//column calculation elements
	$column_form .=		"<input type=\"hidden\" name=\"calc_field\"> \n";
	$column_form .=		"<input type=\"hidden\" name=\"calc_module\"> \n";
	$column_form .=		"<input type=\"hidden\" name=\"name\" > \n";
	$column_form .=		"<input type=\"hidden\" name=\"type\"> \n";
	$column_form .=		"<input type=\"hidden\" name=\"calc_type\"> \n";
 	$column_form .=		"<input type=\"hidden\" name=\"calc_id\"> \n";
	
 	$aqb_sub_xtpl->assign("COLUMN_FORM", $column_form);


//Group By Form
	//core form elements
	$groupby_form =		"<input type=\"hidden\" name=\"module\" value=\"QueryBuilder\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"record\" value=\"{$focus->id}\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"action\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"return_module\" value=\"QueryBuilder\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"return_id\" value=\"{$focus->id}\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"return_action\" value=\"DetailView\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"component\" value=\"GroupBy\"> \n";
	
	//group by elements
	$groupby_form .=		"<input type=\"hidden\" name=\"groupby_record\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"groupby_axis\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"groupby_module\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"groupby_field\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"groupby_calc_module\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"groupby_calc_field\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"groupby_qualifier\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"groupby_qualifier_qty\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"groupby_type\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"groupby_calc_type\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"groupby_qualifier_start\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"groupby_order\"> \n";


	
	//dual elements
	$groupby_form .=		"<input type=\"hidden\" name=\"parent_id\"> \n";
	
	//list order elements
	$groupby_form .=		"<input type=\"hidden\" name=\"change_order\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"magnitude\"> \n";
	$groupby_form .=		"<input type=\"hidden\" name=\"direction\"> \n";



$aqb_sub_xtpl->assign("GROUPBY_FORM", $groupby_form);

//Retrieve the columns to display
$column_object = new QueryColumn();
$column_object->parent_id = $focus->id;
$column_object->retrieve_columns_display($aqb_sub_xtpl, "column");


//Retrieve the y-axis group_bys to display
$groupby_object = new QueryGroupBy();
$groupby_object->parent_id = $focus->id;
$groupby_object->retrieve_groupby_display($aqb_sub_xtpl, "groupby");


//Retrieve the filters to display




//Retrieve the actual SQL statement - precusor to running the query
$the_query = $focus->run_query();


$aqb_sub_xtpl->assign("THE_QUERY", $the_query);



$aqb_sub_xtpl->parse("main");
$aqb_sub_xtpl->out("main.column");

if($aqb_sub_xtpl->parsed("main.groupby.field")) $aqb_sub_xtpl->out("main.groupby.field");
$aqb_sub_xtpl->out("main.groupby");
$aqb_sub_xtpl->out("main");


?>
