<?php
//FILE SUGARCRM flav=pro ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once 'tests/SugarTestDatabaseMock.php';
require_once 'modules/RevenueLineItems/jobs/RevenueLineItemsCurrencyRateUpdate.php';

class RevenueLineItemsCurrencyRateUpdateTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $db;
    private $mock;

    public function setUp()
    {
        $this->db = new SugarTestDatabaseMock();
        $this->db->setUp();
        $this->setupMockClass();
        parent::setUp();
    }

    public function tearDown()
    {
        $this->tearDownMockClass();
        $this->db->tearDown();
        parent::tearDown();
    }

    /**
     * setup the mock class and override getClosedStages to return a static array for the test
     */
    public function setupMockClass()
    {
        $this->mock = $this->getMock('RevenueLineItemsCurrencyRateUpdate', array('getClosedStages'));
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
        $this->mock->expects($this->once())
            ->method('getClosedStages')
            ->will($this->returnValue(array('Closed Won', 'Closed Lost')));

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
        $this->mock->expects($this->once())
            ->method('getClosedStages')
            ->will($this->returnValue(array('Closed Won', 'Closed Lost')));

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

    /**
     * @group opportunities
     */
    public function testDoPostUpdateAction()
    {
         $this->mock->expects($this->once())
            ->method('getClosedStages')
            ->will($this->returnValue(array('Closed Won', 'Closed Lost')));
            
        // setup the query strings we are expecting and what they should return
        $this->db->queries['post_select'] = array(
            'match' => "/SELECT opportunity_id/",
            'rows' => array(
                array('likely'=>'1000', 'best'=>'1000', 'worst'=>'1000', 'opp_id'=>'abc123'),
                array('likely'=>'2000', 'best'=>'2000', 'worst'=>'2000', 'opp_id'=>'abc123'),
            )
        );
        $this->db->queries['post_update'] = array(
            'match' => "/UPDATE opportunities/",
        );

        // run our tests with mockup data
        $result = $this->mock->doPostUpdateAction();
        // make sure we get the expected result and the expected run counts
        $this->assertEquals(true, $result);
        //BEGIN SUGARCRM flav=ent ONLY
        $this->assertEquals(1, $this->db->queries['post_select']['runCount']);
        $this->assertGreaterThan(0, $this->db->queries['post_update']['runCount']);
        //END SUGARCRM flav=ent ONLY
    }


}
