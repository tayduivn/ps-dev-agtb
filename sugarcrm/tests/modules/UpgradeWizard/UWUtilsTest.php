<?php
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

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestHelper::tearDown();
    }


    /**
     * Check that for every old opportunity related products are created via job queue
     * @global type $current_user
	 * @group forecasts
     */
    function testUpdateOppsJob()
    {
        global $db, $current_user;

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->assigned_user_id = $current_user->id;
        $opp->save();

        $exp_opp = array('commit_stage' => $opp->commit_stage,
                        'best_case' => $opp->best_case,
                        'worst_case' => $opp->worst_case,
                        'date_closed_timestamp' => substr($opp->date_closed_timestamp, 0, -2));

        $exp_product = array('name' => $opp->name,
            'best_case' => $opp->amount,
            'likely_case' => $opp->amount,
            'worst_case' => $opp->amount,
            'cost_price' => $opp->amount,
            'quantity' => '1',
            'currency_id' => $opp->currency_id,
            'base_rate' => $opp->base_rate,
            'probability' => $opp->probability,
            'date_closed' => $opp->date_closed,
            'date_closed_timestamp' => $opp->date_closed_timestamp,
            'assigned_user_id' => $opp->assigned_user_id,
            'opportunity_id' => $opp->id,
            'commit_stage' => $opp->commit_stage);

        //unset commit_stage, date_closed_timestamp, best/worst cases
        $db->query("UPDATE opportunities SET commit_stage = '', date_closed_timestamp = '', best_case = '', worst_case = '' WHERE id = '{$opp->id}'");

        //unset opportunity_id in the product which was automatically created during opp save
        $product = BeanFactory::getBean('Products');
        $product->retrieve_by_string_fields(array('opportunity_id' => $opp->id));
        SugarTestProductUtilities::setCreatedProduct(array($product->id));
        $product->opportunity_id = '';
        $product->save();

        $this->job = updateOpps();

        $job = new SchedulersJob();
        $job->retrieve($this->job);
        $job->runnable_ran = true;
        $job->runnable_data = '';
        $job->runJob();

        $updated_opp = $opp->retrieve();
        $act_opp = array('commit_stage' => $opp->commit_stage,
                        'best_case' => intval($opp->best_case),
                        'worst_case' => intval($opp->worst_case),
                        'date_closed_timestamp' => substr($opp->date_closed_timestamp, 0, -2));

        $this->assertEquals($exp_opp, $act_opp, "New forecasts fields hasn't been updated during upgrade process");

        $this->assertTrue($job->runnable_ran);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");

        $product = BeanFactory::getBean('Products');
        $product->retrieve_by_string_fields(array('opportunity_id' => $opp->id));
        SugarTestProductUtilities::setCreatedProduct(array($product->id));

        $act_product = array('name' => $product->name,
            'best_case' => $product->best_case,
            'likely_case' => $product->likely_case,
            'worst_case' => $product->worst_case,
            'cost_price' => $product->cost_price,
            'quantity' => $product->quantity,
            'currency_id' => $product->currency_id,
            'base_rate' => $product->base_rate,
            'probability' => $product->probability,
            'date_closed' => $product->date_closed,
            'date_closed_timestamp' => $product->date_closed_timestamp,
            'assigned_user_id' => $product->assigned_user_id,
            'opportunity_id' => $product->opportunity_id,
            'commit_stage' => $product->commit_stage);

        $this->assertEquals($exp_product, $act_product, "Product info doesn't equal to related opp's one");
    }

}
?>