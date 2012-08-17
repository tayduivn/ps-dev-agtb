<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

$beanList = array();
$beanFiles = array();
require('include/modules.php');
$GLOBALS['beanList'] = $beanList;
$GLOBALS['beanFiles'] = $beanFiles;

/**
 * SugarTestCurrencyUtilities
 *
 * utility class for currencies
 *
 * @author Monte Ohrt <mohrt@sugarcrm.com>
 */
class SugarTestCurrencyUtilities
{
    private static $_createdCurrencies = array();

    private function __construct() {}

    /**
     * createCurrency
     *
     * This creates and returns a new currency object
     *
     * @param $name the name of the currency
     * @param $symbol the symbol for the currency
     * @param iso4217 the 3-letter ISO for the currency
     * @param $conversion_rate the conversion rate from the US dollar
     * @param $id the id for the currency record
     * @return new currency object
     */
    public static function createCurrency($name, $symbol, $iso4217, $conversion_rate, $id = null)
    {
        $currency = BeanFactory::getBean('Currencies');
        $currency->name = $name;
        $currency->symbol = $symbol;
        $currency->iso4217 = $iso4217;
        $currency->conversion_rate = $conversion_rate;
        $currency->status = 'Active';
        if(!empty($id))
        {
            $currency->new_with_id = true;
            $currency->id = $id;
        }
        $currency->save();
        self::$_createdCurrencies[] = $currency;
        return $currency;
    }

    /**
     * getCurrencyByISO
     *
     * get an existing currency by its ISO
     *
     * @param iso4217 the 3-letter ISO for the currency
     * @return new currency object
     */
    public static function getCurrencyByISO($iso4217)
    {
        $currency = BeanFactory::getBean('Currencies');
        $currency->retrieve($currency->retrieveIDByISO($iso4217));
        return $currency;
    }

    /**
     * removeAllCreatedCurrencies
     *
     * remove currencies created by this test utility
     * @return boolean true on successful removal
     */
    public static function removeAllCreatedCurrencies()
    {
        if(empty(self::$_createdCurrencies))
            return true;
        $currency_ids = self::getCreatedCurrencyIds();
        $GLOBALS['db']->query(
            sprintf("DELETE FROM currencies WHERE id IN ('%s');",
            implode("','", $currency_ids))
        );
        self::$_createdCurrencies = array();
        return true;
    }

    /**
     * getCreatedCurrencyIds
     *
     * get array of currency_ids created by this utility
     *
     * @return array list of currency_id's
     */
    public static function getCreatedCurrencyIds()
    {
        $currency_ids = array();
        foreach (self::$_createdCurrencies as $currency) {
            $currency_ids[] = $currency->id;
        }
        return $currency_ids;
    }
}
?>