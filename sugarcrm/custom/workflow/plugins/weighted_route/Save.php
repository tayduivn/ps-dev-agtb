<?php
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
 * $Id: Save.php,v 1.1 2006/07/07 22:00:05 jgreen Exp $
 * Description:  
 ********************************************************************************/

require_once('modules/WorkFlowActionShells/WorkFlowActionShell.php');
require_once('modules/Expressions/Expression.php');
require_once('log4php/LoggerManager.php');



$past_remove = false;


$focus = new WorkFlowActionShell();


if(!empty($_POST['record']) && $_POST['record']!=""){
	$focus->retrieve($_POST['record']);
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

	$weighted_array = array();

	$weighted_array['user_1'] = $_POST['user_1'];
	$weighted_array['user_1_weight'] =$_POST['user_1_weight'];
	$weighted_array['user_2'] = $_POST['user_2'];
	$weighted_array['user_2_weight'] =$_POST['user_2_weight'];

	$focus->parameters = base64_encode(serialize($weighted_array));


	$focus->save();
	$parent_id = $focus->id;


$focus->save();

$workflow_object = $focus->get_workflow_object();

$workflow_object->write_workflow();

$return_id = $focus->parent_id;

if(isset($_POST['return_module']) && $_POST['return_module'] != "") $return_module = $_POST['return_module'];
else $return_module = "WorkFlowTriggerShells";
if(isset($_POST['return_action']) && $_POST['return_action'] != "") $return_action = $_POST['return_action'];
else $return_action = "CreateStep1";
if(isset($_POST['return_id']) && $_POST['return_id'] != "") $return_id = $_POST['return_id'];

$GLOBALS['log']->debug("Saved record with id of ".$return_id);
//exit;
header("Location: index.php?action=$return_action&module=$return_module&record=$return_id&workflow_id=$parent_id&parent_id=$parent_id&special_action=refresh");
?>
