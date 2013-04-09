<?php

require_once 'modules/Products/clients/base/api/ProductsFilterApi.php';

class ProductsFilterApiTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var ProductsFilterApi
     */
    protected $api;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');

        // mark all the current products as deleted
        $db = DBManagerFactory::getInstance();
        $db->query('UPDATE products SET deleted = 1');

        $this->api = new ProductsFilterApi();
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        // create one product with no opp
        $prod1 = SugarTestProductUtilities::createProduct();

        // create a second product associated to the opp
        $prod2 = SugarTestProductUtilities::createProduct();
        $prod2->opportunity_id = $opp->id;
        $prod2->save();
    }

    public function tearDown()
    {
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();

        // mark all the current products as non-deleted
        $db = DBManagerFactory::getInstance();
        $db->query('UPDATE products SET deleted = 0');
    }

    /**
     * @group products
     */
    public function testMakeUserOnlyOneRecordReturns()
    {
        $return = $this->api->filterList(SugarTestRestUtilities::getRestServiceMock(), array('module' => 'Products'));

        $this->assertEquals(1, count($return['records']));
    }

}
