<?php

include_once("include/workflow/alert_utils.php");
include_once("include/workflow/action_utils.php");
include_once("include/workflow/time_utils.php");
include_once("include/workflow/trigger_utils.php");
//BEGIN WFLOW PLUGINS
include_once("include/workflow/custom_utils.php");
//END WFLOW PLUGINS
	class Accounts_workflow {
	function process_wflow_triggers(& $focus){
		include("custom/modules/Accounts/workflow/triggers_array.php");
		include("custom/modules/Accounts/workflow/alerts_array.php");
		include("custom/modules/Accounts/workflow/actions_array.php");
		include("custom/modules/Accounts/workflow/plugins_array.php");
		
 if( ($focus->account_type ==  'Partner')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( ($focus->Partner_Type_c ==  'Gold')	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['3796f78b_266e_7183_216e_4cad1dca1495'])){
		$triggeredWorkflows['3796f78b_266e_7183_216e_4cad1dca1495'] = true;
		 process_workflow_actions($focus, $action_meta_array['Accounts0_action0']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['Accounts'] = isset($_SESSION['WORKFLOW_ALERTS']['Accounts']) && is_array($_SESSION['WORKFLOW_ALERTS']['Accounts']) ? $_SESSION['WORKFLOW_ALERTS']['Accounts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['Accounts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['Accounts'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( ($focus->account_type ==  'Partner')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( ($focus->Partner_Type_c ==  'Silver')	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['386ebbb5_6fe5_00fc_6254_4cad1da4443a'])){
		$triggeredWorkflows['386ebbb5_6fe5_00fc_6254_4cad1da4443a'] = true;
		 unset($alertshell_array); 
		 process_workflow_actions($focus, $action_meta_array['Accounts1_action0']); 
 	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( ($focus->account_type ==  'Partner')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( ($focus->Partner_Type_c ==  'Bronze')	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['3939893f_5c48_8ed8_1a46_4cad1dd7afd5'])){
		$triggeredWorkflows['3939893f_5c48_8ed8_1a46_4cad1dd7afd5'] = true;
		 unset($alertshell_array); 
		 process_workflow_actions($focus, $action_meta_array['Accounts2_action0']); 
 	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( ( !($focus->fetched_row['Support_Service_Level_c'] ==  'premium' )) && 
 (isset($focus->Support_Service_Level_c) && $focus->Support_Service_Level_c ==  'premium')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['3a081025_b069_e35c_01f0_4cad1d674f54'])){
		$triggeredWorkflows['3a081025_b069_e35c_01f0_4cad1d674f54'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "a1498243-fd17-c39a-091f-465cc214cc3c"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Accounts3_alert0'], $alertshell_array, false); 
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