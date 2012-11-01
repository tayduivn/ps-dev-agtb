<?php
//FILE SUGARCRM flav=pro ONLY
//TODO: fix this up for when expected opps is added back in 6.8 - https://sugarcrm.atlassian.net/browse/SFA-255
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

require_once 'modules/SchedulersJobs/SchedulersJob.php';

class SugarJobCreateNextTimePeriodTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $preTestIds = array();

    //These are the default forecast configuration settings we will use to test
    protected $forecastConfigSettings = array (
        array('name' => 'timeperiod_type', 'value' => 'chronological', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_interval', 'value' => TimePeriod::ANNUAL_TYPE, 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_leaf_interval', 'value' => TimePeriod::QUARTER_TYPE, 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_start_month', 'value' => '1', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_start_day', 'value' => '1', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_shown_forward', 'value' => '2', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_shown_backward', 'value' => '2', 'platform' => 'base', 'category' => 'Forecasts')
    );

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function setUp()
    {
        $this->preTestIds = TimePeriod::get_timeperiods_dom();

        $db = DBManagerFactory::getInstance();

        $db->query('UPDATE timeperiods set deleted = 1');

        $admin = BeanFactory::getBean('Administration');

        foreach($this->forecastConfigSettings as $config)
        {
            $admin->saveSetting($config['category'], $config['name'], $config['value'], $config['platform']);
        }

        //Run rebuildForecastingTimePeriods which takes care of creating the TimePeriods based on the configuration data
        $timePeriod = TimePeriod::getByType(TimePeriod::ANNUAL_TYPE);
        $currentForecastSettings = $admin->getConfigForModule('Forecasts', 'base');
        $timePeriod->rebuildForecastingTimePeriods(array(), $currentForecastSettings);
    }

    public function tearDown()
    {
        $db = DBManagerFactory::getInstance();

        //Remove any job_queue entries
        $db->query("DELETE FROM job_queue where name = ".$db->quoted("SugarJobCreateNextTimePeriod"));

        $db->query("UPDATE timeperiods set deleted = 1");

        //Clean up anything else left in timeperiods table that was not deleted
        $db->query("UPDATE timeperiods SET deleted = 0 WHERE id IN ('" . implode("', '", array_keys($this->preTestIds))  . "')");

        $db->query("DELETE FROM timeperiods WHERE deleted = 1");
    }

    /**
     * @group timeperiods
     * @group forecasts
     */
    public function testSugarJobCreateNextTimePeriodJob()
    {
        global $current_user;

        $job = SugarTestJobQueueUtilities::createAndRunJob(
            'TestJobQueue',
            'class::SugarJobCreateNextTimePeriod',
            '',
            $current_user);

        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
    }

}