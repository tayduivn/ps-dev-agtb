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
 * $Id: Save.php 45763 2009-04-01 19:16:18Z majed $
 * Description:  
 ********************************************************************************/

require_once('modules/DataSets/DataSet_Layout.php');

$focus = new CustomQuery();

if(!empty($_REQUEST['record']) && $_REQUEST['record']!=""){
	$focus->retrieve($_REQUEST['record']);
	$focus->get_custom_results();
	$old_column_array = $focus->get_column_array();
	$is_edit = true;
}

	$temp_custom_array = array();

	foreach($focus->column_fields as $field)
	{
		if(isset($_REQUEST[$field]))
		{
			$focus->$field = $_REQUEST[$field];
			$temp_custom_array[$field] = $_REQUEST[$field];
		}
	}
	foreach($focus->additional_column_fields as $field)
	{
		if(isset($_REQUEST[$field]))
		{
			$value = $_REQUEST[$field];
			$focus->$field = $value;
			
		}
	}

	if (!isset($_POST['query_locked'])) $focus->query_locked = 'off';
	
	
	

//Check if query has an error or not
	
	//run valid test	
	$query_error = $focus->get_custom_results(true, false, true);
	
	if($query_error['result']=="Error"){
		
		$record = $focus->id;

		if(!empty($focus->id) && $focus->id!=''){
			$edit='edit=true';
		}

		//save the variables the are temporary right now
		$_SESSION['temp_custom_array'] = $temp_custom_array;	

		$GLOBALS['log']->debug("Saved record with id of ".$return_id);

		header("Location: index.php?action=RepairQuery&module=CustomQueries&record=$record&$edit&error_msg=".$query_error['msg']."");
		exit;

	}

//End check for query error


	
$focus->custom_query = $focus->statis_query;
require_once('include/formbase.php');
$focus = populateFromPost('', $focus);
$focus->save();


//only run this if this is an is_edit query scenario
if(!empty($is_edit) && $is_edit==true){

	//only run this if this is a query that is part of a data set that has custom layout enabled
	//only do if column binding is affected.  If the names are the same, do not
	//do a check here the above two conditions.
	$check_bind = $focus->check_broken_bind($old_column_array);

	if($check_bind==true){
		$_REQUEST['return_action'] = "BindMapView";
		$_SESSION['old_column_array'] = $old_column_array;
	//end if we need to check binding conditions
	} else {
	//check to see if any new columns exist in the CSQL query
	
		$temp_select = $focus->repair_column_binding(true);
	
		foreach($old_column_array as $key => $value){
	
			//eliminate direct matches
			if(!empty($temp_select[$value])){
				unset($temp_select[$value]);
			//end eliminate direct matches
			}

		//end foreach
		}
		
		//if anything is left in the temp_select, then add this as a new column
		foreach($temp_select as $key => $value){
			$focus->add_column_to_layouts($value);

		}

		
		
	//end if else	
	}	
	
//checking if is edit is true
} else {
	$old_column_array = "";
}


$return_id = $focus->id;
//exit;
$edit='';
if(isset($_REQUEST['return_module']) && $_REQUEST['return_module'] != "") $return_module = $_REQUEST['return_module'];
else $return_module = "CustomQueries";
if(isset($_REQUEST['return_action']) && $_REQUEST['return_action'] != "") $return_action = $_REQUEST['return_action'];
else $return_action = "DetailView";
if(isset($_REQUEST['return_id']) && $_REQUEST['return_id'] != "") $return_id = $_REQUEST['return_id'];
if(!empty($_REQUEST['edit'])) {
	$return_id='';
	$edit='edit=true';
}

$GLOBALS['log']->debug("Saved record with id of ".$return_id);
header("Location: index.php?action=$return_action&module=$return_module&record=$return_id&$edit&old_column_array=$old_column_array");
?>
