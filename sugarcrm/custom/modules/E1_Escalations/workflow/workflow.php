<?php

include_once("include/workflow/alert_utils.php");
include_once("include/workflow/action_utils.php");
include_once("include/workflow/time_utils.php");
include_once("include/workflow/trigger_utils.php");
//BEGIN WFLOW PLUGINS
include_once("include/workflow/custom_utils.php");
//END WFLOW PLUGINS
	class E1_Escalations_workflow {
	function process_wflow_triggers(& $focus){
		include("custom/modules/E1_Escalations/workflow/triggers_array.php");
		include("custom/modules/E1_Escalations/workflow/alerts_array.php");
		include("custom/modules/E1_Escalations/workflow/actions_array.php");
		include("custom/modules/E1_Escalations/workflow/plugins_array.php");
		
 if( ( !($focus->fetched_row['urgency'] ==  'Urgent' )) && 
 (isset($focus->urgency) && $focus->urgency ==  'Urgent')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['c3bdf279_9ae7_2420_2273_4ccef994f00e'])){
		$triggeredWorkflows['c3bdf279_9ae7_2420_2273_4ccef994f00e'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "7d946c5c-2ca8-3ccb-c8d3-48f49460cbea"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['E1_Escalations0_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if(true){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( (isset($focus->type_c) && $focus->type_c ==  'Case')	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['c468d03d_72c2_d7bd_09ae_4ccef9bafbea'])){
		$triggeredWorkflows['c468d03d_72c2_d7bd_09ae_4ccef9bafbea'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "979e0b4d-2a38-cbec-c253-4cb77674f66a"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['E1_Escalations1_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 

if(isset($focus->fetched_row['id']) && $focus->fetched_row['id']!=""){ 
 
 if( (isset($focus->type_c) && $focus->type_c ==  'Case')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( ( !($focus->fetched_row['status_c'] ==  'Closed' )) && 
 (isset($focus->status_c) && $focus->status_c ==  'Closed')	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['c53fea9d_d8c6_9b17_27f1_4ccef9182978'])){
		$triggeredWorkflows['c53fea9d_d8c6_9b17_27f1_4ccef9182978'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "ca98d21b-66df-e65b-4bc8-4cbcd75cf6ef"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['E1_Escalations2_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 

		 //End if new, update, or all record
 		} 


	//end function process_wflow_triggers
	}
	
	//end class
	}

?>