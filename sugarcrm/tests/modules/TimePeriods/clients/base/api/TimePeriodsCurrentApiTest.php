<?php
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


require_once("modules/TimePeriods/clients/base/api/TimePeriodsCurrentApi.php");

class TimePeriodsCurrentApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var TimePeriodsCurrentApi
     */
    protected $api;

    //These are the default forecast configuration settings we will use to test
    private static $forecastConfigSettings = array (
        array('name' => 'timeperiod_type', 'value' => 'chronological', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_interval', 'value' => TimePeriod::ANNUAL_TYPE, 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_leaf_interval', 'value' => TimePeriod::QUARTER_TYPE, 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_start_date', 'value' => '2013-01-01', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_shown_forward', 'value' => '2', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_shown_backward', 'value' => '2', 'platform' => 'base', 'category' => 'Forecasts')
    );


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        // delete all current timeperiods
        $db = DBManagerFactory::getInstance();
        $db->query('UPDATE timeperiods SET deleted = 1');

        //setup forecast admin settings for timeperiods to be able to play nice in the suite
        $admin = BeanFactory::getBean('Administration');

        self::$forecastConfigSettings[3]['timeperiod_start_date']['value'] = TimeDate::getInstance()->getNow()->setDate(date('Y'), 1, 1)->asDbDate(false);
        foreach(self::$forecastConfigSettings as $config)
        {
            $admin->saveSetting($config['category'], $config['name'], $config['value'], $config['platform']);
        }
    }

    public function setUp()
    {
        parent::setUp();
        $this->api = new TimePeriodsCurrentApi();
    }

    public static function tearDownAfterClass()
    {
        // delete all current timeperiods
        $db = DBManagerFactory::getInstance();
        $db->query('UPDATE timeperiods SET deleted = 0 where deleted = 1');
        parent::tearDownAfterClass();
    }

    public function tearDown()
    {
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        parent::tearDown();
    }

    /**
     * @expectedException SugarApiExceptionNotFound
     * @group timeperiods
     */
    public function testInvalidTimePeriodThrowsException()
    {
        $restService = SugarTestRestUtilities::getRestServiceMock();
        $this->api->getCurrentTimePeriod($restService, array());
    }

    /**
     * @group timeperiods
     */
    public function testGetCurrentTimePeriod()
    {
        $tp = SugarTestTimePeriodUtilities::createTimePeriod();

        $restService = SugarTestRestUtilities::getRestServiceMock();
        $return = $this->api->getCurrentTimePeriod($restService, array());

        $this->assertEquals($tp->id, $return['id']);
    }
}