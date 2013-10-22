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

class SugarUpgradeForecastWorksheetFixParents extends UpgradeScript
{
    public $order = 2110;
    public $type = self::UPGRADE_DB;

    /**
     * Run the Upgrade Task
     *
     * The reason we need to do before task 2100 (where the Repair and Rebuild happens
     * is that when coming from 6.7 to 7, it will blow away the fields we added that we still need
     * data from.  There for we have to put the RLI module in-place so in another upgrade task we can
     * move/copy the data into the RLI table.
     */
    public function run()
    {
        // only run this when coming from a 6.x upgrade
        if (!version_compare($this->from_version, '7.0', "<")) {
            return;
        }

        // we need to ignore CE
        if (!$this->fromFlavor('pro')) {
            return;
        }

        $this->log('Migrating Forecast Worksheet parent types from Product to RevenueLineItem.');
                
        $sql = "UPDATE forecast_worksheets " .
               "SET parent_type = 'RevenueLineItems' " .
               "WHERE parent_type = 'Products'";
        $this->db->query($sql);
        
        $this->log('Done migrating Forecast Worksheet parent types from Product to RevenueLineItem.');
    }
}
