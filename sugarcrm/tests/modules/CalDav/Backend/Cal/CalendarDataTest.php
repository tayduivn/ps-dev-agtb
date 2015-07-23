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

use Sabre\CalDAV;
use Sabre\DAV\PropPatch;

use Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData;

class CalendarDataTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestCalDavUtilities::deleteAllCreatedCalendars();
    }

    /**
     * Retrieve user calendar test
     */
    public function testGetCalendarsForUser()
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();

        $calendar = new CalendarData();

        $result = $calendar->getCalendarsForUser('principals/'.$sugarUser->user_name);

        $this->assertInternalType('array',$result);
        $this->assertEquals(1,count($result));
        $this->assertEquals('principals/'.$sugarUser->user_name, $result[0]['principaluri']);
    }

    public function testUpdateCalendar()
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();

        $calendarID = SugarTestCalDavUtilities::createCalendar($sugarUser,
            array(
                '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set' => new CalDAV\Xml\Property\SupportedCalendarComponentSet(array('VEVENT')),
                '{DAV:}displayname' => 'Default calendar',
                '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp' => new CalDAV\Xml\Property\ScheduleCalendarTransp('transparent'),
            ));
        $propPatch = new PropPatch(array(
            '{DAV:}displayname' => 'myCalendar',
            '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp' => new CalDAV\Xml\Property\ScheduleCalendarTransp('opaque'),
        ));

        $calendar = new CalendarData();

        $calendar->updateCalendar($calendarID, $propPatch);
        $result = $propPatch->commit();

        $this->assertTrue($result);

        $result = $calendar->getCalendarsForUser('principals/'.$sugarUser->user_name);

        $this->assertEquals('myCalendar', $result[0]['{DAV:}displayname']);
        $this->assertEquals('opaque', $result[0]['{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp']->getValue());
        $this->assertEquals(2, $result[0]['{http://sabredav.org/ns}sync-token']);
    }
}
