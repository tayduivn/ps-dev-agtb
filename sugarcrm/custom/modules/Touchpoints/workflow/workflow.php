<?php

include_once("include/workflow/alert_utils.php");
include_once("include/workflow/action_utils.php");
include_once("include/workflow/time_utils.php");
include_once("include/workflow/trigger_utils.php");
//BEGIN WFLOW PLUGINS
include_once("include/workflow/custom_utils.php");
//END WFLOW PLUGINS
	class Touchpoints_workflow {
	function process_wflow_triggers(& $focus){
		include("custom/modules/Touchpoints/workflow/triggers_array.php");
		include("custom/modules/Touchpoints/workflow/alerts_array.php");
		include("custom/modules/Touchpoints/workflow/actions_array.php");
		include("custom/modules/Touchpoints/workflow/plugins_array.php");
		if(empty($focus->fetched_row['id'])){ 
 	 $primary_array = array();
	 $primary_array = check_rel_filter($focus, $primary_array, 'campaigns', $trigger_meta_array['trigger_0'], 'any'); 

 if(($primary_array['results']==true)
){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( (isset($focus->partner_assigned_to_c) && $focus->partner_assigned_to_c ==  '')	 ){ 
	 

	 //Secondary Trigger number #2
	 if( (isset($focus->lead_source) && $focus->lead_source ==  'Partner')	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['9431097b_1b88_535d_6de5_4cad1d27c836'])){
		$triggeredWorkflows['9431097b_1b88_535d_6de5_4cad1d27c836'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "e335d3a2-c580-e6c8-bd86-4b7b1fd1aa9c"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Touchpoints0_alert0'], $alertshell_array, false); 
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

		 //End if new, update, or all record
 		} 


	//end function process_wflow_triggers
	}
	
	//end class
	}

?>