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
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 */

class SugarUpgradeRevenueLineItemFixBaseRate extends UpgradeScript
{
    public $order = 2150;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        if (version_compare($this->from_version, '7.2.0', '<=') && $this->toFlavor('ent')) {
            $sql = "UPDATE revenue_line_items SET base_rate = (discount_price/discount_usdollar)
                    WHERE discount_price IS NOT NULL AND discount_usdollar IS NOT NULL;";
            $r = $this->db->query($sql);

            $this->log('Updated base_rate on ' . $this->db->getAffectedRowCount($r) . ' rows');

            // update all the usd fields
            $sql = 'UPDATE revenue_line_items SET
                discount_amount_usdollar = (discount_amount/base_rate),
                deal_calc_usdollar = (deal_calc/base_rate),
                cost_usdollar = (cost_price/base_rate),
                discount_usdollar = (discount_price/base_rate),
                list_usdollar = (list_price/base_rate),
                book_value_usdollar = (book_value/base_rate)
              ';
            $r = $this->db->query($sql);
            $this->log('Updated usdollar fields on ' . $this->db->getAffectedRowCount($r) . ' rows');
        }
    }
}
