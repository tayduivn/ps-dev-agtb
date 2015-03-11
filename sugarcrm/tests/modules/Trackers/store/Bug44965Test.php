<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
 
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
