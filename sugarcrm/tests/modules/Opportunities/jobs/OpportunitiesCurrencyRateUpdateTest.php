<?php
//FILE SUGARCRM flav=pro ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

require_once 'tests/SugarTestDatabaseMock.php';
require_once 'modules/Opportunities/jobs/OpportunitiesCurrencyRateUpdate.php';

class OpportunitiesCurrencyRateUpdateTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $db;
    private $mock;

    public function setUp()
    {
        SugarTestHelper::setUp('app_strings');
        $this->db = new SugarTestDatabaseMock();
        $this->db->setUp();
        $this->setupMockClass();
        parent::setUp();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        $this->tearDownMockClass();
        $this->db->tearDown();
        parent::tearDown();
    }

    /**
     * setup the mock class and override getClosedStages to return a static array for the test
     */
    public function setupMockClass()
    {
        $this->mock = $this->getMock('OpportunitiesCurrencyRateUpdate', array('getClosedStages'));
        $this->mock->expects($this->once())
            ->method('getClosedStages')
            ->will($this->returnValue(array('Closed Won', 'Closed Lost')));
        // we want to use our mock database for these tests, so replace it
        SugarTestReflection::setProtectedValue($this->mock, 'db', $this->db);
    }

    /**
     * tear down mock class
     */
    public function tearDownMockClass()
    {
        unset($this->mock);
    }

    /**
     * @group opportunities
     */
    public function testDoCustomUpdateRate()
    {
        // setup the query strings we are expecting and what they should return
        $this->db->queries['get_rate'] = array(
            'match' => "/SELECT conversion_rate FROM currencies WHERE id = 'abc'/",
            'rows' => array(array('1.234')),
        );
        $this->db->queries['rate_update'] = array(
            'match' => "/UPDATE mytable SET mycolumn = '1\.234'/",
            'rows' => array(array(1)),
        );

        // run our tests with mockup data
        $result = $this->mock->doCustomUpdateRate('mytable', 'mycolumn', 'abc');
        // make sure we get the expected result and the expected run counts
        $this->assertEquals(true, $result);
        $this->assertEquals(1, $this->db->queries['get_rate']['runCount']);
        $this->assertEquals(1, $this->db->queries['rate_update']['runCount']);
    }

    /**
     * @group opportunities
     */
    public function testDoCustomUpdateUsDollarRate()
    {
        // setup the query strings we are expecting and what they should return
        $this->db->queries['rate_update'] = array(
            'match' => "/UPDATE mytable SET amount_usdollar = 1\.234 \/ base_rate/",
            'rows' => array(array(1)),
        );

        // run our tests with mockup data
        $result = $this->mock->doCustomUpdateUsDollarRate('mytable', 'amount_usdollar', '1.234', 'abc');
        // make sure we get the expected result and the expected run counts
        $this->assertEquals(true, $result);
        $this->assertEquals(1, $this->db->queries['rate_update']['runCount']);
    }

}
