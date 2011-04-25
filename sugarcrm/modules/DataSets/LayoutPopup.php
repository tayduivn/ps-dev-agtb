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
 * $Id: LayoutPopup.php 45763 2009-04-01 19:16:18Z majed $
 * Description:
 ********************************************************************************/

global $theme;
require_once('modules/DataSets/DataSet_Layout.php');







global $app_strings;
global $app_list_strings;
global $mod_strings;

global $urlPrefix;
global $currentModule;


$seed_object = new DataSet();

if(isset($_REQUEST['record']) && isset($_REQUEST['record'])) {
    $seed_object->retrieve($_REQUEST['record']);

}




////////////////////////////////////////////////////////
// Start the output
////////////////////////////////////////////////////////
if (!isset($_REQUEST['html'])) {
	$form =new XTemplate ('modules/DataSets/Layout_Popup_picker.html');
	$GLOBALS['log']->debug("using file modules/DataSets/Layout_Popup_picker.html");
}
else {
    $_REQUEST['html'] = preg_replace("/[^a-zA-Z0-9_]/", "", $_REQUEST['html']);
    $GLOBALS['log']->debug("_REQUEST['html'] is ".$_REQUEST['html']);
	$form =new XTemplate ('modules/DataSets/'.$_REQUEST['html'].'.html');
	$GLOBALS['log']->debug("using file modules/DataSets/".$_REQUEST['html'].'.html');
}

$form->assign("MOD", $mod_strings);
$form->assign("APP", $app_strings);

// the form key is required
//if(!isset($_REQUEST['form']))
//	sugar_die("Missing 'form' parameter");

// This code should always return an answer.
// The form name should be made into a parameter and not be hard coded in this file.

$focus = new DataSet_Layout();

if(isset($_REQUEST['layout_record']) ) {
    $focus->retrieve($_REQUEST['layout_record']);

	//obtain the calc_id if it exists


//Move this into the sub panel php files///////////////////////!!!!!!!!!!!

	$head_object = $focus->get_att_object("Head");
	if (!empty($head_object->id) && $head_object->id!=""){
		$form->assign("MODIFY_HEAD", "checked");
		$form->assign("TOGGLE_HEAD_START", "toggleDisplay('attheaddiv');");
		$form->assign("HEAD_ATT_ID", $head_object->id);

		if($head_object->display_type=="Scalar"){
		$form->assign("TOGGLE_SCALAR_START", "toggleDisplay('attheadscalardiv');");
		}
	}

   	$body_object = $focus->get_att_object("Body");
	if (!empty($body_object->id) && $body_object->id!=""){
		$form->assign("MODIFY_BODY", "checked");
		$form->assign("TOGGLE_BODY_START", "toggleDisplay('attbodydiv');");
		$form->assign("BODY_ATT_ID", $body_object->id);
   	}
//end if layout record is present
}

        $the_javascript  = "<script type='text/javascript' language='JavaScript'>\n";
        $the_javascript .= "function set_return() {\n";
    	$the_javascript .= "    window.opener.document.DetailView.action.value = 'DetailView';\n";
    	$the_javascript .= "	window.opener.document.DetailView.submit(); \n";
		$the_javascript .= "	window.close(); \n";
    	$the_javascript .= "}\n";
        $the_javascript .= "</script>\n";



//DataSet_Layout form variables needed
$form->assign("LAYOUT_ID", $focus->id);
$form->assign("DATASET_ID", $focus->parent_id);	//hidden var
$form->assign("PARENT_VALUE", $focus->parent_value);	//visible var
$form->assign("LIST_ORDER_Z", $focus->list_order_z);	//visible only, but editable by controller
$form->assign("LAYOUT_TYPE", get_select_options_with_id($app_list_strings['dataset_layout_type_dom'], $focus->layout_type));
$form->assign("LIST_ORDER_X", $focus->list_order_x);	//visible var, controller arrow editble only
$form->assign("ROW_HEADER_ID", $focus->row_header_id);	//visible


if ($focus->hide_column == 'on') $form->assign("HIDE_COLUMN", "checked");

//disable this selector because you are always coming to this popup, knowing what layout type it is.
$form->assign("LAYOUT_TYPE_DISABLE", "disabled");	//visible
$form->assign("DISPLAY_TYPE_DISABLE", "disabled");	//visible

$form->assign("MODULE_NAME", $currentModule);
//$form->assign("FORM", $_REQUEST['form']);
$form->assign("GRIDLINE", $gridline);
$form->assign("SET_RETURN_JS", $the_javascript);

insert_popup_header($theme);


$form->parse("embeded");
$form->out("embeded");



/////////////////Head Attribute Information/////////////////////////

		$form->assign('HEAD_TITLE', "<h3> Add/Edit Header Attributes: </h3><BR>");

		$form->assign('HEAD_FONT_SIZE', get_select_options_with_id($app_list_strings['font_size_dom'],$head_object->font_size));
		$form->assign("HEAD_BG_COLOR", get_select_options_with_id($app_list_strings['report_color_dom'],$head_object->bg_color));
		$form->assign("HEAD_FONT_COLOR", get_select_options_with_id($app_list_strings['report_color_dom'],$head_object->font_color));
		$form->assign("HEAD_STYLE", get_select_options_with_id($app_list_strings['dataset_style_dom'],$head_object->style));


		if ($head_object->wrap == 'on') $form->assign("HEAD_WRAP", "checked");


		$form->assign("HEAD_DISPLAY_TYPE", get_select_options_with_id($app_list_strings['dataset_att_display_type_dom'], $head_object->display_type));
		$form->assign("HEAD_DISPLAY_NAME", $head_object->display_name);
		if ($head_object->display_type == 'Scalar') $form->assign("HEAD_DISPLAY_NAME_DISABLED", "disabled");

		$form->assign("HEAD_FORMAT_TYPE", get_select_options_with_id($app_list_strings['dataset_att_format_type_scalar_dom'], $head_object->format_type));

$form->parse("main.attribute_head");


/////////////////Body Attribute Information/////////////////////////


		$form->assign('BODY_TITLE', "<h3> Add/Edit Body Attributes: </h3><BR>");

		$form->assign('BODY_FONT_SIZE', get_select_options_with_id($app_list_strings['font_size_dom'],$body_object->font_size));
		$form->assign("BODY_BG_COLOR", get_select_options_with_id($app_list_strings['report_color_dom'],$body_object->bg_color));
		$form->assign("BODY_FONT_COLOR", get_select_options_with_id($app_list_strings['report_color_dom'],$body_object->font_color));
		$form->assign("BODY_STYLE", get_select_options_with_id($app_list_strings['dataset_style_dom'],$body_object->style));


		if ($body_object->wrap == 'on') $form->assign("BODY_WRAP", "checked");


		$form->assign("BODY_FORMAT_TYPE", get_select_options_with_id($app_list_strings['dataset_att_format_type_dom'], $body_object->format_type));
		//$form->assign("BODY_FORMAT", $body_object->format);

		$form->assign("BODY_SIZE_TYPE", get_select_options_with_id($app_list_strings['width_type_dom'],$body_object->size_type));
		$form->assign('BODY_CELL_SIZE', $body_object->cell_size);

$form->parse("main.attribute_body");

$form->parse("main");
$form->out("main");


echo "<p><p>";

?>

<?php insert_popup_footer(); ?>
