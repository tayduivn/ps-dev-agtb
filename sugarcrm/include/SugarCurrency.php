<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
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

/**
 * SugarCurrency
 *
 * A class for manipulating currencies and currency amounts
 *
 * @author Monte Ohrt <mohrt@sugarcrm.com>
 */
class SugarCurrency
{

    /**
     * convert a currency from one to another
     *
     * @access public
     * @param  float  $amount
     * @param  string $from_id source currency_id
     * @param  string $to_id target currency_id
     * @return float   converted amount
     */
    public static function convertAmount( $amount, $from_id, $to_id ) {
        $currency1 = BeanFactory::getBean('Currencies');
        $currency1->retrieve($from_id);
        $currency2 = BeanFactory::getBean('Currencies');
        $currency2->retrieve($to_id);
        // NOTE: we always calculate in maximum precision, which the database defines to 6
        // formatting to two decimals is done with formatting functions
        return round($amount * $currency1->conversion_rate / $currency2->conversion_rate, 6);
    }

    /**
     * convenience function: convert a currency to base currency
     *
     * @access public
     * @param  float  $amount
     * @param  string $from_id source currency_id
     * @return float   converted amount
     */
    public static function convertAmountToBase( $amount, $from_id ) {
        return self::convertAmount($amount, $from_id, '-99');
    }

    /**
     * convenience function: convert a currency from base currency
     *
     * @access public
     * @param  float  $amount
     * @param  string $from_id source currency_id
     * @return float   converted amount
     */
    public static function convertAmountFromBase( $amount, $to_id ) {
        return self::convertAmount($amount, '-99', $to_id);
    }

    /**
     * format a currency amount with symbol and defined formatting
     *
     * @access public
     * @param  float  $amount
     * @param  string $currency_id
     * @param  int    $decimal_precision Optional the number of decimal places to use
     * @param  string $decimal_separator Optional the string to use as decimal separator
     * @param  string $number_grouping_separator Optional the string to use for thousands separator
     * @param  string $symbol_separator Optional string between symbol and amount
     * @return string  formatted amount
     */
    public static function formatAmount(
        $amount,
        $currency_id,
        $decimal_precision = 2,
        $decimal_separator = '.',
        $number_grouping_separator = ',',
        $symbol_separator = ''
    ) {
        $currency = BeanFactory::getBean('Currencies');
        $currency->retrieve($currency_id);

        return $currency->symbol . $symbol_separator . number_format($amount, $decimal_precision, $decimal_separator, $number_grouping_separator);
    }

    /**
     * format a currency amount with symbol and user defined formatting
     *
     * @access public
     * @param  float  $amount
     * @param  string $currency_id
     * @param  string $symbol_separator Optional string between symbol and amount
     * @return string  formatted amount
     */
    public static function formatAmountUserLocale(
        $amount,
        $currency_id,
        $symbol_separator = ''
    ) {
        global $locale;
        // get user defined preferences
        $decimal_precision = $locale->getPrecision();
        $decimal_separator = $locale->getDecimalSeparator();
        $number_grouping_separator = $locale->getNumberGroupingSeparator();

        return self::formatAmount($amount, $currency_id, $decimal_precision, $decimal_separator, $number_grouping_separator, $symbol_separator);
    }

    /**
     * get system base currency object
     *
     * @access public
     * @return object  currency object
     */
    public static function getBaseCurrency( ) {
        // the base currency has a hard-coded currency_id of -99
        $currency = BeanFactory::getBean('Currencies');
        $currency->retrieve('-99');
        return $currency;
    }

    /**
     * get a currency object by currency_id
     *
     * @access public
     * @param  string $currency_id
     * @return object  currency object
     */
    public static function getCurrencyByID( $currency_id = null ) {
        $currency = BeanFactory::getBean('Currencies');
        $currency->retrieve($currency_id);
        return $currency;
    }

    /**
     * get a currency object by ISO
     *
     * @access public
     * @param  string $ISO ISO4217 value
     * @return object  currency object
     */
    public static function getCurrencyByISO( $ISO ) {
        $currency = BeanFactory::getBean('Currencies');
        $currency_id = $currency->retrieveIDByISO($ISO);
        $currency->retrieve($currency_id);
        return $currency;
    }

    /**
     * get a currency object by user preferences
     *
     * @access public
     * @param  object $user Optional the user object
     * @return object  currency object
     * @outputBuffering disabled
     */
    public static function getUserLocaleCurrency( $user = null ) {
        $currency = BeanFactory::getBean('Currencies');
        if(empty($user))
        {
            global $current_user;
            $user = $current_user;
        }
        $currency_id = $user->getPreference('currency');
        $currency->retrieve($currency_id);
        return $currency;
    }


}
