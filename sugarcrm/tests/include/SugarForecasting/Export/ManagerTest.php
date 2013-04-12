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
require_once('include/SugarForecasting/Export/Manager.php');

class SugarForecasting_Export_ManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private static $reportee;

    /**
     * @var array
     */
    private static $reportee2;

    /**
     * @var array
     */
    protected static $manager;

    /**
     * @var array
     */
    protected static $manager2;

    /**
     * @var TimePeriod
     */
    protected static $timeperiod;

    /**
     * @var array
     */
    protected static $managerData;

    /**
     * @var array
     */
    protected static $managerData2;

    /**
     * @var array
     */
    protected static $repData;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestForecastUtilities::setUpForecastConfig();

        self::$manager = SugarTestForecastUtilities::createForecastUser();
        //set up another manager, and assign him to the first manager manually so his data is generated
        //correctly.
        self::$manager2 = SugarTestForecastUtilities::createForecastUser();
        self::$manager2["user"]->reports_to_id = self::$manager['user']->id;
        self::$manager2["user"]->save();

        self::$reportee = SugarTestForecastUtilities::createForecastUser(
            array('user' => array('reports_to' => self::$manager['user']->id))
        );
        self::$reportee2 = SugarTestForecastUtilities::createForecastUser(
            array('user' => array('reports_to' => self::$manager2['user']->id))
        );

        self::$timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();

        self::$managerData = array(
            "amount" => self::$manager['opportunities_total'],
            "quota" => self::$manager['quota']->amount,
            "quota_id" => self::$manager['quota']->id,
            "best_case" => self::$manager['forecast']->best_case,
            "likely_case" => self::$manager['forecast']->likely_case,
            "worst_case" => self::$manager['forecast']->worst_case,
            "best_adjusted" => self::$manager['worksheet']->best_case,
            "likely_adjusted" => self::$manager['worksheet']->likely_case,
            "worst_adjusted" => self::$manager['worksheet']->worst_case,
            "commit_stage" => self::$manager['worksheet']->commit_stage,
            "forecast_id" => self::$manager['forecast']->id,
            "worksheet_id" => self::$manager['worksheet']->id,
            "show_opps" => true,
            "id" => self::$manager['user']->id,
            "name" => 'Opportunities (' . self::$manager['user']->first_name . ' ' . self::$manager['user']->last_name . ')',
            "user_id" => self::$manager['user']->id,

        );

        self::$managerData2 = array(
            "amount" => self::$manager2['opportunities_total'],
            "quota" => self::$manager2['quota']->amount,
            "quota_id" => self::$manager2['quota']->id,
            "best_case" => self::$manager2['forecast']->best_case,
            "likely_case" => self::$manager2['forecast']->likely_case,
            "worst_case" => self::$manager2['forecast']->worst_case,
            "best_adjusted" => self::$manager2['worksheet']->best_case,
            "likely_adjusted" => self::$manager2['worksheet']->likely_case,
            "worst_adjusted" => self::$manager2['worksheet']->worst_case,
            "commit_stage" => self::$manager2['worksheet']->commit_stage,
            "forecast_id" => self::$manager2['forecast']->id,
            "worksheet_id" => self::$manager2['worksheet']->id,
            "show_opps" => true,
            "id" => self::$manager2['user']->id,
            "name" => 'Opportunities (' . self::$manager2['user']->first_name . ' ' . self::$manager2['user']->last_name . ')',
            "user_id" => self::$manager2['user']->id,

        );

        self::$repData = array(
            "amount" => self::$reportee['opportunities_total'],
            "quota" => self::$reportee['quota']->amount,
            "quota_id" => self::$reportee['quota']->id,
            "best_case" => self::$reportee['forecast']->best_case,
            "likely_case" => self::$reportee['forecast']->likely_case,
            "worst_case" => self::$reportee['forecast']->worst_case,
            "best_adjusted" => self::$reportee['worksheet']->best_case,
            "likely_adjusted" => self::$reportee['worksheet']->likely_case,
            "worst_adjusted" => self::$reportee['worksheet']->worst_case,
            "commit_stage" => self::$reportee['worksheet']->commit_stage,
            "forecast_id" => self::$reportee['forecast']->id,
            "worksheet_id" => self::$reportee['worksheet']->id,
            "show_opps" => true,
            "id" => self::$reportee['user']->id,
            "name" => self::$reportee['user']->first_name . ' ' . self::$reportee['user']->last_name,
            "user_id" => self::$reportee['user']->id,

        );
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestHelper::tearDown();
    }

    /**
     * exportForecastWorksheetsProvider
     *
     * This is the dataProvider function for testExportForecastWorksheets
     */
    /**
     * exportForecastWorksheetsProvider
     *
     * This is the dataProvider function for testExportForecastWorksheets
     */
    public function exportForecastWorksheetProvider()
    {
        return array
        (
            array('show_worksheet_best', '1', 'assertRegExp', '/Best Case/'),
            array('show_worksheet_best', '0', 'assertNotRegExp', '/Best Case/'),
            array('show_worksheet_likely', '1', 'assertRegExp', '/Likely Case/'),
            array('show_worksheet_likely', '0', 'assertNotRegExp', '/Likely Case/'),
            array('show_worksheet_worst', '1', 'assertRegExp', '/Worst Case/'),
            array('show_worksheet_worst', '0', 'assertNotRegExp', '/Worst Case/'),
        );
    }

    /**
     * testExport
     *
     * This is a test to check that we get a response back from the export data call
     *
     * @group forecasts
     * @group export
     *
     * @dataProvider exportForecastWorksheetProvider
     */
    public function testExport($hide, $value, $method, $expectedRegex)
    {
        global $current_user;
        $current_user = self::$manager2['user'];
        $args = array();
        $args['timeperiod_id'] = self::$timeperiod->id;
        $args['user_id'] = self::$manager2['user']->id;

        // hide/show any columns
        SugarTestConfigUtilities::setConfig('Forecasts', $hide, $value);

        $obj = new SugarForecasting_Export_Manager($args);
        $content = $obj->process();

        $this->assertNotEmpty($content, "content empty. Rep data should have returned csv file contents.");
        $this->$method($expectedRegex, $content);
    }


    /**
     * This is a function to test the getFilename function
     *
     * @group export
     * @group forecasts
     */
    public function testGetFilename()
    {
        $args = array();
        $args['timeperiod_id'] = self::$timeperiod->id;
        $args['user_id'] = self::$manager2['user']->id;
        $obj = new SugarForecasting_Export_Manager($args);

        $this->assertRegExp('/\_manager\_forecast.csv$/', $obj->getFilename());
    }

    /**
     * This is a function to test the bug 58397
     *
     * @group export
     * @group forecasts
     */
    public function testBug58397()
    {
        $worksheet = SugarTestManagerWorksheetUtilities::getManagerWorksheetForUserAndTimePeriod(
            self::$reportee2['user']->id,
            self::$timeperiod->id
        );

        $worksheet->name .= "'";
        $worksheet->save();

        $args = array();
        $args['timeperiod_id'] = self::$timeperiod->id;
        $args['user_id'] = self::$manager2['user']->id;
        $args['encode_to_html'] = false;

        $obj = new SugarForecasting_Export_Manager($args);
        $content = $obj->process();

        $this->assertNotEmpty($content, "content empty. Rep data should have returned csv file contents.");
        $this->assertNotContains('#039', $content);
        $this->assertContains("'", $content);
    }
}
