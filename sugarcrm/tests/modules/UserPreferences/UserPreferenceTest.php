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
require_once 'modules/Users/User.php';

class UserPreferenceTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    protected static $user;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        self::$user = SugarTestHelper::setUp('current_user', array(true, false));
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        global $current_user;
        $current_user = self::$user;
    }

    public function tearDown()
    {
        $_SESSION = array();
    }

    public function testSettingAUserPreferenceInSession()
    {
        self::$user->setPreference('test_pref', 'dog');

        $this->assertEquals('dog', self::$user->getPreference('test_pref'));
        $this->assertEquals('dog', $_SESSION[self::$user->user_name . '_PREFERENCES']['global']['test_pref']);
    }

    public function testGetUserDateTimePreferences()
    {
        $res = self::$user->getUserDateTimePreferences();
        $this->assertArrayHasKey('date', $res);
        $this->assertArrayHasKey('time', $res);
        $this->assertArrayHasKey('userGmt', $res);
        $this->assertArrayHasKey('userGmtOffset', $res);
    }

    public function testUpdateAllUserPrefs()
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $bean = new UserPreference();
        $result = $bean->updateAllUserPrefs('test_pref', 'Value');
        $this->assertEmpty($result);
    }

    public function testPreferenceLifeTime()
    {
        $bean = new UserPreference(self::$user);
        $bean->setPreference('test_pref', 'Value2');
        $this->assertEquals('Value2', self::$user->getPreference('test_pref'));
        $bean->removePreference('test_pref');
        $this->assertEmpty(self::$user->getPreference('test_pref'));
    }

    /**
     * @depends testSettingAUserPreferenceInSession
     */
    public function testResetPreferences()
    {
        self::$user->setPreference('reminder_time', 25);
        self::$user->setPreference('test_pref', 'Value3');
        self::$user->resetPreferences();
        $this->assertEquals(1800, self::$user->getPreference('reminder_time'));
        $this->assertEmpty(self::$user->getPreference('test_pref'));
    }
}
