<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once('tests/rest/RestTestBase.php');

/***
 * Used to test Forecast Module endpoints from ForecastModuleApi.php
 *
 * @group forecasts
 */
class ForecastsCurrentUserApiTest extends RestTestBase
{

    private static $currentUser;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');

        self::$currentUser = SugarTestUserUtilities::createAnonymousUser();
        self::$currentUser->user_name = 'employee0';
        self::$currentUser->save();
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDown();
    }

    //Override tearDown so we don't lose current user settings
    public function tearDown()
    {

    }

    /**
     * This test is to make sure Forecasts/me endpoint returns an isManager property
     */
    public function testCheckForecastSpecificIsManager()
    {
        $restReply = $this->_restCall("Forecasts/me");
        $currentUser = $restReply['reply']['current_user'];
        $this->assertArrayHasKey('isManager', $currentUser, "Forecasts/me did not return isManager");
    }

    /**
     * This test is to make sure Forecasts/me endpoint returns an showOpps property
     */
    public function testCheckForecastSpecificShowOpps()
    {
        $restReply = $this->_restCall("Forecasts/me");
        $currentUser = $restReply['reply']['current_user'];
        $this->assertArrayHasKey('showOpps', $currentUser, "Forecasts/me did not return showOpps");
    }

    /**
     * This test is to make sure Forecasts/me endpoint returns an first_name property
     */
    public function testCheckForecastSpecificFirstName()
    {
        $restReply = $this->_restCall("Forecasts/me");
        $currentUser = $restReply['reply']['current_user'];
        $this->assertArrayHasKey('first_name', $currentUser, "Forecasts/me did not return first_name");
    }

    /**
     * This test is to make sure Forecasts/me endpoint returns an last_name property
     */
    public function testCheckForecastSpecificLastName()
    {
        $restReply = $this->_restCall("Forecasts/me");
        $currentUser = $restReply['reply']['current_user'];
        $this->assertArrayHasKey('last_name', $currentUser, "Forecasts/me did not return last_name");
    }

    /**
     * This test is to see that the data returned for the name field is set correctly when locale name format changes
     *
     * @group testGetLocaleFormattedName
     */
    public function testGetLocaleFormattedName()
    {
        global $locale;
        $defaultPreference = $this->_user->getPreference('default_locale_name_format');
        $this->_user->setPreference('default_locale_name_format', 'l, f', 0, 'global');
        $this->_user->savePreferencesToDB();
        $this->_user->reloadPreferences();
        $restReply = $this->_restCall("Forecasts/user/" . self::$currentUser->id);
        $expectedData = $locale->getLocaleFormattedName(self::$currentUser->first_name, self::$currentUser->last_name);
        $this->assertEquals($expectedData, $restReply['reply']['full_name']);
        $this->_user->setPreference('default_locale_name_format', $defaultPreference, 0, 'global');
        $this->_user->savePreferencesToDB();
        $this->_user->reloadPreferences();
    }
}
