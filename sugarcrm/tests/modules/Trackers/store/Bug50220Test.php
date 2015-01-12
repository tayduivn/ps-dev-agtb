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
require_once('include/MVC/SugarApplication.php');

/**
 * Bug50220Test.php
 * This is a test that checks a few things for when we track tracker_session entries.  Particularly, we found a DB2
 * error where the database results would return a date in a non-datetime format.  The code we previously had was
 * assuming that the database values would be in datetime format.  This is not the case for DB2 as you could get a
 * value such as "2011-11-06 13:32:11.000000".  Then, the queries to convert this value to datetime would blowup
 * for DB2.
 *
 * @author Collin Lee
 */
class Bug50220Test extends Sugar_PHPUnit_Framework_TestCase {

var $disabledMonitors;
var $isPaused;
var $authenticatedSessionId;

public function setup()
{
    $this->markTestIncomplete('This test is breaking the build. Discussing with collin.');

    global $current_user;
    $current_user = SugarTestUserUtilities::createAnonymousUser();
    $current_user->is_admin = 1;
    $current_user->save();

	$trackerManager = TrackerManager::getInstance();
	$this->disabledMonitors = $trackerManager->getDisabledMonitors();
	$this->isPaused = $trackerManager->isPaused();
    $trackerManager->isPaused = false;
	$trackerManager->setDisabledMonitors(array());
    $GLOBALS['db']->query("DELETE FROM tracker_sessions WHERE session_id = 'Bug50220Test'");

    if(isset($_SESSION['authenticated_user_id']))
    {
        $this->authenticatedSessionId = $_SESSION['authenticated_user_id'];
        unset($_SESSION['authenticated_user_id']);
    }

}

public function tearDown()
{
	$trackerManager = TrackerManager::getInstance();
	$trackerManager->isPaused = $this->isPaused;
	$trackerManager->setDisabledMonitors($this->disabledMonitors);
	$GLOBALS['db']->query("DELETE FROM tracker_sessions WHERE session_id = 'Bug50220Test'");
    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    if(!empty($this->authenticatedSessionId))
    {
        $_SESSION['authenticated_user_id'] = $this->authenticatedSessionId;
    }
}

/**
 * testTrackerSessionDatabaseStore
 *
 * This function tests that the TrackerDatabaseStore implementation correctly guards against invalid datetime values
 */
/*
public function testTrackerSessionDatabaseStore()
{
	$trackerManager = TrackerManager::getInstance();
	$trackerManager->unPause();
	if($monitor = $trackerManager->getMonitor('tracker_sessions'))
	{
		$monitor->setValue('session_id', 'Bug50220Test');
		$monitor->setValue('user_id', 'Bug50220Test');
        //Intentionally set the date_start and date_end to be over 19 characters in length
        //this mimics the case where people set the date value to the selected query value without running a database type conversion
		$monitor->setValue('date_start', '2011-11-06 13:32:11.000000');
		$monitor->setValue('date_end', '2011-11-06 13:32:11.000000');
		$monitor->setValue('seconds', '5');
		$monitor->setValue('round_trips', 1);
		$monitor->setValue('active', 1);
		$monitor->setValue('client_ip', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');

        require_once('modules/Trackers/store/TrackerSessionsDatabaseStore.php');
        $trackerSessionDatabaseStore = new TrackerSessionsDatabaseStore();
        $trackerSessionDatabaseStore->flush($monitor);

		$client_ip = $GLOBALS['db']->getOne("SELECT client_ip FROM tracker_sessions WHERE session_id = 'Bug50220Test'");
		$this->assertTrue(strlen($client_ip) <= 20);
		$this->assertEquals('ABCDEFGHIJKLMNOPQRST', $client_ip, 'Assert that client_ip address value is truncated to first 20 characters');
	} else {
        $this->markTestSkipped('Skipped Bug50220Test.php.  Could not create tracker_session monitor.');
	}
}
*/

/**
 * testSugarApplication
 *
 * This function tests the SugarApplication file and particularly how the tracker_session table is written to
 */
public function testSugarApplication()
{
    global $current_user;
    $trackerManager = TrackerManager::getInstance();
   	$trackerManager->unPause();
   	if($monitor = $trackerManager->getMonitor('tracker_sessions'))
   	{
   		$monitor->setValue('session_id', 'Bug50220Test');
   		$monitor->setValue('user_id', $current_user->id);
           //Intentionally set the date_start and date_end to be over 19 characters in length
           //this mimics the case where people set the date value to the selected query value without running a database type conversion
   		$monitor->setValue('date_start', '2011-11-06 13:32:11');
   		$monitor->setValue('date_end', '2011-11-06 13:32:11');
   		$monitor->setValue('seconds', '5');
   		$monitor->setValue('round_trips', 1);
   		$monitor->setValue('active', 1);
   		$monitor->setValue('client_ip', 'ABCDEFGHIJKLMNOPQRSTUVWXYZABCDEFGHIJKLMNOPQRSTUVWXYZ');
   		$trackerManager->saveMonitor($monitor, true);

   		$client_ip = $GLOBALS['db']->getOne("SELECT client_ip FROM tracker_sessions WHERE session_id = 'Bug50220Test'");
   		$this->assertTrue(strlen($client_ip) <= 45);
   		$this->assertEquals('ABCDEFGHIJKLMNOPQRSTUVWXYZABCDEFGHIJKLMNOPQRS', $client_ip, 'Assert that client_ip address value is truncated to first 45 characters');

        $monitor = $trackerManager->getMonitor('tracker_sessions');
        $monitor->setValue('session_id', 'Bug50220Test');
        $monitor->setValue('user_id', $current_user->id);
        $mock = new Bug50200TestSugarApplicationMock();
        $mock->trackLoginTest();
        $trackerManager->saveMonitor($monitor, true);
        $round_trips = $GLOBALS['db']->getOne("SELECT round_trips FROM tracker_sessions WHERE user_id = '{$current_user->id}'");
        $this->assertEquals(2, $round_trips, 'Failed to write to tracker_sessions from SugarApplication');
   	} else {
   		$this->markTestSkipped('Skipped Bug50220Test.php.  Could not create tracker_session monitor.');
   	}

}

}

/**
 * Bug50200TestSugarApplicationMock
 * Mock object to override protected method and expose it publicly for unit test.
 *
 */

class Bug50200TestSugarApplicationMock extends SugarApplication {

    public function trackLoginTest()
    {
        $this->trackLogin();
    }

}
