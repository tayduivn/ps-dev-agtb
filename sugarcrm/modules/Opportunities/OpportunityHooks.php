<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */


class OpportunityHooks
{
    /**
     * Utility Method to make sure Forecast is setup and usable
     *
     * @return bool
     */
    public static function isForecastSetup()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');
        return $settings['is_setup'] == 1;
    }

    /**
     * This is a general hook that takes the Opportunity and saves it to the forecast worksheet record.
     *
     * @param Opportunity $bean             The bean we are working with
     * @param string $event                 Which event was fired
     * @param array $args                   Any additional Arguments
     * @return bool
     */
    public static function saveWorksheet(Opportunity $bean, $event, $args)
    {
        if (static::isForecastSetup()) {
            /* @var $worksheet ForecastWorksheet */
            $worksheet = BeanFactory::getBean('ForecastWorksheets');
            $worksheet->saveRelatedOpportunity($bean);
            return true;
        }

        return false;
    }

    /**
     * Mark all related RLI's on a given opportunity to be deleted
     *
     * @param Opportunity $bean
     * @param $event
     * @param $args
     */
    public static function deleteOpportunityRevenueLineItems(Opportunity $bean, $event, $args)
    {
        if (static::isForecastSetup()) {
            $products = $bean->get_linked_beans('products', 'Products');
            foreach ($products as $product) {
                $product->mark_deleted($product->id);
            }
        }
    }
}
