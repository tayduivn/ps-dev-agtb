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
 * $Id: ColumnSave.php 13782 2006-06-06 17:58:55Z majed $
 * Description:
 ********************************************************************************/

require_once('modules/QueryBuilder/QueryColumn.php');
require_once('modules/QueryBuilder/QueryCalc.php');
require_once('include/controller/Controller.php');




$focus = new QueryColumn();


if(!empty($_POST['column_record'])){
	$focus->retrieve($_POST['column_record']);

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


//Conduct a column data save

//echo "COLUMN_MODULE:".$focus->column_module;
//echo "COLUMN_TYPE:".$focus->column_type;
//echo "COLUMN_NAME:".$focus->column_name;
//echo "ACTION:".$_REQUEST['action'];


$controller = new Controller();

//run through change order if needed
if(!empty($_REQUEST['change_order']) && $_REQUEST['change_order']=="Y"){


///This is a hack, fix this. Maybe create a separate save file for when you change order
	$focus->retrieve($_POST['column_record']);

	$magnitude = 1;
	$direction = $_REQUEST['direction'];

	$controller->init($focus, "Save");
	$controller->change_component_order($magnitude, $direction, $focus->parent_id);

}

//run the order graber if this is new
if(empty($focus->id)){
	$controller->init($focus, "New");
	$controller->change_component_order("", "", $focus->parent_id);
}


	if($focus->column_type=="Calculation"){
		//echo "TYPE:".$_POST['type']."<BR>";
		//echo "CALC_TYPE:".$_POST['calc_type']."<BR>";
		//echo "CALC_NAME:".$_POST['name']."<BR>";
		//echo "CALC_FIELD:".$_POST['calc_field']."<BR>";
		//echo "CALC_MODULE:".$_POST['calc_module']."<BR>";

		//Clean out column information
		$focus->column_module = "";
		$focus->column_name = "";

		//Save the calculation information
		$calc_object = new QueryCalc();
		if(!empty($_REQUEST['calc_id'])){
			$calc_object->retrieve($_REQUEST['calc_id']);
		}

		foreach($calc_object->column_fields as $field)
		{
			if(isset($_POST[$field]))
			{
				$calc_object->$field = $_POST[$field];
			}
		}

		if(!empty($calc_object->type) && $calc_object->type=="Math"){

			$calc_object->calc_module = "";
			$calc_object->calc_field = "";
		}


	//end if a calculation exists
	}

$focus->save();


//if this is a calculation column, save now, but only if this isnt from the controller
if(!empty($_REQUEST['change_order']) && $_REQUEST['change_order']=="Y"){
} else {
	if($focus->column_type=="Calculation"){
		$calc_object->parent_id = $focus->id;
		$calc_object->save();
	}
}

$return_id = $focus->id;

if(isset($_POST['return_module']) && $_POST['return_module'] != "") $return_module = $_POST['return_module'];
else $return_module = "QueryBuilder";
if(isset($_POST['return_action']) && $_POST['return_action'] != "") $return_action = $_POST['return_action'];
else $return_action = "DetailView";
if(isset($_POST['return_id']) && $_POST['return_id'] != "") $return_id = $_POST['return_id'];

$GLOBALS['log']->debug("Saved record with id of ".$return_id);

header("Location: index.php?action=$return_action&module=$return_module&record=$return_id");
?>
