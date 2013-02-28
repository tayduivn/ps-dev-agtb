<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

class ProductsTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var Product
     */
    private $product;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('Products'));
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        $this->product = SugarTestProductUtilities::createProduct();
        parent::setUp();
    }

    public function tearDown()
    {
        SugarTestProductUtilities::removeAllCreatedProducts();
        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    /**
     * This test checks to see that we can save a product where date_closed is set to null
     * @group products
     */
    public function testCreateProductWithoutDateClosed()
    {
        $this->product->date_closed = null;
        $this->product->save();
        $this->assertEmpty($this->product->date_closed);
    }


    /**
     * This is a test to check that the create_new_list_query function returns a where clause to filter
     * "opportunity_id is null" so that products created for opportunities are not displayed by default
     *
     * @group forecasts
     * @group products
     */
    public function testCreateNewListQuery()
    {
        $ret_array = $this->product->create_new_list_query('', '', array(), array(), 0, '', true);
        $this->assertContains(
            "products.opportunity_id is not null OR products.opportunity_id <> ''",
            $ret_array['where'],
            "Did not find products.opportunity_id is not null OR products.opportunity_id <> '' clause"
        );

        $query = $this->product->create_new_list_query('', '', array(), array(), 0, '', false);
        $this->assertContains(
            "products.opportunity_id is not null OR products.opportunity_id <> ''",
            $query,
            "Did not find products.opportunity_id is not null OR products.opportunity_id <> '' clause"
        );
    }


    /**
     * This is a test to check that the create_export_query function returns a where clause to filter
     * "opportunity_id is null" so that products created for opportunities are not displayed by default
     *
     * @group forecasts
     * @group products
     */
    public function testCreateExportQuery()
    {
        $orderBy = '';
        $where = '';
        $query = $this->product->create_export_query($orderBy, $where);
        $this->assertContains(
            "products.opportunity_id is not null OR products.opportunity_id <> ''",
            $query,
            "Did not find products.opportunity_id is not null OR products.opportunity_id <> '' clause"
        );
    }

    /**
     * With SFA-585, it cause the LEFT JOIN was getting added twice, and something got fixed in the system
     * which caused it to be added twice.
     *
     * @ticket SFA-585
     * @group products
     */
    public function testCreateNewListQueryOnlyContainsOneLeftJoinToContacts()
    {
        $ret_array = $this->product->create_new_list_query('', '', array(), array(), 0, '', true);

        $this->assertEquals(
            1,
            substr_count($ret_array['from'], 'LEFT JOIN contacts on contacts.id = products.contact_id')
        );
    }


    /**
     * @group products
     */
    public function testSalesStatusIsNewWhenProductCreatedWithOutId()
    {
        $product = new MockProduct();
        $product->handleSalesStatus();

        $this->assertEquals('New', $product->sales_status);
        unset($product);
    }

    /**
     * @group products
     */
    public function testSalesStatusIsNewWhenProductsCreatedWithId()
    {
        $product = new MockProduct();
        $product->id = "test_id";
        $product->new_with_id = true;
        $product->handleSalesStatus();

        $this->assertEquals('New', $product->sales_status);
        unset($product);
    }

    /**
     * @group products
     */
    public function testSalesStatusChangesFromNewToInProgressWhenSalesStageChanges()
    {
        $product = new MockProduct();
        $product->id = "test_id";
        $product->sales_status = 'New';
        $product->sales_stage = 'test2';
        $product->fetched_row = array(
            'sales_status' => 'New',
            'sales_stage' => 'test1'
        );

        $product->handleSalesStatus();

        $this->assertEquals('In Progress', $product->sales_status);
        unset($product);
    }

    /**
     * @group products
     */
    public function testSalesStatusChangesFromInProgressToClosedWonWhenSalesStageEqualsClosedWon()
    {
        $product = new MockProduct();
        $product->id = "test_id";
        $product->sales_status = 'In Progress';
        $product->sales_stage = 'Closed Won';
        $product->fetched_row = array(
            'sales_status' => 'In Progress',
            'sales_stage' => 'test1'
        );

        $product->handleSalesStatus();

        $this->assertEquals('Closed Won', $product->sales_status);
        unset($product);
    }

    /**
     * @group products
     */
    public function testSalesStatusChangesInProgressToClosedLostWhenSalesStageEqualsClosedLost()
    {
        $product = new MockProduct();
        $product->id = "test_id";
        $product->sales_status = 'In Progress';
        $product->sales_stage = 'Closed Lost';
        $product->fetched_row = array(
            'sales_status' => 'In Progress',
            'sales_stage' => 'test1'
        );

        $product->handleSalesStatus();

        $this->assertEquals('Closed Lost', $product->sales_status);
        unset($product);
    }

    /**
     * @group products
     */
    public function testSalesStatusDoesNotChangeWhenStatusAndStageChange()
    {
        $product = new MockProduct();
        $product->id = "test_id";
        $product->sales_status = 'In Progress';
        $product->sales_stage = 'Closed Lost';
        $product->fetched_row = array(
            'sales_status' => 'New',
            'sales_stage' => 'test1'
        );

        $product->handleSalesStatus();

        $this->assertEquals('In Progress', $product->sales_status);
        unset($product);
    }

    /**
     * @group products
     */
    public function testSalesStatusDoesNotChangeWhenSalesStageDoesNotChange()
    {
        $product = new MockProduct();
        $product->id = "test_id";
        $product->sales_status = 'New';
        $product->sales_stage = 'test1';
        $product->fetched_row = array(
            'sales_status' => 'New',
            'sales_stage' => 'test1'
        );

        $product->handleSalesStatus();

        $this->assertEquals('New', $product->sales_status);
        unset($product);
    }


    /**
     * @group products
     *
     * Test that the account_id in Product instance is properly set for a given Opportunity id.  I am
     * currently creating Opportunities with new Opportunity() because the test helper for Opportunities
     * creates accounts automatically.
     */
    public function testSetAccountForOpportunity()
    {
        $opp = new Opportunity();
        $opp->name = "opp1";
        $opp->save();
        $opp->load_relationship('accounts');
        $account = SugarTestAccountUtilities::createAccount();
        $opp->accounts->add($account);
        $product = new MockProduct();
        $this->assertTrue($product->setAccountIdForOpportunity($opp->id));

        $opp2 = new Opportunity();
        $opp2->name = "opp2";
        $opp2->save();
        SugarTestOpportunityUtilities::setCreatedOpportunity(array($opp->id, $opp2->id));
        $product2 = new MockProduct();
        $this->assertFalse($product2->setAccountIdForOpportunity($opp2->id));
    }

    /**
     * @group products
     * @ticket SFA-567
     */
    public function testProductCreatedFromOpportunityContainsSalesStage()
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $opp->load_relationship('products');

        $products = $opp->products->getBeans();

        $this->assertEquals(1, count($products));
        /* @var $product Product */
        $product = array_shift($products);

        SugarTestProductUtilities::setCreatedProduct(array($product->id));

        $this->assertNotNull($opp->sales_stage); // make sure it's not set to null
        $this->assertEquals($opp->sales_stage, $product->sales_stage);

    }
}

class MockProduct extends Product
{
    public function handleSalesStatus()
    {
        parent::handleSalesStatus();
    }

    public function setAccountIdForOpportunity($oppId)
    {
        return parent::setAccountIdForOpportunity($oppId);
    }
}
