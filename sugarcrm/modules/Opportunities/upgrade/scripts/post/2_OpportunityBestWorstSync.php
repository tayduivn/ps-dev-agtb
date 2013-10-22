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

class SugarUpgradeOpportunityBestWorstSync extends UpgradeScript
{
    public $order = 2110;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        // are we coming from anything before 6.7?
        if (!version_compare($this->from_version, '6.7.0', '<')) {
            return;
        }

        // are we going to 7.0 standard
        if (!version_compare($this->to_version, '7.0', '<')) {
            return;
        }

        // we need to ignore CE
        if (!$this->fromFlavor('pro')) {
            return;
        }

        $this->log('Syncing Opportunity Amount to Best Case if it\'s empty or null');
        $sql = "UPDATE opportunities
                SET    best_case = amount
                WHERE  ( best_case = '' OR best_case IS NULL );";
        $this->db->query($sql);

        $this->log('Syncing Opportunity Amount to Worst Case if it\'s empty or null');
        $sql = "UPDATE opportunities
                SET    worst_case = amount
                WHERE  ( worst_case = '' OR worst_case IS NULL );";
        $this->db->query($sql);

        $this->log('Done Syncing Opportunity Best and Worst Case with Amount Field');
    }
}
