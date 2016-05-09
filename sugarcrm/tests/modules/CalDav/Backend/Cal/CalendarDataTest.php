<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
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
    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, 1));

        global $current_user;
        $current_user->setPreference('caldav_interval', '6 month');
        $current_user->setPreference('timezone', 'Europe/Moscow');
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestCalDavUtilities::deleteAllCreatedCalendars();
        SugarTestCalDavUtilities::deleteCreatedEvents();

        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function calendarQueryProvider()
    {
        return array(
            array(
                'calendarData' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:test
DTSTART;VALUE=DATE:20160101
DURATION:P2D
END:VEVENT
END:VCALENDAR',
                'filter' => array(
                    'name' => 'VCALENDAR',
                    'comp-filters' => array(
                        array(
                            'name' => 'VEVENT',
                            'comp-filters' => array(),
                            'prop-filters' => array(),
                            'is-not-defined' => false,
                            'time-range' => null,
                        ),
                    ),
                    'prop-filters' => array(),
                    'is-not-defined' => false,
                    'time-range' => null,
                ),
                'found' => true,
            ),
            array(
                'calendarData' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:test
DTSTART;VALUE=DATE:20160101
DTSTART;VALUE=DATE:20170101
DURATION:P2D
END:VEVENT
END:VCALENDAR',
                'filter' => array(
                    'name' => 'VCALENDAR',
                    'comp-filters' => array(
                        array(
                            'name' => 'VEVENT',
                            'comp-filters' => array(),
                            'prop-filters' => array(),
                            'is-not-defined' => false,
                            'time-range' => array(
                                'start' => new \DateTime('2011-01-01 10:00:00', new \DateTimeZone('GMT')),
                            ),
                        ),
                    ),
                    'prop-filters' => array(),
                    'is-not-defined' => false,
                    'time-range' => null,
                ),
                'found' => true,
            ),
            array(
                'calendarData' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:test
DTSTART;VALUE=DATE:20160101
DTSTART;VALUE=DATE:20170101
DURATION:P2D
END:VEVENT
END:VCALENDAR',
                'filter' => array(
                    'name' => 'VCALENDAR',
                    'comp-filters' => array(
                        array(
                            'name' => 'VEVENT',
                            'comp-filters' => array(),
                            'prop-filters' => array(),
                            'is-not-defined' => false,
                            'time-range' => array(
                                'start' => new \DateTime('2011-01-01 10:00:00', new \DateTimeZone('GMT')),
                                'end' => new \DateTime('2012-01-01 10:00:00', new \DateTimeZone('GMT'))
                            ),
                        ),
                    ),
                    'prop-filters' => array(),
                    'is-not-defined' => false,
                    'time-range' => null,
                ),
                'found' => false,
            ),
        );
    }

    /**
     * Retrieve user calendar test
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::getCalendarsForUser
     */
    public function testGetCalendarsForUser()
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();

        $backendMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                            ->setMethods(null)
                            ->getMock();

        $result = $backendMock->getCalendarsForUser('principals/users/' . $sugarUser->user_name);

        $this->assertInternalType('array', $result);
        $this->assertEquals(1, count($result));
        $this->assertEquals('principals/users/' . $sugarUser->user_name, $result[0]['principaluri']);

        SugarTestCalDavUtilities::addCalendarToCreated($result[0]['id']);

        $result = $backendMock->getCalendarsForUser('principals/contacts/' . $sugarUser->id);

        $this->assertEquals(array(), $result);
    }

    /**
     * Update calendar test
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::updateCalendar
     */
    public function testUpdateCalendar()
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();

        $calendarID = SugarTestCalDavUtilities::createCalendar($sugarUser);
        $propPatch = new PropPatch(array(
            '{DAV:}displayname' => 'myCalendar',
            '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp' => new CalDAV\Xml\Property\ScheduleCalendarTransp('opaque'),
        ));

        $backendMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                            ->setMethods(null)
                            ->getMock();

        $backendMock->updateCalendar($calendarID, $propPatch);
        $result = $propPatch->commit();

        $this->assertTrue($result);

        $result = $backendMock->getCalendarsForUser('principals/users/' . $sugarUser->user_name);

        $this->assertEquals('myCalendar', $result[0]['{DAV:}displayname']);
        $this->assertEquals('opaque',
            $result[0]['{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp']->getValue());
        $this->assertEquals(1, $result[0]['{http://sabredav.org/ns}sync-token']);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::getEventsBean
     */
    public function testGetCalendarEventsBean()
    {
        $backendMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                            ->setMethods(null)
                            ->getMock();

        $event = $backendMock->getEventsBean();

        $this->assertInstanceOf('\CalDavEventCollection', $event);
        $this->assertFalse($event->doLocalDelivery);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::getCalendarObjects
     */
    public function testGetCalendarObjects()
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();
        $calendarID = SugarTestCalDavUtilities::createCalendar($sugarUser);

        $event1 = SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:test
DTSTART;VALUE=DATE:' . (date('Ymd', strtotime('-3 month'))).'
DURATION:P2D
END:VEVENT
END:VCALENDAR',
            'calendarid' => $calendarID,
            'eventURI' => 'test'
        ));

        $event2 = SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:test1
DTSTART;VALUE=DATE:' . (date('Ymd', strtotime('-5 month'))).'
DURATION:P2D
END:VEVENT
END:VCALENDAR',
            'calendarid' => $calendarID,
            'eventURI' => 'test1'
        ));

        $event3 = SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:test2
DTSTART;VALUE=DATE:' . (date('Ymd', strtotime('-10 month'))).'
DURATION:P2D
END:VEVENT
END:VCALENDAR',
            'calendarid' => $calendarID,
            'eventURI' => 'test2'
        ));

        $backendMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
            ->setMethods(null)
            ->getMock();

        $result = $backendMock->getCalendarObjects($calendarID);

        $idData = array();

        foreach ($result as $event) {
            $idData[] = $event['id'];
        }

        $this->assertEquals(2, count($idData));
        $this->assertContains($event1->id, $idData);
        $this->assertContains($event2->id, $idData);
        $this->assertNotContains($event3->id, $idData);
    }

    /**
     * @param string $calendarData
     * @param array $filters
     * @param bool $found
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::calendarQuery
     *
     * @dataProvider calendarQueryProvider
     */
    public function testCalendarQuery($calendarData, array $filters, $found)
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();
        $calendarID = SugarTestCalDavUtilities::createCalendar($sugarUser);
        $event = SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => $calendarData,
            'calendarid' => $calendarID,
            'eventURI' => 'test'
        ));

        $backendMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                            ->setMethods(null)
                            ->getMock();

        $result = $backendMock->calendarQuery($calendarID, $filters);

        if ($found) {
            $this->assertContains($event->uri, $result);
        } else {
            $this->assertNotContains($event->uri, $result);
        }
    }

    /**
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::getCalendarObject
     */
    public function testGetCalendarObject()
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();
        $calendarID = SugarTestCalDavUtilities::createCalendar($sugarUser);
        SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:test
DTSTART;VALUE=DATE:20160101
DURATION:P2D
END:VEVENT
END:VCALENDAR',
            'calendarid' => $calendarID,
            'eventURI' => 'test'
        ));

        SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:test2
DTSTART;VALUE=DATE:20160101
DURATION:P2D
END:VEVENT
END:VCALENDAR',
            'calendarid' => $calendarID,
            'eventURI' => 'test2'
        ));

        $backendMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                            ->setMethods(null)
                            ->getMock();

        $result = $backendMock->getCalendarObject($calendarID, 'test');

        $this->assertEquals('test', $result['uri']);
    }

    /**
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::getMultipleCalendarObjects
     */
    public function testGetMultipleCalendarObjects()
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();
        $sugarUser1 = SugarTestUserUtilities::createAnonymousUser();
        $calendarID = SugarTestCalDavUtilities::createCalendar($sugarUser);
        $calendarID1 = SugarTestCalDavUtilities::createCalendar($sugarUser1);

        SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:test1
DTSTART;VALUE=DATE:20160101
DURATION:P2D
END:VEVENT
END:VCALENDAR',
            'calendarid' => $calendarID,
            'eventURI' => 'test1'
        ));

        SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:test2
DTSTART;VALUE=DATE:20160101
DURATION:P2D
END:VEVENT
END:VCALENDAR',
            'calendarid' => $calendarID,
            'eventURI' => 'test2'
        ));

        SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:test3
DTSTART;VALUE=DATE:20160101
DURATION:P2D
END:VEVENT
END:VCALENDAR',
            'calendarid' => $calendarID,
            'eventURI' => 'test3'
        ));
        SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:test4
DTSTART;VALUE=DATE:20160101
DURATION:P2D
END:VEVENT
END:VCALENDAR',
            'calendarid' => $calendarID1,
            'eventURI' => 'test4'
        ));

        $backendMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                            ->setMethods(null)
                            ->getMock();

        $result = $backendMock->getMultipleCalendarObjects($calendarID, array('test1', 'test3', 'test4'));

        $uriData = array();

        foreach ($result as $event) {
            $uriData[] = $event['uri'];
        }

        $this->assertEquals(2, count($uriData));
        $this->assertContains('test1', $uriData);
        $this->assertContains('test3', $uriData);
        $this->assertNotContains('test2', $uriData);
        $this->assertNotContains('test4', $uriData);
    }

    /**
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::deleteCalendarObject
     */
    public function testDeleteCalendarObject()
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();
        $calendarID = SugarTestCalDavUtilities::createCalendar($sugarUser);

        $event = SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:test1
DTSTART;VALUE=DATE:20160101
DURATION:P2D
END:VEVENT
END:VCALENDAR',
            'calendarid' => $calendarID,
            'eventURI' => 'test1'
        ));

        $backendMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                            ->setMethods(null)
                            ->getMock();

        $backendMock->deleteCalendarObject($calendarID, 'test1');

        $deletedEvent = $event->retrieve($event->id);

        $this->assertNull($deletedEvent);

        $result = $backendMock->getCalendarsForUser('principals/users/' . $sugarUser->user_name);

        $this->assertEquals(2, $result[0]['{http://sabredav.org/ns}sync-token']);
    }

    public function testGetCalendarObjectByUID()
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();

        $backendMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                            ->setMethods(null)
                            ->getMock();

        $calendarInfo = $backendMock->getCalendarsForUser('principals/users/' . $sugarUser->user_name);

        $event = SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
DTSTART;VALUE=DATE:20160101
DURATION:P2D
UID:uidtest1
END:VEVENT
END:VCALENDAR',
            'calendarid' => $calendarInfo[0]['id'],
            'eventURI' => 'test1'
        ));

        SugarTestCalDavUtilities::addCalendarToCreated($calendarInfo[0]['id']);

        $result = $backendMock->getCalendarObjectByUID('principals/users/' . $sugarUser->user_name, 'uidtest1');

        $this->assertEquals($calendarInfo[0]['uri'] . '/' . $event->uri, $result);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::getChangesForCalendar
     */
    public function testGetChangesForCalendar()
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();
        $calendarID = SugarTestCalDavUtilities::createCalendar($sugarUser);

        $syncToken = 1;
        $syncLevel = 1;
        $limit = null;
        $uid1 = create_guid();
        $event1 = SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:' . $uid1 . '
DTSTART;VALUE=DATE:' . (date('Ymd', strtotime('-3 month'))) . '
DURATION:P2D
SUMMARY:Test event title
END:VEVENT
END:VCALENDAR',
            'calendarid' => $calendarID,
            'eventURI' => $uid1,
        ));

        $uid1 = create_guid();
        $event2 = SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:' . $uid1 . '
DTSTART;VALUE=DATE:' . (date('Ymd', strtotime('-5 month'))) . '
DURATION:P2D
END:VEVENT
END:VCALENDAR',
            'calendarid' => $calendarID,
            'eventURI' => $uid1,
        ));

        $uid1 = create_guid();
        $event3 = SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:' . $uid1 . '
DTSTART;VALUE=DATE:' . (date('Ymd', strtotime('-10 month'))) . '
DURATION:P2D
END:VEVENT
END:VCALENDAR',
            'calendarid' => $calendarID,
            'eventURI' => $uid1,
        ));

        $backendMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                            ->setMethods(null)
                            ->getMock();

        //update event1
        $event1->calendar_data = str_replace('Test event title', 'Test event title!', $event1->calendar_data);
        $event1->processed = true;
        $event1->save();
        $event1->retrieve($event1->id);

        //mark deleted even2
        $event2->mark_deleted($event2->id);

        $result = $backendMock->getChangesForCalendar($calendarID, 0, $syncLevel, $limit);

        $this->assertEquals(5, $result['syncToken']);
        $this->assertEquals($event3->uri, $result['added'][0]);
        $this->assertEquals($event1->uri, $result['modified'][0]);
        $this->assertEquals($event2->uri, $result['deleted'][0]);

        $result = $backendMock->getChangesForCalendar($calendarID, 4, $syncLevel, $limit);
        $this->assertEquals($event2->uri, $result['deleted'][0]);

    }
}
