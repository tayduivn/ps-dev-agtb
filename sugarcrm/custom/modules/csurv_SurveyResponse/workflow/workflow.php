<?php

include_once("include/workflow/alert_utils.php");
include_once("include/workflow/action_utils.php");
include_once("include/workflow/time_utils.php");
include_once("include/workflow/trigger_utils.php");
//BEGIN WFLOW PLUGINS
include_once("include/workflow/custom_utils.php");
//END WFLOW PLUGINS
	class csurv_SurveyResponse_workflow {
	function process_wflow_triggers(& $focus){
		include("custom/modules/csurv_SurveyResponse/workflow/triggers_array.php");
		include("custom/modules/csurv_SurveyResponse/workflow/alerts_array.php");
		include("custom/modules/csurv_SurveyResponse/workflow/actions_array.php");
		include("custom/modules/csurv_SurveyResponse/workflow/plugins_array.php");
		
 if(true){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['a3d777c4_f969_7823_cac3_4cad1daa3863'])){
		$triggeredWorkflows['a3d777c4_f969_7823_cac3_4cad1daa3863'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "4387782c-3087-d3eb-d34b-478e4140024b"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['csurv_SurveyResponse0_alert0'], $alertshell_array, false); 
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