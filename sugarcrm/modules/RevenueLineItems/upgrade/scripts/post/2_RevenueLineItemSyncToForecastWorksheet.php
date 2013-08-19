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

class SugarUpgradeRevenueLineItemSyncToForecastWorksheet extends UpgradeScript
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
        if (!$this->fromFlavor('ent')) {
            return;
        }

        $this->log('Updating Revenue Line Item Rows in Forecast Worksheet');

        $fields = array(
            'name',
            'account_id',
            'account_name',
            'likely_case',
            'best_case',
            'base_rate',
            'worst_case',
            'currency_id',
            'date_closed',
            'date_closed_timestamp',
            'probability',
            'commit_stage',
            'sales_stage',
            'assigned_user_id',
            'created_by',
            'date_entered',
            'deleted',
            'team_id',
            'team_set_id',
            'opportunity_id',
            'opportunity_name',
            'description',
            'next_step',
            'lead_source',
            'product_type',
            'campaign_id',
            'campaign_name',
            'product_template_id',
            'product_template_name',
            'category_id',
            'category_name',
            'list_price',
            'cost_price',
            'discount_price',
            'discount_amount',
            'quantity',
            'total_amount'
        );

        $sqlSet = "%s=(SELECT %s from revenue_line_items r WHERE r.id = fw.parent_id and fw.parent_type = 'RevenueLineItems')";

        $sqlSetArray = array();

        foreach ($fields as $field) {
            $key = $field;
            if (is_array($field)) {
                $key = array_shift(array_keys($field));
                $field = array_shift($field);
            }

            switch ($field) {
                case 'account_name':
                    $sqlSetArray[] = sprintf(
                        "%s = (SELECT DISTINCT a.name FROM accounts a INNER JOIN revenue_line_items r on
                            r.account_id = a.id WHERE r.id = fw.parent_id and fw.parent_type = 'RevenueLineItems')",
                        $field
                    );
                    break;
                case 'opportunity_name':
                    $sqlSetArray[] = sprintf(
                        "%s = (SELECT DISTINCT o.name FROM opportunities o INNER JOIN revenue_line_items r on
                            r.opportunity_id = o.id WHERE r.id = fw.parent_id and fw.parent_type = 'RevenueLineItems')",
                        $field
                    );
                    break;
                case 'campaign_name':
                    $sqlSetArray[] = sprintf(
                        "%s = (SELECT DISTINCT c.name FROM campaigns c INNER JOIN revenue_line_items r on
                            r.campaign_id = c.id WHERE r.id = fw.parent_id and fw.parent_type = 'RevenueLineItems')",
                        $field
                    );
                    break;
                case 'product_template_name':
                    $sqlSetArray[] = sprintf(
                        "%s = (SELECT DISTINCT p.name FROM product_templates p INNER JOIN revenue_line_items r on
                            r.product_template_id = p.id WHERE r.id = fw.parent_id and fw.parent_type = 'RevenueLineItems')",
                        $field
                    );
                    break;
                case 'category_name':
                    $sqlSetArray[] = sprintf(
                        "%s = (SELECT DISTINCT c.name FROM product_categories c INNER JOIN revenue_line_items r on
                            r.category_id = c.id WHERE r.id = fw.parent_id and fw.parent_type = 'RevenueLineItems')",
                        $field
                    );
                    break;
                default;
                    $sqlSetArray[] = sprintf($sqlSet, $key, $field);
                    break;
            }
        }

        $sql = "update forecast_worksheets as fw SET " . join(",", $sqlSetArray) . "
          where exists (SELECT * from revenue_line_items r WHERE r.id = fw.parent_id and fw.parent_type = 'RevenueLineItems')";

        $r = $this->db->query($sql);

        $this->log('SQL Ran, Updated ' . $this->db->getAffectedRowCount($r) . ' Rows');
    }
}
