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
 * $Id: ControllerPopup.php 45763 2009-04-01 19:16:18Z majed $
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
	$form =new XTemplate ('modules/QueryBuilder/Controller_Popup_picker.html');
	$GLOBALS['log']->debug("using file modules/QueryBuilder/Controller_Popup_picker.html");
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



if ($_REQUEST['component'] == 'Column')
{
	$focus = new QueryColumn();
	if(isset($_REQUEST['column_record']) && isset($_REQUEST['column_record'])) {
    	$focus->retrieve($_REQUEST['column_record']);
	}

        $the_javascript  = "<script type='text/javascript' language='JavaScript'>\n";
        $the_javascript .= "function set_return(magnitude, direction) {\n";
        $the_javascript .= "    window.opener.document.ColumnView.action.value = 'ColumnSave';\n";
       	$the_javascript .= "    window.opener.document.ColumnView.column_record.value = '".$focus->id."';\n";
        $the_javascript .= "    window.opener.document.ColumnView.magnitude.value = magnitude;\n";
       	$the_javascript .= "    window.opener.document.ColumnView.direction.value = direction;\n";
       	$the_javascript .= "    window.opener.document.ColumnView.change_order.value = 'Y';\n";
       	$the_javascript .= "	window.opener.document.ColumnView.submit(); \n";
        $the_javascript .= "}\n";
        $the_javascript .= "</script>\n";

        $display_title = $mod_strings['LBL_COLUMN_NAME'];
        $form->assign("DISPLAY_TITLE", $display_title);
        $form->assign("DISPLAY_NAME", $focus->column_name);
}

if ($_REQUEST['component'] == 'GroupBy')
{
	$focus = new QueryGroupBy();
	if(isset($_REQUEST['groupby_record']) && isset($_REQUEST['groupby_record'])) {
    	$focus->retrieve($_REQUEST['groupby_record']);
	}

        $the_javascript  = "<script type='text/javascript' language='JavaScript'>\n";
        $the_javascript .= "function set_return(magnitude, direction) {\n";
        $the_javascript .= "    window.opener.document.GroupByView.action.value = 'GroupBySave';\n";
       	$the_javascript .= "    window.opener.document.GroupByView.groupby_record.value = '".$focus->id."';\n";
        $the_javascript .= "    window.opener.document.GroupByView.magnitude.value = magnitude;\n";
       	$the_javascript .= "    window.opener.document.GroupByView.direction.value = direction;\n";
       	$the_javascript .= "    window.opener.document.GroupByView.change_order.value = 'Y';\n";
       	$the_javascript .= "	window.opener.document.GroupByView.submit(); \n";
        $the_javascript .= "}\n";
        $the_javascript .= "</script>\n";

        $display_title = $mod_strings['LBL_GROUPBY_FIELD'];
        $form->assign("DISPLAY_TITLE", $display_title);
        $form->assign("DISPLAY_NAME", $focus->groupby_field);
}

$form->assign("MODULE_NAME", $currentModule);
$form->assign("FORM", $_REQUEST['form']);
$form->assign("GRIDLINE", $gridline);
$form->assign("SET_RETURN_JS", $the_javascript);

$form->assign('LEFT_INLINE', SugarThemeRegistry::current()->getImage('leftarrow','align="absmiddle" alt="Left" border="0"'));
$form->assign('RIGHT_INLINE', SugarThemeRegistry::current()->getImage('rightarrow','align="absmiddle" alt="Right" border="0"'));
$form->assign('UP_INLINE', SugarThemeRegistry::current()->getImage('uparrow_inline','align="absmiddle" alt="Up" border="0"'));
$form->assign('DOWN_INLINE', SugarThemeRegistry::current()->getImage('downarrow_inline','align="absmiddle" alt="Down" border="0"'));

insert_popup_header($theme);



$form->parse("main");
$form->out("main");


?>

<?php insert_popup_footer(); ?>
