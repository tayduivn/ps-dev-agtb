<?php

include_once("include/workflow/alert_utils.php");
include_once("include/workflow/action_utils.php");
include_once("include/workflow/time_utils.php");
include_once("include/workflow/trigger_utils.php");
//BEGIN WFLOW PLUGINS
include_once("include/workflow/custom_utils.php");
//END WFLOW PLUGINS
	class LeadContacts_workflow {
	function process_wflow_triggers(& $focus){
		include("custom/modules/LeadContacts/workflow/triggers_array.php");
		include("custom/modules/LeadContacts/workflow/alerts_array.php");
		include("custom/modules/LeadContacts/workflow/actions_array.php");
		include("custom/modules/LeadContacts/workflow/plugins_array.php");
		
 if( (isset($focus->region_c) && $focus->region_c ==  'Middle East')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['bfb5c633_d9aa_8fcb_957b_4cced7a1ce08'])){
		$triggeredWorkflows['bfb5c633_d9aa_8fcb_957b_4cced7a1ce08'] = true;
		 unset($alertshell_array); 
		 process_workflow_actions($focus, $action_meta_array['LeadContacts0_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts0_action1']); 
 	}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'Europe')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(isset($focus->primary_address_country) && in_array($focus->primary_address_country, $plugin_meta_array["plugin_1_secondary_0"]["compare_array"])==true	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['c35822ea_2bed_dfea_6873_4cced7210869'])){
		$triggeredWorkflows['c35822ea_2bed_dfea_6873_4cced7210869'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts1_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts1_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'Europe')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(isset($focus->primary_address_country) && in_array($focus->primary_address_country, $plugin_meta_array["plugin_2_secondary_0"]["compare_array"])==true	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['c6c9370a_9ed9_a35b_8e27_4cced73d5721'])){
		$triggeredWorkflows['c6c9370a_9ed9_a35b_8e27_4cced73d5721'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts2_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts2_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'Europe')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(isset($focus->primary_address_country) && in_array($focus->primary_address_country, $plugin_meta_array["plugin_3_secondary_0"]["compare_array"])==true	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['ca226d41_29c4_cfb7_f02d_4cced7fad3ad'])){
		$triggeredWorkflows['ca226d41_29c4_cfb7_f02d_4cced7fad3ad'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts3_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts3_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if(($focus->fetched_row['primary_address_country'] != $focus->primary_address_country) && 
 (( in_array( 'AUSTRALIA',explode(',',str_replace('^,^', ',', $focus->primary_address_country)) ) ) || 
( in_array( 'NEW ZEALAND',explode(',',str_replace('^,^', ',', $focus->primary_address_country)) ) ))){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['cd810028_8f95_84f9_dea5_4cced7fdb6bf'])){
		$triggeredWorkflows['cd810028_8f95_84f9_dea5_4cced7fdb6bf'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts4_action0']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'Europe')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(isset($focus->primary_address_country) && in_array($focus->primary_address_country, $plugin_meta_array["plugin_5_secondary_0"]["compare_array"])==true	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['cf8b0a79_8426_c277_ce3d_4cced7e70fed'])){
		$triggeredWorkflows['cf8b0a79_8426_c277_ce3d_4cced7e70fed'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts5_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts5_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if(isset($focus->region_c) && in_array($focus->region_c, $plugin_meta_array["plugin_6"]["compare_array"])==true){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['d2dd4d53_52b0_96b2_9e38_4cced7963073'])){
		$triggeredWorkflows['d2dd4d53_52b0_96b2_9e38_4cced7963073'] = true;
		 unset($alertshell_array); 
		 process_workflow_actions($focus, $action_meta_array['LeadContacts6_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts6_action1']); 
 	}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'China')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['d6271d3b_6d60_3819_0bbe_4cced7eb61b3'])){
		$triggeredWorkflows['d6271d3b_6d60_3819_0bbe_4cced7eb61b3'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts7_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts7_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'Pacific Rim')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['d971c8e2_3a33_886b_d663_4cced7fc9d90'])){
		$triggeredWorkflows['d971c8e2_3a33_886b_d663_4cced7fc9d90'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts8_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts8_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'India')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['dcb05e63_a4a6_596a_e6aa_4cced7d42a89'])){
		$triggeredWorkflows['dcb05e63_a4a6_596a_e6aa_4cced7d42a89'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts9_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts9_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'Europe')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(isset($focus->primary_address_country) && in_array($focus->primary_address_country, $plugin_meta_array["plugin_10_secondary_0"]["compare_array"])==true	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['e0048a2d_a3b6_37d2_5323_4cced7748dbe'])){
		$triggeredWorkflows['e0048a2d_a3b6_37d2_5323_4cced7748dbe'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts10_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts10_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'Europe')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(isset($focus->primary_address_country) && in_array($focus->primary_address_country, $plugin_meta_array["plugin_11_secondary_0"]["compare_array"])==true	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['e353e1c3_a06d_cc53_ee59_4cced788b8b2'])){
		$triggeredWorkflows['e353e1c3_a06d_cc53_ee59_4cced788b8b2'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts11_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts11_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'Europe')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(isset($focus->primary_address_country) && in_array($focus->primary_address_country, $plugin_meta_array["plugin_12_secondary_0"]["compare_array"])==true	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['e6c27efc_85a1_cb31_a548_4cced73de6fa'])){
		$triggeredWorkflows['e6c27efc_85a1_cb31_a548_4cced73de6fa'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts12_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts12_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'Europe')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(isset($focus->primary_address_country) && in_array($focus->primary_address_country, $plugin_meta_array["plugin_13_secondary_0"]["compare_array"])==true	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['ea164941_8cbc_bf9f_73a0_4cced782cb36'])){
		$triggeredWorkflows['ea164941_8cbc_bf9f_73a0_4cced782cb36'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts13_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts13_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'Europe')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(isset($focus->primary_address_country) && in_array($focus->primary_address_country, $plugin_meta_array["plugin_14_secondary_0"]["compare_array"])==true	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['ed665157_123d_ac0f_5316_4cced7a938a2'])){
		$triggeredWorkflows['ed665157_123d_ac0f_5316_4cced7a938a2'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts14_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts14_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if(isset($focus->primary_address_country) && in_array($focus->primary_address_country, $plugin_meta_array["plugin_15"]["compare_array"])==true){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 

	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['f0b55f73_76be_9885_5819_4cced7b5bacf'])){
		$triggeredWorkflows['f0b55f73_76be_9885_5819_4cced7b5bacf'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts15_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts15_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'Europe')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(isset($focus->primary_address_country) && in_array($focus->primary_address_country, $plugin_meta_array["plugin_16_secondary_0"]["compare_array"])==true	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['f40cf2ae_dd48_2418_f891_4cced71f1df6'])){
		$triggeredWorkflows['f40cf2ae_dd48_2418_f891_4cced71f1df6'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts16_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts16_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'Africa')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(isset($focus->primary_address_country) && in_array($focus->primary_address_country, $plugin_meta_array["plugin_17_secondary_0"]["compare_array"])==true	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['343b0efd_e424_8666_9b25_4cced7b98692'])){
		$triggeredWorkflows['343b0efd_e424_8666_9b25_4cced7b98692'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts17_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts17_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'Africa')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(isset($focus->primary_address_country) && in_array($focus->primary_address_country, $plugin_meta_array["plugin_18_secondary_0"]["compare_array"])==true	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['6a3b0eae_e69b_eb10_00f6_4cced70f039c'])){
		$triggeredWorkflows['6a3b0eae_e69b_eb10_00f6_4cced70f039c'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts18_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts18_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'Africa')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(isset($focus->primary_address_country) && in_array($focus->primary_address_country, $plugin_meta_array["plugin_19_secondary_0"]["compare_array"])==true	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['a0e5092d_6a9d_ff94_e602_4cced792dcda'])){
		$triggeredWorkflows['a0e5092d_6a9d_ff94_e602_4cced792dcda'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts19_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts19_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'Africa')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(isset($focus->primary_address_country) && in_array($focus->primary_address_country, $plugin_meta_array["plugin_20_secondary_0"]["compare_array"])==true	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['d5e00562_e05d_e7da_6e35_4cced7f68af8'])){
		$triggeredWorkflows['d5e00562_e05d_e7da_6e35_4cced7f68af8'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts20_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts20_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'Africa')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(isset($focus->primary_address_country) && in_array($focus->primary_address_country, $plugin_meta_array["plugin_21_secondary_0"]["compare_array"])==true	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['10ab8046_c5ec_33b9_078b_4cced780ad90'])){
		$triggeredWorkflows['10ab8046_c5ec_33b9_078b_4cced780ad90'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts21_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts21_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'USA')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(isset($focus->primary_address_country) && in_array($focus->primary_address_country, $plugin_meta_array["plugin_22_secondary_0"]["compare_array"])==true	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['13fb5101_7894_2137_e5a8_4cced75cc4b5'])){
		$triggeredWorkflows['13fb5101_7894_2137_e5a8_4cced75cc4b5'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts22_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts22_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


 if( (isset($focus->region_c) && $focus->region_c ==  'USA')){ 
 

	 //Frame Secondary 

	 $secondary_array = array(); 
	 //Secondary Triggers 
	 //Secondary Trigger number #1
	 if(isset($focus->primary_address_country) && in_array($focus->primary_address_country, $plugin_meta_array["plugin_23_secondary_0"]["compare_array"])==true	 ){ 
	 


	global $triggeredWorkflows;
	if (!isset($triggeredWorkflows['17481700_5766_3887_9735_4cced70c6874'])){
		$triggeredWorkflows['17481700_5766_3887_9735_4cced70c6874'] = true;
		 process_workflow_actions($focus, $action_meta_array['LeadContacts23_action0']); 
 	 process_workflow_actions($focus, $action_meta_array['LeadContacts23_action1']); 
 	$_SESSION['WORKFLOW_ALERTS'] = isset($_SESSION['WORKFLOW_ALERTS']) && is_array($_SESSION['WORKFLOW_ALERTS']) ? $_SESSION['WORKFLOW_ALERTS'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = isset($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) && is_array($_SESSION['WORKFLOW_ALERTS']['LeadContacts']) ? $_SESSION['WORKFLOW_ALERTS']['LeadContacts'] : array();
		$_SESSION['WORKFLOW_ALERTS']['LeadContacts'] = array_merge($_SESSION['WORKFLOW_ALERTS']['LeadContacts'],array ());	}
 

	 //End Frame Secondary 

	 // End Secondary Trigger number #1
 	 } 

	 unset($secondary_array); 
 

 //End if trigger is true 
 } 


	//end function process_wflow_triggers
	}
	
	//end class
	}

?>