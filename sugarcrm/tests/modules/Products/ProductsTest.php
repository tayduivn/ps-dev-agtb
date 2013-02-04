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

class ProductsTest extends Sugar_PHPUnit_Framework_TestCase {

    private $product;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('mod_strings', array('Products'));
    }

    public function setUp()
    {
        $this->product = SugarTestProductUtilities::createProduct();
    }

    public function tearDown()
    {
        SugarTestProductUtilities::removeAllCreatedProducts();
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * This test checks to see that we can save a product where date_closed is set to null
     *
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
     * @group forecasts
     */
    public function testCreateNewListQuery() {
        $ret_array = $this->product->create_new_list_query('', '', array(), array(), 0, '', true);
        $this->assertContains('products.opportunity_id is null', $ret_array['where'], 'Did not find products.opportunity_id is null clause');

        $query = $this->product->create_new_list_query('', '', array(), array(), 0, '', false);
        $this->assertContains('products.opportunity_id is null', $query, 'Did not find products.opportunity_id is null clause');
    }


    /**
     * This is a test to check that the create_export_query function returns a where clause to filter
     * "opportunity_id is null" so that products created for opportunities are not displayed by default
     * @group forecasts
     */
    public function testCreateExportQuery() {
        $orderBy = '';
        $where = '';
        $query = $this->product->create_export_query($orderBy, $where);
        $this->assertContains('products.opportunity_id is null', $query, 'Did not find products.opportunity_id is null clause');
    }
}