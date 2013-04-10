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
require_once('include/SugarForecasting/Progress/Individual.php');
class SugarForecasting_Progress_IndividualTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var array args to be passed onto methods
     */
    protected static $args = array();

    /**
     * @var array array of users used throughout class
     */
    protected static $users = array();

    /**
     * @var Currency
     */
    protected static $currency;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setup('mod_strings', array('manager', 'Forecasts'));
        SugarTestHelper::setup('current_user');
        SugarTestForecastUtilities::setUpForecastConfig();

        $timeperiod = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');

        self::$args['timeperiod_id'] = $timeperiod->id;

        self::$currency = SugarTestCurrencyUtilities::createCurrency('Yen','¥','YEN',78.87);

        SugarTestForecastUtilities::setTimePeriod($timeperiod);

        global $current_user;

        $config = array(
            'timeperiod_id' => $timeperiod->id,
            'currency_id' => self::$currency->id,
            'quota' => array('amount' => 27000)
        );
        self::$users['reportee'] = SugarTestForecastUtilities::createForecastUser($config);

        $current_user = self::$users['reportee']['user'];
        $current_user->setPreference('currency', self::$currency->id);
        self::$args['user_id'] = self::$users['reportee']['user']->id;

    }

    /**
     * reset after each test back to manager id, some tests may have changed to use top manager
     */
    public function setup() {
        global $current_user;

        self::$args['user_id'] = self::$users['reportee']['user']->id;
        $current_user = self::$users['reportee']['user'];
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestHelper::tearDown();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        parent::tearDown();
    }

    /**
     * check process method to make sure what is returned to the endpoint is correct
     *
     * @group forecasts
     * @group forecastsprogress
     */
    public function testProcess()
    {
        $obj = new SugarForecasting_Progress_Individual(self::$args);
        $data = $obj->process();

        //find expected quota object for the created quotas
        foreach(SugarTestQuotaUtilities::getCreatedQuotaIds() as $quotaID) {
            $quota = BeanFactory::getBean('Quotas', $quotaID);
            if($quota->timeperiod_id == self::$args['timeperiod_id'] && $quota->user_id == self::$args['user_id'] && $quota->quota_type == "Direct"){
                break;
            }
        }
        //test parts of the process return
        $this->assertEquals($quota->amount, $data['quota_amount'], "Quota not matching expected amount.  Expected: ".$quota->amount." Actual: ".$data['quota_amount']);
    }

    /**
     * check process method to make sure what is returned to the endpoint is correct
     *
     * @group forecasts
     * @group forecastsprogress
     */
    public function testProcessNewUser()
    {
        $newUser = SugarTestUserUtilities::createAnonymousUser();
        $newUser->save();
        self::$args['user_id'] = $newUser->id;
        $obj = new SugarForecasting_Progress_Individual(self::$args);
        $data = $obj->process();
        //test parts of the process return
        $this->assertEquals(0, $data['quota_amount'], "Quota not matching expected amount.  Expected: 0 Actual: ".$data['quota_amount']);
    }


}
