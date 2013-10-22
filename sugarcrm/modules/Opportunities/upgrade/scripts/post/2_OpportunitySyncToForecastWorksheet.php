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

class SugarUpgradeOpportunitySyncToForecastWorksheet extends UpgradeScript
{
    public $order = 2190;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        // are we coming from anything before 7.0?
        if (!version_compare($this->from_version, '7.0', '<')) {
            return;
        }

        // we need to anything other than ENT and ULT
        if (!$this->fromFlavor('pro')) {
            return;
        }

        $this->log('Updating Opportunity Rows in Forecast Worksheet');

        $fields = array(
            'name',
            'account_id',
            'account_name',
            array('likely_case' => 'amount'),
            'best_case',
            'base_rate',
            'worst_case',
            'currency_id',
            'date_closed',
            'date_closed_timestamp',
            'sales_stage',
            'probability',
            'commit_stage',
            'assigned_user_id',
            'created_by',
            'date_entered',
            'deleted',
            'team_id',
            'team_set_id',
            'sales_status',
            'description',
            'next_step',
            'lead_source',
            array('product_type' => 'opportunity_type'),
            'campaign_id',
            'campaign_name'
        );

        $sqlSet = "%s=(SELECT %s from opportunities o WHERE o.id = fw.parent_id and fw.parent_type = 'Opportunities')";

        $sqlSetArray = array();

        foreach ($fields as $field) {
            $key = $field;
            if (is_array($field)) {
                $key = array_shift(array_keys($field));
                $field = array_shift($field);
            }

            if ($field == 'account_name') {
                $sqlSetArray[] = sprintf(
                    "%s = (SELECT DISTINCT a.name FROM accounts a INNER JOIN accounts_opportunities ac on
                    ac.account_id = a.id WHERE ac.opportunity_id = fw.parent_id and fw.parent_type = 'Opportunities')",
                    $field
                );
            } else {
                if ($field == 'campaign_name') {
                    $sqlSetArray[] = sprintf(
                        "%s = (SELECT DISTINCT c.name FROM campaigns c INNER JOIN opportunities o on
                            o.campaign_id = c.id WHERE o.id = fw.parent_id and fw.parent_type = 'Opportunities')",
                        $field
                    );
                } else {
                    $sqlSetArray[] = sprintf($sqlSet, $key, $field);
                }
            }

        }

        $sql = "update forecast_worksheets as fw SET " . join(",", $sqlSetArray) . "
          where exists (SELECT * from opportunities o WHERE o.id = fw.parent_id and fw.parent_type = 'Opportunities')";

        $r = $this->db->query($sql);

        $this->log('SQL Ran, Updated ' . $this->db->getAffectedRowCount($r) . ' Rows');
    }
}
