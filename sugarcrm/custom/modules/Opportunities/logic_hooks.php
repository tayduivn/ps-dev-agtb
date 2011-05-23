<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
$hook_version = 1; 
$hook_array = Array(); 

// position, file, function 
$hook_array['before_save'] = Array(); 
$hook_array['before_save'][] = Array(1, 'duplicateContacts', 'custom/modules/Opportunities/OpportunityLogicHooks.php','OpportunityLogicHooks', 'duplicateContacts');
$hook_array['before_save'][] = Array(1, 'Opportunities push feed', 'modules/Opportunities/SugarFeeds/OppFeed.php','OppFeed', 'pushFeed'); 
$hook_array['before_save'][] = Array(1, 'Opportunities number set', 'custom/modules/Opportunities/OpportunityLogicHooks.php','OpportunityLogicHooks', 'setOppNumber'); 
$hook_array['before_save'][] = Array(1, 'Opportunities db set btt options', 'custom/modules/Opportunities/OpportunityLogicHooks.php','OpportunityLogicHooks', 'setBTTOptions');
$hook_array['before_save'][] = Array(1, 'Opportunities primary contact', 'custom/modules/Opportunities/OpportunityLogicHooks.php','OpportunityLogicHooks', 'setPrimaryContactRelationship'); 
// BEGIN sadek - SugarAlerts logic hooks
$hook_array['before_save'][] = Array(1, 'SugarAlerts before save', 'custom/modules/Opportunities/OpportunityLogicHooks.php','OpportunityLogicHooks', 'sugarAlerts'); 
// END sadek - SugarAlerts logic hooks

$hook_array['after_save'] = Array(); 
$hook_array['after_save'][] = Array(1, 'duplicateContacts', 'custom/modules/Opportunities/OpportunityLogicHooks.php','OpportunityLogicHooks', 'duplicateContacts');
$hook_array['after_save'][] = Array(1, 'Opportunities primary contact', 'custom/modules/Opportunities/OpportunityLogicHooks.php','OpportunityLogicHooks', 'setPrimaryContactRelationship'); 
$hook_array['after_save'][] = Array(1, 'saveTags', 'custom/modules/Opportunities/OpportunityLogicHooks.php', 'OpportunityLogicHooks', 'saveTags');
// BEGIN sadek - SugarAlerts logic hooks
$hook_array['after_save'][] = Array(1, 'SugarAlerts after save', 'custom/modules/Opportunities/OpportunityLogicHooks.php','OpportunityLogicHooks', 'sugarAlerts'); 
// END sadek - SugarAlerts logic hooks

$hook_array['after_retrieve'] = Array();
$hook_array['after_retrieve'][] = Array(1, 'Opportunities for display btt options', 'custom/modules/Opportunities/OpportunityLogicHooks.php','OpportunityLogicHooks', 'setBTTOptions');
$hook_array['after_retrieve'][] = Array(1, 'prepareDuplicate', 'custom/modules/Opportunities/OpportunityLogicHooks.php','OpportunityLogicHooks', 'prepareDuplicate');
$hook_array['after_retrieve'][] = Array(1, 'getTags', 'custom/modules/Opportunities/OpportunityLogicHooks.php', 'OpportunityLogicHooks', 'getTags');

// BEGIN sadek - SIMILAR OPPORTUNITIES CALCULATOR
$hook_array['after_relationship_delete'] = Array();
$hook_array['after_relationship_delete'][] = Array(1, 'addOppsToSimCalcQueue', 'custom/modules/Opportunities/OpportunityLogicHooks.php', 'OpportunityLogicHooks', 'addOppsToSimCalcQueue');
// END sadek - SIMILAR OPPORTUNITIES CALCULATOR
