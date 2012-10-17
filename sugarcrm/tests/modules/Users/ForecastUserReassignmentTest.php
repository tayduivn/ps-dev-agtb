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


require_once('modules/Forecasts/ForecastsSeedData.php');
require_once('modules/Forecasts/WorksheetSeedData.php');

require_once('install/seed_data/ForecastTreeSeedData.php');

/**
 * Bug nutmeg:sfa-219
 * Fix reassignment of records when user set to Inactive
 *
 * @ticket sfa-219
 */
class ForecastUserReassignmentTest extends  Sugar_PHPUnit_Framework_TestCase
{
    private $_users;
    private $_users_ids;
    private $_users_opps;
    private $_users_worksheets_count;
    private $_timeperiod;

    /**
     * create user
     * @param $user
     * @param null $report_user
     */
    private function _createUser($user, $report_user = null)
    {
        $this->_users[$user] = SugarTestUserUtilities::createAnonymousUser($save = false, $is_admin=0);
        $this->_users[$user]->id = create_guid();
        $this->_users[$user]->new_with_id = true;
        $this->_users[$user]->user_name = $user;
        $this->_users[$user]->first_name = $user;
        $this->_users[$user]->reports_to_id =  $report_user && isset($this->_users[$report_user]) ?  $this->_users[$report_user]->id : null;
        $this->_users[$user]->save();
        $this->_users_ids[] = $this->_users[$user]->id;
    }

    /**
     * create opportunities for the user
     * @param $user
     * @param $count
     */
    private function _createOpportunityForUser($user, $count)
    {
        for ( $i = 0; $i < $count; $i++ )
        {
            $opp = SugarTestOpportunityUtilities::createOpportunity();
            $opp->assigned_user_id = $this->_users[$user]->id;
            $opp->save();
            $this->_users_opps[$user][] = $opp;
        }

    }

    /**
     * return count of opportunities for user
     * @param $user
     * @return int
     */
    private function _getOpportunitiesCountForUser($user)
    {
        $db = DBManagerFactory::getInstance();
        $row = $db->fetchOne("SELECT count(*) as cnt FROM opportunities WHERE assigned_user_id = '".$this->_users[$user]->id."' and deleted = '0'");
        return $row['cnt'];

    }

    /**
     * return count of products for user
     * @param $user
     * @return int
     */
    private function _getProductsCountForUser($user)
    {
        $db = DBManagerFactory::getInstance();
        $row = $db->fetchOne("SELECT count(*) as cnt FROM products WHERE assigned_user_id = '".$this->_users[$user]->id."' and deleted = '0'");
        return $row['cnt'];

    }

    /**
     * return count of worksheets for user
     * @param $user
     * @return int
     */
    private function _getWorksheetsCountForUser($user)
    {
        $db = DBManagerFactory::getInstance();
        $row = $db->fetchOne("SELECT count(*) as cnt FROM worksheet WHERE user_id = '".$this->_users[$user]->id."' and deleted = '0'");
        return $row['cnt'];

    }

    /**
     * return count of forecasts for user
     * @param $user
     * @return int
     */
    private function _getForecastsCountForUser($user)
    {
        $db = DBManagerFactory::getInstance();
        $row = $db->fetchOne("SELECT count(*) as cnt FROM forecasts WHERE user_id = '".$this->_users[$user]->id."' and deleted = '0'");
        return $row['cnt'];

    }

    /**
     * return count of forecast schedules for user
     * @param $user
     * @return int
     */
    private function _getForecastScheduleCountForUser($user)
    {
        $db = DBManagerFactory::getInstance();
        $row = $db->fetchOne("SELECT count(*) as cnt FROM forecast_schedule WHERE user_id = '".$this->_users[$user]->id."' and deleted = '0'");
        return $row['cnt'];

    }

    /**
     * return count of quotas for user
     * @param $user
     * @return int
     */
    private function _getQuotasCountForUser($user)
    {
        $db = DBManagerFactory::getInstance();
        $row = $db->fetchOne("SELECT count(*) as cnt FROM quotas WHERE user_id = '".$this->_users[$user]->id."' and deleted = '0'");
        return $row['cnt'];

    }

    /**
     * return information about user's reportees
     * @param $user
     * @return array
     */
    private function _getReporteesForUser($user)
    {
        $userID = $this->_users[$user]->id;

        require_once('include/SugarForecasting/ReportingUsers.php');
        $object = new SugarForecasting_ReportingUsers( array('user_id' => $this->_users['sarah']->id) );
        $return = $object->process();

        $children = array();
        if ( isset($return['children']) )
        {
            $children = $return['children'];
        }
        else
        {
            foreach ( $return as $reply )
            {
                if ( $reply['metadata'] && $reply['metadata']['id'] == $userID )
                {
                    $children = $reply['children'];
                }
            }
        }
        return $children;
    }

    /**
     * call action reassignUserRecords
     * @param $fromUser
     * @param $toUser
     */
    private function _doReassign($fromUser, $toUser)
    {
        $_SESSION['reassignRecords'] = array();
        $_SESSION['reassignRecords']['assignedModuleListCache'] = array('ForecastWorksheets' => 'ForecastWorksheet');
        $_SESSION['reassignRecords']['assignedModuleListCacheDisp'] = array ('ForecastWorksheets' => 'ForecastWorksheet');

        $_POST = $_GET = array();
        $_POST['module'] = 'Users';
        $_POST['action'] = 'reassignUserRecords';
        $_POST['fromuser'] = $this->_users[$fromUser]->id;
        $_POST['touser'] = $this->_users[$toUser]->id;
        $_POST['modules'] = array('ForecastWorksheet');
        $_POST['steponesubmit'] = 'Next';
        unset($_GET['execute']);

        global $app_list_strings, $beanFiles, $beanList, $current_user, $mod_strings, $app_strings;
        include('modules/Users/reassignUserRecords.php');

        $_GET['execute'] = true;
        include('modules/Users/reassignUserRecords.php');
    }

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');

        //SugarTestHelper::setUp('mod_strings', array('Calls'));

        $this->_createUser('jim');
        $this->_createUser('sarah', 'jim');
        $this->_createUser('will', 'jim');
        $this->_createUser('sally', 'sarah');
        $this->_createUser('max', 'sarah');
        $this->_createUser('chris', 'will');
        SugarTestHelper::setUp('current_user', array(true, 1));

        $this->_timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();

        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM forecasts WHERE timeperiod_id = '{$this->_timeperiod->id}'");
        $db->query("DELETE FROM forecast_schedule WHERE timeperiod_id = '{$this->_timeperiod->id}'");
        $db->query("DELETE FROM quotas WHERE timeperiod_id = '{$this->_timeperiod->id}'");

        unset($this->_users, $this->_users_ids, $this->_users_opps, $this->_users_worksheets_count, $this->_timeperiod);
        SugarTestHelper::tearDown();
    }

    /**
     * test reassignment rep to rep
     * @group forecast
     * @outputBuffering enabled
     */
    public function testReassignRepToRep()
    {
        $this->_createOpportunityForUser('sally', 10);
        $this->_created_items = ForecastsSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        $worksheets_ids = WorksheetSeedData::populateSeedData();
        SugarTestWorksheetUtilities::setCreatedWorksheet($worksheets_ids);

        $count = $this->_getOpportunitiesCountForUser('sally');
        $this->assertEquals(10, $count);
        $count = $this->_getProductsCountForUser('sally');
        $this->assertEquals(10, $count);

        $expected['worksheets'] = $this->_getWorksheetsCountForUser('sally');
        $expected['opportunities'] = sizeof($this->_users_opps['sally']);

        $this->_doReassign('sally', 'chris');

        // from sally
        $count = $this->_getOpportunitiesCountForUser('sally');
        $this->assertEquals(0, $count, 'Opportunities are not ressigned.');
        $count = $this->_getProductsCountForUser('sally');
        $this->assertEquals(0, $count, 'Products are not ressigned.');
        $count = $this->_getWorksheetsCountForUser('sally');
        $this->assertEquals(0, $count, 'Worksheets are not ressigned.');
        $count = $this->_getForecastsCountForUser('sally');
        $this->assertEquals(0, $count, 'Forecasts are not deleted.');
        $count = $this->_getForecastScheduleCountForUser('sally');
        $this->assertEquals(0, $count, 'ForecastSchedule are not deleted.');
        $count = $this->_getQuotasCountForUser('sally');
        $this->assertEquals(0, $count, 'Quotas are not deleted.');
        // to chris
        $count = $this->_getOpportunitiesCountForUser('chris');
        $this->assertEquals($expected['opportunities'], $count, 'Opportunities are not ressigned.');
        $count = $this->_getProductsCountForUser('chris');
        $this->assertEquals($expected['opportunities'], $count, 'Products are not ressigned.');
        $count = $this->_getWorksheetsCountForUser('chris');
        $this->assertEquals($expected['worksheets'], $count, 'Worksheets are not ressigned.');
    }

    /**
     * test reassignment manager to manager
     * @group forecasts
     * @outputBuffering enabled
     */
    public function testReassignManagerToManager()
    {
        $this->_createOpportunityForUser('sarah', 10);
        $this->_created_items = ForecastsSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        $worksheets_ids = WorksheetSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        SugarTestWorksheetUtilities::setCreatedWorksheet($worksheets_ids);

        $count = $this->_getOpportunitiesCountForUser('sarah');
        $this->assertEquals(10, $count);
        $count = $this->_getProductsCountForUser('sarah');
        $this->assertEquals(10, $count);

        $expected['worksheets'] = $this->_getWorksheetsCountForUser('sarah') - 1; // without worksheet created by sharah for himseft from manager view
        $expected['opportunities'] = sizeof($this->_users_opps['sarah']);

        $this->_doReassign('sarah', 'will');

        // from sarah
        $count = $this->_getOpportunitiesCountForUser('sarah');
        $this->assertEquals(0, $count, 'Opportunities are not ressigned.');
        $count = $this->_getProductsCountForUser('sarah');
        $this->assertEquals(0, $count, 'Products are not ressigned.');
        $count = $this->_getWorksheetsCountForUser('sarah');
        $this->assertEquals(0, $count, 'Worksheets are not ressigned.');
        $count = $this->_getForecastsCountForUser('sarah');
        $this->assertEquals(0, $count, 'Forecasts are not deleted.');
        $count = $this->_getForecastScheduleCountForUser('sarah');
        $this->assertEquals(0, $count, 'ForecastSchedule are not deleted.');
        $count = $this->_getQuotasCountForUser('sarah');
        $this->assertEquals(0, $count, 'Quotas are not deleted.');

        // to will
        $count = $this->_getOpportunitiesCountForUser('will');
        $this->assertEquals($expected['opportunities'], $count, 'Opportunities are not ressigned.');
        $count = $this->_getProductsCountForUser('will');
        $this->assertEquals($expected['opportunities'], $count, 'Products are not ressigned.');
        $count = $this->_getWorksheetsCountForUser('will');
        $this->assertEquals($expected['worksheets'], $count, 'Worksheets are not ressigned.');
    }

    /**
     * test reassignment manager to manager
     * @group forecast
     * @outputBuffering enabled
     */
    public function testReassignManagerToRep()
    {
        $this->_createOpportunityForUser('sarah', 10);
        $this->_created_items = ForecastsSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        $worksheets_ids = WorksheetSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        SugarTestWorksheetUtilities::setCreatedWorksheet($worksheets_ids);

        $count = $this->_getOpportunitiesCountForUser('sarah');
        $this->assertEquals(10, $count);
        $count = $this->_getProductsCountForUser('sarah');
        $this->assertEquals(10, $count);

        $expected['worksheets'] = $this->_getWorksheetsCountForUser('sarah') - 1; // without worksheet created by sharah for himseft from manager view
        $expected['opportunities'] = sizeof($this->_users_opps['sarah']);

        $this->_doReassign('sarah', 'sally');

        // from sarah
        $count = $this->_getOpportunitiesCountForUser('sarah');
        $this->assertEquals(0, $count, 'Opportunities are not ressigned.');
        $count = $this->_getProductsCountForUser('sarah');
        $this->assertEquals(0, $count, 'Products are not ressigned.');
        $count = $this->_getWorksheetsCountForUser('sarah');
        $this->assertEquals(0, $count, 'Worksheets are not ressigned.');
        $count = $this->_getForecastsCountForUser('sarah');
        $this->assertEquals(0, $count, 'Forecasts are not deleted.');
        $count = $this->_getForecastScheduleCountForUser('sarah');
        $this->assertEquals(0, $count, 'ForecastSchedule are not deleted.');
        $count = $this->_getQuotasCountForUser('sarah');
        $this->assertEquals(0, $count, 'Quotas are not deleted.');

        // to will
        $count = $this->_getOpportunitiesCountForUser('sally');
        $this->assertEquals($expected['opportunities'], $count, 'Opportunities are not ressigned.');
        $count = $this->_getProductsCountForUser('sally');
        $this->assertEquals($expected['opportunities'], $count, 'Products are not ressigned.');
        $count = $this->_getWorksheetsCountForUser('sally');
        $this->assertEquals($expected['worksheets'], $count, 'Worksheets are not ressigned.');

        $objSally = new User();
        $objSally->retrieve($this->_users['sally']->id);
        $this->assertEquals($this->_users['sarah']->reports_to_id, $objSally->reports_to_id );
    }

    /**
     * test user's reportees if some user became inactive
     * @group forecast
     */
    public function testInactiveChildren()
    {
        global $current_user;

        $this->_createOpportunityForUser('sarah', 10);

        $children = $this->_getReporteesForUser('sarah');
        $this->assertEquals(3, sizeof($children)); // sally, max and opportunities

        $this->_users['sally']->status = 'Inactive';
        $this->_users['sally']->save();
        $children = $this->_getReporteesForUser('sarah');
        $this->assertEquals(2, sizeof($children)); // max and opportunities

        $this->_users['max']->status = 'Inactive';
        $this->_users['max']->save();
        $children = $this->_getReporteesForUser('sarah');
        $this->assertEquals(0, sizeof($children));
    }

    /**
     * test user's reportees if some user became deleted
     * @group forecast
     */
    public function testDeletedChildren()
    {
        global $current_user;

        $this->_createOpportunityForUser('sarah', 10);

        $children = $this->_getReporteesForUser('sarah');
        $this->assertEquals(3, sizeof($children)); // sally, max and opportunities

        $this->_users['sally']->deleted = 1;
        $this->_users['sally']->save();
        $children = $this->_getReporteesForUser('sarah');
        $this->assertEquals(2, sizeof($children)); // max and opportunities

        $this->_users['max']->deleted = 1;
        $this->_users['max']->save();
        $children = $this->_getReporteesForUser('sarah');
        $this->assertEquals(0, sizeof($children));
    }

    /**
     * test worksheets after reassignment rep to rep
     * @outputBuffering enabled
     * @group forecasts
     */
    public function testWorksheetRepToRep()
    {
        $this->_createOpportunityForUser('sally', 10);
        $this->_created_items = ForecastsSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        $worksheets_ids = WorksheetSeedData::populateSeedData();
        SugarTestWorksheetUtilities::setCreatedWorksheet($worksheets_ids);

        require_once('include/SugarForecasting/Individual.php');

        $api = new SugarForecasting_Individual( array('timeperiod_id' => $this->_timeperiod->id, 'user_id' => $this->_users['sally']->id) );
        $result = $api->process();
        $this->assertEquals(10, sizeof($result));

        $this->_doReassign('sally', 'chris');

        $api = new SugarForecasting_Individual( array('timeperiod_id' => $this->_timeperiod->id, 'user_id' => $this->_users['chris']->id) );
        $result = $api->process();
        $this->assertEquals(10, sizeof($result));

        $api = new SugarForecasting_Individual( array('timeperiod_id' => $this->_timeperiod->id, 'user_id' => $this->_users['sally']->id) );
        $result = $api->process();
        $this->assertEquals(0, sizeof($result));

    }

    /**
     * test worksheets after reassignment manager to manager
     * @outputBuffering enabled
     * @group forecasts
     */
    public function testWorksheetManagerToManager()
    {
        $this->_createOpportunityForUser('sarah', 10);
        $this->_created_items = ForecastsSeedData::populateSeedData( array($this->_timeperiod->id => $this->_timeperiod) );
        $worksheets_ids = WorksheetSeedData::populateSeedData();
        SugarTestWorksheetUtilities::setCreatedWorksheet($worksheets_ids);

        require_once('include/SugarForecasting/Manager.php');

        $api = new SugarForecasting_Manager( array('timeperiod_id' => $this->_timeperiod->id, 'user_id' => $this->_users['sarah']->id) );
        $result = $api->process();
        $this->assertEquals(3, sizeof($result)); // 3 sarah's opps + sally + max

        $this->_doReassign('sarah', 'will');

        $api = new SugarForecasting_Manager( array('timeperiod_id' => $this->_timeperiod->id, 'user_id' => $this->_users['will']->id) );
        $result = $api->process();
        $this->assertEquals(4, sizeof($result)); // 4 = will's opps + chris + (sally + max)

        $api = new SugarForecasting_Manager( array('timeperiod_id' => $this->_timeperiod->id, 'user_id' => $this->_users['sarah']->id) );
        $result = $api->process();
        $this->assertEquals(1, sizeof($result)); // sarah opps only
    }

    /**
     * @outputBuffering enabled
     * @group forecasts
     */
    public function testReportsToSarahToSally()
    {
        $this->_doReassign('sarah', 'sally');

        $objJim = new User();
        $objJim->retrieve($this->_users['jim']->id);
        $this->assertEmpty($objJim->reports_to_id, 'Jim report_to_id is not empty');

        $objSarah = new User();
        $objSarah->retrieve($this->_users['sarah']->id);
        $this->assertEmpty($objSarah->reports_to_id, 'Sarah report_to_id is not empty');

        $objSally = new User();
        $objSally->retrieve($this->_users['sally']->id);
        $this->assertEquals($this->_users['jim']->id, $objSally->reports_to_id, 'Sally does not report to Jim');

        $objMax = new User();
        $objMax->retrieve($this->_users['max']->id);
        $this->assertEquals($this->_users['sally']->id, $objMax->reports_to_id, 'Max does not report to Sally');

        $objWill = new User();
        $objWill->retrieve($this->_users['will']->id);
        $this->assertEquals($this->_users['jim']->id, $objWill->reports_to_id, 'Will does not report to Jim');

        $objChris = new User();
        $objChris->retrieve($this->_users['chris']->id);
        $this->assertEquals($this->_users['will']->id, $objChris->reports_to_id, 'Chris does not report to Will');

    }

    /**
     * @outputBuffering enabled
     * @group forecasts
     */
    public function testReportsToJimToSally()
    {
        $this->_doReassign('jim', 'sally');

        $objJim = new User();
        $objJim->retrieve($this->_users['jim']->id);
        $this->assertEmpty($objJim->reports_to_id, 'Jim report_to_id is not empty');

        $objSally = new User();
        $objSally->retrieve($this->_users['sally']->id);
        $this->assertEmpty($objSally->reports_to_id, 'Sally report_to_id is not empty');

        $objSarah = new User();
        $objSarah->retrieve($this->_users['sarah']->id);
        $this->assertEquals($this->_users['sally']->id, $objSarah->reports_to_id, 'Sarah does not report to Sally');

        $objMax = new User();
        $objMax->retrieve($this->_users['max']->id);
        $this->assertEquals($this->_users['sarah']->id, $objMax->reports_to_id, 'Max does not report to Sarah');

        $objWill = new User();
        $objWill->retrieve($this->_users['will']->id);
        $this->assertEquals($this->_users['sally']->id, $objWill->reports_to_id, 'Will does not report to Sally');

        $objChris = new User();
        $objChris->retrieve($this->_users['chris']->id);
        $this->assertEquals($this->_users['will']->id, $objChris->reports_to_id, 'Chris does not report to Will');

    }

    /**
     * @outputBuffering enabled
     * @group forecasts
     */
    public function testReportsToSallyToChris()
    {
        $this->_doReassign('sally', 'chris');

        $objSally = new User();
        $objSally->retrieve($this->_users['sally']->id);
        $this->assertEmpty($objSally->reports_to_id, 'Sally report_to_id is not empty');

        $objChris = new User();
        $objChris->retrieve($this->_users['chris']->id);
        $this->assertEquals($this->_users['will']->id, $objChris->reports_to_id, 'Chris does not report to Will');

        $objWill = new User();
        $objWill->retrieve($this->_users['will']->id);
        $this->assertEquals($this->_users['jim']->id, $objWill->reports_to_id, 'Will does not report to Jim');

        $objMax = new User();
        $objMax->retrieve($this->_users['max']->id);
        $this->assertEquals($this->_users['sarah']->id, $objMax->reports_to_id, 'Max does not report to Sarah');

        $objSarah = new User();
        $objSarah->retrieve($this->_users['sarah']->id);
        $this->assertEquals($this->_users['jim']->id, $objSarah->reports_to_id, 'Sarah does not report to Jim');
    }

    /**
     * @outputBuffering enabled
     * @group forecasts
     */
    public function testReportsToSarahToSallyAndThenJimToSally()
    {
        $this->_doReassign('sarah', 'sally');
        $this->_doReassign('jim', 'sally');

        $objSarah = new User();
        $objSarah->retrieve($this->_users['sarah']->id);
        $this->assertEmpty($objSarah->reports_to_id, 'Sarah report_to_id is not empty');

        $objJim = new User();
        $objJim->retrieve($this->_users['jim']->id);
        $this->assertEmpty($objJim->reports_to_id, 'Jim report_to_id is not empty');

        $objSally = new User();
        $objSally->retrieve($this->_users['sally']->id);
        $this->assertEmpty($objSally->reports_to_id, 'Sally report_to_id is not empty');

        $objMax = new User();
        $objMax->retrieve($this->_users['max']->id);
        $this->assertEquals($this->_users['sally']->id, $objMax->reports_to_id, 'Max does not report to Sally');
    }


    /**
     * @outputBuffering enabled
     * @group forecasts
     */
    public function testReportsToSarahToWill()
    {
        $this->_doReassign('sarah', 'will');

        $objSarah = new User();
        $objSarah->retrieve($this->_users['sarah']->id);
        $this->assertEmpty($objSarah->reports_to_id, 'Sarah report_to_id is not empty');

        $objSally = new User();
        $objSally->retrieve($this->_users['sally']->id);
        $this->assertEquals($this->_users['will']->id, $objSally->reports_to_id, 'Sally does not report to Will');

        $objMax = new User();
        $objMax->retrieve($this->_users['max']->id);
        $this->assertEquals($this->_users['will']->id, $objMax->reports_to_id, 'Sally does not report to Will');

        $objChris = new User();
        $objChris->retrieve($this->_users['chris']->id);
        $this->assertEquals($this->_users['will']->id, $objChris->reports_to_id, 'Chris does not report to Will');

        $objWill = new User();
        $objWill->retrieve($this->_users['will']->id);
        $this->assertEquals($this->_users['jim']->id, $objWill->reports_to_id, 'Will does not report to Jim');

    }
}
