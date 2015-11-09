<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

class RevenueLineItemHooks
{
    /**
     * After the relationship is deleted, we need to resave the RLI.  This
     * will ensure that it will pick up an accout from the associated Opportunity.
     *
     * @param RevenueLineItem $bean
     * @param string $event
     * @param array $args
     */
    public static function afterRelationshipDelete($bean, $event, $args)
    {
        if ($event == 'after_relationship_delete') {
            if ($args['link'] == 'account_link' && $bean->deleted == 0) {
                $bean->save();
                return true;
            }
        }
        return false;
    }

    /**
     * Before we save, we need to check to see if this rli is in a closed state. If so,
     * set it to the proper included/excluded state in case mass_update tried to set it to something wonky
     * @param RevenueLineItem $bean
     * @param string $event
     * @param array $args
     */
    public static function beforeSaveIncludedCheck($bean, $event, $args)
    {
        $settings = Forecast::getSettings(true);

        if ($settings['is_setup'] && $event == 'before_save') {
            $forecast_ranges = $settings['forecast_ranges'];
            $ranges = $settings[$forecast_ranges . '_ranges'];
            $commit_stage = "";

            //find the proper include stage for the percentage
            foreach ($ranges as $key => $value) {
                if ($bean->probability >= $value['min'] && $bean->probability <= $value['max']) {
                    $commit_stage = $key;
                    break;
                }
            }

            $bean->commit_stage = $commit_stage;
        }
    }
}
