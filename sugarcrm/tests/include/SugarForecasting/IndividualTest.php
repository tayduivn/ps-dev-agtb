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
require_once('include/SugarForecasting/Individual.php');
class SugarForecasting_IndividualTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    protected static $args = array();

    /**
     * @var array
     */
    protected static $user;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $timeperiod = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');
        self::$args['timeperiod_id'] = $timeperiod->id;

        SugarTestForecastUtilities::setTimePeriod($timeperiod);

        self::$user = SugarTestForecastUtilities::createForecastUser(array('timeperiod_id' => $timeperiod->id));
        self::$args['user_id'] = self::$user['user']->id;
    }

    public function setUp()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setup('mod_strings', array('Forecasts'));
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public static function tearDownAfterClass()
    {
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        parent::tearDown();
    }

    public function testWorksheetContainsCorrectNumberOfRows()
    {
        $obj = new MockSugarForecasting_Individual(self::$args);
        $obj->loadWorksheet();
        $dataArray = $obj->getDataArray();

        $this->assertEquals(count(self::$user['opportunities']), count($dataArray));
    }
}

class MockSugarForecasting_Individual extends SugarForecasting_Individual
{
    public function loadWorksheet()
    {
        parent::loadWorksheet();
    }
}