<?php
//FILE SUGARCRM flav=pro ONLY
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

require_once('modules/Opportunities/Opportunity.php');

/**
 * SetCommitStageTest.php
 *
 * This is a test to check that the probability value for an opportunity correctly adjusts the commit_stage value
 * during a save operation.
 *
 */
class SetCommitStageTest extends Sugar_PHPUnit_Framework_TestCase
{
    var $opp;
    static $isSetup;
    static $forecastRanges;
    static $rangeValues;

    public function setUp()
    {
        $this->opp = SugarTestOpportunityUtilities::createOpportunity();
        unset($this->opp->probability);
        unset($this->opp->commit_stage);
    }

    public function tearDown()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
    }

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setup('app_list_strings');
        SugarTestHelper::setUp('current_user');
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');
        self::$isSetup = $settings['is_setup'];
        self::$forecastRanges = $settings['forecast_ranges'];
        self::$rangeValues = $settings['show_binary_ranges'];

        $admin->saveSetting('Forecasts', 'is_setup', '1', 'base');
        $admin->saveSetting('Forecasts', 'forecast_ranges', 'show_binary', 'base');
        $values = json_encode(array('include' => array('min' => 70, 'max' => 100), 'exclude' => array('min' => 0, 'max' => 69)));
        $admin->saveSetting('Forecasts', 'show_binary_ranges', $values, 'base');
    }

    public static function tearDownAfterClass()
    {
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'is_setup', self::$isSetup, 'base');
        $admin->saveSetting('Forecasts', 'forecast_ranges', self::$forecastRanges, 'base');
        $admin->saveSetting('Forecasts', 'show_binary_ranges', json_encode(self::$rangeValues), 'base');
        SugarTestHelper::tearDown();
    }

    public function probabilityProvider()
    {
        return array(
            array(0, "exclude"),
            array(25, "exclude"),
            array(65, "exclude"),
            array(85, "include"),
            array(100, "include")
        );
    }

    /**
     * Tests the probability against the expected commit_stage value with the supplied probabilityProvider function
     * @dataProvider probabilityProvider
     * @group forecasts
     */
    public function testSetCommitStage($probability, $commit_stage)
    {
        //Test setting field 'commit_stage'
        $this->opp->probability = $probability;
        $this->opp->save();
        $this->assertEquals($commit_stage, $this->opp->commit_stage, "commit stage should be {$commit_stage} when probability is {$probability}");
    }

    /**
     * Tests the forecast and commit_stage to be updated when sales_stage is "Closed Lost"
     * @group forecasts
     */
    public function testUpdateForecastAndCommitStage()
    {
        $this->opp->sales_stage = "Closed Lost";
        $this->opp->save();
        $this->assertEquals('exclude', $this->opp->commit_stage, "commit_stage should be set to exclude");
    }
}
