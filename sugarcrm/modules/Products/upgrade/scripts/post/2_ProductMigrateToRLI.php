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

class SugarUpgradeProductMigrateToRLI extends UpgradeScript
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

        $this->log('Migrating Products to Revenue Line Items.');
        
        // Only run this sql if coming from 6.5.. all Products are the result of Quotes, so we
        // need to copy over Products that are quoted and associated to an Opportunity
        if (version_compare($this->from_version, '6.7.0', "<")) {
            $this->log('Migrating 6.5 Products assigned to Quotes that have Opportunities.');
            $sql = "INSERT INTO revenue_line_items " .
                   "SELECT  p.id, " .
                           "p.name, " .
                           "p.date_entered, " .
                           "p.date_modified, " .
                           "p.modified_user_id, " .
                           "p.created_by, " .
                           "p.description, " .
                           "p.deleted, " .
                           "q.assigned_user_id, " .
                           "p.team_id, " .
                           "p.team_set_id, " .
                           "p.product_template_id, " .
                           "p.account_id, " .
                           "p.discount_price * p.quantity, " . //calculate total_amount
                           "p.type_id, " .
                           "p.quote_id, " .
                           "p.manufacturer_id, " .
                           "p.category_id, " .
                           "p.mft_part_num, " .
                           "p.vendor_part_num, " .
                           "p.date_purchased, " .
                           "p.cost_price, " .
                           "p.discount_price, " .
                           "p.discount_amount, " .
                           "null, " . //discount_rate_percent
                           "p.discount_amount_usdollar, " .
                           "p.discount_select, " .
                           "p.deal_calc, " .
                           "p.deal_calc_usdollar, " .
                           "p.list_price, " .
                           "p.cost_usdollar, " .
                           "p.discount_usdollar, " .
                           "p.list_usdollar, " .
                           "p.currency_id, " .
                           "p.discount_usdollar / p.discount_price, " . //base_rate
                           "p.status, " .
                           "p.tax_class, " .
                           "p.website, " .
                           "p.weight, " .
                           "p.quantity, " .
                           "p.support_name, " .
                           "p.support_description, " .
                           "p.support_contact, " .
                           "p.support_term, " .
                           "p.date_support_expires, " .
                           "p.date_support_starts, " .
                           "p.pricing_formula, " .
                           "p.pricing_factor, " .
                           "p.serial_number, " .
                           "p.asset_number, " .
                           "p.book_value, " .
                           "p.book_value_usdollar, " .
                           "p.book_value_date, " .
                           "o.amount, " . //best_case
                           "o.amount, " . //likely_case
                           "o.amount, " . //worst_case
                           "o.date_closed, " .
                           "0, " . //date_closed_timestamp -- needs to be updated later
                           "o.next_step, " .
                           "null, " . //commit_stage
                           "o.sales_stage, " .
                           "o.probability, " .
                           "o.lead_source, " .
                           "o.campaign_id, " .
                           "o.id, " .
                           "o.opportunity_type " .
                   "FROM products p  " .
                   "INNER JOIN quotes q  " .
                   "ON q.id = p.quote_id " .
                   "INNER JOIN quotes_opportunities qo " .
                   "ON qo.quote_id = q.id " .
                   "INNER JOIN opportunities o " .
                   "ON o.id = qo.opportunity_id";
            $this->db->query($sql);
            $this->log('Done migrating 6.5 Products assigned to Quotes that have Opportunities.');
        }

        //Now we need to do some migration on the 6.7 data, which is a bit more like what we need in 7.
        if (version_compare($this->from_version, '6.7.0', ">=")) {
            $this->log('Migrating 6.7 Products with Opportunities and without Quotes.');
            $sql = "INSERT INTO revenue_line_items " .
                   "SELECT  p.id, " .
                           "p.name, " .
                           "p.date_entered, " .
                           "p.date_modified, " .
                           "p.modified_user_id, " .
                           "p.created_by, " .
                           "p.description, " .
                           "p.deleted, " .
                           "p.assigned_user_id, " .
                           "p.team_id, " .
                           "p.team_set_id, " .
                           "p.product_template_id, " .
                           "p.account_id, " .
                           "p.discount_price * p.quantity, " . //calculate total amount
                           "p.type_id, " .
                           "p.quote_id, " .
                           "p.manufacturer_id, " .
                           "p.category_id, " .
                           "p.mft_part_num, " .
                           "p.vendor_part_num, " .
                           "p.date_purchased, " .
                           "p.cost_price, " .
                           "p.discount_price, " .
                           "p.discount_amount, " .
                           "null, " . //discount_rate_percent
                           "p.discount_amount_usdollar, " .
                           "p.discount_select, " .
                           "p.deal_calc, " .
                           "p.deal_calc_usdollar, " .
                           "p.list_price, " .
                           "p.cost_usdollar, " .
                           "p.discount_usdollar, " .
                           "p.list_usdollar, " .
                           "p.currency_id, " .
                           "p.base_rate, " .
                           "p.status, " .
                           "p.tax_class, " .
                           "p.website, " .
                           "p.weight, " .
                           "p.quantity, " .
                           "p.support_name, " .
                           "p.support_description, " .
                           "p.support_contact, " .
                           "p.support_term, " .
                           "p.date_support_expires, " .
                           "p.date_support_starts, " .
                           "p.pricing_formula, " .
                           "p.pricing_factor, " .
                           "p.serial_number, " .
                           "p.asset_number, " .
                           "p.book_value, " .
                           "p.book_value_usdollar, " .
                           "p.book_value_date, " .
                           "p.best_case, " .
                           "p.likely_case, " .
                           "p.worst_case, " .
                           "p.date_closed, " .
                           "p.date_closed_timestamp, " .
                           "o.next_step, " .
                           "p.commit_stage, " .
                           "o.sales_stage, " .
                           "p.probability, " .
                           "o.lead_source, " .
                           "o.campaign_id, " .
                           "p.opportunity_id, " .
                           "o.opportunity_type " .
                   "FROM products p " .
                   "INNER JOIN opportunities o " .
                   "on o.id = p.opportunity_id " .
                   "WHERE p.opportunity_id IS NOT NULL " .
                   "AND p.quote_id IS NULL";
            $this->db->query($sql);
            $this->log('Done migrating 6.7 Products with Opportunities and without Quotes.');
            
            $this->log('Migrating 6.7 Products assigned to Quotes that have Opportunities.');
            $sql = "INSERT INTO revenue_line_items " .
                   "SELECT  p.id, " .
                           "p.name, " .
                           "p.date_entered, " .
                           "p.date_modified, " .
                           "p.modified_user_id, " .
                           "p.created_by, " .
                           "p.description, " .
                           "p.deleted, " .
                           "q.assigned_user_id, " .
                           "p.team_id, " .
                           "p.team_set_id, " .
                           "p.product_template_id, " .
                           "p.account_id, " .
                           "p.discount_price * p.quantity, " . //calculate total_amount
                           "p.type_id, " .
                           "p.quote_id, " .
                           "p.manufacturer_id, " .
                           "p.category_id, " .
                           "p.mft_part_num, " .
                           "p.vendor_part_num, " .
                           "p.date_purchased, " .
                           "p.cost_price, " .
                           "p.discount_price, " .
                           "p.discount_amount, " .
                           "null, " . //discount_rate_percent
                           "p.discount_amount_usdollar, " .
                           "p.discount_select, " .
                           "p.deal_calc, " .
                           "p.deal_calc_usdollar, " .
                           "p.list_price, " .
                           "p.cost_usdollar, " .
                           "p.discount_usdollar, " .
                           "p.list_usdollar, " .
                           "p.currency_id, " .
                           "p.base_rate, " .
                           "p.status, " .
                           "p.tax_class, " .
                           "p.website, " .
                           "p.weight, " .
                           "p.quantity, " .
                           "p.support_name, " .
                           "p.support_description, " .
                           "p.support_contact, " .
                           "p.support_term, " .
                           "p.date_support_expires, " .
                           "p.date_support_starts, " .
                           "p.pricing_formula, " .
                           "p.pricing_factor, " .
                           "p.serial_number, " .
                           "p.asset_number, " .
                           "p.book_value, " .
                           "p.book_value_usdollar, " .
                           "p.book_value_date, " .
                           "p.best_case, " .
                           "p.likely_case, " .
                           "p.worst_case, " .
                           "p.date_closed, " .
                           "p.date_closed_timestamp, " .
                           "o.next_step, " .
                           "p.commit_stage, " .
                           "o.sales_stage, " .
                           "p.probability, " .
                           "o.lead_source, " .
                           "o.campaign_id, " .
                           "p.opportunity_id, " .
                           "o.opportunity_type " .
                   "FROM products p  " .
                   "INNER JOIN quotes q  " .
                   "ON q.id = p.quote_id " .
                   "INNER JOIN quotes_opportunities qo " .
                   "ON qo.quote_id = q.id " .
                   "INNER JOIN opportunities o " .
                   "ON o.id = qo.opportunity_id";
            $this->db->query($sql);
            $this->log('Done migrating 6.7 Products assigned to Quotes that have Opportunities.');
        }
        
        //clean up products that we've just moved over (non quoted)
        $this->log('Removing Products that were moved.');
        $sql = "DELETE FROM products " .
               "WHERE opportunity_id IS NOT NULL " .
               "AND quote_id IS NULL";
        $this->db->query($sql);
        $this->log('Done removing Products that were moved.');
        
        $this->log('Done migrating Products to Revenue Line Items.');
    }
}
