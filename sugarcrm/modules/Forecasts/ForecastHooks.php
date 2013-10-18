<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once('modules/Forecasts/AbstractForecastHooks.php');
class ForecastHooks extends AbstractForecastHooks
{
    /**
     * This method, just set the date_modified to the value from the db, vs the user formatted value that sugarbean sets
     * after it has been retrieved
     *
     * @param Forecast $forecast
     * @param string $event
     * @param array $params
     */
    public static function fixDateModified(Forecast $forecast, $event, $params = array())
    {
        $forecast->date_modified = $forecast->fetched_row['date_modified'];
    }

    /**
     * If the commit_stage field is empty on a bean but the probability is not and Forecasts is setup, then try and
     * match the commit_stage to where the probability falls in the ranges defined by the forecast config.
     *
     * @param RevenueLineItem|Opportunity|SugarBean $bean
     * @param string $event
     * @param array $params
     */
    public function setCommitStageIfEmpty($bean, $event, $params = array())
    {
        // only run on before_save logic hooks
        if ($event != 'before_save') {
            return;
        }
        if (static::isForecastSetup() && empty($bean->commit_stage) && $bean->probability !== '') {
            //Retrieve Forecasts_category_ranges and json decode as an associative array
            $forecast_ranges = isset(static::$settings['forecast_ranges']) ? static::$settings['forecast_ranges'] : '';
            $category_ranges = isset(static::$settings[$forecast_ranges . '_ranges']) ?
                (array)static::$settings[$forecast_ranges . '_ranges'] : array();
            foreach ($category_ranges as $key => $entry) {
                if ($bean->probability >= $entry['min'] && $bean->probability <= $entry['max']) {
                    $bean->commit_stage = $key;
                    break;
                }
            }
        }
    }
}
