<?php

include_once("include/workflow/alert_utils.php");
    class Cases_alerts {
    function process_wflow_Cases12_alert0(&$focus){
            include("custom/modules/Cases/workflow/alerts_array.php");

	 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "This alert should be triggered by the time elapsed workflow"; 

	 $alertshell_array['source_type'] = "Normal Message"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases12_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
	 }


    
    //end class
    }

?>