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

class QuoteTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestCurrencyUtilities::createCurrency('MonkeyDollars', '$', 'MOD', 2.0);
    }

    public function tearDown()
    {
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestHelper::tearDown();
    }

    /*
     * Test that the base_rate field is populated with rate
     * of currency_id
     *
     */
    public function testQuoteRate()
    {
        $quote = SugarTestQuoteUtilities::createQuote();
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        $quote->currency_id = $currency->id;
        $quote->save();
        $this->assertEquals(
            sprintf('%.6f', $quote->base_rate),
            sprintf('%.6f', $currency->conversion_rate)
        );
    }

    /**
     * test related opportunity count
     */
    public function testGetRelatedOpportunityCount()
    {
        $quote = SugarTestQuoteUtilities::createQuote();
        $this->assertEquals(0, $quote->getRelatedOpportunityCount());
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        SugarTestQuoteUtilities::relateQuoteToOpportunity($quote->id, $opp->id);
        $this->assertEquals(1, $quote->getRelatedOpportunityCount());
    }

}
