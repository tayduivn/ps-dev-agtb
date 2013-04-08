<?php
// FILE SUGARCRM flav=pro ONLY
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once('include/SugarForecasting/Manager.php');
class SugarForecasting_ManagerTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    protected $args = array();

    protected $users = array();

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setup('mod_strings', array('Forecasts'));
        SugarTestHelper::setUp('current_user');
        SugarTestForecastUtilities::setUpForecastConfig();
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function setUp()
    {
        $timeperiod = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');

        $this->args['timeperiod_id'] = $timeperiod->id;

        SugarTestForecastUtilities::setTimePeriod($timeperiod);


        $this->users['manager'] = SugarTestForecastUtilities::createForecastUser(
            array('timeperiod_id' => $timeperiod->id)
        );

        global $current_user;
        $current_user = $this->users['manager']['user'];

        $config = array(
            'timeperiod_id' => $timeperiod->id,
            'user' =>
            array('reports_to' => $this->users['manager']['user']->id)
        );
        $this->users['reportee'] = SugarTestForecastUtilities::createForecastUser($config);

        $config = array(
            'timeperiod_id' => $timeperiod->id,
            'user' =>
            array('reports_to' => $this->users['reportee']['user']->id)
        );
        $this->users['reportee_reportee'] = SugarTestForecastUtilities::createForecastUser($config);

        $this->users['reportee']['forecast'] = SugarTestForecastUtilities::createManagerRollupForecast(
            $this->users['reportee'],
            $this->users['reportee_reportee']
        );

    }

    public function tearDown()
    {
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
    }

    /**
     * @group forecasts
     */
    public function testLoadUsersThrowExceptionWhenUserIsNotManager()
    {
        $testUser = SugarTestUserUtilities::createAnonymousUser();

        $args = $this->args;

        $args['user_id'] = $testUser->id;

        $obj = new MockSugarForecasting_Manager($args);

        try {
            $obj->loadUsers();
            $this->fail('Exception was not thrown');
        } catch (SugarForecasting_Exception $sfe) {
            $this->assertInstanceOf('SugarForecasting_Exception', $sfe);
        }
    }

    /**
     * @group forecasts
     */
    public function testLoadUsersReturnsTwoUsersForCurrentUser()
    {
        $obj = new MockSugarForecasting_Manager($this->args);
        $obj->loadUsers();

        $this->assertEquals(2, count($obj->getDataArray()));
    }

    /**
     * @depends testLoadUsersReturnsTwoUsersForCurrentUser
     * @dataProvider dataProviderUserTypes
     * @group forecasts
     */
    public function testLoadUserAmount($user)
    {
        $obj = new MockSugarForecasting_Manager($this->args);
        $obj->loadUsers();
        $obj->loadUsersAmount();

        $dataArray = $obj->getDataArray();

        $this->assertEquals(
            $this->users[$user]['opportunities_total'],
            $dataArray[$this->users[$user]['user']->user_name]['amount']
        );
    }

    public function dataProviderUserTypes()
    {
        return array(
            array('manager'),
            array('reportee'),
        );
    }


    /**
     * @depends testLoadUsersReturnsTwoUsersForCurrentUser
     * @dataProvider dataProviderUserTypes
     * @group forecasts
     */
    public function testLoadUsersQuota($user)
    {
        $obj = new MockSugarForecasting_Manager($this->args);
        $obj->loadUsers();
        $obj->loadUsersQuota();

        $dataArray = $obj->getDataArray();

        $actual = $dataArray[$this->users[$user]['user']->user_name]['quota'];
        $this->assertEquals($this->users[$user]['quota']->amount, $actual);
    }

    /**
     * @depends testLoadUsersReturnsTwoUsersForCurrentUser
     * @dataProvider dataProviderLoadForecastValues
     * @group forecasts
     */
    public function testLoadForecastValuesForUser($user, $dataKey)
    {
        $obj = new MockSugarForecasting_Manager($this->args);
        $obj->loadUsers();
        $obj->loadForecastValues();

        $dataArray = $obj->getDataArray();

        $this->assertEquals(
            $this->users[$user]['forecast']->$dataKey,
            $dataArray[$this->users[$user]['user']->user_name][$dataKey]
        );
    }

    public function dataProviderLoadForecastValues()
    {
        return array(
            array('manager', 'best_case'),
            array('manager', 'likely_case'),
            array('manager', 'worst_case'),
            array('reportee', 'best_case'),
            array('reportee', 'likely_case'),
            array('reportee', 'worst_case'),
        );
    }

    /**
     * @depends testLoadUsersReturnsTwoUsersForCurrentUser
     * @dataProvider dataProviderLoadWorksheetAdjustedValues
     * @group forecasts
     */
    public function testLoadWorksheetAdjustedValuesForUser($user, $dataKey, $worksheetKey)
    {

        $obj = new MockSugarForecasting_Manager($this->args);
        $obj->loadUsers();
        $obj->loadWorksheetAdjustedValues();

        $dataArray = $obj->getDataArray();

        $this->assertEquals(
            $this->users[$user]['worksheet']->$worksheetKey,
            $dataArray[$this->users[$user]['user']->user_name][$dataKey]
        );
    }

    public function dataProviderLoadWorksheetAdjustedValues()
    {
        return array(
            array('manager', 'best_adjusted', 'best_case'),
            array('manager', 'likely_adjusted', 'likely_case'),
            array('manager', 'worst_adjusted', 'worst_case'),
            array('reportee', 'best_adjusted', 'best_case'),
            array('reportee', 'likely_adjusted', 'likely_case'),
            array('reportee', 'worst_adjusted', 'worst_case'),
        );
    }

    /**
     * @depends testLoadUsersReturnsTwoUsersForCurrentUser
     * @group forecasts
     */
    public function testLoadManagerAmountsForReporteeIsTotalOfReporteeAndHisReportee()
    {
        $obj = new MockSugarForecasting_Manager($this->args);
        $obj->loadUsers();
        $obj->loadUsersAmount();
        $obj->loadForecastValues();
        $obj->loadManagerAmounts();

        $dataArray = $obj->getDataArray();

        $expected = $this->users['reportee']['opportunities_total'] + $this->users['reportee_reportee']['opportunities_total'];

        $this->assertEquals($expected, $dataArray[$this->users['reportee']['user']->user_name]['amount']);
    }

    /**
     * @depends testLoadUsersReturnsTwoUsersForCurrentUser
     * @dataProvider dataProviderLoadWorksheetAdjustedValues
     * @group forecasts
     */
    public function testMakeSureAdjustedNumberAreNotEmpty($user, $dataKey, $worksheetKey)
    {
        $obj = new MockSugarForecasting_Manager($this->args);
        $obj->loadUsers();
        $obj->loadWorksheetAdjustedValues();

        $dataArray = $obj->getDataArray();

        $this->assertEquals(
            $this->users[$user]['worksheet']->$worksheetKey,
            $dataArray[$this->users[$user]['user']->user_name][$dataKey]
        );
    }

    public function testForecastsHaveCurrencyValues()
    {
        $this->assertEquals('-99', $this->users['reportee']['forecast']->currency_id);
        $this->assertEquals('1', $this->users['reportee']['forecast']->base_rate);
    }

    /**
     * @group forecasts
     *
     */
    public function testDataDoesNotContainInActiveUsers()
    {
        $config = array(
            'user' => array('reports_to' => $this->users['manager']['user']->id)
        );
        $user = SugarTestForecastUtilities::createForecastUser($config);

        $user['user']->status = 'Inactive';
        $user['user']->save();

        $obj = new MockSugarForecasting_Manager($this->args);

        $return = $obj->process();

        $this->assertEquals(2, count($return));


    }
}

class MockSugarForecasting_Manager extends SugarForecasting_Manager
{
    public function loadUsers()
    {
        parent::loadUsers();
    }

    public function loadUsersAmount()
    {
        parent::loadUsersAmount();
    }

    public function loadUsersQuota()
    {
        parent::loadUsersQuota();
    }

    public function loadForecastValues()
    {
        parent::loadForecastValues();
    }

    public function loadWorksheetAdjustedValues()
    {
        parent::loadWorksheetAdjustedValues();
    }

    public function loadManagerAmounts()
    {
        parent::loadManagerAmounts();
    }
}
