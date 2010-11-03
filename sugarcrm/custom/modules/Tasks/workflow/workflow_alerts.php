<?php

include_once("include/workflow/alert_utils.php");
    class Tasks_alerts {
    function process_wflow_Tasks3_alert0(&$focus){
            include("custom/modules/Tasks/workflow/alerts_array.php");

	 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "b98a3a59-805a-a410-4790-47608d9cfd0c"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Tasks3_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
	 }


    
    //end class
    }

?>