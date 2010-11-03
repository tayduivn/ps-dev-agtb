<?php

include_once("include/workflow/alert_utils.php");
    class Bugs_alerts {
    function process_wflow_Bugs0_alert0(&$focus){
            include("custom/modules/Bugs/workflow/alerts_array.php");

	 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "b50494ca-7427-5fe0-35c8-476092316938"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Bugs0_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
	 }


    
    //end class
    }

?>