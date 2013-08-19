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

class SugarUpgradeForecastWorksheetHasDraftRecord extends UpgradeScript
{
    public $order = 2180;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        // are we coming from 6.7 but before 7.0
        if (!version_compare($this->from_version, '6.7.0', '>=') &&
            !version_compare($this->from_version, '7.0', '<')) {
            return;
        }

        // we need to anything other than ENT and ULT
        if (!$this->fromFlavor('pro')) {
            return;
        }

        $this->log('Creating Forecast Worksheet Draft Records');

        $sql = "INSERT INTO forecast_worksheets " .
               "SELECT fw.parent_id, " .
                      "fw.name, " .
                      "fw.date_entered, " .
                      "fw.date_modified, " .
                      "fw.modified_user_id, " .
                      "fw.created_by, " .
                      "fw.description, " .
                      "fw.deleted, " .
                      "fw.assigned_user_id, " .
                      "fw.team_id, " .
                      "fw.team_set_id, " .
                      "fw.parent_id, " .
                      "fw.parent_type, " .
                      "fw.likely_case, " .
                      "fw.best_case, " .
                      "fw.worst_case, " .
                      "fw.base_rate, " .
                      "fw.currency_id, " .
                      "fw.date_closed, " .
                      "fw.date_closed_timestamp, " .
                      "fw.sales_stage, " .
                      "fw.probability, " .
                      "fw.commit_stage, " .
                      "1 as draft, " .
                      "fw.opportunity_id, " .
                      "fw.opportunity_name, " .
                      "fw.account_name, " .
                      "fw.account_id, " .
                      "fw.campaign_id, " .
                      "fw.campaign_name, " .
                      "fw.product_template_id, " .
                      "fw.product_template_name, " .
                      "fw.category_id, " .
                      "fw.category_name, " .
                      "fw.sales_status, " .
                      "fw.next_step, " .
                      "fw.lead_source, " .
                      "fw.product_type, " .
                      "fw.list_price, " .
                      "fw.cost_price, " .
                      "fw.discount_price, " .
                      "fw.discount_amount, " .
                      "fw.quantity, " .
                      "fw.total_amount " .
              "FROM forecast_worksheets fw " .
              "LEFT JOIN forecast_worksheets fw2 " .
              "ON fw.parent_type = fw2.parent_type " .
                  "AND fw.parent_id = fw2.parent_id " .
                  "AND fw2.draft = 1 " .
              "WHERE fw.deleted = 0 " .
                  "AND fw.draft = 0 " .
                  "AND fw2.id IS NULL";

        $result = $this->db->query($sql);
        
        $this->log('Added ' . $this->db->getAffectedRowCount($result) . ' Draft Records');
        $this->log('Done Creating Forecast Worksheet Draft Records');
    }
}
