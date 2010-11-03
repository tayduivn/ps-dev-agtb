<?php

include_once("include/workflow/alert_utils.php");
include_once("include/workflow/action_utils.php");
include_once("include/workflow/time_utils.php");
include_once("include/workflow/trigger_utils.php");
//BEGIN WFLOW PLUGINS
include_once("include/workflow/custom_utils.php");
//END WFLOW PLUGINS
	class Cases_workflow {
	function process_wflow_triggers(& $focus){
		include("custom/modules/Cases/workflow/triggers_array.php");
		include("custom/modules/Cases/workflow/alerts_array.php");
		include("custom/modules/Cases/workflow/actions_array.php");
		include("custom/modules/Cases/workflow/plugins_array.php");
		if(empty($focus->fetched_row['id'])){ 
 
 if( ($focus->priority_level ==  'P1 System Down')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( ($focus->request_type_c ==  'technical_support')	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['74a4472d_9a54_411c_6f98_4cad1d5d6885'])){
		$triggeredWorkflows['74a4472d_9a54_411c_6f98_4cad1d5d6885'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "58cc1b06-beb6-7854-6f46-440683a7d3e9"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases0_alert0'], $alertshell_array, false); 
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

if(empty($focus->fetched_row['id'])){ 
 	 $primary_array = array();
	 $primary_array = check_rel_filter($focus, $primary_array, 'account', $trigger_meta_array['trigger_1'], 'any'); 

 if(($primary_array['results']==true)
){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( (isset($focus->request_type_c) && $focus->request_type_c ==  'technical_support')	 ){ 
	 

	 //Secondary Trigger number #2
	 if( ($focus->priority_level ==  'P1 System Down')	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['7569b0ee_807e_18c5_ee08_4cad1d1d64ad'])){
		$triggeredWorkflows['7569b0ee_807e_18c5_ee08_4cad1d1d64ad'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "58cc1b06-beb6-7854-6f46-440683a7d3e9"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases1_alert0'], $alertshell_array, false); 
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


 if( (  ( isset($focus->status) && $focus->fetched_row['status'] != $focus->status)  || ( $focus->fetched_row['status'] == null && !isset($focus->status) ) )  ||  (  isset($focus->status) && isset($_SESSION['workflow_parameters']) && $_SESSION['workflow_parameters'] == $focus->status 
 && !empty($_SESSION['workflow_cron']) && $_SESSION['workflow_cron']=="Yes" ) ){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( (isset($focus->support_service_level_c) && $focus->support_service_level_c ==  'Premium Support')	 ){ 
	 

	 //Secondary Trigger number #2
	 if( ($focus->status ==  'New')	 ){ 
	 

	 //Secondary Trigger number #3
	 if( ($focus->priority_level ==  'P1 System Down')	 ){ 
	 

	 $time_array['time_int'] = '900'; 

	 $time_array['parameters'] = $focus->status; 

	 $time_array['time_int_type'] = 'normal'; 

	 $time_array['target_field'] = 'none'; 

	 $workflow_id = 'b67b3b55-ab89-f511-78f6-44069f6ff464'; 

if(!empty($_SESSION["workflow_cron"]) && $_SESSION["workflow_cron"]=="Yes" && 
				!empty($_SESSION["workflow_id_cron"]) && $_SESSION["workflow_id_cron"]==$workflow_id){
				
	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['7622f5ea_4c88_4b89_93ba_4cad1d51a5e9'])){
		$triggeredWorkflows['7622f5ea_4c88_4b89_93ba_4cad1d51a5e9'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "b47a42b2-c8c7-09cd-bcc8-44071b4dd5fe"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases2_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
}

else{
		 check_for_schedule($focus, $workflow_id, $time_array); 

 }

 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 // End Secondary Trigger number #2
 	 } 

	 // End Secondary Trigger number #3
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( ( !($focus->fetched_row['assigned_user_id'] ==  '8402a329-9ff6-154f-f58f-446375173cef' )) && 
 ($focus->assigned_user_id ==  '8402a329-9ff6-154f-f58f-446375173cef')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['76a988c9_b13b_0b07_9357_4cad1d4a8a52'])){
		$triggeredWorkflows['76a988c9_b13b_0b07_9357_4cad1d4a8a52'] = true;
		 unset($alertshell_array); 
		 process_workflow_actions($focus, $action_meta_array['Cases3_action0']); 
 	}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( ( !($focus->fetched_row['request_type_c'] ==  'pre_sales_support' )) && 
 ($focus->request_type_c ==  'pre_sales_support')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['7777792a_fbb0_4744_32d9_4cad1d8b8a92'])){
		$triggeredWorkflows['7777792a_fbb0_4744_32d9_4cad1d8b8a92'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "333a0899-82a9-765e-32b6-4457c9b45994"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases4_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (  ( !($focus->fetched_row['status'] ==  'New' )) && 
 ($focus->status ==  'New') )  ||  (  ($focus->status ==  'New') && !empty($_SESSION['workflow_cron']) && $_SESSION['workflow_cron']=="Yes" ) ){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( ($focus->request_type_c ==  'pre_sales_support')	 ){ 
	 

	 $trigger_time_count = '1'; 

 	 $time_array['time_int'] = '86400'; 

	 $time_array['time_int_type'] = 'normal'; 

	 $time_array['target_field'] = 'none'; 

	 $workflow_id = '5e345d00-e7c1-12e9-dbf2-4464a0bee3ff'; 

if(!empty($_SESSION["workflow_cron"]) && $_SESSION["workflow_cron"]=="Yes" && 
				!empty($_SESSION["workflow_id_cron"]) && $_SESSION["workflow_id_cron"]==$workflow_id){
				
	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['78341f32_bd42_1dd9_b75e_4cad1d4d0583'])){
		$triggeredWorkflows['78341f32_bd42_1dd9_b75e_4cad1d4d0583'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "4a38ffa1-618c-9fcf-97a1-4404ddd9ac53"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases5_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
}

else{
		 check_for_schedule($focus, $workflow_id, $time_array); 

 }

 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 

if(isset($focus->fetched_row['id']) && $focus->fetched_row['id']!=""){ 
 
 if($focus->fetched_row['status'] != $focus->status){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( ($focus->request_type_c ==  'pre_sales_support')	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['78c664a9_ceda_2e69_693f_4cad1de17b0a'])){
		$triggeredWorkflows['78c664a9_ceda_2e69_693f_4cad1de17b0a'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "365f03d8-114b-87b8-01fe-44ecf9dce386"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases6_alert0'], $alertshell_array, false); 
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

if(isset($focus->fetched_row['id']) && $focus->fetched_row['id']!=""){ 
 
 if($focus->fetched_row['status'] != $focus->status){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( ($focus->request_type_c ==  'technical_support')	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['795c0b11_df05_eda6_7840_4cad1de2e412'])){
		$triggeredWorkflows['795c0b11_df05_eda6_7840_4cad1de2e412'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "4a8e6995-ad64-461b-cb4f-4509efffd912"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases7_alert0'], $alertshell_array, false); 
 	 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "4a8e6995-ad64-461b-cb4f-4509efffd912"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases7_alert1'], $alertshell_array, false); 
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


 if( ( !(( in_array( 'Closed Defect',explode(',',str_replace('^,^', ',', $focus->fetched_row['status'])) ) ) || 
( in_array( 'Closed Feature',explode(',',str_replace('^,^', ',', $focus->fetched_row['status'])) ) ) || 
( in_array( 'Closed No Response',explode(',',str_replace('^,^', ',', $focus->fetched_row['status'])) ) ) || 
( in_array( 'Closed',explode(',',str_replace('^,^', ',', $focus->fetched_row['status'])) ) ) )) && 
 (( in_array( 'Closed Defect',explode(',',str_replace('^,^', ',', $focus->status)) ) ) || 
( in_array( 'Closed Feature',explode(',',str_replace('^,^', ',', $focus->status)) ) ) || 
( in_array( 'Closed No Response',explode(',',str_replace('^,^', ',', $focus->status)) ) ) || 
( in_array( 'Closed',explode(',',str_replace('^,^', ',', $focus->status)) ) ))){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( (isset($focus->request_type_c) && $focus->request_type_c ==  'technical_support')	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['7a2625ac_8964_ce9a_f961_4cad1d2cb00e'])){
		$triggeredWorkflows['7a2625ac_8964_ce9a_f961_4cad1d2cb00e'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "9e9902c7-88a1-7a12-063e-473ceadeb63f"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases8_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( ( !($focus->fetched_row['request_type_c'] ==  'pre_sales_support' )) && 
 ($focus->request_type_c ==  'pre_sales_support')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(isset($focus->assigned_user_name) && in_array($focus->assigned_user_name, $plugin_meta_array["plugin_9_secondary_0"]["compare_array"])==true	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['7ac3f0e6_2b6d_04d0_0a32_4cad1df35ac6'])){
		$triggeredWorkflows['7ac3f0e6_2b6d_04d0_0a32_4cad1df35ac6'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "333a0899-82a9-765e-32b6-4457c9b45994"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases9_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( ( !(
 	 ( 
 		$focus->fetched_row['discuss_in_cometo_c'] ==  'true'|| 
 		$focus->fetched_row['discuss_in_cometo_c'] ==  'on'||  
 		$focus->fetched_row['discuss_in_cometo_c'] ==  '1'
 	 )  
 )) && 
 (
 	 ( 
 		isset($focus->discuss_in_cometo_c) && $focus->discuss_in_cometo_c ==  'true'|| 
 		isset($focus->discuss_in_cometo_c) && $focus->discuss_in_cometo_c ==  'on'||  
 		isset($focus->discuss_in_cometo_c) && $focus->discuss_in_cometo_c ==  '1'
 	 )  
)){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['7b58753e_3bd8_5ae1_ed1d_4cad1dd83a49'])){
		$triggeredWorkflows['7b58753e_3bd8_5ae1_ed1d_4cad1dd83a49'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "931991d3-0837-e65e-1984-489b877a1e52"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases10_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (  ( isset($focus->status) && $focus->fetched_row['status'] != $focus->status)  || ( $focus->fetched_row['status'] == null && !isset($focus->status) ) )  ||  (  isset($focus->status) && isset($_SESSION['workflow_parameters']) && $_SESSION['workflow_parameters'] == $focus->status 
 && !empty($_SESSION['workflow_cron']) && $_SESSION['workflow_cron']=="Yes" ) ){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if( (isset($focus->status) && $focus->status ==  'New')	 ){ 
	 

	 //Secondary Trigger number #2
	 if( (isset($focus->request_type_c) && $focus->request_type_c ==  'technical_support')	 ){ 
	 

	 $time_array['time_int'] = '300'; 

	 $time_array['parameters'] = $focus->status; 

	 $time_array['time_int_type'] = 'normal'; 

	 $time_array['target_field'] = 'none'; 

	 $workflow_id = 'de884607-7687-b83c-2eb0-494ac38e056e'; 

if(!empty($_SESSION["workflow_cron"]) && $_SESSION["workflow_cron"]=="Yes" && 
				!empty($_SESSION["workflow_id_cron"]) && $_SESSION["workflow_id_cron"]==$workflow_id){
				
	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['7c07e744_c777_e9fc_f4df_4cad1dd92c16'])){
		$triggeredWorkflows['7c07e744_c777_e9fc_f4df_4cad1dd92c16'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "957c7da1-13be-d132-c88c-4509f138377d"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases11_alert0'], $alertshell_array, false); 
 	 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "957c7da1-13be-d132-c88c-4509f138377d"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases11_alert1'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
}

else{
		 check_for_schedule($focus, $workflow_id, $time_array); 

 }

 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 // End Secondary Trigger number #2
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 

if(empty($focus->fetched_row['id'])){ 
 	 $primary_array = array();
	 $primary_array = check_rel_filter($focus, $primary_array, 'account', $trigger_meta_array['trigger_12'], 'any'); 

 if(($primary_array['results']==true)
){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['7d052a31_fe65_c416_0324_4cad1dff6aff'])){
		$triggeredWorkflows['7d052a31_fe65_c416_0324_4cad1dff6aff'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "1fa82a45-f411-7c08-a3cb-49908039ab48"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases12_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 

		 //End if new, update, or all record
 		} 

if(empty($focus->fetched_row['id'])){ 
 	 $primary_array = array();
	 $primary_array = check_rel_filter($focus, $primary_array, 'account', $trigger_meta_array['trigger_13'], 'any'); 

 if(($primary_array['results']==true)
){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['7dc0a614_d6b2_6fea_7a9c_4cad1dfd3506'])){
		$triggeredWorkflows['7dc0a614_d6b2_6fea_7a9c_4cad1dfd3506'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "8ac84368-b364-4af4-aab6-4a25520c61d8"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases13_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 

		 //End if new, update, or all record
 		} 

if(empty($focus->fetched_row['id'])){ 
 	 $primary_array = array();
	 $primary_array = check_rel_filter($focus, $primary_array, 'account', $trigger_meta_array['trigger_14'], 'any'); 

 if(($primary_array['results']==true)
){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['7e855fc8_77f0_e2f9_028d_4cad1d52b0b4'])){
		$triggeredWorkflows['7e855fc8_77f0_e2f9_028d_4cad1d52b0b4'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "8ac84368-b364-4af4-aab6-4a25520c61d8"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases14_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 

		 //End if new, update, or all record
 		} 

if(empty($focus->fetched_row['id'])){ 
 	 $primary_array = array();
	 $primary_array = check_rel_filter($focus, $primary_array, 'account', $trigger_meta_array['trigger_15'], 'any'); 

 if(($primary_array['results']==true)
){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['7f4371fb_f868_a122_788a_4cad1d0e3fa7'])){
		$triggeredWorkflows['7f4371fb_f868_a122_788a_4cad1d0e3fa7'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "8ac84368-b364-4af4-aab6-4a25520c61d8"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases15_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 

		 //End if new, update, or all record
 		} 

if(empty($focus->fetched_row['id'])){ 
 	 $primary_array = array();
	 $primary_array = check_rel_filter($focus, $primary_array, 'account', $trigger_meta_array['trigger_16'], 'any'); 

 if(($primary_array['results']==true)
){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['8006480b_866a_b29d_99e8_4cad1d251215'])){
		$triggeredWorkflows['8006480b_866a_b29d_99e8_4cad1d251215'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "8ac84368-b364-4af4-aab6-4a25520c61d8"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases16_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 

		 //End if new, update, or all record
 		} 

if(empty($focus->fetched_row['id'])){ 
 	 $primary_array = array();
	 $primary_array = check_rel_filter($focus, $primary_array, 'account', $trigger_meta_array['trigger_17'], 'any'); 

 if(($primary_array['results']==true)
){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['80ce2b95_d0bd_aa4d_66b4_4cad1dd91514'])){
		$triggeredWorkflows['80ce2b95_d0bd_aa4d_66b4_4cad1dd91514'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "8ac84368-b364-4af4-aab6-4a25520c61d8"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases17_alert0'], $alertshell_array, false); 
 	 unset($alertshell_array); 
		}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 

		 //End if new, update, or all record
 		} 

if(empty($focus->fetched_row['id'])){ 
 	 $primary_array = array();
	 $primary_array = check_rel_filter($focus, $primary_array, 'account', $trigger_meta_array['trigger_18'], 'any'); 

 if(($primary_array['results']==true)
){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['81920e80_a614_1682_cd6a_4cad1d397449'])){
		$triggeredWorkflows['81920e80_a614_1682_cd6a_4cad1d397449'] = true;
		 $alertshell_array = array(); 

	 $alertshell_array['alert_msg'] = "8ac84368-b364-4af4-aab6-4a25520c61d8"; 

	 $alertshell_array['source_type'] = "Custom Template"; 

	 $alertshell_array['alert_type'] = "Email"; 

	 process_workflow_alerts($focus, $alert_meta_array['Cases18_alert0'], $alertshell_array, false); 
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