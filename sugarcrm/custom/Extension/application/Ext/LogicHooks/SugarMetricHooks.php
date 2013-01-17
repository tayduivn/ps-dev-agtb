<?php

if (!isset($hook_array) || !is_array($hook_array)) {
    $hook_array = array();
}
if (!isset($hook_array['after_entry_point']) || !is_array($hook_array['after_entry_point'])) {
    $hook_array['after_entry_point'] = array();
}

if (!isset($hook_array['server_round_trip']) || !is_array($hook_array['server_round_trip'])) {
    $hook_array['server_round_trip'] = array();
}

$hook_array['after_entry_point'][] = array(2, 'smm', 'include/SugarMetric/HookManager.php', 'SugarMetric_HookManager', 'afterEntryPoint');
$hook_array['server_round_trip'][] = array(3, 'smm', 'include/SugarMetric/HookManager.php', 'SugarMetric_HookManager', 'serverRoundTrip');