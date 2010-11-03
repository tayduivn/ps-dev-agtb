<?php

include_once("include/workflow/alert_utils.php");
    class Leads_alerts {
    function process_wflow_Leads1_alert0(&$focus){
            include("custom/modules/Leads/workflow/alerts_array.php");

	 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "c746cca3-d804-ef06-0b35-44a937a2d987"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Leads1_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
	 }


    
    //end class
    }

?>