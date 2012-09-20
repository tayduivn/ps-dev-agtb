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

require_once('tests/rest/RestTestBase.php');

/***
 * Used to test Forecast Module endpoints from ForecastModuleApi.php
 *
 */
class ForecastsFiltersApiTest extends RestTestBase
{

    private static $currentUser;
    private static $employee1;
    private static $employee2;
    private static $employee3;
    private static $employee4;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        self::$currentUser = SugarTestUserUtilities::createAnonymousUser();
        self::$currentUser->user_name = 'employee0';
        self::$currentUser->save();

        self::$employee1 = SugarTestUserUtilities::createAnonymousUser();
        self::$employee1->reports_to_id = self::$currentUser->id;
        self::$employee1->user_name = 'employee1';
        self::$employee1->save();

        self::$employee2 = SugarTestUserUtilities::createAnonymousUser();
        self::$employee2->reports_to_id = self::$currentUser->id;
        self::$employee2->user_name = 'employee2';
        self::$employee2->save();

        self::$employee3 = SugarTestUserUtilities::createAnonymousUser();
        self::$employee3->reports_to_id = self::$employee2->id;
        self::$employee3->user_name = 'employee3';
        self::$employee3->save();

        self::$employee4 = SugarTestUserUtilities::createAnonymousUser();
        self::$employee4->reports_to_id = self::$employee3->id;
        self::$employee4->user_name = 'employee4';
        self::$employee4->save();

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

    /***
     * Test that we get the reportees assigned to the currentUser id
     *
     * @group forecastapi
     * @group forecasts
     */
    public function testReportees() {

        $restReply = $this->_restCall("Forecasts/reportees/" . self::$currentUser->id);
        $this->assertEquals($restReply['reply']['metadata']['id'], self::$currentUser->id, "currentUser's id was not found in the Expected place in the rest reply" );

        // get the user ids from first level
        $firstLevel = array();
        foreach($restReply['reply']['children'] as $children ) {
            array_push($firstLevel, $children['metadata']['id']);
        }

        // assertContains in case the order is ever jumbled
        $this->assertContains(self::$employee1->id, $firstLevel, "employee1's id was not found in the Expected place in the rest reply" );
        $this->assertContains(self::$employee2->id, $firstLevel, "employee2's id was not found in the Expected place in the rest reply" );
    }

    /**
     * Test that a deleted user does not show up from the filter call
     *
     * @group forecastapi
     * @group forecasts
     */
    public function testDeletedReportees() {
        // delete one user for this test
        self::$employee2->deleted = 1;
        self::$employee2->save();
        $GLOBALS['db']->commit();

        $restReply = $this->_restCall("Forecasts/reportees/" . self::$currentUser->id);

        $fullNames = array();

        foreach($restReply['reply']['children'] as $children ) {
            array_push($fullNames, $children['data']);
        }

        $this->assertNotContains(self::$employee2->full_name, $fullNames, "Deleted employee2 was found in the rest reply when it should not have been" );

        // Undelete user if needed for other tests
        self::$employee2->deleted = 0;
        self::$employee2->save();
        $GLOBALS['db']->commit();
    }


    /**
     * Test the timeperiods and that we don't return any fiscal year timeperiods
     *
     * @group forecastapi
     * @group forecasts
     */
    public function testTimeperiods()
    {
        $restReply = $this->_restCall("Forecasts/timeframes/");
        $db = DBManagerFactory::getInstance();

        $result = $db->query('SELECT id, name FROM timeperiods WHERE is_fiscal_year = 1 AND deleted=0');
        while(($row = $db->fetchByAssoc($result)))
        {
            $fiscal_timeperiods[$row['id']]=$row['name'];
        }

        foreach($fiscal_timeperiods as $id=>$ftp)
        {
            $this->assertArrayNotHasKey($id, $restReply['reply'], "filter contains ". $ftp['name'] . " fiscal timeperiod");
        }
    }


    /**
     * This test is to see that the data returned for the name field is set correctly when locale name format changes
     *
     * @group testGetLocaleFormattedName
     * @group forecastapi
     * @group forecasts
     */
    public function testGetLocaleFormattedName()
    {
        global $locale;
        $defaultPreference = $this->_user->getPreference('default_locale_name_format');
        $this->_user->setPreference('default_locale_name_format', 'l, f', 0, 'global');
        $this->_user->savePreferencesToDB();
        $this->_user->reloadPreferences();
        $restReply = $this->_restCall("Forecasts/reportees/" . self::$currentUser->id);
        $expectedData = $locale->getLocaleFormattedName(self::$currentUser->first_name, self::$currentUser->last_name);
        $this->assertEquals($expectedData, $restReply['reply']['data']);
        $this->_user->setPreference('default_locale_name_format', $defaultPreference, 0, 'global');
        $this->_user->savePreferencesToDB();
        $this->_user->reloadPreferences();
    }
}
