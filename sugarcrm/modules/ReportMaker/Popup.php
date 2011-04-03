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
 * $Id: Popup.php 45763 2009-04-01 19:16:18Z majed $
 * Description:
 ********************************************************************************/

global $theme;







global $app_strings;
global $app_list_strings;
global $mod_strings;

global $urlPrefix;
global $currentModule;


$seed_object = new DataSet();


$where = "";
if(isset($_REQUEST['query']))
{
	$search_fields = Array("name", "description");

	$where_clauses = Array();

	append_where_clause($where_clauses, "name", "data_sets.name");
	append_where_clause($where_clauses, "description", "data_sets.description");

	$where = generate_where_statement($where_clauses);
	$GLOBALS['log']->info($where);
}




////////////////////////////////////////////////////////
// Start the output
////////////////////////////////////////////////////////
if (!isset($_REQUEST['html'])) {
	$form =new XTemplate ('modules/DataSets/Popup_picker.html');
	$GLOBALS['log']->debug("using file modules/DataSets/Popup_picker.html");
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
if(!isset($_REQUEST['form']))
	sugar_die("Missing 'form' parameter");

// This code should always return an answer.
// The form name should be made into a parameter and not be hard coded in this file.

if ($_REQUEST['form'] == 'EditView')
{
        $the_javascript  = "<script type='text/javascript' language='JavaScript'>\n";
        $the_javascript .= "function set_return(parent_id, parent_name) {\n";
        $the_javascript .= "    window.opener.document.EditView.parent_name.value = parent_name;\n";
        $the_javascript .= "    window.opener.document.EditView.parent_id.value = parent_id;\n";
        $the_javascript .= "}\n";
        $the_javascript .= "</script>\n";
        $button  = "<form action='index.php' method='post' name='form' id='form'>\n";

        $button .= "<input title='".$app_strings['LBL_CLEAR_BUTTON_TITLE']."' accessKey='".$app_strings['LBL_CLEAR_BUTTON_KEY']."' class='button' LANGUAGE=javascript onclick=\"window.opener.document.EditView.parent_name.value = '';window.opener.document.EditView.parent_id.value = ''; window.close()\" type='submit' name='button' value='  Clear  '>\n";
        $button .= "<input title='".$app_strings['LBL_CANCEL_BUTTON_TITLE']."' accessKey='".$app_strings['LBL_CANCEL_BUTTON_KEY']."' class='button' LANGUAGE=javascript onclick=\"window.close()\" type='submit' name='button' value='  ".$app_strings['LBL_CANCEL_BUTTON_LABEL']."  '>\n";
        $button .= "</form>\n";
}


$form->assign("SET_RETURN_JS", $the_javascript);
$form->assign("MODULE_NAME", $currentModule);
$form->assign("FORM", $_REQUEST['form']);

insert_popup_header($theme);

// Quick search.
echo get_form_header($mod_strings['LBL_SEARCH_FORM_TITLE'], "", false);

if (isset($_REQUEST['description']))
{
	$last_search['DESCRIPTION'] = $_REQUEST['description'];

}

if (isset($_REQUEST['name']))
{
	$last_search['NAME'] = $_REQUEST['name'];

}

if (isset($last_search))
{
	$form->assign("LAST_SEARCH", $last_search);
}

$form->parse("main.SearchHeader");
$form->out("main.SearchHeader");

$form->parse("main.SearchHeaderEnd");
$form->out("main.SearchHeaderEnd");

// Reset the sections that are already in the page so that they do not print again later.
$form->reset("main.SearchHeader");
$form->reset("main.SearchHeaderEnd");

// Stick the form header out there.



$ListView = new ListView();
$ListView->show_export_button = false;
$ListView->setXTemplate($form);
$ListView->setHeaderTitle($mod_strings['LBL_LIST_FORM_TITLE']);
$ListView->setHeaderText($button);
$ListView->setQuery($where, "", "name", "DATA_SET");
$ListView->setModStrings($mod_strings);
$ListView->processListView($seed_object, "main", "DATA_SET");
?>

<?php insert_popup_footer(); ?>
