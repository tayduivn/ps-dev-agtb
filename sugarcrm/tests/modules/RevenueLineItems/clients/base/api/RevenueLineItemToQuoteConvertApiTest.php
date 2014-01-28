<?php
//FILE SUGARCRM flav=pro ONLY
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once('modules/RevenueLineItems/clients/base/api/RevenueLineItemToQuoteConvertApi.php');
class RevenueLineItemToQuoteConvertApiTests extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Opportunity
     */
    protected static $opp;

    /**
     * @var RevenueLineItem
     */
    protected static $revenueLineItem;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        parent::setUpBeforeClass();
        self::$opp = SugarTestOpportunityUtilities::createOpportunity();

        self::$revenueLineItem = new RevenueLineItem();
        self::$revenueLineItem->opportunity_id = self::$opp->id;
        self::$revenueLineItem->save();
    }

    public static function tearDownAfterClass()
    {
        self::$revenueLineItem->mark_deleted(self::$revenueLineItem->id);
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
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
}
