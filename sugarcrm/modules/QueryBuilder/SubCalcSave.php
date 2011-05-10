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
 * $Id: SubCalcSave.php 13782 2006-06-06 17:58:55Z majed $
 * Description:  
 ********************************************************************************/

require_once('modules/QueryBuilder/QueryFilter.php');




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
