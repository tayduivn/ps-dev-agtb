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





$focus = new QueryFilter();


if(!empty($_POST['filter_id'])){
	$focus->retrieve($_POST['filter_id']);

}


foreach($focus->column_fields as $field)
{
	if(isset($_POST[$field]))
	{
		$focus->$field = $_POST[$field];
	}
}

foreach($focus->additional_column_fields as $field)
{
	if(isset($_POST[$field]))
	{
		$value = $_POST[$field];
		$focus->$field = $value;
		
	}
}

	if (!isset($_POST['calc_enclosed'])) $focus->calc_enclosed = 'off';

//set the filter type to sub-calc
$focus->filter_type = "Sub-Calc";


//Clear out the following fields if this calculation is a group or none
	if (!empty($_POST['left_type']) && $_POST['left_type']!=="Field"){
		$focus->left_module = "";
		$focus->left_field = "";
	}

	if (!empty($_POST['right_type']) && $_POST['right_type']!=="Field"){
		$focus->right_module = "";
		$focus->right_field = "";
	}
	
	if (!empty($_POST['parent_filter_id'])){
		$focus->split_parent_filter_id();
	}

$focus->save();




//SUB CALC ID
$filter_id = $focus->id;

if(isset($_POST['return_module']) && $_POST['return_module'] != "") $return_module = $_POST['return_module'];
else $return_module = "QueryBuilder";
if(isset($_POST['return_action']) && $_POST['return_action'] != "") $return_action = $_POST['return_action'];
else $return_action = "DetailView";

//QUERY BUILDER ID 
if(isset($_POST['return_id']) && $_POST['return_id'] != "") $return_id = $_POST['return_id'];

//COLUMN ID
if(isset($_POST['column_record']) && $_POST['column_record'] != "") $column_record = $_POST['column_record'];

//CALC ID
if(isset($_POST['calc_id']) && $_POST['calc_id'] != "") $calc_id = $_POST['calc_id'];


//COMPONENT
if(isset($_POST['component']) && $_POST['component'] != "") $component = $_POST['component'];



$GLOBALS['log']->debug("Saved record with id of ".$filter_id);

header("Location: index.php?action=$return_action&module=$return_module&filter_id=$filter_id&record=$return_id&column_record=$column_record&calc_id=$calc_id&to_pdf=true&component=$component");
?>
