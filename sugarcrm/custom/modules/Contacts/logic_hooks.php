<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will	
// be automatically rebuilt in the future. 
 $hook_version = 1; 
$hook_array = Array(); 
// position, file, function 


$hook_array['before_save'] = Array(); 
$hook_array['before_save'][] = Array(1, 'Contacts push feed', 'modules/Contacts/SugarFeeds/ContactFeed.php','ContactFeed', 'pushFeed'); 

$hook_array['after_retrieve'] = Array();
$hook_array['after_retrieve'][] = Array(1, 'getTags', 'custom/modules/Contacts/ContactLogicHooks.php', 'ContactLogicHooks', 'getTags');

$hook_array['after_save'] = Array();
$hook_array['after_save'][] = Array(1, 'saveTags', 'custom/modules/Contacts/ContactLogicHooks.php', 'ContactLogicHooks', 'saveTags');

$hook_array['process_record'] = Array();
$hook_array['process_record'][] = Array(1, 'strikeSuppressedFields', 'custom/modules/Contacts/ContactLogicHooks.php', 'ContactLogicHooks', 'strikeSuppressedFields');
