<?php

include_once("include/workflow/alert_utils.php");
include_once("include/workflow/action_utils.php");
include_once("include/workflow/time_utils.php");
include_once("include/workflow/trigger_utils.php");
//BEGIN WFLOW PLUGINS
include_once("include/workflow/custom_utils.php");
//END WFLOW PLUGINS
	class Opportunities_workflow {
	function process_wflow_triggers(& $focus){
		include("custom/modules/Opportunities/workflow/triggers_array.php");
		include("custom/modules/Opportunities/workflow/alerts_array.php");
		include("custom/modules/Opportunities/workflow/actions_array.php");
		include("custom/modules/Opportunities/workflow/plugins_array.php");
		
 if( ($focus->opportunity_type ==  'Sugar Network')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( ($focus->sales_stage ==  'Closed Won')	 ){ 
	 

	 //Secondary Trigger number #2
	 $secondary_array = check_rel_filter($focus, $secondary_array, 'accounts', $trigger_meta_array['trigger_0_secondary_1'], 'any'); 
	 if(($secondary_array['results']==true)	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['9a9474b3_cc98_6ea3_e947_4cad1d613462'])){
		$triggeredWorkflows['9a9474b3_cc98_6ea3_e947_4cad1d613462'] = true;
		 process_workflow_actions($focus, $action_meta_array['Opportunities0_action0']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['Opportunities'] = isset($_SESSION['WORKFLOW_ALERTS']['Opportunities']) && is_array($_SESSION['WORKFLOW_ALERTS']['Opportunities']) ? $_SESSION['WORKFLOW_ALERTS']['Opportunities'] : array();
		$_SESSION['WORKFLOW_ALERTS']['Opportunities'] = array_merge($_SESSION['WORKFLOW_ALERTS']['Opportunities'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 // End Secondary Trigger number #2
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( ( !($focus->fetched_row['sales_stage'] ==  'Closed Won' )) && 
 ($focus->sales_stage ==  'Closed Won')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['9b656b57_fe8c_ee67_988d_4cad1d25d588'])){
		$triggeredWorkflows['9b656b57_fe8c_ee67_988d_4cad1d25d588'] = true;
		 unset($alertshell_array); 
		 process_workflow_actions($focus, $action_meta_array['Opportunities1_action0']); 
 	}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->sales_stage) && $focus->sales_stage !=  'Finance Closed')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( (isset($focus->Term_c) && $focus->Term_c ==  'Remainder of Term')	 ){ 
	 

	 //Secondary Trigger number #2
	 if( (isset($focus->sales_stage) && $focus->sales_stage !=  'Sales Ops Closed')	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['9c320827_fc0c_350d_285f_4cad1dfdac0a'])){
		$triggeredWorkflows['9c320827_fc0c_350d_285f_4cad1dfdac0a'] = true;
		 unset($alertshell_array); 
		 process_workflow_actions($focus, $action_meta_array['Opportunities2_action0']); 
 	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 // End Secondary Trigger number #2
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->opportunity_type) && $focus->opportunity_type ==  'Sugar Express')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(true){ 
	 

	 //Secondary Trigger number #2
	 if( (isset($focus->sales_stage) && $focus->sales_stage ==  'Closed Won')	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['9d024d08_d8f3_56f4_d9e3_4cad1d8c4937'])){
		$triggeredWorkflows['9d024d08_d8f3_56f4_d9e3_4cad1d8c4937'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "55576a43-64ae-6733-2fa1-4a43d239c791"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Opportunities3_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 // End Secondary Trigger number #2
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( ( !($focus->fetched_row['Revenue_Type_c'] ==  'Additional' )) && 
 (isset($focus->Revenue_Type_c) && $focus->Revenue_Type_c ==  'Additional')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['9d8bacef_0022_9a1f_bd29_4cad1d7fdad9'])){
		$triggeredWorkflows['9d8bacef_0022_9a1f_bd29_4cad1d7fdad9'] = true;
		 unset($alertshell_array); 
		 process_workflow_actions($focus, $action_meta_array['Opportunities4_action0']); 
 	}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->opportunity_type) && $focus->opportunity_type ==  'Profesional Services')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( ( !($focus->fetched_row['sales_stage'] ==  'Sales Ops Closed' )) && 
 (isset($focus->sales_stage) && $focus->sales_stage ==  'Sales Ops Closed')	 ){ 
	 

	 //Secondary Trigger number #2
	 if( (isset($focus->order_number) && $focus->order_number !=  '')	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['9e5f35a7_6a73_ea3f_10d2_4cad1d2ed610'])){
		$triggeredWorkflows['9e5f35a7_6a73_ea3f_10d2_4cad1d2ed610'] = true;
		 process_workflow_actions($focus, $action_meta_array['Opportunities5_action0']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['Opportunities'] = isset($_SESSION['WORKFLOW_ALERTS']['Opportunities']) && is_array($_SESSION['WORKFLOW_ALERTS']['Opportunities']) ? $_SESSION['WORKFLOW_ALERTS']['Opportunities'] : array();
		$_SESSION['WORKFLOW_ALERTS']['Opportunities'] = array_merge($_SESSION['WORKFLOW_ALERTS']['Opportunities'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 // End Secondary Trigger number #2
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


	//end function process_wflow_triggers
	}
	
	//end class
	}

?>