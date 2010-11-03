<?php

include_once("include/workflow/alert_utils.php");
include_once("include/workflow/action_utils.php");
include_once("include/workflow/time_utils.php");
include_once("include/workflow/trigger_utils.php");
//BEGIN WFLOW PLUGINS
include_once("include/workflow/custom_utils.php");
//END WFLOW PLUGINS
	class KBDocuments_workflow {
	function process_wflow_triggers(& $focus){
		include("custom/modules/KBDocuments/workflow/triggers_array.php");
		include("custom/modules/KBDocuments/workflow/alerts_array.php");
		include("custom/modules/KBDocuments/workflow/actions_array.php");
		include("custom/modules/KBDocuments/workflow/plugins_array.php");
		if(empty($focus->fetched_row['id'])){ 
 
 if(true){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['71266f4f_56ab_a01e_c0dd_4cad1d130675'])){
		$triggeredWorkflows['71266f4f_56ab_a01e_c0dd_4cad1d130675'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "d7bb51ac-b7db-d598-e64a-477c1ca6180c"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['KBDocuments0_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
 

	 //End Frame Secondary 

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