<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

class PurchasedLineItem extends Basic
{
    public $module_dir = 'PurchasedLineItems';
    public $object_name = 'PurchasedLineItem';
    public $table_name = 'purchased_line_items';
    public $module_name = 'PurchasedLineItems';

    public $name;
    public $book_value_usdollar;
    public $cost_price;
    public $cost_usdollar;
    public $date_closed;
    public $date_closed_timestamp;
    public $deal_calc;
    public $deal_calc_usdollar;
    public $discount_amount;
    public $discount_amount_signed;
    public $discount_amount_usdollar;
    public $discount_price;
    public $discount_select;
    public $discount_usdollar;
    public $list_usdollar;
    public $mft_part_num;
    public $quantity;
    public $revenue;
    public $revenue_usdollar;
    public $total_amount;
    public $yearly_revenue;
    // Fields for relationships
    public $categories;
    public $category_id;
    public $category_name;
    public $manufacturer;
    public $manufacturer_id;
    public $manufacturer_name;
    public $product_templates;
    public $product_template_id;
    public $product_template_name;
    public $product_type;
    public $product_type_id;
    public $product_type_name;
    public $revenuelineitem;
    public $revenuelineitem_id;
    public $revenuelineitem_name;
    // Fields for "Activity" relationships
    public $calls;
    public $emails;
    public $meetings;
    public $notes;
    public $tasks;

    public $importable = true;

    /**
     * {@inheritDoc}
     */
    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }
}
