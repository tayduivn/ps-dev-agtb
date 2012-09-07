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
//FILE SUGARCRM lic=sub ONLY
require_once 'modules/Users/User.php';

/**
 * @ticket 47152
 */
class Bug47152Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
	public $_user = null;

	protected static $admin_settings;

	static public function setUpBeforeClass()
    {
        $admin = new Administration();
        $admin->retrieveSettings(false, true);
        self::$admin_settings = $admin->settings;
        if(!isset($GLOBALS['current_language'])) {
            $GLOBALS['current_language'] = 'en_us';
        }
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Users');
    }

	static public function tearDownAfterClass()
    {
        $admin = new Administration();
        $admin->settings = self::$admin_settings;
        $admin->saveSetting("license", "enforce_user_limit", $admin->settings["license_enforce_user_limit"]);
        $admin->saveSetting("license", "users", $admin->settings["license_users"]);
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['app_strings']);
        unset($GLOBALS['request_string']);
	}

	public function setUp()
	{
        $this->markTestIncomplete('Temporarily marking test as incomplete to debug DB2 failing test');
        return;
    	$GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    	$GLOBALS['current_user']->is_admin = true;
	    $this->admin = new Administration();
	    $this->admin->retrieveSettings();
        if(isset($_SESSION['license_seats_needed']))
        {
	        unset($_SESSION['license_seats_needed']);
        }
	    $_SESSION['EXCEEDS_MAX_USERS'] = 0;
        if(isset($_SESSION['authenticated_user_id']))
        {
	        unset($_SESSION['authenticated_user_id']);
        }
	}

	public function tearDown()
	{
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($_SESSION['EXCEEDS_MAX_USERS']);
	}

	protected function checkWarnings($expect)
	{
        global $sugar_config, $theme, $current_user, $sugar_version, $sugar_flavor, $mod_strings, $app_strings, $app_list_strings, $action;
        global $gridline, $request_string, $modListHeader, $dashletData, $authController, $locale, $currentModule, $import_bean_map, $image_path, $license;
        global $user_unique_key, $server_unique_key, $barChartColors, $modules_exempt_from_availability_check, $dictionary, $current_language, $beanList, $beanFiles, $sugar_build, $sugar_codename;
        global $timedate, $login_error; // cn: bug 13855 - timedate not available to classic views.
        $admin = $this->admin;
        $this->admin->retrieveSettings();
        include 'sugar_version.php';
        include "modules/Users/Login.php";
        if($expect) {
            $this->assertEquals(1, $_SESSION['EXCEEDS_MAX_USERS']);
        } else {
            $this->assertEquals(0, $_SESSION['EXCEEDS_MAX_USERS']);
        }
	}

    public function testNoEnforceSaveExtraUser()
    {
        global $current_user;
        $this->admin->saveSetting("license", "enforce_user_limit", 0);
        $this->admin->saveSetting("license", "users", 1);
        $newuser = SugarTestUserUtilities::createAnonymousUser();
        $this->checkWarnings(false);
    }

	public function testSaveExtraUser()
    {
        global $current_user;
        $this->admin->saveSetting("license", "enforce_user_limit", 0);
        $current_count = count( get_user_array(false, "", "", false, null, " AND ".User::getLicensedUsersWhere(), false));
        $this->admin->saveSetting("license", "users", $current_count);
        $newuser = SugarTestUserUtilities::createAnonymousUser();
        $this->admin->saveSetting("license", "enforce_user_limit", 1);
        $this->checkWarnings(true);
    }

	public function testEmptyUsernameIgnored()
    {
        global $current_user;
        $this->admin->saveSetting("license", "enforce_user_limit", 0);
        $current_count = count( get_user_array(false, "", "", false, null, " AND ".User::getLicensedUsersWhere(), false));
        $this->admin->saveSetting("license", "users", $current_count);
        $newuser = SugarTestUserUtilities::createAnonymousUser();
        $newuser->user_name = '';
        $newuser->save();
        $this->admin->saveSetting("license", "enforce_user_limit", 1);
        $this->checkWarnings(false);
    }

    public function testPortalOnlyIgnored()
    {
        global $current_user;
        $this->admin->saveSetting("license", "enforce_user_limit", 0);
        $current_count = count( get_user_array(false, "", "", false, null, " AND ".User::getLicensedUsersWhere(), false));
        $this->admin->saveSetting("license", "users", $current_count);
        $newuser = SugarTestUserUtilities::createAnonymousUser();
        $newuser->portal_only = 1;
        $newuser->save();
        $this->admin->saveSetting("license", "enforce_user_limit", 1);
        $this->checkWarnings(false);
    }

    public function testGroupIgnored()
    {
        global $current_user;
        $this->admin->saveSetting("license", "enforce_user_limit", 0);
        $current_count = count( get_user_array(false, "", "", false, null, " AND ".User::getLicensedUsersWhere(), false));
        $this->admin->saveSetting("license", "users", $current_count);
        $newuser = SugarTestUserUtilities::createAnonymousUser();
        $newuser->is_group = 1;
        $newuser->save();
        $this->admin->saveSetting("license", "enforce_user_limit", 1);
        $this->checkWarnings(false);
    }
}

