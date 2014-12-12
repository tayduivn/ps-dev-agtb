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

class ForecastTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var Currency
     */
    protected $currency;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestForecastUtilities::setUpForecastConfig();

        $this->currency = SugarTestCurrencyUtilities::createCurrency('MonkeyDollars','$','MOD',2.0);

        $GLOBALS['current_user']->setPreference('currency', $this->currency->id);
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestHelper::tearDown();
    }

    /**
     * Test that the base_rate field is populated with rate
     * of currency_id
     *
     * @group forecasts
     */
    public function testForecastRate() {
        $timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();
        $forecast = SugarTestForecastUtilities::createForecast($timeperiod, $GLOBALS['current_user']);
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        $forecast->currency_id = $currency->id;
        $forecast->save();
        $this->assertEquals(
            sprintf('%.6f',$forecast->base_rate),
            sprintf('%.6f',$currency->conversion_rate)
        );
    }
}
