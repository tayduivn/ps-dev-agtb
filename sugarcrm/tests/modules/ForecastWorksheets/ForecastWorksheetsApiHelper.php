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

require_once 'tests/SugarTestDatabaseMock.php';
require_once 'modules/ForecastWorksheets/ForecastWorksheetsApiHelper.php';

class ForecastWorksheetTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var SugarTestDatabaseMock
     */
    protected $db;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public function setUp()
    {
        $this->db = new SugarTestDatabaseMock();
        $this->db->setUp();
        parent::setUp();
    }

    public function tearDown()
    {
        $this->db->tearDown();
        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
    }

    public function dataProviderFormatForApiSetsParentDeleted()
    {
        return array(
            array(0),
            array(1)
        );
    }

    /**
     *
     * @dataProvider dataProviderFormatForApiSetsParentDeleted
     *
     * @param $parent_deleted
     */
    public function testFormatForApiSetsParentDeleted($parent_deleted)
    {
        $product_name = 'My Test Product';
        $product_id = 'my_test_id';
        if ($parent_deleted === 0) {
            $this->db->queries['forecast_parent_query'] = array(
                'match' => '/products.deleted = 0 AND products.id = \'' . $product_id . '\'/',
                'rows' => array(
                    array(
                        'id' => $product_id,
                        'name' => $product_name,
                        'deleted' => $parent_deleted
                    ),
                ),
            );
        }

        /* @var $forecast_worksheet ForecastWorksheet */
        $forecast_worksheet = BeanFactory::getBean('ForecastWorksheets');
        $forecast_worksheet->parent_type = 'Products';
        $forecast_worksheet->parent_id = $product_id;

        $api_helper = new ForecastWorksheetsApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $bean = $api_helper->formatForApi($forecast_worksheet);

        $this->assertEquals($parent_deleted, $bean['parent_deleted']);
    }
}
