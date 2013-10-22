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

class SugarUpgradeOpportunityCreateRLI extends UpgradeScript
{
    public $order = 2115;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        // are we coming from anything before 7.0?
        if (!version_compare($this->from_version, '7.0', '<')) {
            return;
        }

        // we need to ignore CE
        if (!$this->fromFlavor('pro')) {
            return;
        }

        $this->log("Creating missing RLIs for orphaned Opportunities");
        $sql = "INSERT INTO revenue_line_items " .
                "(id, " .
                 "opportunity_id, " .
                 "name, " .
                 "worst_case, " .
                 "likely_case, " .
                 "best_case, " .
                 "cost_price, " .
                 "quantity, " .
                 "currency_id, " .
                 "base_rate, " .
                 "probability, " .
                 "date_closed, " .
                 "date_closed_timestamp, " .
                 "assigned_user_id, " .
                 "account_id, " .
                 "commit_stage, " .
                 "sales_stage, " .
                 "deleted, " .
                 "date_entered, " .
                 "date_modified, " .
                 "modified_user_id, " .
                 "created_by, " .
                 "team_id, " .
                 "team_set_id) " .
                "SELECT o.id, " .
                       "o.id, " .
                       "o.name, " .
                       "o.worst_case, " .
                       "o.amount, " .
                       "o.best_case, " .
                       "o.amount, " .
                       "1, " .
                       "o.currency_id, " .
                       "o.amount_usdollar/o.amount, " .
                       "o.probability, " .
                       "o.date_closed, " .
                       "o.date_closed_timestamp, " .
                       "o.assigned_user_id, " .
                       "ac.account_id, " .
                       "o.commit_stage, " .
                       "o.sales_stage, " .
                       "o.deleted, " .
                       "o.date_entered, " .
                       "o.date_modified, " .
                       "o.modified_user_id, " .
                       "o.created_by, " .
                       "o.team_id, " .
                       "o.team_set_id " .
                "FROM opportunities as o " .
                "LEFT JOIN accounts_opportunities as ac " . 
                "ON ac.opportunity_id = o.id " .
                "LEFT JOIN revenue_line_items rli " .
                "ON o.id = rli.opportunity_id " .
                "WHERE rli.id IS NULL";

        $this->log('Running SQL: ' . $sql);
        $r = $this->db->query($sql);
        $this->log('RLI\'s Created: ' . $this->db->getAffectedRowCount($r));

        $this->log("Done creating missing RLIs for orphaned Opportunities");
    }
}
