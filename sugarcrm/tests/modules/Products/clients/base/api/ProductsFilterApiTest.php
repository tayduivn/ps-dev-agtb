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
        $this->markTestIncomplete('SFA - DB Strict Mode Fails on Forecast Worksheets');
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

    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * @group products
     */
    public function testEntMakeUserOnlyOneRecordReturns()
    {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');
        $return = $this->api->filterList(SugarTestRestUtilities::getRestServiceMock(), array('module' => 'Products'));

        $this->assertEquals(1, count($return['records']));
        $this->assertNotEmpty($return['records'][0]['opportunity_id']);
    }
    //END SUGARCRM flav=ent ONLY

    //BEGIN SUGARCRM flav=pro && flav!=ent ONLY
    /**
     * @group products
     */
    public function testProMakeUserOnlyOneRecordReturns()
    {
        $return = $this->api->filterList(SugarTestRestUtilities::getRestServiceMock(), array('module' => 'Products'));

        $this->assertEquals(1, count($return['records']));
        $this->assertFalse(isset($return['records'][0]['opportunity_id']));
    }
    //END SUGARCRM flav=pro && flav!=ent ONLY


}
