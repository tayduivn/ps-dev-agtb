<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

// Product is used to store customer information.
class RevenueLineItem extends SugarBean
{
    const STATUS_CONVERTED_TO_QUOTE = 'Converted to Quote';

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
    public $product_type;

    /**
     * @public String      The Current Sales Status
     */
    public $sales_status;

    // These are for related fields
    public $assigned_user_id;
    public $assigned_user_name;
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
    public $opportunity_id;
    public $opportunity_name;
    public $contact_name;
    public $contact_id;
    public $related_product_id;
    public $contracts;
    public $product_index;

    public $table_name = "revenue_line_items";
    public $rel_manufacturers = "manufacturers";
    public $rel_types = "product_types";
    public $rel_products = "product_product";
    public $rel_categories = "product_categories";

    public $object_name = "RevenueLineItem";
    public $module_dir = 'RevenueLineItems';
    public $new_schema = true;
    public $importable = true;

    public $experts;

    // This is used to retrieve related fields from form posts.
    public $additional_column_fields = array('quote_id', 'quote_name', 'related_product_id');
    

    /**
     * Default Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->team_id = 1; // make the item globally accessible

        $currency = BeanFactory::getBean('Currencies');
        $this->default_currency_symbol = $currency->getDefaultCurrencySymbol();
    }

    /**
     * Get summary text
     */
    public function get_summary_text()
    {
        return "$this->name";
    }

    /**
     * {@inheritdoc}
     */
    public function save($check_notify = false)
    {
        //If an opportunity_id value is provided, lookup the Account information (if available)
        if (!empty($this->opportunity_id)) {
            $this->setAccountIdForOpportunity($this->opportunity_id);
        }

        /* @var $currency Currency */
        $currency = BeanFactory::getBean('Currencies', $this->currency_id);
        // RPS - begin - decimals cant be null in sql server

        if (empty($this->best_case)) {
            $this->best_case = $this->likely_case;
        }
        if (empty($this->worst_case)) {
            $this->worst_case = $this->likely_case;
        }
        
        if ($this->quantity == '') {
            $this->quantity = 1;
        }

        // always set the base rate to what the conversion_rate is in the currency
        $this->base_rate = $currency->conversion_rate;

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

        if ($this->probability == '') {
            $this->mapProbabilityFromSalesStage();
        }
        
        $this->convertDateClosedToTimestamp();
        $this->mapFieldsFromProductTemplate();
        $this->mapFieldsFromOpportunity();

        $id = parent::save($check_notify);
        //BEGIN SUGARCRM flav=ent ONLY
        // this only happens when ent is built out
        $this->saveProductWorksheet();
        if ($this->fetched_row != false && $this->opportunity_id != $this->fetched_row["opportunity_id"]) {
            $this->resaveOppForRecalc($this->fetched_row["opportunity_id"]);
        }
        $this->setOpportunitySalesStatus();
        //END SUGARCRM flav=ent ONLY

        return $id;
    }
    
    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * Handle setting the opportunity status
     *
     * This currently uses Dependency Injection for the Opportunity and Administration beans since we can't
     * Override BeanFactory::getBean()  once that is done we can remove the dependency Injection. When called
     * you should not pass and Opportunity and Administration bean in.
     *
     * @param Opportunity $opp
     * @param Administration $admin
     */
    protected function setOpportunitySalesStatus(Opportunity $opp = null, Administration $admin = null)
    {
        if (is_null($admin)) {
            // if $admin is not passed in then load it up
            $admin = BeanFactory::getBean('Administration');
        }
        $settings = $admin->getConfigForModule('Forecasts');

        if ($settings['is_setup'] != 1) {
            // forecasts is not setup, just ignore this
            return;
        }


        if (is_null($opp)) {
            // if $opp is not set, load it up
            $opp = BeanFactory::getBean('Opportunities', $this->opportunity_id);
        }

        /**
         * If the loaded ID does not match what was on the product, just ignore it.
         */
        if ($opp->id != $this->opportunity_id) {
            return;
        }

        // get the closed won and closed lost values
        $closed_won = $settings['sales_stage_won'];
        $closed_lost = $settings['sales_stage_lost'];

        $won_rlis = count(
            $opp->get_linked_beans(
                'revenuelineitems',
                'RevenueLineItems',
                array(),
                0,
                -1,
                0,
                'sales_stage in ("' . join('","', $closed_won) . '")'
            )
        );

        $lost_rlis = count(
            $opp->get_linked_beans(
                'revenuelineitems',
                'RevenueLineItems',
                array(),
                0,
                -1,
                0,
                'sales_stage in ("' . join('","', $closed_lost) . '")'
            )
        );

        $total_rlis = count($opp->get_linked_beans('revenuelineitems', 'RevenueLineItems'));

        if ($total_rlis > ($won_rlis + $lost_rlis) || $total_rlis === 0) {
            // still in progress
            $opp->sales_status = Opportunity::STATUS_IN_PROGRESS;
            $opp->save();
        } else {
            // they are equal so if the total lost == total rlis then it's closed lost,
            // otherwise it's always closed won
            if ($lost_rlis == $total_rlis) {
                $opp->sales_status = Opportunity::STATUS_CLOSED_LOST;
            } else {
                $opp->sales_status = Opportunity::STATUS_CLOSED_WON;
            }
            $opp->save();
        }
    }
    //END SUGARCRM flav=ent ONLY

    /**
     * Override the current SugarBean functionality to make sure that when this method is called that it will also
     * take care of any draft worksheets by rolling-up the data
     *
     * @param string $id            The ID of the record we want to delete
     */
    public function mark_deleted($id)
    {
        $oppId = $this->opportunity_id;
        parent::mark_deleted($id);
           
        //BEGIN SUGARCRM flav=ent ONLY
        // this only happens when ent is built out
        $this->saveProductWorksheet();
        
        //save to trigger related field recalculations for deleted item
        $this->resaveOppForRecalc($oppId);
        //END SUGARCRM flav=ent ONLY
    }
    
    /**
     * Utility to load/save a related Opp when things are deleted/reassigned so calculated fields
     * in Opportunities update with new totals.
     */
    protected function resaveOppForRecalc($oppId)
    {
        if (!empty($oppId)) {
            $opp = BeanFactory::getBean('Opportunities', $oppId);
            // save the opp via the opp status
            $this->setOpportunitySalesStatus($opp);
        }
    }


    /**
     * map fields if opportunity id is set
     */
    protected function mapFieldsFromOpportunity()
    {
        if (!empty($this->opportunity_id) && empty($this->product_type)) {
            $opp = BeanFactory::getBean('Opportunities', $this->opportunity_id);
            $this->product_type = $opp->opportunity_type;
        }
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

    /**
     * Handling mapping the probability from the sales stage.
     */
    protected function mapProbabilityFromSalesStage()
    {
        global $app_list_strings;
        if (!empty($this->sales_stage)) {
            $prob_arr = $app_list_strings['sales_probability_dom'];
            if (isset($prob_arr[$this->sales_stage])) {
                $this->probability = $prob_arr[$this->sales_stage];
            }
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
     * Handle the mapping of the fields from the product template to the product
     */
    protected function mapFieldsFromProductTemplate()
    {
        if (!empty($this->product_template_id)
            && $this->fetched_row['product_template_id'] != $this->product_template_id
        ) {
            /* @var $pt ProductTemplate */
            $pt = BeanFactory::getBean('ProductTemplates', $this->product_template_id);

            $this->category_id = $pt->category_id;
            $this->mft_part_num = $pt->mft_part_num;
            $this->list_price = SugarCurrency::convertAmount($pt->list_price, $pt->currency_id, $this->currency_id);
            $this->cost_price = SugarCurrency::convertAmount($pt->cost_price, $pt->currency_id, $this->currency_id);
            $this->discount_price = SugarCurrency::convertAmount($pt->discount_price, $pt->currency_id, $this->currency_id); // discount_price = unit price on the front end...
            $this->list_usdollar = $pt->list_usdollar;
            $this->cost_usdollar = $pt->cost_usdollar;
            $this->discount_usdollar = $pt->discount_usdollar;
            $this->tax_class = $pt->tax_class;
            $this->weight = $pt->weight;
        }
    }
    /**
     * {@inheritdoc}
     */
    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
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
    
    /**
     * Converts (copies) RLI to Products (QuotedLineItem)
     * @return Product
     */
    public function convertToQuotedLineItem()
    {
        /* @var $product Product */
        $product = BeanFactory::getBean('Products');
        $product->id = create_guid();
        $product->new_with_id = true;
        foreach ($this->getFieldDefinitions() as $field) {
            if ($field['name'] == 'id') {
                // if it's the ID field, associate it back to the product on the relationship field
                $product->revenuelineitem_id = $this->$field['name'];
            } else {
                $product->$field['name'] = $this->$field['name'];
            }
        }
        // use product name if available
        if (!empty($this->product_template_id)) {
            $pt = BeanFactory::getBean('ProductTemplates', $this->product_template_id);
            if (!empty($pt) && !empty($pt->name)) {
                $product->name = $pt->name;
            }
        }
        // we need to set the discount_price (unit_price) to be the likely_case amount
        $product->discount_price = $this->likely_case;
        return $product;
    }
}
