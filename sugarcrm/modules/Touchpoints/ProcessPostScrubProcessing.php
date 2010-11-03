<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('modules/Leads/Lead.php');

if(isset($_REQUEST['relate_id']) && !empty($_REQUEST['relate_id'])){
	$relate_lead = new Lead();
	$relate_lead->retrieve($_REQUEST['relate_id']);
	
	$found_relate = false;
	foreach($_POST as $key => $value){
		if(substr($key, 0, 7) == 'relate_' && $key != 'relate_id'){
			$found_relate = true;
			$relate_lead->load_relationship('related_leads');
			$relate_lead->related_leads->add(substr($key, 7));
		}
	}
}

if(isset($_REQUEST['rollup_id']) && !empty($_REQUEST['rollup_id'])){
	global $lead_conversion_arrays;
	require_once('modules/Leads/LeadQualmeta_array.php');

	$focus = new Lead();
	$parent_lead = new Lead();

	$focus->retrieve($_REQUEST['rollup_id']);
	$parent_lead->retrieve($focus->parent_lead_id);

	foreach($lead_conversion_arrays['lead_rollup_map'] as $target_field){
		if(!empty($_REQUEST['map_'.$target_field]) && $_REQUEST['map_'.$target_field]=='overwrite'){
			$parent_lead->$target_field = $focus->$target_field;
		}
	}

	$parent_lead->save(false);
}

require_once('include/MVC/SugarApplication.php');
if(!isset($_SESSION['lead_qual_bucket'])){
	SugarApplication::redirect('index.php?module=Leads&action=LeadQualScoredLead&user=c15afb6d-a403-b92a-f388-4342a492003e');
}
else{
	$user = $_SESSION['lead_qual_bucket']['user'];
	SugarApplication::redirect("index.php?module=Leads&action=LeadQualScoredLead&user=$user");
}
?>
