<?php
//BEGIN SUGARCRM flav=pro ONLY
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
require_once('include/SugarCharts/ChartDisplay.php');
require_once('modules/Reports/Report.php');

class ChartDisplayTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var ChartDisplay
     */
    protected $chartDisplay;

    /**
     * @var array
     */
    protected $chartData = array();

    /**
     * List of the default reports
     *
     * @var array
     */
    protected static $report_defs = array();

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        global $app_list_strings, $app_strings, $current_user;
        $app_list_strings = return_app_list_strings_language('en');
        $app_strings = return_application_language('en');
        $current_user = SugarTestUserUtilities::createAnonymousUser(true, 1);

        // create 5 accounts with shipping_states equal to Indiana
        $x=0;
        while($x<5) {
            $account = SugarTestAccountUtilities::createAccount();
            $account->shipping_address_state = "IN";
            $account->account_type = (($x%2)==0) ? 'Customer' : 'Partner';
            $account->save();
            $x++;
        }

        self::$report_defs = array();
        self::$report_defs[] = '{"display_columns":[],"module":"Accounts","group_defs":[{"name":"account_type","label":"Type","table_key":"self","type":"enum"}],"summary_columns":[{"name":"count","label":"Count","field_type":"","group_function":"count","table_key":"self"},{"name":"account_type","label":"Type","table_key":"self"}],"report_name":"Test Run","chart_type":"hBarF","do_round":1,"chart_description":"","numerical_chart_column":"self:count","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"shipping_address_state","table_key":"self","qualifier_name":"equals","input_name0":"IN","input_name1":"on"}}}}';

        parent::setUpBeforeClass();

    }

    public static function tearDownAfterClass()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        parent::tearDownAfterClass();
        SugarTestHelper::tearDown();
    }

    public function setUp()
    {
        parent::setUp();

        // run the code here since we just want to test what it outputs
        $this->chartDisplay = new ChartDisplay();

        $this->chartDisplay->setReporter(new Report(self::$report_defs[0]));
        $json = $this->chartDisplay->generateJson();

        $this->chartData = json_decode($json, true);

    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->chartDisplay);
        unset($this->chartData);
    }

    public function testChartDisplayHasCorrectTitle()
    {
        $this->assertEquals('Total is 5', $this->chartData['properties'][0]['title']);
    }

    public function testChartDataHasTwoLabels()
    {
        $this->assertEquals(2, count($this->chartData['label']));
    }

}