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

/**
 * SugarJobUpdateForecastWorksheetsTest.php
 *
 * This is a test to check that forecast_worksheets entries are created from the SugarJobUpdateForecastWorksheets
 * scheduled job class.
 */
class SugarJobUpdateForecastWorksheetsTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $tp;
    private $user;
    private $opp;
    private $prod;

    public function setUp()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        global $current_user;
        $current_user->is_admin = 1;

        $this->tp = SugarTestTimePeriodUtilities::createTimePeriod('2008-01-01', '2008-03-31');
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->opp = SugarTestOpportunityUtilities::createOpportunity();
        $this->opp->date_closed ='2008-02-01';      // set the dat closed to the middle of the TP
        $this->opp->assigned_user_id = $this->user->id;
        $this->opp->save();
    }

    public function tearDown()
    {
        $db = DBManagerFactory::getInstance();
        //Clean up job_queue and forecast_worksheets entries
        $db->query("DELETE FROM job_queue where name = ".$db->quoted("Update ForecastWorksheets"));
        $db->query("DELETE FROM forecast_worksheets WHERE parent_id = '{$this->opp->id}'");
        $db->query("DELETE FROM forecast_worksheets WHERE parent_id = '{$this->prod->id}'");
        //Clean up other entries using test utilities
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
    }


    /**
     * @group forecasts
     */
    public function testUpdateForecastWorksheets()
    {
        global $current_user;

        $data = array(
            'user_id' => $this->user->id,
            'timeperiod_id' => $this->tp->id
        );

        $job = SugarTestJobQueueUtilities::createAndRunJob(
            'SugarJobUpdateForecastWorksheets',
            'class::SugarJobUpdateForecastWorksheets',
            json_encode($data),
            $current_user);

        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");

        $products = $this->opp->get_linked_beans('products', 'Products');

        $this->assertEquals(1, count($products));

        $db = DBManagerFactory::getInstance();

        foreach($products as $product) {
            $this->prod = $product;
            SugarTestProductUtilities::setCreatedProduct(array($product->id));
            $this->assertEquals($this->opp->id, $product->opportunity_id);
            // make sure that we have a committed version of the product and opportunity
            $result = $db->getOne("SELECT count(id) as total FROM forecast_worksheets WHERE parent_id = '{$product->id}' and draft = 0");
            $this->assertEquals(1, $result['total']);
            $result = $db->getOne("SELECT count(id) as total FROM forecast_worksheets WHERE parent_id = '{$this->opp->id}' and draft = 0");
            $this->assertEquals(1, $result['total']);
        }
    }

}