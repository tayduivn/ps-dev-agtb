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
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestCurrencyUtilities::createCurrency('MonkeyDollars','$','MOD',2.0);
	}

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
    }

    /**
     * @dataProvider dataProviderCaseFieldEqualsAmountWhenCaseFieldEmpty
     */
    public function testCaseFieldEqualsAmountWhenCaseFieldEmpty($case)
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $this->assertEquals($opp->$case, $opp->amount);
    }

    public function dataProviderCaseFieldEqualsAmountWhenCaseFieldEmpty()
    {
        return array(array('best_case'), array('worst_case'));
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
     *
     */
    public function testOpportunitySaveSelectProperTimePeriod()
    {
        global $timedate;
        $timedate->getNow();

        $tp = TimePeriod::retrieveFromDate('2009-02-15');

        if(!($tp instanceof TimePeriod))
        {
           $tp = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');
        }

        $opp = SugarTestOpportunityUtilities::createOpportunity();

        //We are trying to simulate setting a timeperiod_id based on the date_closed
        //so let's retrieve the Opportunity and then try to set the date_closed (BeanFactory::getBean will not work)
        $opp = new Opportunity();
        $opp->retrieve($opp->id);
        $opp->date_closed = "2009-02-15";
        $opp->save();

        $this->assertEquals($tp->id, $opp->timeperiod_id);
    }

    /*
     * Test that the base_rate field is populated with rate
     * of currency_id
     *
     */
    public function testCurrencyRate() {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        // if Euro does not exist, will use default currency
        $opportunity->currency_id = $currency->id;
        $opportunity->name = "Test Opportunity Delete Me";
        $opportunity->amount = "5000.00";
        $opportunity->date_closed = strftime('%m-%d-%Y',strtotime('+10 days'));
        $opportunity->best_case = "1000.00";
        $opportunity->worst_case = "600.00";
        $opportunity->save();
        $this->assertEquals(
            sprintf('%.6f',$opportunity->base_rate),
            sprintf('%.6f',$currency->conversion_rate)
        );
    }

    /*
     * Test that base currency exchange rates from EUR are working properly.
     */
    public function testBaseCurrencyAmounts()
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        // if Euro does not exist, will use default currency
        $opportunity->currency_id = $currency->id;
        $opportunity->name = "Test Opportunity Delete Me";
        $opportunity->amount = "5000.00";
        $opportunity->date_closed = strftime('%m-%d-%Y',strtotime('+10 days'));
        $opportunity->best_case = "1000.00";
        $opportunity->worst_case = "600.00";
        $opportunity->save();

        $this->assertEquals(
            sprintf('%.6f',$opportunity->base_rate),
            sprintf('%.6f',$currency->conversion_rate)
        );
    }
}
