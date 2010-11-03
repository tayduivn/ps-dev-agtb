<?php

include_once("include/workflow/alert_utils.php");
include_once("include/workflow/action_utils.php");
include_once("include/workflow/time_utils.php");
include_once("include/workflow/trigger_utils.php");
//BEGIN WFLOW PLUGINS
include_once("include/workflow/custom_utils.php");
//END WFLOW PLUGINS
	class Bugs_workflow {
	function process_wflow_triggers(& $focus){
		include("custom/modules/Bugs/workflow/triggers_array.php");
		include("custom/modules/Bugs/workflow/alerts_array.php");
		include("custom/modules/Bugs/workflow/actions_array.php");
		include("custom/modules/Bugs/workflow/plugins_array.php");
		if(isset($focus->fetched_row['id']) && $focus->fetched_row['id']!=""){ 
 
 if( ( !($focus->fetched_row['created_by'] ==  '55cf4e63-0d32-83f2-4e30-42dc5314c316' )) && 
 (isset($focus->created_by) && $focus->created_by ==  '55cf4e63-0d32-83f2-4e30-42dc5314c316')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['6c82a802_aef8_ab58_fe73_4cad1d679c6c'])){
		$triggeredWorkflows['6c82a802_aef8_ab58_fe73_4cad1d679c6c'] = true;
		$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['Bugs'] = isset($_SESSION['WORKFLOW_ALERTS']['Bugs']) && is_array($_SESSION['WORKFLOW_ALERTS']['Bugs']) ? $_SESSION['WORKFLOW_ALERTS']['Bugs'] : array();
		$_SESSION['WORKFLOW_ALERTS']['Bugs'] = array_merge($_SESSION['WORKFLOW_ALERTS']['Bugs'],array ('Bugs0_alert0',));	}
 

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