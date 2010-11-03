<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$hook_array['before_save'][] = Array(1, 'label1', 'custom/modules/Notes/customPortalLogic.php', 'NoteCustomPortal', 'sendUpdates');

// BEGIN jostrow customization
// See ITRequest #9623

$hook_array['before_save'][] = Array(2, 'label2', 'custom/modules/Notes/MoofCartNotices.php', 'NoteMoofCartNotices', 'notifyOpportunityOwner');

// END jostrow customization
?>
