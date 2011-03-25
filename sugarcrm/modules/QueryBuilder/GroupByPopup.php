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
 * $Id: GroupByPopup.php 45763 2009-04-01 19:16:18Z majed $
 * Description:
 ********************************************************************************/

global $theme;
require_once('modules/QueryBuilder/QueryBuilder.php');
require_once('modules/QueryBuilder/QueryColumn.php');
require_once('modules/QueryBuilder/QueryGroupBy.php');






global $app_strings;
global $app_list_strings;
global $mod_strings;

global $urlPrefix;
global $currentModule;


$seed_object = new QueryBuilder();

if(isset($_REQUEST['record']) && isset($_REQUEST['record'])) {
    $seed_object->retrieve($_REQUEST['record']);
}




////////////////////////////////////////////////////////
// Start the output
////////////////////////////////////////////////////////
if (!isset($_REQUEST['html'])) {
	$form =new XTemplate ('modules/QueryBuilder/GroupBy_Popup_picker.html');
	$GLOBALS['log']->debug("using file modules/QueryBuilder/GroupBy_Popup_picker.html");
}
else {
    $_REQUEST['html'] = preg_replace("/[^a-zA-Z0-9_]/", "", $_REQUEST['html']);
    $GLOBALS['log']->debug("_REQUEST['html'] is ".$_REQUEST['html']);
	$form =new XTemplate ('modules/QueryBuilder/'.$_REQUEST['html'].'.html');
	$GLOBALS['log']->debug("using file modules/QueryBuilder/".$_REQUEST['html'].'.html');
}

$form->assign("MOD", $mod_strings);
$form->assign("APP", $app_strings);

// the form key is required
if(!isset($_REQUEST['form']))
	sugar_die("Missing 'form' parameter");

// This code should always return an answer.
// The form name should be made into a parameter and not be hard coded in this file.

$focus = new QueryGroupBy();

if(isset($_REQUEST['groupby_record']) && isset($_REQUEST['groupby_record'])) {
   $focus->retrieve($_REQUEST['groupby_record']);
}

if ($_REQUEST['component'] == 'GroupBy')
{
        $the_javascript  = "<script type='text/javascript' language='JavaScript'>\n";
        $the_javascript .= "function set_return(action, groupby_module, groupby_calc_module, groupby_type, groupby_calc_type, groupby_axis, groupby_qualifier, groupby_qualifier_qty, groupby_qualifier_start) {\n";
        $the_javascript .= "    window.opener.document.GroupByView.groupby_field.value = document.getElementById('field_frame').contentDocument.dropdownview.column_name.value;\n";
        $the_javascript .= "    window.opener.document.GroupByView.groupby_module.value = groupby_module;\n";
        $the_javascript .= "    window.opener.document.GroupByView.groupby_calc_field.value = document.getElementById('calc_field_frame').contentDocument.dropdownview.column_name.value;\n";
        $the_javascript .= "    window.opener.document.GroupByView.groupby_calc_module.value = groupby_calc_module;\n";
        $the_javascript .= "    window.opener.document.GroupByView.groupby_type.value = groupby_type;\n";
        $the_javascript .= "    window.opener.document.GroupByView.groupby_calc_type.value = groupby_calc_type;\n";
        $the_javascript .= "    window.opener.document.GroupByView.groupby_axis.value = groupby_axis;\n";
        $the_javascript .= "    window.opener.document.GroupByView.groupby_qualifier.value = groupby_qualifier;\n";
        $the_javascript .= "    window.opener.document.GroupByView.groupby_qualifier_qty.value = groupby_qualifier_qty;\n";
        $the_javascript .= "    window.opener.document.GroupByView.groupby_qualifier_start.value = groupby_qualifier_start;\n";
        $the_javascript .= "    window.opener.document.GroupByView.action.value = action;\n";
       	$the_javascript .= "    window.opener.document.GroupByView.groupby_record.value = '".$focus->id."';\n";
        $the_javascript .= "    window.opener.document.GroupByView.parent_id.value = '".$focus->parent_id."';\n";
        $the_javascript .= "	window.opener.document.GroupByView.submit(); \n";
        $the_javascript .= "}\n";
        $the_javascript .= "</script>\n";




	$groupby_select_array = $seed_object->get_relationship_modules($seed_object->base_module);

	$form->assign("GROUPBY_MODULE", get_select_options_with_id($groupby_select_array,$focus->groupby_module));
	$form->assign("GROUPBY_CALC_MODULE", get_select_options_with_id($groupby_select_array,$focus->groupby_calc_module));

	$form->assign("GROUPBY_FIELD", $focus->groupby_field);
	$form->assign("GROUPBY_CALC_FIELD", $focus->groupby_calc_field);

	$form->assign("GROUPBY_AXIS", get_select_options_with_id($app_list_strings['query_groupby_axis_dom'],$focus->groupby_axis));
	$form->assign("GROUPBY_TYPE", get_select_options_with_id($app_list_strings['query_groupby_type_dom'],$focus->groupby_type));
	$form->assign("GROUPBY_CALC_TYPE", get_select_options_with_id($app_list_strings['query_groupby_calc_type_dom'],$focus->groupby_calc_type));
	$form->assign("GROUPBY_QUALIFIER", get_select_options_with_id($app_list_strings['query_groupby_qualifier_dom'],$focus->groupby_qualifier));
	$form->assign("GROUPBY_QUALIFIER_START", get_select_options_with_id($app_list_strings['query_groupby_qualifier_start_dom'],$focus->groupby_qualifier_start));
	$form->assign("GROUPBY_QUALIFIER_QTY", get_select_options_with_id($app_list_strings['query_groupby_qualifier_qty_dom'],$focus->groupby_qualifier_qty));


}
$form->assign("MODULE_NAME", $currentModule);
$form->assign("FORM", $_REQUEST['form']);
$form->assign("GRIDLINE", $gridline);
$form->assign("SET_RETURN_JS", $the_javascript);

insert_popup_header($theme);

$form->parse("embeded");
$form->out("embeded");


$form->parse("main");
$form->out("main");


?>

<?php insert_popup_footer(); ?>
