<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * OpportunitiesSeedData.php
 *
 * This is a class used for creating OpportunitiesSeedData.  We moved this code out from install/populateSeedData.php so
 * that we may better control and test creating default Opportunities.
 *
 */

class OpportunitiesSeedData {

    static private $_ranges;

    public static $pt_ids = array();

    public static $pc_ids = array();
    /**
     * populateSeedData
     *
     * This is a static function to create Opportunities.
     *
     * @static
     * @param $records Integer value indicating the number of Opportunities to create
     * @param $app_list_strings Array of application language strings
     * @param $accounts Array of Account instances to randomly build data against
     * @param $timeperiods Array of Timeperiods to create timeperiod seed data off of
     * @param $users Array of User instances to randomly build data against
     * @return array Array of Opportunities created
     */
    public static function populateSeedData($records, $app_list_strings, $accounts, $users)
    {
        if (empty($accounts) || empty($app_list_strings) || (!is_int($records) || $records < 1) || empty($users)) {
            return array();
        }

        $opp_config = Opportunity::getSettings(true);
        $usingRLIs = ($opp_config['opps_view_by'] === 'RevenueLineItems');

        // BEGIN SUGARCRM flav = ent ONLY
        if ($usingRLIs) {
            // load up the product template_ids
            $sql = 'SELECT id, list_price, cost_price, discount_price FROM product_templates WHERE deleted = 0';
            /* @var $db DBManager */
            $db = DBManagerFactory::getInstance();
            $results = $db->query($sql);
            while ($row = $db->fetchByAssoc($results)) {
                static::$pt_ids[$row['id']] = $row;
            }
            $sql = 'SELECT id FROM product_categories WHERE deleted = 0';
            $results = $db->query($sql);
            while ($row = $db->fetchByAssoc($results)) {
                static::$pc_ids[] = $row['id'];
            }
        }
        // END SUGARCRM flav = ent ONLY

        $opp_ids = array();
        $timedate = TimeDate::getInstance();

        // get the additional currencies from the table
        /* @var $currency Currency */
        $currency = SugarCurrency::getCurrencyByISO('EUR');

        while ($records-- > 0) {
            $key = array_rand($accounts);
            $account = $accounts[$key];

            /* @var $opp Opportunity */
            $opp = BeanFactory::getBean('Opportunities');

            //Create new opportunities
            $opp->team_id = $account->team_id;
            $opp->team_set_id = $account->team_set_id;

            $opp->assigned_user_id = $account->assigned_user_id;
            $opp->assigned_user_name = $account->assigned_user_name;

            // figure out which one to use
            $base_rate = '1.0';
            $currency_id = '-99';

            if (!$usingRLIs) {
                $seed = rand(1, 15);
                if ($seed % 2 == 0) {
                    $currency_id = $currency->id;
                    $base_rate = $currency->conversion_rate;
                }
            }

            $opp->base_rate = $base_rate;
            $opp->currency_id = $currency_id;

            $opp->name = $account->name;
            $opp->lead_source = array_rand($app_list_strings['lead_source_dom']);
            $opp->sales_stage = array_rand($app_list_strings['sales_stage_dom']);
            $opp->sales_status = 'New';

            if (!$usingRLIs) {
                // If the deal is already done, make the date closed occur in the past.
                $opp->date_closed = ($opp->sales_stage == Opportunity::STAGE_CLOSED_WON || $opp->sales_stage == Opportunity::STAGE_CLOSED_WON)
                    ? self::createPastDate()
                    : self::createDate();
                $opp->date_closed_timestamp = $timedate->fromDbDate($opp->date_closed)->getTimestamp();
            }
            $opp->opportunity_type = array_rand($app_list_strings['opportunity_type_dom']);
            $amount = rand(1000, 7500);
            $opp->amount = $amount;
            $opp->probability = $app_list_strings['sales_probability_dom'][$opp->sales_stage];

            //Setup forecast seed data
            $opp->best_case = $opp->amount;
            $opp->worst_case = $opp->amount;
            $opp->commit_stage = $opp->probability >= 70 ? 'include' : 'exclude';

            $opp->id = create_guid();
            $opp->new_with_id = true;
            // set the acccount on the opps, just for saving to the worksheet table
            $opp->account_id = $account->id;
            $opp->account_name = $account->name;
            $opp->save();

            // BEGIN SUGARCRM flav=ent ONLY
            if ($usingRLIs) {
                static::createRevenueLineItems($opp, rand(3, 5), $app_list_strings);
                // unset the relationship and then resave the opp
                unset($opp->revenuelineitems);
                $opp->save();
            }
            // END SUGARCRM flav=ent ONLY

            // save a draft worksheet for the new forecasts stuff
            /* @var $worksheet ForecastWorksheet */
            $worksheet = BeanFactory::getBean('ForecastWorksheets');
            $worksheet->saveRelatedOpportunity($opp);
            $opp_ids[] = $opp->id;

            //BEGIN SUGARCRM flav=ent ONLY
            if ($usingRLIs) {
                $worksheet->saveOpportunityProducts($opp);
            }
            //END SUGARCRM flav=ent ONLY
        }

        return $opp_ids;
    }

    /**
     * @param Opportunity $opp
     * @param int $rlis_to_create
     * @param array $app_list_strings
     */
    private static function createRevenueLineItems(&$opp, $rlis_to_create, $app_list_strings) {
        $currency_id = $opp->currency_id;
        $base_rate = $opp->base_rate;

        $seed = rand(1, 15);
        if ($seed%2 == 0) {
            $currency = SugarCurrency::getCurrencyByISO('EUR');
            $currency_id = $currency->id;
            $base_rate = $currency->conversion_rate;
        }

        $rlis_created = 0;
        $opp_best_case = 0;
        $opp_worst_case = 0;
        $opp_amount = 0;
        $opp_units = 0;
        $opp->total_revenue_line_items = $rlis_to_create;
        $opp->closed_revenue_line_items = 0;

        SugarBean::enterOperation('saving_related');
        while($rlis_created < $rlis_to_create) {
            $amount = rand(1000, 7500);
            $rand_best_worst = rand(100, 900);
            $doPT = false;
            $quantity = rand(1, 100);
            $cost_price = $amount/2;
            $list_price = $amount;
            $discount_price = ($amount / $quantity);
            if ($rlis_created%2 === 0) {
                $doPT = true;
                $pt_id = array_rand(static::$pt_ids);
                $pt = static::$pt_ids[$pt_id];
                $cost_price = $pt['cost_price'];
                $list_price = $pt['list_price'];
                $discount_price = ($pt['discount_price'] / $quantity);
                $amount = $pt['discount_price'];
                $rand_best_worst = rand(100, $cost_price);
            }
            /* @var $rli RevenueLineItem */
            $rli = BeanFactory::getBean('RevenueLineItems');
            $rli->team_id = $opp->team_id;
            $rli->team_set_id = $opp->team_set_id;
            $rli->name = $opp->name;
            $rli->best_case = $amount+$rand_best_worst;
            $rli->likely_case = $amount;
            $rli->worst_case = $amount-$rand_best_worst;
            $rli->list_price = $list_price;
            $rli->discount_price = $discount_price;
            $rli->cost_price = $cost_price;
            $rli->quantity = $quantity;
            $rli->currency_id = $currency_id;
            $rli->base_rate = $base_rate;
            $rli->discount_amount = '0.00';
            $rli->book_value = '0.00';
            $rli->deal_calc = '0.00';
            $rli->sales_stage = array_rand($app_list_strings['sales_stage_dom']);
            $rli->probability = $app_list_strings['sales_probability_dom'][$rli->sales_stage];
            $isClosed = false;
            if ($rli->sales_stage == Opportunity::STAGE_CLOSED_WON || $rli->sales_stage == Opportunity::STAGE_CLOSED_WON) {
                $isClosed = true;
                $opp->closed_revenue_line_items++;
            }
            $rli->commit_stage = $rli->probability >= 70 ? 'include' : 'exclude';
            $rli->date_closed = ($isClosed) ? self::createPastDate() : self::createDate();
            $rli->assigned_user_id = $opp->assigned_user_id;
            $rli->account_id = $opp->account_id;
            $rli->opportunity_id = $opp->id;
            $rli->lead_source = array_rand($app_list_strings['lead_source_dom']);
            // if this is an even number, assign a product template
            if ($doPT) {
                $rli->product_template_id = $pt_id;
                $rli->discount_amount = rand(100, $rli->cost_price);
                $rli->discount_rate_percent = (($rli->discount_amount/$rli->discount_price)*100);
            } else {
                $rli->discount_amount = 0;
                $rli->discount_rate_percent = 0;
                // if this is not an even number, assign a product category only
                $rli->category_id = static::$pc_ids[array_rand(static::$pc_ids, 1)];
            }
            $rli->total_amount = (($rli->discount_price-$rli->discount_amount)*$rli->quantity);
            $rli->save();

            $opp_units += $rli->quantity;
            $opp_amount += $amount;
            $opp_best_case += $amount+$rand_best_worst;
            $opp_worst_case += $amount-$rand_best_worst;
            $rlis_created++;
        }
        SugarBean::leaveOperation('saving_related');

        $opp->name .= ' - ' . $opp_units . ' Units';
    }

    /**
     * @static creates range of probability for the months
     * @param int $total_months - total count of months
     * @return mixed
     */
    private static function getRanges($total_months = 12)
    {
        if (self::$_ranges === null) {
            self::$_ranges = array();
            for ($i = $total_months; $i >= 0; $i--) {
                // define priority for month,
                self::$_ranges[$total_months-$i] = ( $total_months-$i > 6 )
                    ? self::$_ranges[$total_months-$i] = pow(6, 2) + $i
                    :  self::$_ranges[$total_months-$i] = pow($i, 2) + 1;
                // increase probability for current quarters
                self::$_ranges[$total_months-$i] = $total_months-$i == 0 ? self::$_ranges[$total_months-$i]*2.5 : self::$_ranges[$total_months-$i];
                self::$_ranges[$total_months-$i] = $total_months-$i == 1 ? self::$_ranges[$total_months-$i]*2 : self::$_ranges[$total_months-$i];
                self::$_ranges[$total_months-$i] = $total_months-$i == 2 ? self::$_ranges[$total_months-$i]*1.5 : self::$_ranges[$total_months-$i];
            }
        }
        return self::$_ranges;
    }

    /**
     * @static return month delta as random value using range of probability, 0 - current month, 1 next/previos month...
     * @param int $total_months - total count of months
     * @return int
     */
    public static function getMonthDeltaFromRange($total_months = 12)
    {
        $ranges = self::getRanges($total_months);
        asort($ranges, SORT_NUMERIC);
        $x = mt_rand(1, array_sum($ranges));
        foreach ($ranges as $key => $y) {
            $x -= $y;
            if ($x <= 0) {
                break;
            }
        }
        return $key;
    }

    /**
     * @static generates date
     * @param null $monthDelta - offset from current date in months to create date, 0 - current month, 1 - next month
     * @return string
     */
    public static function createDate($monthDelta = null)
    {
        global $timedate;
        $monthDelta = $monthDelta === null ? self::getMonthDeltaFromRange() : $monthDelta;

        $now = $timedate->getNow(true);
        $now->modify("+$monthDelta month");
        // random day from now to end of month
        $now->setTime(0, 0, 0);
        $day = mt_rand($now->day, $now->days_in_month);
        return $timedate->asDbDate($now->get_day_begin($day));
    }

    /**
     * @static generate past date
     * @param null $monthDelta - offset from current date in months to create past date, 0 - current month, 1 - previous month
     * @return string
     */
    public static function createPastDate($monthDelta = null)
    {
        global $timedate;
        $monthDelta = $monthDelta === null ? self::getMonthDeltaFromRange() : $monthDelta;

        $now = $timedate->getNow(true);
        $now->modify("-$monthDelta month");

        if ($monthDelta == 0 && $now->day == 1) {
            $now->modify("-1 day");
            $day = $now->day;
        } else {
            // random day from start of month to now
            $tmpDay = ($now->day-1 != 0) ? $now->day-1 : 1;
            $day =  mt_rand(1, $tmpDay);
        }
        $now->setTime(0, 0, 0); // always default it to midnight
        return $timedate->asDbDate($now->get_day_begin($day));
    }
}
