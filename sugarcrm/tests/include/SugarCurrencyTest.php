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

class SugarCurrencyTest extends Sugar_PHPUnit_Framework_TestCase
{
    private static $currency_ids = array();
    private static $sugar_config;

    public static function setUpBeforeClass()
    {

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $currency = BeanFactory::getBean('Currencies');
        $currency->status = 'Active';
        $currency->name = 'Singapore';
        $currency->iso4217 = 'SGD';
        $currency->symbol = '$';
        $currency->conversion_rate = 1.246171;
        self::$currency_ids[] = $currency->save();

        $currency = BeanFactory::getBean('Currencies');
        $currency->status = 'Active';
        $currency->name = 'Philippines';
        $currency->iso4217 = 'PHP';
        $currency->symbol = '₱';
        $currency->conversion_rate = 41.82982;
        self::$currency_ids[] = $currency->save();

        $currency = BeanFactory::getBean('Currencies');
        $currency->status = 'Active';
        $currency->name = 'Yen';
        $currency->iso4217 = 'YEN';
        $currency->symbol = '¥';
        $currency->conversion_rate = 78.87;
        self::$currency_ids[] = $currency->save();

    }

    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        $GLOBALS['db']->query(sprintf("DELETE FROM currencies WHERE id IN ('%s');",
            implode("','",self::$currency_ids)));
        self::$currency_ids = array();
    }

    public function testBaseCurrency()
    {
        $currency = SugarCurrency::getBaseCurrency();
        $this->assertInstanceOf('Currency',$currency);
        $this->assertEquals(1.0,$currency->conversion_rate);
    }

    public function testCurrencyGetByID()
    {
        $currency = SugarCurrency::getCurrencyByID('-99');
        $this->assertInstanceOf('Currency',$currency);
        $this->assertEquals('-99',$currency->id);
    }

    public function testCurrencyGetByISO()
    {
        $currency = SugarCurrency::getCurrencyByISO('USD');
        $this->assertInstanceOf('Currency',$currency);
        $this->assertEquals('USD',$currency->iso4217);
        $this->assertEquals(1.0,$currency->conversion_rate);
    }

    public function testCurrencyConvert()
    {
        $currency1 = SugarCurrency::getCurrencyByISO('SGD');
        $currency2 = SugarCurrency::getCurrencyByISO('PHP');
        $this->assertInstanceOf('Currency',$currency1);
        $this->assertInstanceOf('Currency',$currency2);
        $this->assertTrue(is_numeric($currency1->conversion_rate));
        $this->assertTrue(is_numeric($currency2->conversion_rate));
        $dollar_value = 1000.00;
        $converted_amount = round($dollar_value * $currency1->conversion_rate / $currency2->conversion_rate,6);
        $this->assertTrue(is_numeric($converted_amount));
        $amount = SugarCurrency::convertAmount($dollar_value,$currency1->id,$currency2->id);
        $this->assertTrue(is_numeric($amount));
        $this->assertEquals($converted_amount,$amount);
    }

    public function testCurrencyFormat()
    {
        $currency = SugarCurrency::getCurrencyByISO('PHP');
        $amount = 1000;
        $format = SugarCurrency::formatAmount($amount,$currency->id);
        $this->assertEquals($currency->symbol . '1,000.00',$format);
        $amount = 1000.0;
        $format = SugarCurrency::formatAmount($amount,$currency->id);
        $this->assertEquals($currency->symbol . '1,000.00',$format);
        $amount = 1000.00;
        $format = SugarCurrency::formatAmount($amount,$currency->id);
        $this->assertEquals($currency->symbol . '1,000.00',$format);
        $amount = 1000.000;
        $format = SugarCurrency::formatAmount($amount,$currency->id);
        $this->assertEquals($currency->symbol . '1,000.00',$format);
    }

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
        $this->assertEquals(1.0,$currency4->conversion_rate);
        $this->assertEquals('BTC',$currency4->iso4217);
        $this->assertInstanceOf('Currency',$currency1);
        $this->assertInstanceOf('Currency',$currency2);
        $this->assertInstanceOf('Currency',$currency3);
        $this->assertTrue(is_numeric($currency1->conversion_rate));
        $this->assertTrue(is_numeric($currency2->conversion_rate));
        $this->assertTrue(is_numeric($currency3->conversion_rate));
        $dollar_value = 1000.00;
        $converted_amount = round($dollar_value * $currency1->conversion_rate / $currency2->conversion_rate,6);
        $this->assertTrue(is_numeric($converted_amount));
        $amount = SugarCurrency::convertAmount($dollar_value,$currency1->id,$currency2->id);
        $this->assertTrue(is_numeric($amount));
        $this->assertEquals($converted_amount,$amount);
        $converted_amount = round($dollar_value * $currency2->conversion_rate / $currency3->conversion_rate,6);
        $this->assertTrue(is_numeric($converted_amount));
        $amount = SugarCurrency::convertAmount($dollar_value,$currency2->id,$currency3->id);
        $this->assertTrue(is_numeric($amount));
        $this->assertEquals($converted_amount,$amount);
        $converted_amount = round($dollar_value * $currency3->conversion_rate / $currency1->conversion_rate,6);
        $this->assertTrue(is_numeric($converted_amount));
        $amount = SugarCurrency::convertAmount($dollar_value,$currency3->id,$currency1->id);
        $this->assertTrue(is_numeric($amount));
        $this->assertEquals($converted_amount,$amount);
        // reset config values
        $sugar_config = $orig_config;
        sugar_cache_put('sugar_config', $sugar_config);
    }

}
