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
 
class TrackerMonitorTest extends Sugar_PHPUnit_Framework_TestCase {

    function setUp() {
    	$trackerManager = TrackerManager::getInstance();
        $trackerManager->unsetMonitors();
    }    
    
    function tearDown() {

    }
    
    function testValidMonitors() {
        $trackerManager = TrackerManager::getInstance();
        $exceptionThrown = false;
        try {
	        $monitor = $trackerManager->getMonitor('tracker');
	        $monitor2 = $trackerManager->getMonitor('tracker_queries');
	        $monitor3 = $trackerManager->getMonitor('tracker_perf');
	        $monitor4 = $trackerManager->getMonitor('tracker_sessions');
	        $monitor5 = $trackerManager->getMonitor('tracker_tracker_queries');	
        } catch (Exception $ex) {
        	$exceptionThrown = true;
        }
        $this->assertFalse($exceptionThrown);
    }

    function testInvalidMonitors() {
        $trackerManager = TrackerManager::getInstance();
        $exceptionThrown = false;
	    $monitor = $trackerManager->getMonitor('invalid_tracker');
	    $this->assertTrue(get_class($monitor) == 'BlankMonitor');
    }
            
    function testInvalidValue() {        
        $trackerManager = TrackerManager::getInstance();
        $monitor = $trackerManager->getMonitor('tracker');
        $exceptionThrown = false;
        try {
          $monitor->setValue('invalid_column', 'foo');
        } catch (Exception $exception) {
          $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown);
    } 
     
}  
?>