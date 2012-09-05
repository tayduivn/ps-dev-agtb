<?php

require_once('include/SugarForecasting/Manager.php');
class SugarForecasting_ManagerTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    protected $args = array();

    protected $users = array();

    public function setUp()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setup('mod_strings', array('Forecasts'));

        $timeperiod = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');

        $this->args['timeperiod_id'] = $timeperiod->id;

        SugarTestForecastUtilities::setTimePeriod($timeperiod);


        $this->users['manager'] = SugarTestForecastUtilities::createForecastUser(array('timeperiod_id' => $timeperiod->id));

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

        $this->users['reportee']['forecast'] = SugarTestForecastUtilities::createManagerRollupForecast($this->users['reportee'], $this->users['reportee_reportee']);

    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
    }

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

    public function testLoadUsersReturnsTwoUsersForCurrentUser()
    {
        $obj = new MockSugarForecasting_Manager($this->args);
        $obj->loadUsers();

        $this->assertEquals(2, count($obj->getDataArray()));
    }

    /**
     * @depends testLoadUsersReturnsTwoUsersForCurrentUser
     * @dataProvider dataProviderUserTypes
     */
    public function testLoadUserAmount($user)
    {
        $obj = new MockSugarForecasting_Manager($this->args);
        $obj->loadUsers();
        $obj->loadUsersAmount();

        $dataArray = $obj->getDataArray();

        $this->assertEquals($this->users[$user]['opportunities_total'], $dataArray[$this->users[$user]['user']->user_name]['amount']);
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
     */
    public function testLoadForecastValuesForUser($user, $dataKey)
    {
        $obj = new MockSugarForecasting_Manager($this->args);
        $obj->loadUsers();
        $obj->loadForecastValues();

        $dataArray = $obj->getDataArray();

        $this->assertEquals($this->users[$user]['forecast']->$dataKey, $dataArray[$this->users[$user]['user']->user_name][$dataKey]);
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
     */
    public function testLoadWorksheetAdjustedValuesForUser($user, $dataKey, $worksheetKey)
    {
        $obj = new MockSugarForecasting_Manager($this->args);
        $obj->loadUsers();
        $obj->loadWorksheetAdjustedValues();

        $dataArray = $obj->getDataArray();

        $this->assertEquals($this->users[$user]['worksheet']->$worksheetKey, $dataArray[$this->users[$user]['user']->user_name][$dataKey]);
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
     */
    public function testMakeSureAdjustedNumberAreNotEmpty($user, $dataKey, $worksheetKey)
    {
        $obj = new MockSugarForecasting_Manager($this->args);
        $obj->loadUsers();
        $obj->loadWorksheetAdjustedValues();

        $dataArray = $obj->getDataArray();

        $this->assertEquals($this->users[$user]['worksheet']->$worksheetKey, $dataArray[$this->users[$user]['user']->user_name][$dataKey]);
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