<?php

include_once("include/workflow/alert_utils.php");
include_once("include/workflow/action_utils.php");
include_once("include/workflow/time_utils.php");
include_once("include/workflow/trigger_utils.php");
//BEGIN WFLOW PLUGINS
include_once("include/workflow/custom_utils.php");
//END WFLOW PLUGINS
	class Tasks_workflow {
	function process_wflow_triggers(& $focus){
		include("custom/modules/Tasks/workflow/triggers_array.php");
		include("custom/modules/Tasks/workflow/alerts_array.php");
		include("custom/modules/Tasks/workflow/actions_array.php");
		include("custom/modules/Tasks/workflow/plugins_array.php");
		
 if( ($focus->assigned_user_id ==  'bc5bc161-c4f1-422f-d76d-45bab06c211b')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['c6230e2b_78d8_61a4_0100_4cad1d79abd8'])){
		$triggeredWorkflows['c6230e2b_78d8_61a4_0100_4cad1d79abd8'] = true;
		 unset($alertshell_array); 
		 process_workflow_actions($focus, $action_meta_array['Tasks0_action0']); 
 	}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 

if(empty($focus->fetched_row['id'])){ 
 
 if( ($focus->assigned_user_id ==  'ee52d683-1333-32b7-10f2-43f4c99ac523')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( ($focus->created_by ==  'ee52d683-1333-32b7-10f2-43f4c99ac523')	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['c6f96586_8315_3b38_59b9_4cad1d4ecf55'])){
		$triggeredWorkflows['c6f96586_8315_3b38_59b9_4cad1d4ecf55'] = true;
		 process_workflow_actions($focus, $action_meta_array['Tasks1_action0']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['Tasks'] = isset($_SESSION['WORKFLOW_ALERTS']['Tasks']) && is_array($_SESSION['WORKFLOW_ALERTS']['Tasks']) ? $_SESSION['WORKFLOW_ALERTS']['Tasks'] : array();
		$_SESSION['WORKFLOW_ALERTS']['Tasks'] = array_merge($_SESSION['WORKFLOW_ALERTS']['Tasks'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 

		 //End if new, update, or all record
 		} 


 if( (
 	 ( 
 		$focus->sales_ops_task_c ==  'true'|| 
 		$focus->sales_ops_task_c ==  'on'||  
 		$focus->sales_ops_task_c ==  '1'
 	 )  
)){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( ( !($focus->fetched_row['status'] ==  'Completed' )) && 
 ($focus->status ==  'Completed')	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['c7d153ac_8205_98f8_013a_4cad1deef703'])){
		$triggeredWorkflows['c7d153ac_8205_98f8_013a_4cad1deef703'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "54b6c318-bdc8-5cd5-f6cc-468ac3a70d17"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Tasks2_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 

if(isset($focus->fetched_row['id']) && $focus->fetched_row['id']!=""){ 
 
 if((isset($GLOBALS['current_user']->department) && $GLOBALS['current_user']->department == 'Customer Support' && $focus->created_by != $GLOBALS['current_user']->id)){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['c85cc26f_d3f4_a746_ca36_4cad1dddd636'])){
		$triggeredWorkflows['c85cc26f_d3f4_a746_ca36_4cad1dddd636'] = true;
		$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['Tasks'] = isset($_SESSION['WORKFLOW_ALERTS']['Tasks']) && is_array($_SESSION['WORKFLOW_ALERTS']['Tasks']) ? $_SESSION['WORKFLOW_ALERTS']['Tasks'] : array();
		$_SESSION['WORKFLOW_ALERTS']['Tasks'] = array_merge($_SESSION['WORKFLOW_ALERTS']['Tasks'],array ('Tasks3_alert0',));	}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 

		 //End if new, update, or all record
 		} 


 if( ( !(
 	 ( 
 		$focus->fetched_row['sales_management_approval_c'] ==  'true'|| 
 		$focus->fetched_row['sales_management_approval_c'] ==  'on'|| 
 		$focus->fetched_row['sales_management_approval_c'] ==  '1'||  
 		$focus->fetched_row['sales_management_approval_c'] == 1
 	 )  
 )) && 
 (
 	 ( 
 		isset($focus->sales_management_approval_c) && $focus->sales_management_approval_c ==  'true'|| 
 		isset($focus->sales_management_approval_c) && $focus->sales_management_approval_c ==  'on'|| 
 		isset($focus->sales_management_approval_c) && $focus->sales_management_approval_c ==  '1'||  
 		isset($focus->sales_management_approval_c) && $focus->sales_management_approval_c == 1
 	 )  
)){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['c8e8c12b_a5c0_16c9_7055_4cad1d849120'])){
		$triggeredWorkflows['c8e8c12b_a5c0_16c9_7055_4cad1d849120'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "abffb815-44d4-b0d9-3d2d-4a12ddccba52"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Tasks4_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


	//end function process_wflow_triggers
	}
	
	//end class
	}

?>