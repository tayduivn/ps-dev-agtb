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

var $meeting;
var $call;
private $jobs = array();

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestHelper::tearDown();
    }

function setUp()
{
    global $current_user;

    $db = DBManagerFactory::getInstance();
    $timedate = TimeDate::getInstance();

	if($db->dbType != 'mysql')
	{
		$this->markTestSkipped('Skipping for non-mysql dbs');
	}

	$this->meeting = SugarTestMeetingUtilities::createMeeting();
	$date_start = $timedate->nowDb();
	$this->meeting->date_start = $date_start;
	$this->meeting->duration_hours = 2;
	$this->meeting->duration_minutes = 30;
	$this->meeting->save();

	$sql = "UPDATE meetings SET date_end = '{$date_start}' WHERE id = '{$this->meeting->id}'";
	$db->query($sql);

	$this->call = SugarTestCallUtilities::createCall();
	$date_start = $timedate->nowDb();
	$this->call->date_start = $date_start;
	$this->call->duration_hours = 2;
	$this->call->duration_minutes = 30;
	$this->call->save();

	$sql = "UPDATE calls SET date_end = '{$date_start}' WHERE id = '{$this->call->id}'";
	$db->query($sql);
}

function tearDown() {
	global $db, $current_user;
    if($db->dbType != 'mysql') return; // No need to clean up if we skipped the test to begin with

    SugarTestMeetingUtilities::removeAllCreatedMeetings();
	SugarTestCallUtilities::removeAllCreatedCalls();

	$this->meeting = null;
	$this->call = null;

	$meetingsSql = "UPDATE meetings SET date_end = date_add(date_start, INTERVAL + CONCAT(duration_hours, ':', duration_minutes) HOUR_MINUTE)";
	$callsSql = "UPDATE calls SET date_end = date_add(date_start, INTERVAL + CONCAT(duration_hours, ':', duration_minutes) HOUR_MINUTE)";

	$db->query($meetingsSql);
	$db->query($callsSql);

    if(!empty($this->jobs))
    {
        $jobs = implode("','", $this->jobs);
        $db->query(sprintf("DELETE FROM job_queue WHERE id IN ('%s')", $jobs));
    }
}

function testUpgradeDateTimeFields() {

	upgradeDateTimeFields($GLOBALS['sugar_config']['log_file']);

	global $db;
	$query = "SELECT date_start, date_end FROM meetings WHERE id = '{$this->meeting->id}'";
	$result = $db->query($query);
	$row = $db->fetchByAssoc($result);
	$start_time = strtotime($row['date_start']);
	$end_time = strtotime($row['date_end']);
	$this->assertEquals(2.5*60*60, $end_time - $start_time, 'Assert that date_end in meetings table has been properly converted');

	$query = "SELECT date_start, date_end FROM calls WHERE id = '{$this->call->id}'";
	$result = $db->query($query);
	$row = $db->fetchByAssoc($result);
	$start_time = strtotime($row['date_start']);
	$end_time = strtotime($row['date_end']);
	$this->assertEquals(2.5*60*60, $end_time - $start_time,  'Assert that date_end in calls table has been properly converted');
}
//BEGIN SUGARCRM flav=pro ONLY
    /**
     * Check that for every old opportunity related products are created via job queue
     * @global type $current_user
	 * @group forecasts
     */
    function testCreateProductForOpp()
    {
        global $current_user;

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->assigned_user_id = $current_user->id;
        $opp->save();

        //unset opportunity_id in the product which was automatically created during opp save
        $product = BeanFactory::getBean('Products');
        $product->retrieve_by_string_fields(array('opportunity_id' => $opp->id));
        SugarTestProductUtilities::setCreatedProduct(array($product->id));
        $product->opportunity_id = '';
        $product->save();

        $this->jobs = createProductForOpp();

        $job = new SchedulersJob();
        $job->retrieve_by_string_fields(array('data' => $opp->id));
        $job->runnable_ran = true;
        $job->runnable_data = '';
        $job->runJob();

        $this->assertTrue($job->runnable_ran);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");

        $product = BeanFactory::getBean('Products');
        $product->retrieve_by_string_fields(array('opportunity_id' => $opp->id));
        SugarTestProductUtilities::setCreatedProduct(array($product->id));

        $this->assertEquals($opp->name, $product->name, "Product name doesn't equal to related opp's one");
        $this->assertEquals($opp->amount, $product->likely_case, "Product likely_case doesn't equal to related opp's one");
        $this->assertEquals($opp->best_case, $product->best_case, "Product best_case doesn't equal to related opp's one");
        $this->assertEquals($opp->worst_case, $product->worst_case, "Product worst_case doesn't equal to related opp's one");
        $this->assertEquals($opp->assigned_user_id, $product->expert_id, "Product expert_id doesn't equal to related opp's assigned_user_id");
    }
//END SUGARCRM flav=pro ONLY
}
?>