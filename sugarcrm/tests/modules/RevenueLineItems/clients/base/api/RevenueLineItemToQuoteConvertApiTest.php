<?php
//FILE SUGARCRM flav=pro ONLY
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
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
     * @var Product
     */
    protected static $product;
/*
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('current_user');
        parent::setUpBeforeClass();
        self::$opp = SugarTestOpportunityUtilities::createOpportunity();

        //BEGIN SUGARCRM flav=pro && flav!=ent ONLY
        self::$product = array_shift(self::$opp->getProducts());
        //END SUGARCRM flav=pro && flav!=ent ONLY
        //BEGIN SUGARCRM flav=ent ONLY
        self::$product = SugarTestProductUtilities::createProduct();
        self::$product->opportunity_id = self::$opp->id;
        self::$product->save();
        //END SUGARCRM flav=ent ONLY
    }

    public static function tearDownAfterClass()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestProductBundleUtilities::removeAllCreatedProductBundles();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        parent::tearDownAfterClass();
    }
*/
    public function setUp()
    {
        $this->markTestIncomplete('This test is trying to get a property of a non-object.');
    }

    /**
     * @group products
     * @group quotes
     */
    public function testCreateQuoteFromProductApi()
    {
        /* @var $restService RestService */
        $restService = SugarTestRestUtilities::getRestServiceMock();

        $api = new ProductToQuoteConvertApi();
        $return = $api->convertToQuote($restService, array('module' => 'Products', 'record' => self::$product->id));

        $this->assertNotEmpty($return['id']);

        SugarTestQuoteUtilities::setCreatedQuote(array($return['id']));

        // now pull up the quote to make sure it matches the stuff from the opp
        /* @var $quote Quote */
        $quote = BeanFactory::getBean('Quotes', $return['id']);

        $this->assertEquals(self::$opp->id, $quote->opportunity_id);

        // get the product bundle to make sure it contains the product id
        $bundle = array_shift($quote->get_product_bundles());
        $product = array_shift($bundle->get_products());

        SugarTestProductBundleUtilities::setCreatedProductBundle(array($bundle->id));

        $this->assertEquals(self::$product->id, $product->id);

        return $product;
    }

    /**
     * @param $product
     * @group products
     * @group quotes
     * @depends testCreateQuoteFromProductApi
     */
    public function testProductStatusIsQuotes($product)
    {
        $this->assertEquals(Product::STATUS_QUOTED, $product->status);
    }
}
