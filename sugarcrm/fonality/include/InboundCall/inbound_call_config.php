<?php
$inbound_call_config = array(
'version' => 'ENT', // PRO or ENT
'planned_call_period' => '86400',
'opportunity_status_exclude' => array('Closed Won','Closed Lost'),
'case_status_exclude' => array('Closed','Duplicate','Rejected'),
'lead_status_exclude' => array('Converted'),
'show_planned_calls' => true,
'show_related_opportunities' => true,
'show_related_cases' => true,
'show_related_account_contacts' => false,
);
?>