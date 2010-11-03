<?php

global $app_list_strings;

$meta_opportunityRevenueTypeOppTypeMap = array(
	'' => array(
	),
	'Additional' => array(
	),
	'New' => array(
		'sugar_ent_converge',
		'sugar_pro_converge',
		'Support Services',
		'Partner Fees',
		'Partner Sales Training',
		'Profesional Services',
		'Pro Services - Channel',
		'Pro Services - SI Sub',
		'Pro Services - SI Direct',
		'OEM',
		'SugarExchange',
	),
	'Renewal' => array(
		'sugar_ent_converge',
		'sugar_pro_converge',
		'Support Services',
		'Partner Fees',
		'Partner Sales Training',
		'OEM',
		'SugarExchange',
	),
);

$opportunityRevenueTypeOppTypeMap = array();

foreach($meta_opportunityRevenueTypeOppTypeMap as $rev => $rev_arr){
	$opportunityRevenueTypeOppTypeMap[$rev] = array();
	if(!empty($rev_arr)){
		$opportunityRevenueTypeOppTypeMap[$rev][''] = '';
		foreach($rev_arr as $type_key){
			$opportunityRevenueTypeOppTypeMap[$rev][$type_key] = $app_list_strings['opportunity_type_dom'][$type_key];
		}
	}
	else{
		foreach($app_list_strings['opportunity_type_dom'] as $type_key => $type_val){
			$opportunityRevenueTypeOppTypeMap[$rev][$type_key] = $type_val;
		}
	}
}
