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

/**
 * @group 54858
 *
 */
class Bug54858Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->user->email1 = $email = 'test'.uniqid().'@test.com';
        $this->user->save();
        $GLOBALS['current_user'] = $this->user;
        $this->vcal_url =  "{$GLOBALS['sugar_config']['site_url']}/vcal_server.php/type=vfb&source=outlook&email={$email}";

    }

    public function tearDown()
    {
    	unset($GLOBALS['current_user']);
    	SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * Test that new user gets ical key
     */
    public function testCreateNewUser()
    {
        $this->assertNotEmpty($this->user->getPreference('calendar_publish_key'), "Publish key is not set");
    }

	protected function callVcal($key)
	{
       $ch = curl_init($this->vcal_url."&key=$key");
       curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       $return = curl_exec($ch);
	   $info = curl_getinfo($ch);
	   $info['return'] = $return;
	   return $info;
	}

	// test vcal service
    public function testPublishKey()
    {
        $res = $this->callVcal('');
		$this->assertEquals('401', $res['http_code']);

        $res = $this->callVcal('blah');
		$this->assertEquals('401', $res['http_code']);

		$key = $this->user->getPreference('calendar_publish_key');
        $res = $this->callVcal($key);
		$this->assertEquals('200', $res['http_code']);
		$this->assertContains('BEGIN:VCALENDAR', $res['return']);

		// now reset the key
        $this->user->setPreference('calendar_publish_key', '');
        $this->user->savePreferencesToDB();
        $GLOBALS['db']->commit();

        $res = $this->callVcal('');
		$this->assertEquals('401', $res['http_code']);
        $res = $this->callVcal($key);
		$this->assertEquals('401', $res['http_code']);
	}
}