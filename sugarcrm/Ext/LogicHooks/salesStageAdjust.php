<?php

// Full text search.
$hook_array['before_save'][] = array(
    1,
    'salesStageAdjust',
    'include/SugarForecasting/Hooks/StatusAutoAdjustHook.php',
    'StatusAutoAdjustHook',
    'adjustStatus'
);
