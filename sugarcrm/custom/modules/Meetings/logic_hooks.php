<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$hook_array = array();
$hook_array['after_save'] = array();
$hook_array['after_save'][] = array(1, 'notify', 'custom/include/Meetings/ScheduleWebExMeeting.php', 'ScheduleWebExMeeting', 'schedule', 'schedule');
?>
