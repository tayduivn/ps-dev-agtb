<?php
$hook_version = 1;
$hook_array = Array();

$hook_array['after_save'] = Array();
$hook_array['after_save'][] = Array(1, 'lastInteraction', 'custom/modules/Meetings/MeetingLogicHooks.php', 'MeetingLogicHooks', 'lastInteraction');

