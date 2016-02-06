<?php
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
require_once('modules/QueryBuilder/QueryCalc.php');






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
	$form =new XTemplate ('modules/QueryBuilder/Column_Popup_picker.html');
	$GLOBALS['log']->debug("using file modules/QueryBuilder/Column_Popup_picker.html");
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
//if(!isset($_REQUEST['form']))
//	sugar_die("Missing 'form' parameter");

// This code should always return an answer.
// The form name should be made into a parameter and not be hard coded in this file.

$focus = new QueryColumn();
$calc_object = new QueryCalc();

if(isset($_REQUEST['column_record']) ) {
    $focus->retrieve($_REQUEST['column_record']);

	//obtain the calc_id if it exists
    $calc_id = $focus->get_calc_id();

	if(!empty($calc_id) ) {
	    $calc_object->retrieve($calc_id);
	}


}


if ($_REQUEST['component'] == 'Column')
{
        $the_javascript  = "<script type='text/javascript' language='JavaScript'>\n";
        $the_javascript .= "function set_return(action, column_module, column_type, name, calc_module, type, calc_type) {\n";
        $the_javascript .= "    window.opener.document.ColumnView.column_name.value = document.getElementById('listiframe').contentDocument.dropdownview.column_name.value;\n";
        $the_javascript .= "    window.opener.document.ColumnView.column_module.value = column_module;\n";
        $the_javascript .= "    window.opener.document.ColumnView.column_type.value = column_type;\n";
        $the_javascript .= "    window.opener.document.ColumnView.action.value = action;\n";
       	$the_javascript .= "    window.opener.document.ColumnView.column_record.value = '".$focus->id."';\n";


//Calculation informaton

		$the_javascript .= "    window.opener.document.ColumnView.calc_field.value = document.getElementById('calciframe').contentDocument.dropdownview.column_name.value;\n";
    	$the_javascript .= "    window.opener.document.ColumnView.calc_module.value = calc_module;\n";
		$the_javascript .= "    window.opener.document.ColumnView.name.value = name;\n";
    	$the_javascript .= "    window.opener.document.ColumnView.type.value = type;\n";
		$the_javascript .= "    window.opener.document.ColumnView.calc_type.value = calc_type;\n";
    	$the_javascript .= "    window.opener.document.ColumnView.calc_id.value = '".$calc_object->id."';\n";
    	$the_javascript .= "	window.opener.document.ColumnView.submit(); \n";

    	$the_javascript .= "}\n";
        $the_javascript .= "</script>\n";



	$column_select_array = $seed_object->get_relationship_modules($seed_object->base_module);

	$form->assign("AVAILABLE_MODULES", get_select_options_with_id($column_select_array,$focus->column_module));
	$form->assign("CALC_MODULES", get_select_options_with_id($column_select_array,$focus->column_module));


	$form->assign("COLUMN_DISPLAY_SWITCH", get_select_options_with_id($app_list_strings['query_column_type_dom'],$focus->column_type));
    $form->assign("COLUMN_NAME", $focus->column_name);




//Calculation Column Type



    	$column_select_array2 = $seed_object->get_relationship_modules();
   		$form->assign("CALC_MODULES", get_select_options_with_id($column_select_array2,$calc_object->calc_module));

    	$form->assign("CALC_NAME", $calc_object->name);
    	$form->assign("CALC_ID", $calc_object->id);
    	$form->assign("CALC_TYPE", get_select_options_with_id($app_list_strings['query_calc_calc_type_dom'], $calc_object->calc_type));
		$form->assign("TYPE", get_select_options_with_id($app_list_strings['query_calc_type_dom'], $calc_object->type));



	if(!empty($calc_object->type) && $calc_object->type=="Math"){

		//Disable column_type
		//Disable type
		//Disable name
		//Disable calc_type
		$form->assign("SUB_CALC_DISABLE", "disabled");


		//Change select button display name to Finished
		$form->assign("SELECT_BUTTON_LABEL", $mod_strings['LBL_FINISHED_BUTTON_LABEL']);
		$form->assign("SELECT_BUTTON_TITLE", $mod_strings['LBL_FINISHED_BUTTON_TITLE']);

	} else {
	//set stuff for standard calculation
		$form->assign("SELECT_BUTTON_LABEL", $app_strings['LBL_SELECT_BUTTON_LABEL']);
		$form->assign("SELECT_BUTTON_TITLE", $app_strings['LBL_SELECT_BUTTON_TITLE']);

	}



}

$form->assign("MODULE_NAME", $currentModule);
//$form->assign("FORM", $_REQUEST['form']);
$form->assign("GRIDLINE", $gridline);
$form->assign("SET_RETURN_JS", $the_javascript);

insert_popup_header($theme);


$form->parse("embeded");
$form->out("embeded");


$form->parse("main");
$form->out("main");

//Process the sub-query and math sub-calculation window
if(!empty($calc_object->type) && $calc_object->type=="Math"){


//echo "SubcalcView.php".$calc_object->id."JKL";
//in subcalc, build form and make sure you are passing all the necessary stuff


//pass record - querybuilder
//pass column_record - querycolumn


//Display the UI query builder tool
//
//Sub Panel for the UI Tool

echo "<p><p>";

include('modules/QueryBuilder/SubcalcView.php');

}


?>

<?php insert_popup_footer(); ?>
