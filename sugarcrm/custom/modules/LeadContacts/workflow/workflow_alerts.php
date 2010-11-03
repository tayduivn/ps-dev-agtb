<?php

include_once("include/workflow/alert_utils.php");
    class LeadContacts_alerts {
    function process_wflow_LeadContacts9_alert0(&$focus){
            include("custom/modules/LeadContacts/workflow/alerts_array.php");

	 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "2945d635-f8ef-5c7e-b4dc-4a02feed7af4"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['LeadContacts9_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
	 }


    
    //end class
    }

?>