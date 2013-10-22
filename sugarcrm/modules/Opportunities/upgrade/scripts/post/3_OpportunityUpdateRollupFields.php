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

class SugarUpgradeOpportunityUpdateRollupFields extends UpgradeScript
{
    public $order = 3030;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        // are we coming from anything before 7.0?
        if (!version_compare($this->from_version, '7.0', '<')) {
            return;
        }

        // we need to anything other than ENT and ULT
        if (!$this->fromFlavor('ent')) {
            return;
        }

        $this->log('Updating Opportunity Rollup Fields');

        $sql = "SELECT opportunity_id               AS opp_id,
                          Sum(likely_case)             AS likely,
                          Sum(worst_case)              AS worst,
                          Sum(best_case)               AS best,
                          Max(t.date_closed)           AS date_closed,
                          Max(t.date_closed_timestamp) AS date_closed_timestamp,
                          Count(0)                     AS total,
                          ( won + lost )               total_closed,
                          CASE
                            WHEN Count(0) = 0
                                  OR Count(0) > ( won + lost ) THEN
                            'In Progress'
                            ELSE
                              CASE
                                WHEN lost = Count(0) THEN 'Closed Lost'
                                ELSE 'Closed Won'
                              end
                          end                          AS sales_status
                   FROM   (SELECT rli.opportunity_id,
                                  (rli.likely_case/rli.base_rate) as likely_case,
                                  (rli.worst_case/rli.base_rate) as worst_case,
                                  (rli.best_case/rli.base_rate) as best_case,
                                  rli.date_closed,
                                  rli.date_closed_timestamp,
                                  CASE
                                    WHEN rli.sales_stage = 'Closed Lost' THEN 1
                                    ELSE 0
                                  end AS lost,
                                  CASE
                                    WHEN rli.sales_stage = 'Closed Won' THEN 1
                                    ELSE 0
                                  end AS won
                           FROM   revenue_line_items AS rli
                           WHERE  rli.deleted = 0) AS t
                   GROUP  BY opp_id";

        $results = $this->db->query($sql);

        $sql = "UPDATE opportunities SET
                    amount='%f',best_case='%f',worst_case='%f',date_closed='%s',date_closed_timestamp='%s',
                    sales_status='%s',total_revenue_line_items='%d',closed_revenue_line_items='%d' WHERE id = '%s'";
        while ($row = $this->db->fetchRow($results)) {
            $this->db->query(
                sprintf(
                    $sql,
                    $row['likely'],
                    $row['best'],
                    $row['worst'],
                    $row['date_closed'],
                    $row['date_closed_timestamp'],
                    $row['sales_status'],
                    $row['total'],
                    $row['total_closed'],
                    $row['opp_id']
                )
            );
        }

        $this->log('Done Updating Opportunity Rollup Fields');
    }
}
