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
 * Portions created by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once 'modules/Schedulers/Scheduler.php';

/**
 * Bug44819Test
 * Test Scheduler static function initUser() which returns a valid admin user
 * 
 * @author avucinic@sugarcrm.com
 */
class Bug44819Test extends Sugar_PHPUnit_Framework_TestCase
{

	public function setUp()
    {
    	// Create admin user
    	SugarTestUserUtilities::createAnonymousUser(true, 1);
    }

    public function tearDown()
    {
		// Clear the admin user created
    	SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

	public function testInitUser() {
		// Check if the initUser() function returns an Admin user
		$user = Scheduler::initUser();
		$this->assertNotEquals(false, $user, "No admnin users found in the system.");
		$this->assertEquals(1, $user->is_admin, "User returned is not admin.");
		$this->assertEquals("Active", $user->status, "User returned is not active.");
	}

}
