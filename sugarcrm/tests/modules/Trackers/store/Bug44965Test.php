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
 
require_once('modules/Trackers/store/TrackerSessionsDatabaseStore.php');
require_once('modules/Trackers/TrackerManager.php');

class Bug44965Test extends Sugar_PHPUnit_Framework_TestCase {
	
var $disabledMonitors;	
var $isPaused;

public function setup()
{
	$trackerManager = TrackerManager::getInstance();
	$this->disabledMonitors = $trackerManager->getDisabledMonitors();
	$this->isPaused = $trackerManager->isPaused();
    $trackerManager->isPaused = false;	
	$trackerManager->setDisabledMonitors(array());
    $GLOBALS['db']->query("DELETE FROM tracker_sessions WHERE session_id = 'Bug44965Test'");
}

public function tearDown()
{
	$trackerManager = TrackerManager::getInstance();
	$trackerManager->isPaused = $this->isPaused;
	$trackerManager->setDisabledMonitors($this->disabledMonitors);
	$GLOBALS['db']->query("DELETE FROM tracker_sessions WHERE session_id = 'Bug44965Test'");
}

/**
 * Bug #42557
 * IPv6 has max length of 45 chars
 */
public function testTrackerSessionDatabaseStore()
{
	$trackerManager = TrackerManager::getInstance(); 
	$trackerManager->unPause();
	if($monitor = $trackerManager->getMonitor('tracker_sessions'))
	{
		$monitor->setValue('session_id', 'Bug44965Test'); 
		$monitor->setValue('user_id', 'Bug44965Test');
		$monitor->setValue('date_start', TimeDate::getInstance()->nowDb());
		$monitor->setValue('date_end', TimeDate::getInstance()->nowDb());
		$monitor->setValue('seconds', '5');
		$monitor->setValue('round_trips', 1);
		$monitor->setValue('active', 0);
		$monitor->setValue('client_ip', '12345678901234567890123456789012345678901234567890');
		$trackerManager->saveMonitor($monitor, true);
		
		$client_ip = $GLOBALS['db']->getOne("SELECT client_ip FROM tracker_sessions WHERE session_id = 'Bug44965Test'");
        $this->assertLessThanOrEqual(45, strlen($client_ip));
        $this->assertEquals('123456789012345678901234567890123456789012345', $client_ip, 'Assert that client_ip address value is truncated to first 45 characters');
	} else {
		$this->markTestSkipped = true;
	}	
}

}
