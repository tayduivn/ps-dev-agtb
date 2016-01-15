<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/*********************************************************************************

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

$form->assign('LEFT_INLINE', SugarThemeRegistry::current()->getImage('leftarrow','align="absmiddle"  border="0"',null,null,'.gif',$mod_strings['LBL_LEFT']));
$form->assign('RIGHT_INLINE', SugarThemeRegistry::current()->getImage('rightarrow','align="absmiddle" border="0"',null,null,'.gif',$mod_strings['LBL_RIGHT']));
$form->assign('UP_INLINE', SugarThemeRegistry::current()->getImage('uparrow_inline','align="absmiddle" border="0"',null,null,'.gif',$mod_strings['LBL_UP']));
$form->assign('DOWN_INLINE', SugarThemeRegistry::current()->getImage('downarrow_inline','align="absmiddle" border="0"',null,null,'.gif',$mod_strings['LBL_DOWN']));

insert_popup_header($theme);



$form->parse("main");
$form->out("main");


?>

<?php insert_popup_footer(); ?>
