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

class OpportunityTest extends Sugar_PHPUnit_Framework_TestCase
{
    private static $isSetup;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestCurrencyUtilities::createCurrency('MonkeyDollars', '$', 'MOD', 2.0);
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');
        self::$isSetup = $settings['is_setup'];
        $admin->saveSetting('Forecasts', 'is_setup', '1', 'base');
    }

    public function tearDown()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        //BEGIN SUGARCRM flav=pro ONLY
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestProductUtilities::removeAllCreatedProducts();
        //END SUGARCRM flav=pro ONLY
    }

    public static function tearDownAfterClass()
    {
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'is_setup', self::$isSetup, 'base');
        SugarTestHelper::tearDown();
    }

    //BEGIN SUGARCRM flav=pro ONLY
    public function dataProviderCaseFieldEqualsAmountWhenCaseFieldEmpty()
    {
        return array(array('best_case'), array('worst_case'));
    }

    /**
     * @dataProvider dataProviderCaseFieldEqualsAmountWhenCaseFieldEmpty
     */
    public function testCaseFieldEqualsAmountWhenCaseFieldEmpty($case)
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $this->assertEquals($opp->$case, $opp->amount);
    }


    /**
     * @dataProvider dataProviderCaseFieldEqualsAmountWhenCaseFieldEmpty
     */
    public function testCaseFieldEqualsZeroWhenCaseFieldSetToZero($case)
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->$case = 0;
        $opp->save();
        $this->assertEquals(0, $opp->$case);
    }

    /**
     * This test checks to see if we correctly set the timeperiod_id value of an Opportunity record
     * @group forecasts
     */
    public function testOpportunitySaveSelectProperTimePeriod()
    {
        global $timedate;
        $timedate->getNow();

        $tp = TimePeriod::retrieveFromDate('2009-02-15');

        if (empty($tp)) {
            $tp = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');
        }

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->date_closed = "2009-02-15";
        $opp->save();

        //check that the timeperiod covers the date closed timestamp
        $this->assertLessThan($opp->date_closed_timestamp, $tp->start_date_timestamp);
        $this->assertGreaterThanOrEqual($opp->date_closed_timestamp, $tp->end_date_timestamp);
    }

    /**
     * This test checks to see if we the opportunity is still included on the time period on the first day of the span
     * @group forecasts
     */
    public function testOpportunitySaveFirstDayOfTimePeriod()
    {
        global $timedate;
        $timedate->getNow();

        $tp = TimePeriod::retrieveFromDate('2009-02-15');

        if (empty($tp)) {
            $tp = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');
        }

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->date_closed = "2009-01-02";
        $opp->save();

        //check that the timeperiod covers the date closed timestamp
        $this->assertLessThan($opp->date_closed_timestamp, $tp->start_date_timestamp);
        $this->assertGreaterThanOrEqual($opp->date_closed_timestamp, $tp->end_date_timestamp);
    }

    /**
     * This test checks to ensure that opportunities created with a date_closed value have a date_closed_timestamp
     * value that correctly falls within range of the timeperiod for that period.
     * @group forecasts
     */
    public function testOpportunitySaveLastDayOfTimePeriod()
    {
        global $timedate;
        $timedate->getNow();

        $tp = TimePeriod::retrieveFromDate('2009-02-15');

        if (empty($tp)) {
            $tp = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');
        }

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->date_closed = "2009-03-31";
        $opp->save();

        //check that the timeperiod covers the date closed timestamp
        $this->assertLessThan($opp->date_closed_timestamp, $tp->start_date_timestamp);
        $this->assertGreaterThanOrEqual($opp->date_closed_timestamp, $tp->end_date_timestamp);
    }

    //END SUGARCRM flav=pro ONLY

    /*
     * Test that the base_rate field is populated with rate of currency_id
     * @group forecasts
     */
    public function testCurrencyRate()
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        // if Euro does not exist, will use default currency
        $opportunity->currency_id = $currency->id;
        $opportunity->name = "Test Opportunity Delete Me";
        $opportunity->amount = "5000.00";
        $opportunity->date_closed = TimeDate::getInstance()->getNow()->modify("+10 days")->asDbDate();
        //BEGIN SUGARCRM flav=pro ONLY
        $opportunity->best_case = "1000.00";
        $opportunity->worst_case = "600.00";
        //END SUGARCRM flav=pro ONLY
        $opportunity->save();
        $this->assertEquals(
            sprintf('%.6f', $opportunity->base_rate),
            sprintf('%.6f', $currency->conversion_rate)
        );
    }

    /*
     * Test that base currency exchange rates from EUR are working properly.
     * @group forecasts
     */
    public function testBaseCurrencyAmounts()
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        // if Euro does not exist, will use default currency
        $opportunity->currency_id = $currency->id;
        $opportunity->name = "Test Opportunity Delete Me";
        $opportunity->amount = "5000.00";
        $opportunity->date_closed = TimeDate::getInstance()->getNow()->modify("+10 days")->asDbDate();
        //BEGIN SUGARCRM flav=pro ONLY
        $opportunity->best_case = "1000.00";
        $opportunity->worst_case = "600.00";
        //END SUGARCRM flav=pro ONLY
        $opportunity->save();

        $this->assertEquals(
            sprintf('%.6f', $opportunity->base_rate),
            sprintf('%.6f', $currency->conversion_rate)
        );
    }

    //BEGIN SUGARCRM flav=pro ONLY
    /*
     * This method tests that a product record is created for new opportunity and that the necessary opportunity
     * field values are mapped to the product record
     * @group forecasts
     */
    public function testProductEntryWasCreated()
    {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opportunity = BeanFactory::getBean('Products');
        $opportunity->retrieve_by_string_fields(array('opportunity_id' => $opp->id));

        SugarTestProductUtilities::setCreatedProduct(array($opportunity->id));

        $expected = array($opp->name, $opp->amount, $opp->best_case, $opp->worst_case);
        $actual = array($opportunity->name, $opportunity->likely_case, $opportunity->best_case, $opportunity->worst_case);

        $this->assertEquals($expected, $actual);
    }


    /*
     * This method tests that subsequent changes to an opportunity will also update the associated product's data
     * @group forecasts
     * @group opportunities
     * @group opportunities
     * @bug 56433
     */
    public function testOpportunityChangesUpdateRelatedProduct()
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opportunity = BeanFactory::getBean('Products');
        $opportunity->retrieve_by_string_fields(array('opportunity_id' => $opp->id));

        SugarTestProductUtilities::setCreatedProduct(array($opportunity->id));

        //Now we change the opportunity's values again
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        $opp->currency_id = $currency->id;
        $opp->save();

        $opportunity->retrieve_by_string_fields(array('opportunity_id' => $opp->id));
        $this->assertEquals(
            $opp->currency_id,
            $opportunity->currency_id,
            'The opportunity and product currency_id values differ'
        );
    }

    /*
     * This method tests that best/worst cases will be set to opp amount when sales stage is changed to Closed Won
     * @group forecasts
     */
    public function testCaseFieldsEqualsAmountWhenSalesStageEqualsClosedWon()
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->best_case = $opp->amount * 2;
        $opp->worst_case = $opp->amount / 2;
        $opp->save();

        $this->assertNotEquals($opp->best_case, $opp->amount);
        $this->assertNotEquals($opp->worst_case, $opp->amount);
        $opp->sales_stage = Opportunity::STAGE_CLOSED_WON;
        $opp->save();

        $this->assertEquals($opp->best_case, $opp->amount);
        $this->assertEquals($opp->worst_case, $opp->amount);
    }
    //END SUGARCRM flav=pro ONLY
    
    /**
     * @group opportunities
     */
    public function testSalesStatusIsNewWhenProductCreatedWithOutId()
    {
        $opportunity = new MockOpportunityBean();
        $opportunity->handleSalesStatus();

        $this->assertEquals('New', $opportunity->sales_status);
        unset($opportunity);
    }

    /**
     * @group opportunities
     */
    public function testSalesStatusIsNewWhenProductsCreatedWithId()
    {
        $opportunity = new MockOpportunityBean();
        $opportunity->id = "test_id";
        $opportunity->new_with_id = true;
        $opportunity->handleSalesStatus();

        $this->assertEquals('New', $opportunity->sales_status);
        unset($opportunity);
    }

    /**
     * @group opportunities
     */
    public function testSalesStatusChangesFromNewToInProgressWhenSalesStageChanges()
    {
        $opportunity = new MockOpportunityBean();
        $opportunity->id = "test_id";
        $opportunity->sales_status = 'New';
        $opportunity->sales_stage = 'test2';
        $opportunity->fetched_row = array(
            'sales_status' => 'New',
            'sales_stage' => 'test1'
        );

        $opportunity->handleSalesStatus();

        $this->assertEquals('In Progress', $opportunity->sales_status);
        unset($opportunity);
    }

    /**
     * @group opportunities
     */
    public function testSalesStatusChangesFromInProgressToClosedWonWhenSalesStageEqualsClosedWon()
    {
        $opportunity = new MockOpportunityBean();
        $opportunity->id = "test_id";
        $opportunity->sales_status = 'In Progress';
        $opportunity->sales_stage = 'Closed Won';
        $opportunity->fetched_row = array(
            'sales_status' => 'In Progress',
            'sales_stage' => 'test1'
        );

        $opportunity->handleSalesStatus();

        $this->assertEquals('Closed Won', $opportunity->sales_status);
        unset($opportunity);
    }

    /**
     * @group opportunities
     */
    public function testSalesStatusChangesInProgressToClosedLostWhenSalesStageEqualsClosedLost()
    {
        $opportunity = new MockOpportunityBean();
        $opportunity->id = "test_id";
        $opportunity->sales_status = 'In Progress';
        $opportunity->sales_stage = 'Closed Lost';
        $opportunity->fetched_row = array(
            'sales_status' => 'In Progress',
            'sales_stage' => 'test1'
        );

        $opportunity->handleSalesStatus();

        $this->assertEquals('Closed Lost', $opportunity->sales_status);
        unset($opportunity);
    }

    /**
     * @group opportunities
     */
    public function testSalesStatusDoesNotChangeWhenStatusAndStageChange()
    {
        $opportunity = new MockOpportunityBean();
        $opportunity->id = "test_id";
        $opportunity->sales_status = 'In Progress';
        $opportunity->sales_stage = 'Closed Lost';
        $opportunity->fetched_row = array(
            'sales_status' => 'New',
            'sales_stage' => 'test1'
        );

        $opportunity->handleSalesStatus();

        $this->assertEquals('In Progress', $opportunity->sales_status);
        unset($opportunity);
    }

    /**
     * @group opportunities
     */
    public function testSalesStatusDoesNotChangeWhenSalesStageDoesNotChange()
    {
        $opportunity = new MockOpportunityBean();
        $opportunity->id = "test_id";
        $opportunity->sales_status = 'New';
        $opportunity->sales_stage = 'test1';
        $opportunity->fetched_row = array(
            'sales_status' => 'New',
            'sales_stage' => 'test1'
        );

        $opportunity->handleSalesStatus();

        $this->assertEquals('New', $opportunity->sales_status);
        unset($opportunity);
    }
}


class MockOpportunityBean extends Opportunity
{
    public function handleSalesStatus()
    {
        parent::handleSalesStatus();
    }
}
