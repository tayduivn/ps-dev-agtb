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
     * @var    array $sugar_config
     */
    private static $sugar_config;

    /**
     * pre-class environment setup
     *
     */
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
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
     */
    public function setUp()
    {
    }

    /**
     * object teardown
     *
     */
    public function tearDown()
    {
    }

    /**
     * post-object environment teardown
     *
     */
    public static function tearDownAfterClass()
    {
        // remove test user
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        // remove test currencies
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestHelper::tearDown();
    }

    /**
     * test base currency retrieval
     *
     * @group currency
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
     * @group currency
     */
    public function testCurrencyGetByID()
    {
        // get a currency to test with
        $currency = SugarCurrency::getCurrencyByISO('PHP');
        $currencyId = $currency->id;
        // now fetch by currency_id
        $currency2 = SugarCurrency::getCurrencyByID($currencyId);
        $this->assertInstanceOf('Currency',$currency2);
        // test they are the same currency
        $this->assertEquals($currencyId,$currency2->id);
    }

    /**
     * test currency retrieval by ISO code
     *
     * @group currency
     */
    public function testCurrencyGetByISO()
    {
        $currency = SugarCurrency::getCurrencyByISO('PHP');
        $this->assertInstanceOf('Currency',$currency);
        $this->assertEquals('PHP',$currency->iso4217);
        $this->assertEquals(41.82982,$currency->conversion_rate);
    }

    /**
     * test currency retrieval by user preferences
     *
     * @group currency
     */
    public function testGetUserLocaleCurrency()
    {
        $currency = SugarCurrency::getUserLocaleCurrency();
        $this->assertInstanceOf('Currency',$currency);
    }

    /**
     * test converting amount to base currency
     *
     * @group currency
     */
    public function testConvertAmountToBase()
    {
        $currency1 = SugarCurrency::getCurrencyByISO('SGD');

        $amount = SugarCurrency::convertAmountToBase('1000.00',$currency1->id);
        $this->assertEquals('802.458089',$amount);
    }

    /**
     * test converting amount from base currency
     *
     * @group currency
     */
    public function testConvertAmountFromBase()
    {
        $currency1 = SugarCurrency::getCurrencyByISO('SGD');

        $amount = SugarCurrency::convertAmountFromBase('1000.00',$currency1->id);
        $this->assertEquals('1246.171',$amount);
    }

    /**
     * test converting amount between currencies
     *
     * @group currency
     */
    public function testConvertAmount()
    {
        $currency1 = SugarCurrency::getCurrencyByISO('SGD');
        $currency2 = SugarCurrency::getCurrencyByISO('PHP');

        $amount = SugarCurrency::convertAmount('1000.00', $currency1->id, $currency2->id);
        $this->assertEquals('33566.677446', $amount);
    }


    /**
     * test dollar amount conversions between currencies
     *
     * @dataProvider testConvertWithRateProvider
     * @param $amount
     * @param $rate
     * @param $result
     * @group currency
     */
    public function testConvertWithRate($amount, $rate, $result)
    {
        $this->assertEquals($result,SugarCurrency::convertWithRate($amount, $rate));
    }

    /**
     * convert with rate data provider
     *
     * @group math
     * @access public
     */
    public static function testConvertWithRateProvider() {
        return array(
            array(1000,0.5,2000),
            array(1000,2.0,500),
            array('1000','0.5','2000'),
            array('1000','2.0','500'),
        );
    }

    /**
     * test formatting of currency amount with user locale settings
     *
     * @dataProvider testFormatAmountUserLocaleProvider
     * @param $amount
     * @param $currencyId
     * @param $result
     * @group currency
     */
    public function testFormatAmountUserLocale($amount, $currencyId, $result)
    {
        $format = SugarCurrency::formatAmountUserLocale($amount, $currencyId);
        $this->assertEquals($result, $format);
    }

    /**
     * convert with rate data provider
     *
     * @group math
     * @access public
     */
    public static function testFormatAmountUserLocaleProvider() {
        $currency = SugarCurrency::getCurrencyByISO('PHP');
        return array(
            array('1000',$currency->id,$currency->symbol . '1,000.00'),
            array('1000.0',$currency->id,$currency->symbol . '1,000.00'),
            array('1000.00',$currency->id,$currency->symbol . '1,000.00'),
            array('1000.000',$currency->id,$currency->symbol . '1,000.00'),
        );
    }

    /**
     * test formatting of currency amount manually
     *
     * @dataProvider testFormatAmountProvider
     * @param $amount
     * @param $currencyId
     * @param $precision
     * @param $decimal
     * @param $thousands
     * @param $showSymbol
     * @param $symbolSeparator
     * @param $result
     * @group currency
     */
    public function testFormatAmount($amount, $currencyId, $precision, $decimal, $thousands, $showSymbol, $symbolSeparator, $result)
    {
        $format = SugarCurrency::formatAmount($amount, $currencyId, $precision, $decimal, $thousands, $showSymbol, $symbolSeparator);
        $this->assertEquals($result, $format);
    }

    /**
     * format amount data provider
     *
     * @group math
     * @access public
     */
    public static function testFormatAmountProvider() {
        $currency = SugarCurrency::getCurrencyByISO('PHP');
        return array(
            array('1000', $currency->id, 2, '.', ',', true, '', $currency->symbol . '1,000.00'),
            array('1000', $currency->id, 2, '.', ',', true, '&nbsp;', $currency->symbol . '&nbsp;1,000.00'),
            array('1000', $currency->id, 2, ',', '.', true, '', $currency->symbol . '1.000,00'),
            array('1000', $currency->id, 3, '.', ',', true, '', $currency->symbol . '1,000.000'),
            array('1000', $currency->id, 3, '.', '', true, '', $currency->symbol . '1000.000'),
            array('1000', $currency->id, 3, ',', '.', true, '', $currency->symbol . '1.000,000'),
            array('-1000', $currency->id, 2, '.', ',', true, '', $currency->symbol . '-1,000.00'),
            array('-1000', $currency->id, 2, '.', ',', true, '&nbsp;', $currency->symbol . '&nbsp;-1,000.00'),
            array('-1000', $currency->id, 2, ',', '.', true, '', $currency->symbol . '-1.000,00'),
            array('-1000', $currency->id, 3, '.', ',', true, '', $currency->symbol . '-1,000.000'),
            array('-1000', $currency->id, 3, '.', '', true, '', $currency->symbol . '-1000.000'),
            array('-1000', $currency->id, 3, ',', '.', true, '', $currency->symbol . '-1.000,000'),
            array('10000', $currency->id, 2, '.', ',', true, '', $currency->symbol . '10,000.00'),
            array('100000', $currency->id, 2, '.', ',', true, '', $currency->symbol . '100,000.00'),
            array('1000000', $currency->id, 2, '.', ',', true, '', $currency->symbol . '1,000,000.00'),
            array('10000000', $currency->id, 2, '.', ',', true, '', $currency->symbol . '10,000,000.00'),
            array('100000000', $currency->id, 2, '.', ',', true, '', $currency->symbol . '100,000,000.00'),
            array('1000000000', $currency->id, 2, '.', ',', true, '', $currency->symbol . '1,000,000,000.00'),
            array('-10000', $currency->id, 2, '.', ',', true, '', $currency->symbol . '-10,000.00'),
            array('-100000', $currency->id, 2, '.', ',', true, '', $currency->symbol . '-100,000.00'),
            array('-1000000', $currency->id, 2, '.', ',', true, '', $currency->symbol . '-1,000,000.00'),
            array('-10000000', $currency->id, 2, '.', ',', true, '', $currency->symbol . '-10,000,000.00'),
            array('-100000000', $currency->id, 2, '.', ',', true, '', $currency->symbol . '-100,000,000.00'),
            array('-1000000000', $currency->id, 2, '.', ',', true, '', $currency->symbol . '-1,000,000,000.00'),
            array('0.9', $currency->id, 2, '.', ',', true, '', $currency->symbol . '0.90'),
            array('0.09', $currency->id, 2, '.', ',', true, '', $currency->symbol . '0.09'),
            array('0.099', $currency->id, 2, '.', ',', true, '', $currency->symbol . '0.10'),
            array('0.094', $currency->id, 2, '.', ',', true, '', $currency->symbol . '0.09'),
            array('0.09499999', $currency->id, 2, '.', ',', true, '', $currency->symbol . '0.09'),
            array('0.09499999', $currency->id, 6, '.', ',', true, '', $currency->symbol . '0.095000'),
        );
    }

    /**
     * test affects of changing base currency type
     *
     * @dataProvider testBaseCurrencyChangeProvider
     * @param $amount
     * @param $currencyId1
     * @param $currencyId2
     * @param $result
     * @group currency
     */
    public function testBaseCurrencyChange($amount, $currencyId1, $currencyId2, $result)
    {
        global $sugar_config;
        // save for resetting after test
        $orig_config = $sugar_config;
        $sugar_config['default_currency_iso4217'] = 'BTC';
        $sugar_config['default_currency_name'] = 'Bitcoin';
        $sugar_config['default_currency_symbol'] = '฿';
        sugar_cache_put('sugar_config', $sugar_config);

        $this->assertEquals($result, SugarCurrency::convertAmount($amount, $currencyId1, $currencyId2));

        // reset config values
        $sugar_config = $orig_config;
        sugar_cache_put('sugar_config', $sugar_config);
    }

    /**
     * base rate change provider
     *
     * @group math
     * @access public
     */
    public static function testBaseCurrencyChangeProvider() {
        $currency1 = SugarCurrency::getCurrencyByISO('SGD');
        $currency2 = SugarCurrency::getCurrencyByISO('PHP');
        $currency3 = SugarCurrency::getCurrencyByISO('YEN');
        $currency4 = SugarCurrency::getBaseCurrency();
        // retrieve values since BeanFactory caches them
        $currency4->retrieve('-99');
        return array(
            array('1000.00', $currency1->id, $currency2->id, '33566.677446'),
            array('1000.00', $currency2->id, $currency3->id, '1885.496997'),
            array('1000.00', $currency3->id, $currency4->id, '12.679092'),
            array('1000.00', $currency4->id, $currency1->id, '1246.171'),
        );
    }


}
