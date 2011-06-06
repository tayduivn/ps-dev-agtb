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
 
require_once('modules/Trackers/TrackerManager.php');

class TrackerReportsUsageTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestTrackerUtility::setup();
        
        $trackerManager = TrackerManager::getInstance();
        $trackerManager->unPause();
        
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        
        //$tracker_sessions_monitor = $trackerManager->getMonitor('tracker_sessions');
        $monitor = $trackerManager->getMonitor('tracker');
        $monitor->setEnabled(true);
        $monitor->setValue('module_name', 'Contacts');
        $monitor->setValue('item_id', '10909d69-2b55-094d-ba89-47b23d3121dd');
        $monitor->setValue('item_summary', 'Foo');
        $monitor->setValue('date_modified', TimeDate::getInstance()->nowDb(), strtotime("-1 day")+5000);
        $monitor->setValue('action', 'index');
        $monitor->setValue('session_id', 'test_session');
        $monitor->setValue('user_id', $GLOBALS['current_user']->id);
        //BEGIN SUGARCRM flav=pro ONLY
        $monitor->setValue('team_id', $GLOBALS['current_user']->getPrivateTeamID());
        //END SUGARCRM flav=pro ONLY
        $trackerManager->save();
        
        $monitor->setValue('module_name', 'Contacts');
        $monitor->setValue('item_id', '10909d69-2b55-094d-ba89-47b23d3121dd');
        $monitor->setValue('item_summary', 'Foo');
        $monitor->setValue('date_modified', gmdate($GLOBALS['timedate']->get_db_date_time_format(), strtotime("-1 week")+5000));
        $monitor->setValue('action', 'index');
        $monitor->setValue('session_id', 'test_session');        
        $monitor->setValue('user_id', $GLOBALS['current_user']->id);
        //BEGIN SUGARCRM flav=pro ONLY
        $monitor->setValue('team_id', $GLOBALS['current_user']->getPrivateTeamID());
        //END SUGARCRM flav=pro ONLY
        $trackerManager->save();
       
        $monitor->setValue('module_name', 'Contacts');
        $monitor->setValue('item_id', '10909d69-2b55-094d-ba89-47b23d3121dd');
        $monitor->setValue('item_summary', 'Foo');
        $monitor->setValue('date_modified', gmdate($GLOBALS['timedate']->get_db_date_time_format(), strtotime("-1 month")+5000));
        $monitor->setValue('action', 'index');
        $monitor->setValue('session_id', 'test_session');
        $monitor->setValue('user_id', $GLOBALS['current_user']->id);            
        //BEGIN SUGARCRM flav=pro ONLY
        $monitor->setValue('team_id', $GLOBALS['current_user']->getPrivateTeamID());
        //END SUGARCRM flav=pro ONLY
        $trackerManager->save();

        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
    }
    
    public function tearDown()
    {
        $query = "DELETE FROM tracker WHERE session_id = 'test_session'";
        $GLOBALS['db']->query($query);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTrackerUtility::restore();
        
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    }
    
    public function testUsageMetricsDay()
    {
        $query = "SELECT module_name, item_id, item_summary, date_modified from tracker where session_id = 'test_session' and user_id = '{$GLOBALS['current_user']->id}' and date_modified > ";
        $query .= db_convert("'". gmdate($GLOBALS['timedate']->get_db_date_time_format(), strtotime("-1 day")) ."'" ,"datetime");
        $result = $GLOBALS['db']->query($query);
        $count = 0;
        while ( $row = $GLOBALS['db']->fetchByAssoc($result) ) $count++;
        $this->assertEquals($count,1);
    }
    
    public function testUsageMetricsWeek()
    {
        $query = "SELECT module_name, item_id, item_summary, date_modified from tracker where session_id = 'test_session' and user_id = '{$GLOBALS['current_user']->id}' and date_modified > ";
        $query .= db_convert("'". gmdate($GLOBALS['timedate']->get_db_date_time_format(), strtotime("-1 week")) ."'" ,"datetime");
        $result = $GLOBALS['db']->query($query);
        $count = 0;
        while ( $row = $GLOBALS['db']->fetchByAssoc($result) ) $count++;
        $this->assertEquals($count,2);
    }
    
    public function testUsageMetricsMonth()
    {
        $query = "SELECT module_name, item_id, item_summary, date_modified from tracker where session_id = 'test_session' and user_id = '{$GLOBALS['current_user']->id}' and date_modified > ";
        $query .= db_convert("'". gmdate($GLOBALS['timedate']->get_db_date_time_format(), strtotime("-1 month")) ."'" ,"datetime");
        $result = $GLOBALS['db']->query($query);
        $count = 0;
        while ( $row = $GLOBALS['db']->fetchByAssoc($result) ) $count++;
        $this->assertEquals($count,3);   	
    }
}

?>
