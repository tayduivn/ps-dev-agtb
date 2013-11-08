<?php
/**
 * Before we save an RevenueLineItem, if the sales stage is in one of the Forecasts Closed States, then we need to
 * make sure we sync the values
 */
$hook_array['before_save'][] = array(
    1,
    'setBestWorstEqualToLikelyAmount',
    'modules/Forecasts/ForecastHooks.php',
    'ForecastHooks',
    'setBestWorstEqualToLikelyAmount',
);
