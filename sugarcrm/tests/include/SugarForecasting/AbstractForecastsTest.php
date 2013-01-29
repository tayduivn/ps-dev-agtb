<?php
//FILE SUGARCRM flav=pro ONLY
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
require_once('include/SugarForecasting/AbstractForecast.php');
class SugarForecasting_AbstractForecastTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var MockSugarForecasting_Abstract
     */
    protected static $obj;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('timedate');
        SugarTestHelper::setUp('current_user');
        self::$obj = new MockSugarForecasting_Abstract(array());

        global $current_user;
        $reportee = SugarTestUserUtilities::createAnonymousUser();
        $reportee->reports_to_id = $current_user->id;
        $reportee->save();
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @group forecasts
     * @dataProvider dataTimeDataProvider
     */
    public function testConvertDateTimeToISO($dt_string, $expected)
    {
        $actual = self::$obj->convertDateTimeToISO($dt_string);
        $this->assertEquals($expected, $actual);
    }

    public static function dataTimeDataProvider()
    {
        global $current_user;
        $current_user->setPreference('timezone', 'America/Indiana/Indianapolis');
        $current_user->setPreference('datef', 'm/d/Y');
        $current_user->setPreference('timef', 'h:iA');
        $current_user->savePreferencesToDB();
        $timedate = TimeDate::getInstance();

        return array(
            array('2012-10-15 14:38:42', $timedate->asIso($timedate->fromDb('2012-10-15 14:38:42'), $current_user)), // db format
            //array('10/15/2012 10:38', $timedate->asIso($timedate->fromDb('2012-10-15 10:38:00'), $current_user)), // from user format
        );
    }

    /**
     * This is a test to ensure that the current_user is the first element in the array from the getUserReportees function
     * @group forecasts
     */
    public function testGetUserReportees() {
        global $current_user;
        $reportees = self::$obj->getUserReportees($current_user->id);
        $keys = array_keys($reportees);
        $this->assertEquals($current_user->id, $keys[0]);
    }
}

class MockSugarForecasting_Abstract extends SugarForecasting_AbstractForecast
{
    /**
     * Needed for the interface, but not uses
     *
     * @return array|string|void
     */
    public function process()
    {
    }

    public function getUserReportees($user_id) {
        return parent::getUserReportees($user_id);
    }

    public function convertDateTimeToISO($dt_string)
    {
        return parent::convertDateTimeToISO($dt_string);
    }
}