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

require_once 'tests/SugarTestCalDavUtilites.php';

use Sugarcrm\Sugarcrm\Dav\Base\Helper;
use Sugarcrm\Sugarcrm\Dav\Cal;

class UserHelperTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestCalDavUtilities::deleteAllCreatedCalendars();
    }

    /**
     * Test for retrieve existing user calendar
     */
    public function testGetCalendarsIfExists()
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();
        $calendarID = SugarTestCalDavUtilities::createCalendar($sugarUser);

        $userHelper = new Helper\UserHelper();

        $calendars = $userHelper->getCalendars('principals/users/' . $sugarUser->user_name);

        $this->assertArrayHasKey(0, $calendars);
        $this->assertEquals($calendarID, $calendars[0]['id']);
        $this->assertEquals($sugarUser->id, $calendars[0]['assigned_user_id']);
        $this->assertEquals(translate('LBL_DAFAULT_CALDAV_NAME'), $calendars[0]['name']);
        $this->assertEquals('VEVENT,VTODO', $calendars[0]['components']);
    }

    /**
     * Test for retrieve not existing user calendar
     */
    public function testGetDefaultCalendarIfNotExists()
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();
        $userHelper = new Helper\UserHelper();

        $calendars = $userHelper->getCalendars('principals/users/' . $sugarUser->user_name);

        $this->assertArrayHasKey(0, $calendars);
        $this->assertEquals($sugarUser->id, $calendars[0]['assigned_user_id']);
        $this->assertEquals(translate('LBL_DAFAULT_CALDAV_NAME'), $calendars[0]['name']);

        SugarTestCalDavUtilities::addCalendarToCreated($calendars[0]['id']);
    }
}
