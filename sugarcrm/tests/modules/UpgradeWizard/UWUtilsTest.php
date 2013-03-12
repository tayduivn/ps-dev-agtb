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

require_once('modules/UpgradeWizard/uw_utils.php');
require_once ('modules/SchedulersJobs/SchedulersJob.php');

class UWUtilsTest extends Sugar_PHPUnit_Framework_TestCase  {

    private $job;
    private static $isSetup;
    private static $forecastRanges;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');
        self::$isSetup = $settings['is_setup'];
        self::$forecastRanges = $settings['forecast_ranges'];
        //Set is_setup to 0 for testing purposes
        $admin->saveSetting('Forecasts', 'is_setup', 1, 'base');
        $admin->saveSetting('Forecasts', 'forecast_ranges', 'show_binary', 'base');
        $admin->saveSetting('Forecasts', 'forecast_by', 'opportunities', 'base');
        $db = DBManagerFactory::getInstance();
        $db->query("UPDATE opportunities SET deleted = 1");

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass()
    {
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'is_setup', self::$isSetup, 'base');
        $admin->saveSetting('Forecasts', 'forecast_ranges', self::$forecastRanges, 'base');
        $admin->saveSetting('Forecasts', 'forecast_by', 'products', 'base');
        
        $db = DBManagerFactory::getInstance();
        $db->query("UPDATE opportunities SET deleted = 0");
        SugarTestHelper::tearDown();

        parent::tearDown();
        parent::tearDownAfterClass();
    }

    public function tearDown()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestJobQueueUtilities::removeAllCreatedJobs();
    }


    /**
     * Check that for every old opportunity related products are created via job queue
     *
     * @global type $current_user
     * @group forecasts
     */
    public function testSugarJobUpdateOpportunities()
    {
        global $db, $current_user;

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->assigned_user_id = $current_user->id;
        $opp->probability = '';
        $opp->commit_stage = '';
        $opp->save();

        $this->assertEmpty($opp->commit_stage, 'Commit stage should be empty for old Opportunity');

        //unset best/worst cases
        $db->query("UPDATE opportunities SET best_case = NULL, worst_case = NULL, probability = 80 WHERE id = '{$opp->id}'");

        $this->job = updateOpportunitiesForForecasting();

        $job = new SchedulersJob();
        $job->retrieve($this->job);
        $job->runnable_ran = true;
        $job->runnable_data = '';
        $job->runJob();

        $updated_opp = BeanFactory::getBean('Opportunities');
        $updated_opp->retrieve($opp->id);
        $this->assertNotEmpty($updated_opp->commit_stage, "Updated opportunity's commit stage should not be empty");

        $exp_product = array('name' => $updated_opp->name,
            'best_case' => $updated_opp->best_case,
            'likely_case' => $updated_opp->amount,
            'worst_case' => $updated_opp->worst_case,
            'cost_price' => $updated_opp->amount,
            'quantity' => '1',
            'currency_id' => $updated_opp->currency_id,
            'base_rate' => $updated_opp->base_rate,
            'probability' => $updated_opp->probability,
            'assigned_user_id' => $updated_opp->assigned_user_id,
            'opportunity_id' => $updated_opp->id,
            'commit_stage' => $updated_opp->commit_stage);

        $this->assertTrue($job->runnable_ran);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");

        // BEGIN SUGARCRM flav=pro && flav!=ent ONLY
        // this only pertains to Pro and Corp, not End and Ult
        $product = BeanFactory::getBean('Products');
        $product->retrieve_by_string_fields(array('opportunity_id' => $opp->id));

        $act_product = array('name' => $product->name,
            'best_case' => $product->best_case,
            'likely_case' => $product->likely_case,
            'worst_case' => $product->worst_case,
            'cost_price' => $product->cost_price,
            'quantity' => $product->quantity,
            'currency_id' => $product->currency_id,
            'base_rate' => $product->base_rate,
            'probability' => $product->probability,
            'assigned_user_id' => $product->assigned_user_id,
            'opportunity_id' => $product->opportunity_id,
            'commit_stage' => $product->commit_stage);

        $this->assertEquals($exp_product, $act_product, "Product info doesn't equal to related opp's one");
        // END SUGARCRM flav=pro && flav!=ent ONLY
    }

    /**
     * @group opportunities
     * @group forecasts
     */
    public function testMultipleJobsCreatedForUpgradeOpportunities()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        // create 5 opportunities
        for ($x=0; $x<= 5; $x++) {
            $opp = SugarTestOpportunityUtilities::createOpportunity();
            $opp->assigned_user_id = $GLOBALS['current_user']->id;
            $opp->probability = '';
            $opp->commit_stage = '';
            $opp->save();
        }

        $jobs = updateOpportunitiesForForecasting(3);

        SugarTestJobQueueUtilities::setCreatedJobs($jobs);

        $this->assertEquals(2, count($jobs));
    }
}
