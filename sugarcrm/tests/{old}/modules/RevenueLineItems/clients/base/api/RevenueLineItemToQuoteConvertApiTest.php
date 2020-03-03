<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use PHPUnit\Framework\TestCase;

class RevenueLineItemToQuoteConvertApiTests extends TestCase
{
    /**
     * @var Opportunity
     */
    protected static $opp;

    /**
     * @var RevenueLineItem
     */
    protected static $revenueLineItem;

    public static function setUpBeforeClass() : void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        self::$opp = SugarTestOpportunityUtilities::createOpportunity();

        self::$revenueLineItem = new RevenueLineItem();
        self::$revenueLineItem->opportunity_id = self::$opp->id;
        self::$revenueLineItem->quantity = '50';
        self::$revenueLineItem->discount_amount = '10.00';
        self::$revenueLineItem->likely_case = '40.00';
        self::$revenueLineItem->discount_price = '1.00';
        self::$revenueLineItem->save();

        SugarTestRevenueLineItemUtilities::setCreatedRevenueLineItem(array(self::$revenueLineItem->id));
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestHelper::tearDown();
    }

    /**
     * @group RevenueLineItems
     * @group quotes
     */
    public function testCreateQuoteFromRevenueLineItemApi()
    {
        /* @var $restService RestService */
        $restService = SugarTestRestUtilities::getRestServiceMock();

        $api = new RevenueLineItemToQuoteConvertApi();
        $return = $api->convertToQuote($restService, array('module' => 'RevenueLineItem', 'record' => self::$revenueLineItem->id));

        $this->assertNotEmpty($return['id']);

        SugarTestQuoteUtilities::setCreatedQuote(array($return['id']));

        // now pull up the quote to make sure it matches the stuff from the opp
        /* @var $quote Quote */
        $quote = BeanFactory::getBean('Quotes', $return['id']);

        $this->assertEquals(self::$opp->id, $quote->opportunity_id);

        // lets make sure the totals are correct
        $this->assertEquals('50.000000', $quote->subtotal);
        $this->assertEquals('10.000000', $quote->deal_tot);
        $this->assertEquals('40.000000', $quote->new_sub);
        $this->assertEquals('40.000000', $quote->total);

        $quote->load_relationship('revenuelineitems');
        $revenueLineItem = $quote->revenuelineitems->getBeans();
        $this->assertNotEmpty($revenueLineItem);
        $revenueLineItem = reset($revenueLineItem);

        $this->assertEquals(self::$revenueLineItem->id, $revenueLineItem->id);

        return $revenueLineItem;
    }

    /**
     * @param $revenueLineItem
     * @group RevenueLineItems
     * @group quotes
     * @depends testCreateQuoteFromRevenueLineItemApi
     */
    public function testRevenueLineItemStatusIsQuotes($revenueLineItem)
    {
        $this->assertEquals(RevenueLineItem::STATUS_QUOTED, $revenueLineItem->status);
    }

    public function testCreateProductBundleFromRLIListThrowsException()
    {
        $mock_rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(array('canConvertToQuote'))
            ->getMock();

        $mock_rli->id = 'unit_test_1';

        $mock_rli->expects($this->once())
            ->method('canConvertToQuote')
            ->willReturn('Some Random String');

        BeanFactory::registerBean($mock_rli);

        $api = new RevenueLineItemToQuoteConvertApi();

        $this->expectException(\SugarApiExceptionRequestMethodFailure::class);
        SugarTestReflection::callProtectedMethod($api, 'createProductBundleFromRLIList', [['unit_test_1']]);
    }
}
