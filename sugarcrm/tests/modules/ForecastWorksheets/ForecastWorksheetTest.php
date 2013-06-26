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

        SugarTestForecastUtilities::setUpForecastConfig();
    }

    public function setUp()
    {
        $this->db = new SugarTestDatabaseMock();
        $this->db->setUp();
    }

    public function tearDown()
    {
        $this->db->tearDown();
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        parent::tearDownAfterClass();
    }

    public function testGetRelatedNameReturnsEmpty()
    {
        $this->db->queries['accountQuery'] = array(
            'match' => '/my_test_id/',
            'rows' => array(
                array(
                    'name' => 'My Test Account'
                ),
            ),
        );

        $forecast_worksheet = BeanFactory::getBean('ForecastWorksheets');
        $return = SugarTestReflection::callProtectedMethod($forecast_worksheet, 'getRelatedName', array('Accounts', 'test_id'));
        $this->assertEmpty($return);
    }

    public function testGetRelatedNameReturnsName()
    {
        $acc_name = 'My Test Account';
        $acc_id = 'my_test_id';
        $this->db->queries['accountQuery'] = array(
            'match' => '/' . $acc_id . '/',
            'rows' => array(
                array(
                    'name' => $acc_name
                ),
            ),
        );

        $forecast_worksheet = BeanFactory::getBean('ForecastWorksheets');
        $return = SugarTestReflection::callProtectedMethod($forecast_worksheet, 'getRelatedName', array('Accounts', $acc_id));
        $this->assertEquals($acc_name, $return);
    }
}
