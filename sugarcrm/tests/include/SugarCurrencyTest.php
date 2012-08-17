<?php
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

require_once 'include/SugarCurrency.php';

/**
 * SugarCurrencyTest
 *
 * unit tests for currencies
 *
 * @author Monte Ohrt <mohrt@sugarcrm.com>
 */
class SugarCurrencyTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * store $sugar_config for later revert
     * @access public
     * @var    array $sugar_config
     */
    private static $sugar_config;

    /**
     * pre-class environment setup
     *
     * @access public
     */
    public static function setUpBeforeClass()
    {
        // setup test user
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        // setup test currencies
        SugarTestCurrencyUtilities::createCurrency('Singapore','$','SGD',1.246171);
        SugarTestCurrencyUtilities::createCurrency('Philippines','₱','PHP',41.82982);
        SugarTestCurrencyUtilities::createCurrency('Yen','¥','YEN',78.87);

    }

    /**
     * object setup
     *
     * @access public
     */
    public function setUp()
    {
    }

    /**
     * object teardown
     *
     * @access public
     */
    public function tearDown()
    {
    }

    /**
     * post-object environment teardown
     *
     * @access public
     */
    public static function tearDownAfterClass()
    {
        // remove test user
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        // remove test currencies
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
    }

    /**
     * test base currency retrieval
     *
     * @access public
     */
    public function testBaseCurrency()
    {
        $currency = SugarCurrency::getBaseCurrency();
        $this->assertInstanceOf('Currency',$currency);
        // base currency is always a rate of 1.0
        $this->assertEquals(1.0,$currency->conversion_rate);
    }

    /**
     * test currency retrieval by currency_id
     *
     * @access public
     */
    public function testCurrencyGetByID()
    {
        // get a currency to test with
        $currency = SugarCurrency::getCurrencyByISO('PHP');
        $currency_id = $currency->id;
        // now fetch by currency_id
        $currency2 = SugarCurrency::getCurrencyByID($currency_id);
        $this->assertInstanceOf('Currency',$currency2);
        // test they are the same currency
        $this->assertEquals($currency_id,$currency2->id);
    }

    /**
     * test currency retrieval by ISO code
     *
     * @access public
     */
    public function testCurrencyGetByISO()
    {
        $currency = SugarCurrency::getCurrencyByISO('PHP');
        $this->assertInstanceOf('Currency',$currency);
        $this->assertEquals('PHP',$currency->iso4217);
        $this->assertEquals(41.82982,$currency->conversion_rate);
    }

    /**
     * test dollar amount conversions between currencies
     *
     * @access public
     */
    public function testCurrencyConvert()
    {
        $currency1 = SugarCurrency::getCurrencyByISO('SGD');
        $currency2 = SugarCurrency::getCurrencyByISO('PHP');
        $base_currency = SugarCurrency::getBaseCurrency();
        $this->assertInstanceOf('Currency',$currency1);
        $this->assertInstanceOf('Currency',$currency2);
        $this->assertTrue(is_numeric($currency1->conversion_rate));
        $this->assertTrue(is_numeric($currency2->conversion_rate));
        $dollar_value = 1000.00;

        // test convert to base currency
        $converted_amount = round($dollar_value * $currency1->conversion_rate / $base_currency->conversion_rate, 6);
        $this->assertTrue(is_numeric($converted_amount));
        $amount = SugarCurrency::convertAmountToBase($dollar_value,$currency1->id);
        $this->assertTrue(is_numeric($amount));
        $this->assertEquals($converted_amount,$amount);

        // test convert from one currency to another
        $converted_amount = round($dollar_value * $currency1->conversion_rate / $currency2->conversion_rate, 6);
        $this->assertTrue(is_numeric($converted_amount));
        $amount = SugarCurrency::convertAmount($dollar_value, $currency1->id, $currency2->id);
        $this->assertTrue(is_numeric($amount));
        $this->assertEquals($converted_amount, $amount);

    }

    /**
     * test formatting of currency amount
     *
     * @access public
     */
    public function testCurrencyFormat()
    {
        // locale formatting tests
        $currency = SugarCurrency::getCurrencyByISO('PHP');
        $amount = 1000;
        $format = SugarCurrency::formatAmountUserLocale($amount,$currency->id);
        $this->assertEquals($currency->symbol . '1,000.00',$format);
        $amount = 1000.0;
        $format = SugarCurrency::formatAmountUserLocale($amount,$currency->id);
        $this->assertEquals($currency->symbol . '1,000.00',$format);
        $amount = 1000.00;
        $format = SugarCurrency::formatAmountUserLocale($amount,$currency->id);
        $this->assertEquals($currency->symbol . '1,000.00',$format);
        $amount = 1000.000;
        $format = SugarCurrency::formatAmountUserLocale($amount,$currency->id);
        $this->assertEquals($currency->symbol . '1,000.00',$format);
        // manual formatting tests
        $amount = 1000;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2,'.',',','');
        $this->assertEquals($currency->symbol . '1,000.00',$format);
        $format = SugarCurrency::formatAmount($amount,$currency->id,3,'.',',','');
        $this->assertEquals($currency->symbol . '1,000.000',$format);
        $format = SugarCurrency::formatAmount($amount,$currency->id,2,',','','');
        $this->assertEquals($currency->symbol . '1000,00',$format);
        $format = SugarCurrency::formatAmount($amount,$currency->id,2,',','.','');
        $this->assertEquals($currency->symbol . '1.000,00',$format);
        $format = SugarCurrency::formatAmount($amount,$currency->id,2,'.',',','&nbsp;');
        $this->assertEquals($currency->symbol . '&nbsp;1,000.00',$format);
        // manual formatting tests, negative amounts
        $amount = -1000;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2,'.',',','');
        $this->assertEquals($currency->symbol . '-1,000.00',$format);
        $format = SugarCurrency::formatAmount($amount,$currency->id,3,'.',',','');
        $this->assertEquals($currency->symbol . '-1,000.000',$format);
        $format = SugarCurrency::formatAmount($amount,$currency->id,2,',','','');
        $this->assertEquals($currency->symbol . '-1000,00',$format);
        $format = SugarCurrency::formatAmount($amount,$currency->id,2,',','.','');
        $this->assertEquals($currency->symbol . '-1.000,00',$format);
        $format = SugarCurrency::formatAmount($amount,$currency->id,2,'.',',','&nbsp;');
        $this->assertEquals($currency->symbol . '&nbsp;-1,000.00',$format);
        // large amounts
        $amount = 10000;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '10,000.00',$format);
        $amount = 100000;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '100,000.00',$format);
        $amount = 1000000;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '1,000,000.00',$format);
        $amount = 10000000;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '10,000,000.00',$format);
        $amount = 100000000;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '100,000,000.00',$format);
        $amount = 1000000000;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '1,000,000,000.00',$format);
        $amount = -10000;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '-10,000.00',$format);
        $amount = -100000;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '-100,000.00',$format);
        $amount = -1000000;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '-1,000,000.00',$format);
        $amount = -10000000;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '-10,000,000.00',$format);
        $amount = -100000000;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '-100,000,000.00',$format);
        $amount = -1000000000;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '-1,000,000,000.00',$format);
        // decimal amounts, rounding
        $amount = 0.9;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '0.90',$format);
        $amount = 0.09;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '0.09',$format);
        $amount = 0.099;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '0.10',$format);
        $amount = 0.094;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '0.09',$format);
        $amount = 0.09499999;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '0.09',$format);
        $amount = 0.09499999;
        $format = SugarCurrency::formatAmount($amount,$currency->id,6);
        $this->assertEquals($currency->symbol . '0.095000',$format);
        $amount = -0.9;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '-0.90',$format);
        $amount = -0.09;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '-0.09',$format);
        $amount = -0.099;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '-0.10',$format);
        $amount = -0.094;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '-0.09',$format);
        $amount = -0.09499999;
        $format = SugarCurrency::formatAmount($amount,$currency->id,2);
        $this->assertEquals($currency->symbol . '-0.09',$format);
        $amount = -0.09499999;
        $format = SugarCurrency::formatAmount($amount,$currency->id,6);
        $this->assertEquals($currency->symbol . '-0.095000',$format);

    }

    /**
     * test affects of changing base currency type
     *
     * @access public
     */
    public function testBaseCurrencyChange()
    {
        global $sugar_config;
        // save for resetting after test
        $orig_config = $sugar_config;
        $sugar_config['default_currency_iso4217'] = 'BTC';
        $sugar_config['default_currency_name'] = 'Bitcoin';
        $sugar_config['default_currency_symbol'] = '฿';
        sugar_cache_put('sugar_config', $sugar_config);
        // change base currency to bitcoin, test
        // conversions in different currencies
        $currency1 = SugarCurrency::getCurrencyByISO('SGD');
        $currency2 = SugarCurrency::getCurrencyByISO('PHP');
        $currency3 = SugarCurrency::getCurrencyByISO('YEN');
        // get base currency
        $currency4 = SugarCurrency::getBaseCurrency();
        $this->assertInstanceOf('Currency', $currency1);
        $this->assertInstanceOf('Currency', $currency2);
        $this->assertInstanceOf('Currency', $currency3);
        $this->assertInstanceOf('Currency', $currency4);
        $this->assertTrue(is_numeric($currency1->conversion_rate));
        $this->assertTrue(is_numeric($currency2->conversion_rate));
        $this->assertTrue(is_numeric($currency3->conversion_rate));
        $this->assertTrue(is_numeric($currency4->conversion_rate));
        // base currency rate is always 1.0
        $this->assertEquals(1.0, $currency4->conversion_rate);
        $this->assertEquals('BTC', $currency4->iso4217);
        $dollar_value = 1000.00;
        $converted_amount = round($dollar_value * $currency1->conversion_rate / $currency2->conversion_rate, 6);
        $this->assertTrue(is_numeric($converted_amount));
        $amount = SugarCurrency::convertAmount($dollar_value, $currency1->id, $currency2->id);
        $this->assertTrue(is_numeric($amount));
        $this->assertEquals($converted_amount, $amount);
        $converted_amount = round($dollar_value * $currency2->conversion_rate / $currency3->conversion_rate, 6);
        $this->assertTrue(is_numeric($converted_amount));
        $amount = SugarCurrency::convertAmount($dollar_value, $currency2->id, $currency3->id);
        $this->assertTrue(is_numeric($amount));
        $this->assertEquals($converted_amount, $amount);
        $converted_amount = round($dollar_value * $currency3->conversion_rate / $currency4->conversion_rate, 6);
        $this->assertTrue(is_numeric($converted_amount));
        $amount = SugarCurrency::convertAmount($dollar_value, $currency3->id, $currency4->id);
        $this->assertTrue(is_numeric($amount));
        $this->assertEquals($converted_amount, $amount);
        $converted_amount = round($dollar_value * $currency4->conversion_rate / $currency1->conversion_rate, 6);
        $this->assertTrue(is_numeric($converted_amount));
        $amount = SugarCurrency::convertAmount($dollar_value, $currency4->id, $currency1->id);
        $this->assertTrue(is_numeric($amount));
        $this->assertEquals($converted_amount, $amount);
        // reset config values
        $sugar_config = $orig_config;
        sugar_cache_put('sugar_config', $sugar_config);
    }

}
