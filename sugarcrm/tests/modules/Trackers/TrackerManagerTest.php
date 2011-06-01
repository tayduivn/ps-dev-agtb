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
 
class TrackerManagerTest extends Sugar_PHPUnit_Framework_TestCase {

	function setUp() {
		$user = new User();
		$user->retrieve('1');
		$GLOBALS['current_user'] = $user;
	}
	
    function tearDown()
    {
    	$trackerManager = TrackerManager::getInstance();
    	$trackerManager->unPause();
    	
		$user = new User();
		$user->retrieve('1');
		$GLOBALS['current_user'] = $user;    	
    }
    
    function testPausing() {
        $trackerManager = TrackerManager::getInstance();
        $trackerManager->unPause();
        $this->assertFalse($trackerManager->isPaused());
        $trackerManager->pause();
        $this->assertTrue($trackerManager->isPaused());
    }
    
    function testPausing2() {
        $query = "select count(id) as total from tracker";
    	$result = $GLOBALS['db']->query($query);
    	$count1 = 0;
		while($row = $GLOBALS['db']->fetchByAssoc($result)){
		      $count1 = $row['total'];
		}

		$trackerManager = TrackerManager::getInstance();
		$trackerManager->pause();
		
        $monitor = $trackerManager->getMonitor('tracker');         
        $monitor->setValue('module_name', 'Contacts');
        $monitor->setValue('item_id', '10909d69-2b55-094d-ba89-47b23d3121dd');
        $monitor->setValue('item_summary', 'Foo');
        $monitor->setValue('date_modified', TimeDate::getInstance()->nowDb(), strtotime("-1 day")+5000);
        $monitor->setValue('action', 'index');
        $monitor->setValue('session_id', 'test_session');
        $monitor->setValue('user_id', 1);
		//BEGIN SUGARCRM flav=pro ONLY
        $monitor->setValue('team_id', $GLOBALS['current_user']->getPrivateTeamID());
		//END SUGARCRM flav=pro ONLY
        $trackerManager->save();
        
        $count2 = 0;
        $query = "select count(id) as total from tracker";
    	$result = $GLOBALS['db']->query($query);        
    	while($row = $GLOBALS['db']->fetchByAssoc($result)){
		      $count2 = $row['total'];
		}
		$this->assertEquals($count1, $count2);		
    }
    
//BEGIN SUGARCRM flav=pro ONLY
    function testPausing3() {
    	
    	$query = "select count(id) as total from tracker_queries";
    	$result = $GLOBALS['db']->query($query);
    	$count1 = 0;
		while($row = $GLOBALS['db']->fetchByAssoc($result)){
		      $count1 = $row['total'];
		}

    	$dumpSlowQuery = $GLOBALS['sugar_config']['dump_slow_queries'];
    	$slowQueryTime = $GLOBALS['sugar_config']['slow_query_time_msec'];
    	$GLOBALS['sugar_config']['dump_slow_queries'] = true;
    	$GLOBALS['sugar_config']['slow_query_time_msec'] = 0;

        $trackerManager = TrackerManager::getInstance();
		$trackerManager->pause();	
		
        $count2 = 0;
        $query = "select count(id) as total from tracker_queries";
    	$result = $GLOBALS['db']->query($query);        
    	while($row = $GLOBALS['db']->fetchByAssoc($result)){
		      $count2 = $row['total'];
		}
		$this->assertEquals($count1, $count2);
		$GLOBALS['sugar_config']['dump_slow_queries'] = $dumpSlowQuery;
    	$GLOBALS['sugar_config']['slow_query_time_msec'] = $slowQueryTime;
    }    
//END SUGARCRM flav=pro ONLY

}  
?>