<?php
// Do not store anything in this file that is not part of the array or the hook version.  This file will    
// be automatically rebuilt in the future. 
 $hook_version = 1;
$hook_array = Array();
// position, file, function 


$hook_array['after_retrieve'] = Array();
$hook_array['after_retrieve'][] = Array(1, 'getTags', 'custom/modules/Documents/DocumentLogicHooks.php', 'DocumentLogicHooks', 'getTags');

$hook_array['after_save'] = Array();
$hook_array['after_save'][] = Array(1, 'saveTags', 'custom/modules/Documents/DocumentLogicHooks.php', 'DocumentLogicHooks', 'saveTags');

