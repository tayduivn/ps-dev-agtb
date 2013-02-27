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
     * @var object pointers to currency objects
     */
    private static $currencySGD;
    private static $currencyPHP;
    private static $currencyYEN;
    private static $currencyBase;

    /**
     * pre-class environment setup
     *
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        // setup test user
        global $current_user;
        $current_user->setPreference('dec_sep', '.');
        $current_user->setPreference('num_grp_sep', ',');
        $current_user->setPreference('default_currency_significant_digits', 2);

        // setup test currencies
        self::$currencySGD = SugarTestCurrencyUtilities::createCurrency('Singapore','$','SGD',1.246171,'currency-sgd');
        self::$currencyPHP = SugarTestCurrencyUtilities::createCurrency('Philippines','₱','PHP',41.82982,'currency-php');
        self::$currencyYEN = SugarTestCurrencyUtilities::createCurrency('Yen','¥','YEN',78.87,'currency-yen');
        self::$currencyBase = BeanFactory::getBean('Currencies','-99');
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
        parent::tearDownAfterClass();
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
        // get test currency id
        $currencyId = 'currency-php';
        // now fetch by currency id
        $currency = SugarCurrency::getCurrencyByID($currencyId);
        $this->assertInstanceOf('Currency',$currency);
        // test they are the same currency
        $this->assertEquals($currencyId,$currency->id);
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
        $this->assertEquals(self::$currencyPHP->conversion_rate,$currency->conversion_rate);
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
        $amount = SugarCurrency::convertAmountToBase('1000.00',self::$currencySGD->id);
        $this->assertEquals('802.458090',$amount);
    }

    /**
     * test converting amount from base currency
     *
     * @group currency
     */
    public function testConvertAmountFromBase()
    {
        $amount = SugarCurrency::convertAmountFromBase('1000.00',self::$currencySGD->id);
        $this->assertEquals('1246.171',$amount);
    }

    /**
     * test converting amount between currencies
     *
     * @group currency
     */
    public function testConvertAmount()
    {
        $amount = SugarCurrency::convertAmount('1000.00', self::$currencySGD->id, self::$currencyPHP->id);
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
        $currencyId = 'currency-php';
        $currencySymbol = '₱';
        return array(
            array('1000', $currencyId, $currencySymbol . '1,000.00'),
            array('1000.0', $currencyId, $currencySymbol . '1,000.00'),
            array('1000.00', $currencyId, $currencySymbol . '1,000.00'),
            array('1000.000', $currencyId, $currencySymbol . '1,000.00'),
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
        $currencyId = 'currency-php';
        $currencySymbol = '₱';
        return array(
            array('1000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '1,000.00'),
            array('1000', $currencyId, 2, '.', ',', true, '&nbsp;', $currencySymbol . '&nbsp;1,000.00'),
            array('1000', $currencyId, 2, ',', '.', true, '', $currencySymbol . '1.000,00'),
            array('1000', $currencyId, 3, '.', ',', true, '', $currencySymbol . '1,000.000'),
            array('1000', $currencyId, 3, '.', '', true, '', $currencySymbol . '1000.000'),
            array('1000', $currencyId, 3, ',', '.', true, '', $currencySymbol . '1.000,000'),
            array('-1000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '-1,000.00'),
            array('-1000', $currencyId, 2, '.', ',', true, '&nbsp;', $currencySymbol . '&nbsp;-1,000.00'),
            array('-1000', $currencyId, 2, ',', '.', true, '', $currencySymbol . '-1.000,00'),
            array('-1000', $currencyId, 3, '.', ',', true, '', $currencySymbol . '-1,000.000'),
            array('-1000', $currencyId, 3, '.', '', true, '', $currencySymbol . '-1000.000'),
            array('-1000', $currencyId, 3, ',', '.', true, '', $currencySymbol . '-1.000,000'),
            array('10000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '10,000.00'),
            array('100000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '100,000.00'),
            array('1000000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '1,000,000.00'),
            array('10000000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '10,000,000.00'),
            array('100000000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '100,000,000.00'),
            array('1000000000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '1,000,000,000.00'),
            array('-10000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '-10,000.00'),
            array('-100000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '-100,000.00'),
            array('-1000000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '-1,000,000.00'),
            array('-10000000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '-10,000,000.00'),
            array('-100000000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '-100,000,000.00'),
            array('-1000000000', $currencyId, 2, '.', ',', true, '', $currencySymbol . '-1,000,000,000.00'),
            array('0.9', $currencyId, 2, '.', ',', true, '', $currencySymbol . '0.90'),
            array('0.09', $currencyId, 2, '.', ',', true, '', $currencySymbol . '0.09'),
            array('0.099', $currencyId, 2, '.', ',', true, '', $currencySymbol . '0.10'),
            array('0.094', $currencyId, 2, '.', ',', true, '', $currencySymbol . '0.09'),
            array('0.09499999', $currencyId, 2, '.', ',', true, '', $currencySymbol . '0.09'),
            array('0.09499999', $currencyId, 6, '.', ',', true, '', $currencySymbol . '0.095000'),
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
        return array(
            array('1000.00', 'currency-sgd', 'currency-php', '33566.677446'),
            array('1000.00', 'currency-php', 'currency-yen', '1885.496997'),
            array('1000.00', 'currency-yen', '-99', '12.679092'),
            array('1000.00', '-99', 'currency-sgd', '1246.171'),
        );
    }


}
