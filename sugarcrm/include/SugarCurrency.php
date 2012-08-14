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
 * Sugar Currency container
 *
 * @api
 */
class SugarCurrency
{

    /**
     * @access private
     * @var $currency_obj currency bean object
     */
    private static $_currency;

    /**
     * get a currency object
     *
     * @access private
     * @return object   currency object
     */
    private static function _getCurrencyObj() {
        if(!self::$_currency) {
            self::$_currency = BeanFactory::getBean('Currencies');
        }
        return self::$_currency;
    }

    /**
     * convert a currency from one to another
     *
     * @access public
     * @param  float  $amount
     * @param  string $from_id source currency_id
     * @param  string $to_id target currency_id
     * @param  string $datetime the date/time of exchange rate (default is current)
     * @return float   returns the converted amount
     */
    public static function convertAmount( $amount, $from_id, $to_id, $datetime = null ) {
        // TODO: implement datetime
        $currency = self::_getCurrencyObj();
        $currency1 = $currency->retrieve($from_id);
        $currency2 = $currency->retrieve($to_id);
        return round($amount * $currency1->conversion_rate / $currency2->conversion_rate, 6);
    }

    /**
     * format a currency amount with symbol and user locale
     *
     * @access public
     * @param  float  $amount
     * @param  string $currency_id
     * @param  string $separator the separator used between symbol and amount
     * @return float   returns the converted amount
     */
    public static function formatAmountUserLocale(
        $amount,
        $currency_id,
        $separator = ''
    ) {
        global $locale;
        $currency = self::_getCurrencyObj();
        $currency->retrieve($currency_id);

        // get user defined preferences
        $decimal_separator = $locale->getDecimalSeparator();
        $number_grouping_separator = $locale->getNumberGroupingSeparator();
        $decimal_precision = $locale->getPrecision();

        return $currency->symbol . $separator . number_format($amount, $decimal_precision, $decimal_separator, $number_grouping_separator);
    }

    /**
     * get a currency record by currency_id
     *
     * @access public
     * @param  string   $currency_id
     * @param  datetime $datetime the date/time of exchange rate (default current)
     * @return array     returns the currency record
     */
    public static function getCurrency( $currency_id = null, $datetime = null ) {
        // TODO: implement datetime
        $currency = self::_getCurrencyObj();
        $currency->retrieve($currency_id);
        return $currency;
    }

    /**
     * get a currency record by ISO
     *
     * @access public
     * @param  string   $ISO ISO4217 value
     * @param  datetime $datetime the date/time of exchange rate (default current)
     * @return array     returns the currency record
     */
    public static function getCurrencyByISO( $ISO, $datetime = null ) {
        // TODO: implement datetime
        $currency = self::_getCurrencyObj();
        $currency_id = $currency->retrieveIDByISO($ISO);
        return $currency->retrieve($currency_id);
    }

}
