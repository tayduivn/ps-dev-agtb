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
require_once('include/SugarFields/SugarFieldHandler.php');

class SugarFieldCurrencyTest extends Sugar_PHPUnit_Framework_TestCase
{
    static $currency, $currency2;

    /**
     *
     * @access public
     */
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        self::$currency = SugarTestCurrencyUtilities::createCurrency('foo', 'f', 'f', .5);
        self::$currency2 = SugarTestCurrencyUtilities::createCurrency('Singapore', '$', 'SGD', 1.246171, 'currency-sgd');
    }

    /**
     *
     * @access public
     */
    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
    }

    /**
     *
     * @group export
     * @group currency
     * @access public
     */
    public function testExportSanitize()
    {
        global $sugar_config;
        $obj = BeanFactory::getBean('Opportunities');
        $obj->amount = '1000';
        $obj->base_rate = 1;
        $obj->currency_id = '-99';

        $vardef = $obj->field_defs['amount'];

        $field = SugarFieldHandler::getSugarField('currency');
        $value = $field->exportSanitize($obj->amount, $vardef, $obj);
        $expectedValue = SugarCurrency::formatAmountUserLocale($obj->amount, -99);
        $this->assertEquals($expectedValue, $value);

        $obj->currency_id = self::$currency->id;
        $expectedValue = SugarCurrency::formatAmountUserLocale($obj->amount, self::$currency->id);
        $value = $field->exportSanitize($obj->amount, $vardef, $obj);
        $this->assertEquals($expectedValue, $value);

        //Test that we can use the row overload feature in exportSanitize
        $obj->currency_id = '';
        $expectedValue = SugarCurrency::formatAmountUserLocale($obj->amount, self::$currency->id);
        $value = $field->exportSanitize($obj->amount, $vardef, $obj, array('currency_id'=>self::$currency->id));
        $this->assertEquals($expectedValue, $value);


    }

    /**
     *
     * @group export
     * @group currency
     * @access public
     */
    public function testExportSanitizeConvertToBase()
    {
        global $sugar_config;
        $obj = BeanFactory::getBean('Opportunities');
        $obj->amount = '1000';
        $obj->base_rate = self::$currency2->conversion_rate;
        $obj->currency_id = self::$currency2->id;

        //Test conversion to base_rate
        $field = SugarFieldHandler::getSugarField('currency');
        $vardef['convertToBase'] = true;
        $convertedValue = '802.46';
        $expectedValue = SugarCurrency::formatAmountUserLocale($convertedValue, $obj->currency_id);
        $value = $field->exportSanitize($obj->amount, $vardef, $obj);
        $this->assertEquals($expectedValue, $value);

    }

}