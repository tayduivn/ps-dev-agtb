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

class SugarUpgradeRevenueLineItemAccountSync extends UpgradeScript
{
    public $order = 2180;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        // are we coming from anything before 7.0?
        if (!version_compare($this->from_version, '7.0', '<')) {
            return;
        }

        $this->log('Syncing Accounts to RLI Table');

        $sql = "UPDATE revenue_line_items rli
               SET account_id = (SELECT ac.account_id
                                 FROM accounts_opportunities ac
                                 WHERE ac.opportunity_id = rli.opportunity_id and ac.deleted = 0)
               WHERE rli.account_id IS NULL or rli.account_id = ''";

        $r = $this->db->query($sql);
        $this->log('SQL Ran, Updated ' . $this->db->getAffectedRowCount($r) . ' Rows');

        $this->log('Done Syncing Accounts to RLI Table');
    }
}
