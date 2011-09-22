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

class Bug47025Test extends Sugar_PHPUnit_Framework_TestCase  {

var $user;

public function setUp()
{
    //Set all Users to have deleted to 2 so we only test one user
    $GLOBALS['db']->query('UPDATE users SET deleted = 2 WHERE deleted = 0');
    global $current_user;
    $current_user = SugarTestUserUtilities::createAnonymousUser();
    $current_user->setPreference('user_theme', 'Green', 0, 'global');
    $current_user->setPreference('max_tabs', '10', 0, 'global');
    $current_user->save();
    $this->user = $current_user;

	require('include/modules.php');
	$GLOBALS['beanList'] = $beanList;
	$GLOBALS['beanFiles'] = $beanFiles;
    $_SESSION['upgrade_from_flavor'] = 'SugarCE to SugarPro';
    require_once('modules/UpgradeWizard/uw_utils.php');
    $this->useOutputBuffering = true;
}

public function tearDown()
{
	SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    if(isset($_SESSION['upgrade_from_flavor']))
    {
       unset($_SESSION['upgrade_from_flavor']);
    }
    $GLOBALS['db']->query('UPDATE users SET deleted = 0 WHERE deleted = 2');
}

public function testUpgradeUserPreferencesCeToPro()
{
    upgradeUserPreferences();
    unset($_SESSION[$this->user->user_name.'_PREFERENCES']['global']);
    $user = new User();
    $user->retrieve($this->user->id);
    $theme = $user->getPreference('user_theme');
    $tabs = (int)$user->getPreference('max_tabs');
    $this->assertEquals('Sugar', $theme, 'Assert that theme is upgraded to Sugar on CE->PRO upgrade');
    $this->assertEquals(10, $tabs, 'Assert that number of tabs is not changed');
}

public function testUpgradeUserPreferencesCeToProWithTabValue()
{
    $user = new User();
    $user->retrieve($this->user->id);
    $user->setPreference('max_tabs', '0', 0, 'global');
    $user->savePreferencesToDB();
    upgradeUserPreferences();
    unset($_SESSION[$this->user->user_name.'_PREFERENCES']['global']);
    $user->retrieve($this->user->id);
    $theme = $user->getPreference('user_theme');
    $tabs = (int)$user->getPreference('max_tabs');
    $this->assertEquals('Sugar', $theme, 'Assert that theme is upgraded to Sugar on CE->PRO upgrade');
    $this->assertEquals(7, $tabs, 'Assert that number of tabs defaults to 7 if it was empty');
}

public function testUpgradeUserPreferencesNonFlavor()
{
    unset($_SESSION['upgrade_from_flavor']);
    upgradeUserPreferences();
    unset($_SESSION[$this->user->user_name.'_PREFERENCES']['global']);
    $user = new User();
    $user->retrieve($this->user->id);
    $theme = $user->getPreference('user_theme');
    $tabs = (int)$user->getPreference('max_tabs');
    $this->assertEquals('Green', $theme, 'Assert that theme is not upgraded if not flavor conversion');
    $this->assertEquals(10, $tabs, 'Assert that number of tabs is not changed');
}

}

