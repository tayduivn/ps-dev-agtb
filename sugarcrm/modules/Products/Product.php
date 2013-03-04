<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

// Product is used to store customer information.
class Product extends SugarBean
{
    CONST STATUS_CONVERTED_TO_QUOTE = 'Converted to Quote';

    const STATUS_QUOTED = 'Quotes';

    // Stored fields
    public $id;
    public $deleted;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $created_by;
    public $created_by_name;
    public $modified_by_name;
    public $field_name_map;
    public $name;
    public $product_template_id;
    public $description;
    public $vendor_part_num;
    public $cost_price;
    public $discount_price;
    public $list_price;
    public $list_usdollar;
    public $discount_usdollar;
    public $cost_usdollar;
    public $deal_calc;
    public $deal_calc_usdollar;
    public $discount_amount_usdollar;
    public $currency_id;
    public $mft_part_num;
    public $status;
    public $date_purchased;
    public $weight;
    public $quantity;
    public $website;
    public $tax_class;
    public $support_name;
    public $support_description;
    public $support_contact;
    public $support_term;
    public $date_support_expires;
    public $date_support_starts;
    public $pricing_formula;
    public $pricing_factor;
    public $team_id;
    public $serial_number;
    public $asset_number;
    public $book_value;
    public $book_value_usdollar;
    public $book_value_date;
    public $currency_symbol;
    public $currency_name;
    public $default_currency_symbol;
    public $discount_amount;
    public $best_case = 0;
    public $likely_case = 0;
    public $worst_case = 0;
    public $base_rate;
    public $probability;
    public $date_closed;
    public $date_closed_timestamp;
    public $commit_stage;
    public $assigned_user_id;
    public $opportunity_id;

    /**
     * @public String      The Current Sales Stage
     */
    public $sales_stage;
    /**
     * @public String      The Current Sales Status
     */
    public $sales_status;

    // These are for related fields
    public $type_name;
    public $type_id;
    public $quote_id;
    public $quote_name;
    public $manufacturer_name;
    public $manufacturer_id;
    public $category_name;
    public $category_id;
    public $account_name;
    public $account_id;
    public $contact_name;
    public $contact_id;
    public $related_product_id;
    public $contracts;

    public $table_name = "products";
    public $rel_manufacturers = "manufacturers";
    public $rel_types = "product_types";
    public $rel_products = "product_product";
    public $rel_categories = "product_categories";

    public $object_name = "Product";
    public $module_dir = 'Products';
    public $new_schema = true;
    public $importable = true;

    public $experts;

    // This is used to retrieve related fields from form posts.
    public $additional_column_fields = Array('quote_id', 'quote_name', 'related_product_id');

    public $relationship_fields = Array('related_product_id' => 'related_products');


    // This is the list of fields that are copied over from product template.
    //#9668: removed description from this list..default product desc was overwriting the
    //the description provided by the user in the quote screen.
    public $template_fields = array(
        'mft_part_num',
        'vendor_part_num',
        'website',
        'tax_class',
        'manufacturer_id',
        'type_id',
        'category_id',
        'team_id',
        'weight',
        'support_name',
        'support_term',
        'support_description',
        'support_contact'
    );

    /**
     * This is a depreciated method, please start using __construct() as this method will be removed in a future version
     *
     * @see __construct
     * @deprecated
     */
    public function Product()
    {
        $this->__construct();
    }

    public function __construct()
    {

        parent::__construct();

        $this->team_id = 1; // make the item globally accessible

        $currency = BeanFactory::getBean('Currencies');
        $this->default_currency_symbol = $currency->getDefaultCurrencySymbol();


    }


    public function get_summary_text()
    {
        return "$this->name";
    }


    /** Returns a list of the associated products
     * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
     * All Rights Reserved.
     * Contributor(s): ______________________________________..
     */
    public function create_new_list_query(
        $order_by,
        $where,
        $filter = array(),
        $params = array(),
        $show_deleted = 0,
        $join_type = '',
        $return_array = false,
        $parentbean = null,
        $singleSelect = false
    ) {
        if (!empty($filter) && (isset($filter['discount_amount']) || isset($filter['deal_calc']))) {
            $filter['discount_select'] = 1;
            $filter['deal_calc_usdollar'] = 1;
            $filter['discount_amount_usdollar'] = 1;
        }
        $ret_array = parent::create_new_list_query(
            $order_by,
            $where,
            $filter,
            $params,
            $show_deleted,
            $join_type,
            $return_array,
            $parentbean,
            $singleSelect
        );
        $ret_array['from'] = $ret_array['from'] . " LEFT JOIN contacts on contacts.id = products.contact_id";

        //If return_array is set to true, return as an Array
        if ($return_array) {
            //Add clause to remove opportunity related products
            $ret_array['where'] = $ret_array['where'] .
                " AND (products.opportunity_id is not null OR products.opportunity_id <> '')";
            return $ret_array;
        }

        return str_replace(
            'where products.deleted=0',
            "where products.deleted=0 AND (products.opportunity_id is not null OR products.opportunity_id <> '')",
            $ret_array
        );
    }


    public function create_export_query(&$order_by, &$where, $relate_link_join = '')
    {
        $custom_join = $this->custom_fields->getJOIN(true, true, $where);
        if ($custom_join) {
            $custom_join['join'] .= $relate_link_join;
        }
        $query = "SELECT $this->table_name.* ";
        if ($custom_join) {
            $query .= $custom_join['select'];
        }
        $query .= " FROM $this->table_name ";

        if ($custom_join) {
            $query .= $custom_join['join'];
        }

        $where_auto = "$this->table_name.deleted=0 AND
            ($this->table_name.opportunity_id is not null OR $this->table_name.opportunity_id <> '')";

        if ($where != "") {
            $query .= "where ($where) AND " . $where_auto;
        } else {
            $query .= "where " . $where_auto;
        }

        if (!empty($order_by)) {
            $query .= " ORDER BY $order_by";
        }

        return $query;
    }


    public function fill_in_additional_list_fields()
    {
        $this->fill_in_additional_detail_fields();
    }

    public function fill_in_additional_detail_fields()
    {
        parent::fill_in_additional_detail_fields();


        $currency = BeanFactory::getBean('Currencies', $this->currency_id);
        $this->currency_symbol = $currency->symbol;
        $this->currency_name = $currency->name;
        if ($currency->id != $this->currency_id || $currency->deleted == 1) {
            $this->cost_price = $this->cost_usdollar;
            $this->discount_price = $this->discount_usdollar;
            $this->list_price = $this->list_usdollar;
            $this->deal_calc = $this->deal_calc_usdollar;
            if (!(isset($this->discount_select) && $this->discount_select)) {
                $this->discount_amount = $this->discount_amount_usdollar;
            }
            $this->currency_id = $currency->id;
        }

        if (isset($this->discount_select) && $this->discount_select) {
            $this->discount_amount = format_number($this->discount_amount, 2);
        }

        $this->get_account();
        $this->get_contact();
        $this->get_quote();
        $this->get_manufacturer();
        $this->get_type();
        $this->get_category();
    }


    /** Returns a list of the associated opportunities
     * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
     * All Rights Reserved..
     * Contributor(s): ______________________________________..
     */
    public function get_quote()
    {
        $query = "SELECT q.id, q.name, q.assigned_user_id from quotes q, $this->table_name obj where q.id = obj.quote_id and obj.id = '$this->id' and obj.deleted=0 and q.deleted=0";
        $result = $this->db->query($query, true, " Error filling in additional detail fields: ");

        // Get the id and the name.
        $row = $this->db->fetchByAssoc($result);

        if ($row != null) {
            $this->quote_name_owner = $row['assigned_user_id'];
            $this->quote_name_mod = 'Quotes';
            $this->quote_name = $row['name'];
            $this->quote_id = $row['id'];
        } else {
            $this->quote_name = '';
            $this->quote_name_owner = '';
            $this->quote_name_mod = '';
            $this->quote_id = '';
        }
    }

    /** Returns a list of the associated opportunities
     * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
     * All Rights Reserved..
     * Contributor(s): ______________________________________..
     */
    public function get_account()
    {
        $query = "SELECT a1.id, a1.name, a1.assigned_user_id from accounts a1, $this->table_name obj where a1.id = obj.account_id and obj.id = '$this->id' and obj.deleted=0 and a1.deleted=0";
        $result = $this->db->query($query, true, " Error filling in additional detail fields: ");

        // Get the id and the name.
        $row = $this->db->fetchByAssoc($result);

        if ($row != null) {
            $this->account_name = $row['name'];
            $this->account_id = $row['id'];
            $this->account_name_owner = $row['assigned_user_id'];
            $this->account_name_mod = 'Accounts';
        } else {
            $this->account_name = '';
            $this->account_id = '';
            $this->account_name_owner = '';
            $this->account_name_mod = '';
        }
    }

    /** Returns a list of the associated opportunities
     * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
     * All Rights Reserved..
     * Contributor(s): ______________________________________..
     */
    public function get_contact()
    {
        $query = "SELECT c1.id, c1.first_name, c1.last_name, c1.assigned_user_id from contacts  c1, $this->table_name p1 where c1.id = p1.contact_id and p1.id = '$this->id' and p1.deleted=0 and c1.deleted=0";
        $result = $this->db->query($query, true, " Error filling in additional detail fields: ");

        global $locale;

        // Get the id and the name.
        $row = $this->db->fetchByAssoc($result);

        if ($row != null) {
            $this->contact_name = $locale->getLocaleFormattedName($row['first_name'], $row['last_name']);
            $this->contact_id = $row['id'];
            $this->contact_name_owner = $row['assigned_user_id'];
            $this->contact_name_mod = 'Contacts';
        } else {
            $this->contact_name = '';
            $this->contact_id = '';
            $this->contact_name_owner = '';
            $this->contact_name_mod = '';
        }
    }

    /** Returns a list of the associated opportunities
     * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
     * All Rights Reserved..
     * Contributor(s): ______________________________________..
     */
    public function get_manufacturer()
    {
        $query = "SELECT m1.name from $this->rel_manufacturers m1, $this->table_name p1 where m1.id = p1.manufacturer_id and p1.id = '$this->id' and p1.deleted=0 and m1.deleted=0";
        $result = $this->db->query($query, true, " Error filling in additional detail fields: ");

        // Get the id and the name.
        $row = $this->db->fetchByAssoc($result);

        if ($row != null) {
            $this->manufacturer_name = $row['name'];
        } else {
            $this->manufacturer_name = '';
        }
    }

    /** Returns a list of the associated opportunities
     * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
     * All Rights Reserved..
     * Contributor(s): ______________________________________..
     */
    public function get_type()
    {
        $query = "SELECT t1.name from $this->rel_types t1, $this->table_name p1 where t1.id = p1.type_id and p1.id = '$this->id' and p1.deleted=0 and t1.deleted=0";
        $result = $this->db->query($query, true, " Error filling in additional detail fields: ");

        // Get the id and the name.
        $row = $this->db->fetchByAssoc($result);

        if ($row != null) {
            $this->type_name = $row['name'];
        } else {
            $this->type_name = '';
        }
    }

    /** Returns a list of the associated opportunities
     * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
     * All Rights Reserved..
     * Contributor(s): ______________________________________..
     */
    public function get_category()
    {
        $query = "SELECT t1.name from $this->rel_categories t1, $this->table_name p1 where t1.id = p1.category_id and p1.id = '$this->id' and p1.deleted=0 and t1.deleted=0";
        $result = $this->db->query($query, true, " Error filling in additional detail fields: ");

        // Get the id and the name.
        $row = $this->db->fetchByAssoc($result);

        if ($row != null) {
            $this->category_name = $row['name'];
        } else {
            $this->category_name = '';
        }
    }

    /**
     * get_list_view_data
     * Returns a list view of the associated Products.  This view is used in the Subpanel
     * listings.
     *
     */
    public function get_list_view_data()
    {
        global $current_language, $app_strings, $app_list_strings, $current_user, $timedate, $locale;
        $product_mod_strings = return_module_language($current_language, "Products");
        require_once('modules/Products/config.php');
        //$this->format_all_fields();

        if ($this->date_purchased == '0000-00-00') {
            $the_date_purchased = '';
        } else {
            $the_date_purchased = $this->date_purchased;
            $db_date_purchased = $timedate->to_db_date($this->date_purchased, false);

        }
        $the_date_support_expires = $this->date_support_expires;
        $db_date_support_expires = $timedate->to_db_date($this->date_support_expires, false);

        $expired = $timedate->asDbDate($timedate->getNow()->get($support_expired));
        $coming_due = $timedate->asDbDate($timedate->getNow()->get($support_coming_due));

        /**
         * Convert price related data into users preferred currency
         * for display in subpanels
         */
        // See if a user has a preferred currency
        if ($current_user->getPreference('currency')) {
            // Retrieve the product currency
            $currency = BeanFactory::getBean('Currencies', $this->currency_id);
            // Retrieve the users currency
            $userCurrency = BeanFactory::getBean('Currencies', $current_user->getPreference('currency'));
            // If the product currency and the user default currency are different, convert to users currency
            if ($userCurrency->id != $currency->id) {
                $this->cost_price = $userCurrency->convertFromDollar($currency->convertToDollar($this->cost_price));
                $this->discount_price = $userCurrency->convertFromDollar(
                    $currency->convertToDollar($this->discount_price)
                );
                $this->list_price = $userCurrency->convertFromDollar($currency->convertToDollar($this->list_price));
                $this->deal_calc = $userCurrency->convertFromDollar($currency->convertToDollar($this->deal_calc));

                if (!(isset($this->discount_select) && $this->discount_select)) {
                    $this->discount_amount = $userCurrency->convertFromDollar(
                        $currency->convertToDollar($this->discount_amount)
                    );
                }

                $this->currency_symbol = $userCurrency->symbol;
                $this->currency_name = $userCurrency->name;
                $this->currency_id = $userCurrency->id;
            }
        }

        if (!empty($the_date_support_expires) && $db_date_support_expires < $expired) {
            $the_date_support_expires = "<strong><font color='$support_expired_color'>$the_date_support_expires</font></strong>";
        }
        if (!empty($the_date_support_expires) && $db_date_support_expires < $coming_due) {
            $the_date_support_expires = "<strong><font color='$support_coming_due_color'>$the_date_support_expires</font></strong>";
        }
        if ($this->date_support_expires == '0000-00-00') {
            $the_date_support_expires = '';
        }

        $temp_array = $this->get_list_view_array();
        $temp_array['NAME'] = (($this->name == "") ? "<em>blank</em>" : $this->name);
        if (!empty($this->status)) {
            $temp_array['STATUS'] = $app_list_strings['product_status_dom'][$this->status];
        }
        $temp_array['ENCODED_NAME'] = $this->name;
        $temp_array['DATE_SUPPORT_EXPIRES'] = $the_date_support_expires;
        $temp_array['DATE_PURCHASED'] = $the_date_purchased;


        $params['currency_id'] = $this->currency_id;
        $temp_array['LIST_PRICE'] = $this->list_price;
        $temp_array['DISCOUNT_PRICE'] = $this->discount_price;
        $temp_array['COST_PRICE'] = $this->cost_price;
        if (isset($this->discount_select) && $this->discount_select) {
            $temp_array['DISCOUNT_AMOUNT'] = $this->discount_amount . "%";
        } else {
            $temp_array['DISCOUNT_AMOUNT'] = $this->discount_amount;
        }

        $this->get_account();
        $this->get_contact();

        $temp_array['ACCOUNT_NAME'] = empty($this->account_name) ? '' : $this->account_name;
        $temp_array['CONTACT_NAME'] = empty($this->contact_name) ? '' : $this->contact_name;
        return $temp_array;
    }

    /**
    builds a generic search based on the query string using or
    do not include any $this-> because this is called on without having the class instantiated
     */
    public function build_generic_where_clause($the_query_string)
    {
        $where_clauses = Array();
        $the_query_string = $GLOBALS['db']->quote($the_query_string);
        array_push($where_clauses, "name like '$the_query_string%'");
        if (is_numeric($the_query_string)) {
            array_push($where_clauses, "mft_part_num like '%$the_query_string%'");
            array_push($where_clauses, "vendor_part_num like '%$the_query_string%'");
        }

        $the_where = "";
        foreach ($where_clauses as $clause) {
            if ($the_where != "") {
                $the_where .= " or ";
            }
            $the_where .= $clause;
        }


        return $the_where;
    }

    public function save($check_notify = false)
    {

        //If an opportunity_id value is provided, lookup the Account information (if available)
        if (!empty($this->opportunity_id)) {
            $this->setAccountIdForOpportunity($this->opportunity_id);
        }

        $currency = BeanFactory::getBean('Currencies', $this->currency_id);
        // RPS - begin - decimals cant be null in sql server
        if ($this->cost_price == '') {
            $this->cost_price = '0';
        }
        if ($this->discount_price == '') {
            $this->discount_price = '0';
        }
        if ($this->list_price == '') {
            $this->list_price = '0';
        }
        if ($this->weight == '') {
            $this->weight = '0';
        }
        if ($this->book_value == '') {
            $this->book_value = '0';
        }
        if ($this->discount_amount == '') {
            $this->discount_amount = '0';
        }
        if ($this->deal_calc == '') {
            $this->deal_calc = '0';
        }

        //US DOLLAR
        if (isset($this->discount_price) && (!empty($this->discount_price) || $this->discount_price == '0')) {
            $this->discount_usdollar = $currency->convertToDollar($this->discount_price);
        }
        if (isset($this->list_price) && (!empty($this->list_price) || $this->list_price == '0')) {
            $this->list_usdollar = $currency->convertToDollar($this->list_price);
        }
        if (isset($this->cost_price) && (!empty($this->cost_price) || $this->cost_price == '0')) {
            $this->cost_usdollar = $currency->convertToDollar($this->cost_price);
        }
        if (isset($this->book_value) && (!empty($this->book_value) || $this->book_value == '0')) {
            $this->book_value_usdollar = $currency->convertToDollar($this->book_value);
        }
        if (isset($this->deal_calc) && (!empty($this->deal_calc) || $this->deal_calc == '0')) {
            $this->deal_calc_usdollar = $currency->convertToDollar($this->deal_calc);
        }
        if (isset($this->discount_amount) && (!empty($this->discount_amount) || $this->discount_amount == '0')) {
            if (isset($this->discount_select) && $this->discount_select) {
                $this->discount_amount_usdollar = $this->discount_amount;
            } else {
                $this->discount_amount_usdollar = $currency->convertToDollar($this->discount_amount);
            }
        }

        $this->handleSalesStatus();
        $this->convertDateClosedToTimestamp();

        $id = parent::save($check_notify);
        //BEGIN SUGARCRM flav=ent ONLY
        // this only happens when ent is built out
        $this->saveProductWorksheet();
        //END SUGARCRM flav=ent ONLY

        // We need to update the associated product bundle and quote totals that might be impacted by this product.
        if (isset($id)) {
            $tax_rate = 0.00;
            $query = "select * from quotes INNER JOIN taxrates on quotes.taxrate_id=taxrates.id where quotes.id='" . $this->quote_id . "' and quotes.deleted=0 and taxrates.deleted=0";
            $result = $this->db->query($query);
            if ($row = $this->db->fetchByAssoc($result)) {
                $tax_rate = $row['value'] / 100;
                $shipping_usdollar = $row['shipping_usdollar'];
            }
            $query = "select product_bundles.id as bundle_id from product_bundle_product" .
                " INNER JOIN product_bundles on product_bundles.id=product_bundle_product.bundle_id" .
                " where product_bundle_product.deleted=0 AND product_bundle_product.product_id='" . $id . "' AND product_bundles.deleted=0";
            $result = $this->db->query($query);
            if ($row = $this->db->fetchByAssoc($result)) {
                $bundle_id = $row['bundle_id'];
                $query = "select shipping_usdollar from product_bundles where id='" . $bundle_id . "' and deleted=0";
                $result = $this->db->query($query);
                if ($row = $this->db->fetchByAssoc($result)) {
                    $shipping_usdollar = $row['shipping_usdollar'];
                }
                $query = "select * from product_bundle_product where bundle_id='" . $bundle_id . "' and deleted=0";
                $result = $this->db->query($query);
                $new_sub_usdollar = 0.00;
                $deal_tot_usdollar = 0.00;
                $deal_tot = 0.00;
                $new_sub = 0.00;
                $subtotal_usdollar = 0.00;
                $tax_usdollar = 0.00;
                $total_usdollar = 0.00;
                if ($row = $this->db->fetchByAssoc($result)) {
                    while ($row != null) {
                        $product = BeanFactory::getBean('Products');
                        $product->id = $row['product_id'];
                        $product->retrieve();
                        $subtotal_usdollar += $product->discount_usdollar * $product->quantity;

                        if (isset($this->discount_select) && $this->discount_select) {
                            $deal_tot_usdollar += ($product->discount_amount / 100) * $product->discount_usdollar * $product->quantity;
                        } else {
                            $deal_tot_usdollar += $product->discount_amount;
                        }
                        $new_sub_usdollar = $subtotal_usdollar - $deal_tot_usdollar;
                        if ($product->tax_class == 'Taxable') {
                            $tax_usdollar += ($product->discount_usdollar * $product->quantity) * $tax_rate;

                        }
                        $row = $this->db->fetchByAssoc($result);
                    }
                    $total_usdollar += $new_sub_usdollar + $tax_usdollar + $shipping_usdollar;
                    $total = $currency->convertFromDollar($total_usdollar);
                    $subtotal = $currency->convertFromDollar($subtotal_usdollar);
                    $new_sub = $currency->convertFromDollar($new_sub_usdollar);
                    $tax = $currency->convertFromDollar($tax_usdollar);
                    $deal_tot = $currency->convertFromDollar($deal_tot_usdollar);
                    $updateQuery = "update product_bundles set tax=" . $tax . ",tax_usdollar=" . $tax_usdollar . ",total=" . $total . ",deal_tot_usdollar=" . $deal_tot_usdollar . ",deal_tot=" . $deal_tot . ",total_usdollar=" . $total_usdollar .
                        ",new_sub=" . $new_sub . ",new_sub_usdollar=" . $new_sub_usdollar . ",subtotal=" . $subtotal .
                        ",subtotal_usdollar=" . $subtotal_usdollar . " where id='" . $bundle_id . "'";
                    $result = $this->db->query($updateQuery);
                    //Update the Grand Total for the Quote
                    $subtotal_usdollar = 0.00;
                    $tax_usdollar = 0.00;
                    $total_usdollar = 0.00;
                    $shipping_usdollar = 0.00;
                    $new_sub_usdollar = 0.00;
                    $query = "select sum(product_bundles.total_usdollar) as total_usdollar,sum(product_bundles.subtotal_usdollar) as subtotal_usdollar,sum(product_bundles.new_sub_usdollar) as new_sub_usdollar,sum(product_bundles.deal_tot_usdollar) as deal_tot_usdollar,sum(product_bundles.tax_usdollar) as tax_usdollar," .
                        "sum(product_bundles.shipping_usdollar) as shipping_usdollar from product_bundle_quote INNER JOIN product_bundles on " .
                        "product_bundles.id=product_bundle_quote.bundle_id where product_bundle_quote.quote_id='" . $this->quote_id . "' " .
                        "and product_bundle_quote.deleted=0 and product_bundles.deleted=0";
                    $result = $this->db->query($query);
                    if ($row = $this->db->fetchByAssoc($result)) {
                        /*
                        while ($row != null) {
                            $subtotal_usdollar += $row['subtotal_usdollar'];
                            $tax_usdollar += $row['tax_usdollar'];
                            $shipping_usdollar += $row['shipping_usdollar'];
                            $row =  $this->db->fetchByAssoc($result);
                        }*/
                        $total_usdollar += $row['new_sub_usdollar'] + $row['tax_usdollar'] + $row['shipping_usdollar'];
                        $total = $currency->convertFromDollar($total_usdollar);
                        $subtotal = $currency->convertFromDollar($row['subtotal_usdollar']);
                        $deal_tot_usdollar = $row['deal_tot_usdollar'];
                        $deal_tot = $currency->convertFromDollar($deal_tot_usdollar);
                        $new_sub_usdollar = $row['new_sub_usdollar'];
                        $new_sub = $currency->convertFromDollar($new_sub_usdollar);
                        $tax = $currency->convertFromDollar($row['tax_usdollar']);
                        $updateQuery = "update quotes set tax=" . $tax . ",tax_usdollar=" . $tax_usdollar . ",total=" . $total . ",total_usdollar=" . $total_usdollar . ",deal_tot=" . $deal_tot . ",deal_tot_usdollar=" . $deal_tot_usdollar . ",new_sub=" . $new_sub . ",new_sub_usdollar=" . $new_sub_usdollar . ",subtotal=" . $subtotal .
                            ",subtotal_usdollar=" . $subtotal_usdollar . " where id='" . $this->quote_id . "'";
                        $result = $this->db->query($updateQuery);
                    }

                }
            }
        }
        return $id;
    }

    /**
     * Handle Converting DateClosed to a Timestamp
     */
    protected function convertDateClosedToTimestamp()
    {
        $timedate = TimeDate::getInstance();
        if ($timedate->check_matching_format($this->date_closed, TimeDate::DB_DATE_FORMAT)) {
            $date_close_db = $this->date_closed;
        } else {
            $date_close_db = $timedate->to_db_date($this->date_closed);
        }

        if (!empty($date_close_db)) {
            $date_close_datetime = $timedate->fromDbDate($date_close_db);
            $this->date_closed_timestamp = $date_close_datetime->getTimestamp();
        }
    }

    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * Save the updated product to the worksheet, this will create one if one does not exist
     * this will also update one if a draft version exists
     *
     * @return bool         True if the worksheet was saved/updated, false otherwise
     */
    protected function saveProductWorksheet()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');
        if ($settings['is_setup']) {
            // save the a draft of each product
            /* @var $worksheet ForecastWorksheet */
            $worksheet = BeanFactory::getBean('ForecastWorksheets');
            $worksheet->saveRelatedProduct($this);
            return true;
        }

        return false;
    }
    //END SUGARCRM flav=ent ONLY

    /**
     * Sets the account_id value for instance given an opportunityId argument of the Opportunity id
     *
     * @param $opportunityId String value of the Opportunity id
     * @return bool true if account_id was set; false otherwise
     */
    protected function setAccountIdForOpportunity($opportunityId)
    {
        $opp = BeanFactory::getBean('Opportunities', $opportunityId);
        if ($opp->load_relationship('accounts')) {
            $accounts = $opp->accounts->query(array('where' => 'accounts.deleted=0'));
            foreach ($accounts['rows'] as $accountId => $value) {
                $this->account_id = $accountId;
                return true;
            }
        }
        return false;
    }

    /**
     * Code to make sure that the Sales Status field is mapped correctly with the Sales Stage field
     */
    protected function handleSalesStatus()
    {
        // in this class we use the values from the Opportunity module constants as they are directly mapped 1-to-1 with
        // products

        // only run this when the sales_status doesn't change and the sales_stage does
        if (($this->fetched_row['sales_status'] == $this->sales_status)
            && $this->fetched_row['sales_stage'] != $this->sales_stage
        ) {
            // handle closed lost and closed won
            if ($this->sales_stage == Opportunity::STAGE_CLOSED_LOST
                || $this->sales_stage == Opportunity::STAGE_CLOSED_WON
            ) {
                $this->sales_status = $this->sales_stage;
            } else {
                // move it to in progress
                $this->sales_status = Opportunity::STATUS_IN_PROGRESS;
            }
        }

        if (empty($this->fetched_row['quote_id']) && !empty($this->quote_id)) {
            $this->sales_status = Product::STATUS_CONVERTED_TO_QUOTE;
        }

        // if we have a new bean, set the sales_status to be 'New'
        if (empty($this->id) || $this->new_with_id == true) {
            // we have a new record set the sales_status to new;
            $this->sales_status = Opportunity::STATUS_NEW;
        }
    }

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }

    public function listviewACLHelper()
    {
        $array_assign = parent::listviewACLHelper();

        $is_owner = false;
        if (!empty($this->contact_name)) {

            if (!empty($this->contact_name_owner)) {
                global $current_user;
                $is_owner = $current_user->id == $this->contact_name_owner;
            }
        }
        if (ACLController::checkAccess('Contacts', 'view', $is_owner)) {
            $array_assign['CONTACT'] = 'a';
        } else {
            $array_assign['CONTACT'] = 'span';
        }
        $is_owner = false;
        if (!empty($this->account_name)) {

            if (!empty($this->account_name_owner)) {
                global $current_user;
                $is_owner = $current_user->id == $this->account_name_owner;
            }
        }
        if (ACLController::checkAccess('Accounts', 'view', $is_owner)) {
            $array_assign['ACCOUNT'] = 'a';
        } else {
            $array_assign['ACCOUNT'] = 'span';
        }
        $is_owner = false;
        if (!empty($this->quote_name)) {

            if (!empty($this->quote_name_owner)) {
                global $current_user;
                $is_owner = $current_user->id == $this->quote_name_owner;
            }
        }
        if (ACLController::checkAccess('Quotes', 'view', $is_owner)) {
            $array_assign['QUOTE'] = 'a';
        } else {
            $array_assign['QUOTE'] = 'span';
        }

        return $array_assign;
    }
}
