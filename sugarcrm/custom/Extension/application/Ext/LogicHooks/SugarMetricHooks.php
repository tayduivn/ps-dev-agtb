<?php

if (!isset($hook_array) || !is_array($hook_array)) {
    $hook_array = array();
}
if (!isset($hook_array['after_entry_point']) || !is_array($hook_array['after_entry_point'])) {
    $hook_array['after_entry_point'] = array();
}

$hook_array['after_entry_point'][] = array(2, 'metric_manager', 'include/SugarMetric/HookManager.php', 'SugarMetric_HookManager', 'afterEntryPoint');