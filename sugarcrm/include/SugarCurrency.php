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
     * get a currency object
     *
     * @access protected
     * @param  string $currencyId Optional if empty, base currency is returned
     * @return object   currency object
     */
    protected static function _getCurrency( $currencyId = null ) {
        if(empty($currencyId)) {
            $currencyId = '-99';
        }
        $currency = BeanFactory::getBean('Currencies', $currencyId);
        //$currency->retrieve($currencyId);
        return $currency;
    }

    /**
     * convert a currency from one to another
     *
     * @access public
     * @param  float  $amount
     * @param  string $fromId source currency_id
     * @param  string $toId target currency_id
     * @return float   converted amount
     */
    public static function convertAmount( $amount, $fromId, $toId ) {
        $currency1 = self::_getCurrency($fromId);
        $currency2 = self::_getCurrency($toId);
        // NOTE: we always calculate in maximum precision, which the database defines to 6
        // formatting to two decimals is done with formatting functions
        return round($amount * $currency1->conversion_rate / $currency2->conversion_rate, 6);
    }

    /**
     * convenience function: convert a currency to base currency
     *
     * @access public
     * @param  float  $amount
     * @param  string $fromId source currency_id
     * @return float   converted amount
     */
    public static function convertAmountToBase( $amount, $fromId ) {
        return self::convertAmount($amount, $fromId, '-99');
    }

    /**
     * convenience function: convert a currency from base currency
     *
     * @access public
     * @param  float  $amount
     * @param  string $toId source currency_id
     * @return float   converted amount
     */
    public static function convertAmountFromBase( $amount, $toId ) {
        return self::convertAmount($amount, '-99', $toId);
    }

    /**
     * format a currency amount with symbol and defined formatting
     *
     * @access public
     * @param  float  $amount
     * @param  string $currencyId
     * @param  int    $decimalPrecision Optional the number of decimal places to use
     * @param  string $decimalSeparator Optional the string to use as decimal separator
     * @param  string $numberGroupingSeparator Optional the string to use for thousands separator
     * @param  bool   $showSymbol Optional show symbol along with currency default true
     * @param  string $symbolSeparator Optional string between symbol and amount
     * @return string  formatted amount
     */
    public static function formatAmount(
        $amount,
        $currencyId,
        $decimalPrecision = 2,
        $decimalSeparator = '.',
        $numberGroupingSeparator = ',',
        $showSymbol = true,
        $symbolSeparator = ''
    ) {
        $currency = self::_getCurrency($currencyId);

        return $showSymbol
            ? $currency->symbol . $symbolSeparator . number_format($amount, $decimalPrecision, $decimalSeparator, $numberGroupingSeparator)
            : number_format($amount, $decimalPrecision, $decimalSeparator, $numberGroupingSeparator);
    }

    /**
     * format a currency amount with symbol and user defined formatting
     *
     * @access public
     * @param  float  $amount
     * @param  string $currencyId
     * @param  bool   $showSymbol Optional show symbol along with currency default true
     * @param  string $symbolSeparator Optional string between symbol and amount
     * @return string  formatted amount
     */
    public static function formatAmountUserLocale(
        $amount,
        $currencyId,
        $showSymbol=true,
        $symbolSeparator = ''
    ) {
        global $locale;
        // get user defined preferences
        $decimalPrecision = $locale->getPrecision();
        $decimalSeparator = $locale->getDecimalSeparator();
        $numberGroupingSeparator = $locale->getNumberGroupingSeparator();

        return self::formatAmount($amount, $currencyId, $decimalPrecision, $decimalSeparator, $numberGroupingSeparator, $showSymbol, $symbolSeparator);
    }

    /**
     * get system base currency object
     *
     * @access public
     * @return object  currency object
     */
    public static function getBaseCurrency( ) {
        // the base currency has a hard-coded currency id of -99
        return self::_getCurrency('-99');
    }

    /**
     * get a currency object by currency_id
     *
     * @access public
     * @param  string $currencyId
     * @return object  currency object
     */
    public static function getCurrencyByID( $currencyId = null ) {
        return self::_getCurrency($currencyId);
    }

    /**
     * get a currency object by ISO
     *
     * @access public
     * @param  string $ISO ISO4217 value
     * @return object  currency object
     */
    public static function getCurrencyByISO( $ISO ) {
        $currency = self::_getCurrency();
        $currencyId = $currency->retrieveIDByISO($ISO);
        $currency->retrieve($currencyId);
        return $currency;
    }

    /**
     * Get a currency object by user preferences.  If no user is supplied, attempt to use the global $current_user.
     * If global $current_user object is empty then use default system currency id (-99).
     *
     * @access public
     * @param  object $user Optional the user object
     * @return object  currency object
     */
    public static function getUserLocaleCurrency( $user = null ) {

        if(empty($user))
        {
           global $current_user;
           $user = $current_user;
        }

        $currencyId = !empty($user) ? $user->getPreference('currency') : '-99';
        return self::_getCurrency($currencyId);
    }


}
