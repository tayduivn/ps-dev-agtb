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
require_once 'modules/CalDav/Event.php';

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

use Sabre\VObject;

/**
 * CalDav bean tests
 * Class CalDavTest
 *
 * @coversDefaultClass \CalDavEvent
 */
class CalDavEventTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \CalDavEventMock
     */
    protected $beanMock;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestCalDavUtilities::deleteAllCreatedCalendars();
        SugarTestCalDavUtilities::deleteCreatedEvents();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        parent::tearDown();
    }

    public function saveBeanDataProvider()
    {
        return array(
            array(
                'content' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
uid:test
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR',
                'size' => 90,
                'ETag' => 'c3d48c3c99615a99a764be4fc95c9ca9',
                'type' => 'VEVENT',
                'firstoccurence' => strtotime('20160101Z'),
                'lastoccurence' => strtotime('20160101Z') + 86400,
                'uid' => 'test',
            ),
        );
    }

    public function sizeAndETagDataProvider()
    {
        return array(
            array(
                'content' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR',
                'size' => 81,
                'ETag' => '852ca4ec17e847ca5190754e21d53c54',
            ),
        );
    }

    public function componentTypeProvider()
    {
        return array(
            array(
                'content' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR',
                'component' => 'VEVENT',
            ),
            array(
                'content' => 'BEGIN:VCALENDAR
BEGIN:VTIMEZONE
END:VTIMEZONE
BEGIN:VEVENT
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR',
                'component' => 'VEVENT',
            ),
            array(
                'content' => 'BEGIN:VCALENDAR
BEGIN:VTIMEZONE
END:VTIMEZONE
END:VCALENDAR',
                'component' => null,
            ),
            array(
                'content' => 'BEGIN:VCALENDAR
BEGIN:VTODO
DTSTART:20110101T120000Z
DURATION:PT1H
END:VTODO
END:VCALENDAR',
                'component' => 'VTODO',
            ),
        );
    }

    public function calendarObjectProvider()
    {
        return array(
            array(
                'content' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR',
            ),
        );
    }

    public function calendarObjectBoundariesProvider()
    {
        return array(
            //DTSTART type DATE-TIME ISO format UTC. Lastoccurence should be calculated
            array(
                'content' => 'BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
DTSTART;VALUE=DATE-TIME:20160101T100000Z
END:VEVENT
END:VCALENDAR',
                'firstoccurence' => strtotime('20160101T100000Z'),
                'lastoccurence' => strtotime('20160101T100000Z'),
            ),
            //DTSTART type DATE-TIME with custom timezone set. Lastoccurence should be calculated
            array(
                'content' => 'BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
DTSTART;TZID=UTC:20160101T100000
END:VEVENT
END:VCALENDAR',
                'firstoccurence' => strtotime('20160101T100000Z'),
                'lastoccurence' => strtotime('20160101T100000Z'),
            ),
            //DTSTART type DATE. Lastoccurence should be calculated
            array(
                'content' => 'BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR',
                'firstoccurence' => strtotime('20160101Z'),
                'lastoccurence' => strtotime('20160101Z') + 86400,
            ),
            //DTSTART and DTEND are set
            array(
                'content' => 'BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
DTSTART;VALUE=DATE-TIME:20160101T100000Z
DTEND:20160201T110000Z
END:VEVENT
END:VCALENDAR',
                'firstoccurence' => strtotime('20160101T100000Z'),
                'lastoccurence' => strtotime('20160201T110000Z'),
            ),
            //DTSTART and DURATION are set. Lastoccurence should be calculated
            array(
                'content' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
DTSTART;VALUE=DATE:20160101
DURATION:P2D
END:VEVENT
END:VCALENDAR',
                'firstoccurence' => strtotime('20160101Z'),
                'lastoccurence' => strtotime('20160101Z') + 86400 * 2,
            ),
            //Ending recurrence. Lastoccurence should be calculated
            array(
                'content' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
DTSTART;VALUE=DATE-TIME:20160101T100000Z
DTEND;VALUE=DATE-TIME:20160101T110000Z
UID:foo
RRULE:FREQ=DAILY;COUNT=500
END:VEVENT
END:VCALENDAR',
                'firstoccurence' => strtotime('20160101T100000Z'),
                'lastoccurence' => strtotime('20160101T110000Z') + 86400 * 499,
            ),
            //Infinite recurrence. Lastoccurence should be calculated.
            array(
                'content' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
DTSTART;VALUE=DATE-TIME:20160101T100000Z
RRULE:FREQ=DAILY
UID:foo
END:VEVENT
END:VCALENDAR',
                'firstoccurence' => strtotime('20160101T100000Z'),
                'lastoccurence' => strtotime('20160101T100000Z') + 86400 * 1000,
            ),
        );
    }

    public function toCalDavArrayProvider()
    {
        return array(
            array(
                'beanData' => array(
                    'id' => '1',
                    'uri' => 'test',
                    'date_modified' => '2015-07-28 13:41:29',
                    'etag' => 'test',
                    'calendarid' => '2',
                    'size' => '2',
                    'calendardata' => '22',
                    'componenttype' => 'VEVENT',
                ),
                'expectedArray' => array(
                    'id' => '1',
                    'uri' => 'test',
                    'lastmodified' => strtotime('2015-07-28 13:41:29'),
                    'etag' => '"test"',
                    'calendarid' => '2',
                    'size' => '2',
                    'calendardata' => '22',
                    'component' => 'vevent',
                ),
            )
        );
    }

    public function addChangeProvider()
    {
        return array(
            array(
                'beanData' => array('id' => 1, 'calendarid' => 1, 'deleted' => 0, 'uri' => 'uri'),
                'expectedChange' => array('calendarid' => 1, 'operation' => 2, 'uri' => 'uri'),
            ),
            array(
                'beanData' => array('id' => null, 'calendarid' => 1, 'deleted' => 0, 'uri' => 'uri'),
                'expectedChange' => array('calendarid' => 1, 'operation' => 1, 'uri' => 'uri')
            ),
            array(
                'beanData' => array('id' => 1, 'calendarid' => 1, 'deleted' => 1, 'uri' => 'uri'),
                'expectedChange' => array('calendarid' => 1, 'operation' => 3, 'uri' => 'uri')
            ),
        );
    }

    protected function getVObjectEventData()
    {
        return 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Mozilla.org/NONSGML Mozilla Calendar V1.1//EN
BEGIN:VTIMEZONE
TZID:Europe/Berlin
X-LIC-LOCATION:Europe/Berlin
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
TZNAME:CEST
DTSTART:19700329T020000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
TZNAME:CET
DTSTART:19701025T030000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
CREATED:20150806T065243Z
LAST-MODIFIED:20150806T130928Z
DTSTAMP:20150806T130928Z
UID:9bff32ff-efcc-4250-8cc1-3512433587fb
SUMMARY:Test event title
ORGANIZER;RSVP=TRUE;PARTSTAT=ACCEPTED;ROLE=CHAIR:mailto:test@sugarcrm.com
ATTENDEE;RSVP=TRUE;PARTSTAT=NEEDS-ACTION;ROLE=REQ-PARTICIPANT:mailto:SALLY@
 EXAMPLE.COM
ATTENDEE;RSVP=TRUE;PARTSTAT=NEEDS-ACTION;ROLE=CHAIR:mailto:test@test.com
ATTENDEE;RSVP=TRUE;PARTSTAT=NEEDS-ACTION;ROLE=OPT-PARTICIPANT:mailto:test1@
 test.com
RRULE:FREQ=DAILY;UNTIL=20150813T080000Z
X-MOZ-LASTACK:20150806T074504Z
DTSTART;TZID=Europe/Berlin:20150806T100000
DTEND;TZID=Europe/Berlin:20150806T110000
TRANSP:OPAQUE
LOCATION:office
X-MOZ-SEND-INVITATIONS:TRUE
X-MOZ-SEND-INVITATIONS-UNDISCLOSED:FALSE
DESCRIPTION:Test event description
X-MOZ-GENERATION:3
SEQUENCE:1
CLASS:PUBLIC
BEGIN:VALARM
ACTION:DISPLAY
TRIGGER;VALUE=DURATION:-PT15M
DESCRIPTION:Default Mozilla Description
END:VALARM
END:VEVENT
END:VCALENDAR
';
    }

    protected function getVObjectTaskData()
    {
        return 'BEGIN:VCALENDAR
PRODID:-//Mozilla.org/NONSGML Mozilla Calendar V1.1//EN
VERSION:2.0
BEGIN:VTIMEZONE
TZID:Europe/Minsk
X-LIC-LOCATION:Europe/Minsk
BEGIN:STANDARD
TZOFFSETFROM:+0300
TZOFFSETTO:+0300
TZNAME:FET
DTSTART:19700101T000000
END:STANDARD
END:VTIMEZONE
BEGIN:VTODO
CREATED:20150806T075546Z
LAST-MODIFIED:20150806T075625Z
DTSTAMP:20150806T075625Z
UID:63bf1da3-5918-455c-9493-617ef0f7f68a
SUMMARY:Test task title
RRULE:FREQ=WEEKLY;COUNT=5
DTSTART;TZID=Europe/Minsk:20150814T110000
DUE;TZID=Europe/Minsk:20150814T130000
DESCRIPTION:Test task description
LOCATION:office1
CLASS:CONFIDENTIAL
BEGIN:VALARM
ACTION:DISPLAY
TRIGGER;VALUE=DATE-TIME:20150810T120000Z
DESCRIPTION:Default Mozilla Description
END:VALARM
END:VTODO
END:VCALENDAR';
    }

    protected function getEmptyVObject()
    {
        return 'BEGIN:VCALENDAR
PRODID:-//Mozilla.org/NONSGML Mozilla Calendar V1.1//EN
VERSION:2.0
BEGIN:VTIMEZONE
TZID:Europe/Minsk
X-LIC-LOCATION:Europe/Minsk
BEGIN:STANDARD
TZOFFSETFROM:+0300
TZOFFSETTO:+0300
TZNAME:FET
DTSTART:19700101T000000
END:STANDARD
END:VTIMEZONE
END:VCALENDAR';
    }

    public function getVObjectProvider()
    {
        return array(
            array('vCalendar' => $this->getVObjectEventData()),
            array('vCalnedar' => $this->getVObjectTaskData()),
            array('vCalendar' => null),
        );
    }

    public function getTitleProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getVObjectEventData(),
                'result' => 'Test event title',
            ),
            array(
                'vCalendar' => $this->getVObjectTaskData(),
                'result' => 'Test task title',
            ),
            array(
                'vCalendar' => null,
                'result' => null,
            ),
        );
    }

    public function getDescriptionProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getVObjectEventData(),
                'result' => 'Test event description',
            ),
            array(
                'vCalendar' => $this->getVObjectTaskData(),
                'result' => 'Test task description',
            ),
            array(
                'vCalendar' => null,
                'result' => null,
            ),
        );
    }

    public function getStartDateProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getVObjectEventData(),
                'result' => '2015-08-06 08:00:00',
            ),
            array(
                'vCalendar' => $this->getVObjectTaskData(),
                'result' => '2015-08-14 08:00:00',
            ),
            array(
                'vCalendar' => null,
                'result' => null,
            ),
        );
    }

    public function getDurationProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getVObjectEventData(),
                'result' => 60,
            ),
            array(
                'vCalendar' => $this->getVObjectTaskData(),
                'result' => 120,
            ),
            array(
                'vCalendar' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
DTSTART:20110101T120000Z
DURATION:P15DT5H0M20S
END:VEVENT
END:VCALENDAR',
                'result' => 21900,
            ),
            array(
                'vCalendar' => null,
                'result' => 0,
            ),
        );
    }

    public function getDurationHoursProvider()
    {
        return array(
            array(
                'minutes' => 65,
                'result' => 1,
            ),
            array(
                'minutes' => 120,
                'result' => 2,
            ),
            array(
                'minutes' => 10,
                'result' => 0
            ),
        );
    }

    public function getDurationMinutesProvider()
    {
        return array(
            array(
                'minutes' => 65,
                'result' => 5,
            ),
            array(
                'minutes' => 120,
                'result' => 0,
            ),
            array(
                'minutes' => 10,
                'result' => 10
            ),
        );
    }

    public function getEndDateProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getVObjectEventData(),
                'result' => '2015-08-06 09:00:00',
            ),
            array(
                'vCalendar' => $this->getVObjectTaskData(),
                'result' => '2015-08-14 10:00:00',
            ),
            array(
                'vCalendar' => null,
                'result' => null,
            ),
        );
    }

    public function getTimeZoneProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getVObjectEventData(),
                'result' => 'Europe/Berlin',
            ),
            array(
                'vCalendar' => $this->getVObjectTaskData(),
                'result' => 'Europe/Minsk',
            ),
            array(
                'vCalendar' => null,
                'result' => 'Europe/Berlin',
            ),
        );
    }

    public function getLocationProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getVObjectEventData(),
                'result' => 'office',
            ),
            array(
                'vCalendar' => $this->getVObjectTaskData(),
                'result' => 'office1',
            ),
            array(
                'vCalendar' => null,
                'result' => null,
            ),
        );
    }

    public function getVisibilityProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getVObjectEventData(),
                'result' => 'PUBLIC',
            ),
            array(
                'vCalendar' => $this->getVObjectTaskData(),
                'result' => 'CONFIDENTIAL',
            ),
            array(
                'vCalendar' => null,
                'result' => null,
            ),
        );
    }

    public function getOrganizerProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getVObjectEventData(),
                'result' => array(
                    'user' => 'mailto:test@sugarcrm.com',
                    'status' => 'ACCEPTED',
                    'role' => 'CHAIR',
                ),
            ),
            array(
                'vCalendar' => $this->getVObjectTaskData(),
                'result' => null,
            ),
            array(
                'vCalendar' => null,
                'result' => null,
            ),
        );
    }

    public function getParticipantsProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getVObjectEventData(),
                'result' => array(
                    array(
                        'user' => 'mailto:SALLY@EXAMPLE.COM',
                        'status' => 'NEEDS-ACTION',
                        'role' => 'REQ-PARTICIPANT',
                    ),
                    array(
                        'user' => 'mailto:test@test.com',
                        'status' => 'NEEDS-ACTION',
                        'role' => 'CHAIR',
                    ),
                    array(
                        'user' => 'mailto:test1@test.com',
                        'status' => 'NEEDS-ACTION',
                        'role' => 'OPT-PARTICIPANT',
                    ),
                ),
            ),
            array(
                'vCalendar' => $this->getVObjectTaskData(),
                'result' => array(),
            ),
            array(
                'vCalendar' => null,
                'result' => array(),
            ),
        );
    }

    public function getReminderProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getVObjectEventData(),
                'result' => array(
                    'DISPLAY' => array(
                        'duration' => 900,
                        'description' => 'Default Mozilla Description',
                        'attendees' => array(),
                    )
                )
            ),
            array(
                'vCalendar' => $this->getVObjectTaskData(),
                'result' => array(),
            ),
            array(
                'vCalendar' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
BEGIN:VALARM
X-EVOLUTION-ALARM-UID:20150812T121904Z-13371-1000-6716-2@dolbik-ubu
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;
 RSVP=FALSE:test@test.com
TRIGGER;VALUE=DURATION;RELATED=START:-PT15M
ACTION:DISPLAY
DESCRIPTION:alarm test
END:VALARM
BEGIN:VALARM
X-EVOLUTION-ALARM-UID:20150812T121932Z-13371-1000-6716-3@dolbik-ubu
ACTION:EMAIL
TRIGGER;VALUE=DURATION;RELATED=START:-PT20M
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;
 RSVP=FALSE:test@test.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;
 RSVP=FALSE:test1@test.com
DESCRIPTION:alarm test
END:VALARM
END:VEVENT
END:VCALENDAR',
                'result' => array(
                    'DISPLAY' => array(
                        'duration' => 900,
                        'description' => 'alarm test',
                        'attendees' => array(
                            array(
                                'user' => 'test@test.com',
                                'status' => 'NEEDS-ACTION',
                                'role' => 'REQ-PARTICIPANT',
                            ),
                        ),
                    ),
                    'EMAIL' => array(
                        'duration' => 1200,
                        'description' => 'alarm test',
                        'attendees' => array(
                            array(
                                'user' => 'test@test.com',
                                'status' => 'NEEDS-ACTION',
                                'role' => 'REQ-PARTICIPANT',
                            ),
                            array(
                                'user' => 'test1@test.com',
                                'status' => 'NEEDS-ACTION',
                                'role' => 'REQ-PARTICIPANT',
                            ),
                        ),
                    ),
                )
            ),
            array(
                'vCalendar' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
BEGIN:VALARM
X-EVOLUTION-ALARM-UID:20150812T121904Z-13371-1000-6716-2@dolbik-ubu
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;
 RSVP=FALSE:test@test.com
TRIGGER;VALUE=DURATION;RELATED=START:PT15M
ACTION:DISPLAY
DESCRIPTION:alarm test
END:VALARM
BEGIN:VALARM
X-EVOLUTION-ALARM-UID:20150812T121932Z-13371-1000-6716-3@dolbik-ubu
ACTION:EMAIL
TRIGGER;VALUE=DURATION;RELATED=START:-PT20M
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;
 RSVP=FALSE:test@test.com
ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;
 RSVP=FALSE:test1@test.com
DESCRIPTION:alarm test
END:VALARM
END:VEVENT
END:VCALENDAR',
                'result' => array(
                    'EMAIL' => array(
                        'duration' => 1200,
                        'description' => 'alarm test',
                        'attendees' => array(
                            array(
                                'user' => 'test@test.com',
                                'status' => 'NEEDS-ACTION',
                                'role' => 'REQ-PARTICIPANT',
                            ),
                            array(
                                'user' => 'test1@test.com',
                                'status' => 'NEEDS-ACTION',
                                'role' => 'REQ-PARTICIPANT',
                            ),
                        ),
                    ),
                )
            ),
            array(
                'vCalendar' => null,
                'result' => array(),
            ),
        );
    }

    public function getRRuleProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getVObjectEventData(),
                'result' => array(
                    'until' => '2015-08-13 08:00:00',
                    'type' => 'Daily',
                )
            ),
            array(
                'vCalendar' => $this->getVObjectTaskData(),
                'result' => array(
                    'count' => 5,
                    'type' => 'Weekly',
                )
            ),
            array(
                'vCalendar' => null,
                'result' => null,
            ),
        );
    }

    public function getStatusProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getVObjectEventData(),
                'result' => null,
            ),
            array(
                'vCalendar' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
STATUS:CANCELLED
END:VEVENT
END:VCALENDAR',
                'result' => 'Not Held',
            ),
            array(
                'vCalendar' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
STATUS:CONFIRMED
END:VEVENT
END:VCALENDAR',
                'result' => 'Planned',
            ),
            array(
                'vCalendar' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
STATUS:TENTATIVE
END:VEVENT
END:VCALENDAR',
                'result' => null,
            ),
            array(
                'vCalendar' => null,
                'result' => null,
            ),
        );
    }

    public function setComponentProvider()
    {
        return array(
            array(
                'currentEvent' => $this->getVObjectEventData(),
                'component' => 'VEVENT',
                'class' => 'Sabre\VObject\Component\VEvent'
            ),
            array(
                'currentEvent' => $this->getEmptyVObject(),
                'component' => 'VTODO',
                'class' => 'Sabre\VObject\Component\VTodo'
            ),
        );
    }

    public function setTitleProvider()
    {
        return array(
            array(
                'currentEvent' => $this->getVObjectEventData(),
                'newValue' => 'test1',
                'result' => true
            ),
            array(
                'currentEvent' => $this->getVObjectEventData(),
                'newValue' => 'Test event title',
                'result' => false
            ),
            array(
                'currentEvent' => $this->getEmptyVObject(),
                'newValue' => 'test',
                'result' => true
            ),
        );
    }

    public function setDescriptionProvider()
    {
        return array(
            array(
                'currentEvent' => $this->getVObjectEventData(),
                'newValue' => 'test1',
                'result' => true
            ),
            array(
                'currentEvent' => $this->getVObjectEventData(),
                'newValue' => 'Test event description',
                'result' => false
            ),
            array(
                'currentEvent' => $this->getEmptyVObject(),
                'newValue' => 'test',
                'result' => true
            ),
        );
    }

    public function setLocationProvider()
    {
        return array(
            array(
                'currentEvent' => $this->getVObjectEventData(),
                'newValue' => 'test1',
                'result' => true
            ),
            array(
                'currentEvent' => $this->getVObjectEventData(),
                'newValue' => 'office',
                'result' => false
            ),
            array(
                'currentEvent' => $this->getEmptyVObject(),
                'newValue' => 'test',
                'result' => true
            ),
        );
    }

    public function setStatusProvider()
    {
        return array(
            array(
                'currentEvent' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
STATUS:CANCELLED
END:VEVENT
END:VCALENDAR',
                'newValue' => 'Planned',
                'result' => true,
                'newStatus' => 'CONFIRMED'
            ),
            array(
                'currentEvent' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
STATUS:CONFIRMED
END:VEVENT
END:VCALENDAR',
                'newValue' => 'Not Held',
                'result' => true,
                'newStatus' => 'CANCELLED'
            ),
            array(
                'currentEvent' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
STATUS:CANCELLED
END:VEVENT
END:VCALENDAR',
                'newValue' => 'Not Held',
                'result' => false,
                'newStatus' => 'CANCELLED'
            ),
            array(
                'currentEvent' => $this->getEmptyVObject(),
                'newValue' => 'Planned',
                'result' => true,
                'newStatus' => 'CONFIRMED'
            ),
            array(
                'currentEvent' => $this->getEmptyVObject(),
                'newValue' => 'Planned1',
                'result' => false,
                'newStatus' => null,
            ),
        );
    }

    public function setDurationProvider()
    {
        return array(
            array(
                'currentEvent' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
DTSTART:20110101T120000Z
DURATION:PT1H
END:VEVENT
END:VCALENDAR',
                'hours' => 2,
                'minutes' => 0,
                'newValue' => 'PT2H',
                'result' => true,
            ),
            array(
                'currentEvent' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
DTSTART:20110101T120000Z
DURATION:PT1H
END:VEVENT
END:VCALENDAR',
                'hours' => 0,
                'minutes' => 60,
                'newValue' => 'PT1H',
                'result' => false,
            ),
            array(
                'currentEvent' => $this->getEmptyVObject(),
                'hours' => 2,
                'minutes' => 30,
                'newValue' => 'PT2H30M',
                'result' => true,
            ),
        );
    }

    public function setReminderProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getVObjectEventData(),
                'params' => array(
                    array(
                        'seconds' => 1200,
                        'action' => 'DISPLAY',
                        'duration' => '-PT20M',
                        'result' => true,
                    ),
                ),
            ),
            array(
                'vCalendar' => $this->getVObjectEventData(),
                'params' => array(
                    array(
                        'seconds' => 900,
                        'action' => 'DISPLAY',
                        'duration' => '-PT15M',
                        'result' => false,
                    ),
                ),
            ),
            array(
                'vCalendar' => $this->getVObjectEventData(),
                'params' => array(
                    array(
                        'seconds' => 900,
                        'action' => 'EMAIL',
                        'duration' => '-PT15M',
                        'result' => true,
                    ),
                ),
            ),
            array(
                'vCalendar' => $this->getEmptyVObject(),
                'params' => array(
                    array(
                        'seconds' => 600,
                        'action' => 'DISPLAY',
                        'duration' => '-PT10M',
                        'result' => true,
                    ),
                    array(
                        'seconds' => 600,
                        'action' => 'DISPLAY',
                        'duration' => '-PT10M',
                        'result' => false,
                    ),
                    array(
                        'seconds' => 600,
                        'action' => 'EMAIL',
                        'duration' => '-PT10M',
                        'result' => true,
                    ),
                ),
            ),
        );
    }

    public function setStartDateProvider()
    {
        return array(
            array(
                'currentEvent' => $this->getVObjectEventData(),
                'sugarDateTime' => '2014-12-31 21:00:01',
                'datetime' => '20141231T210001Z',
                'result' => true,
            ),
            array(
                'currentEvent' => $this->getVObjectEventData(),
                'sugarDateTime' => '2015-08-06 08:00:00',
                'datetime' => '20150806T100000',
                'result' => false,
            ),
            array(
                'currentEvent' => $this->getEmptyVObject(),
                'sugarDateTime' => '2014-12-31 21:00:01',
                'datetime' => '20141231T210001Z',
                'result' => true,
            ),
        );
    }

    public function setEndDateProvider()
    {
        return array(
            array(
                'currentEvent' => $this->getVObjectEventData(),
                'sugarDateTime' => '2014-12-31 21:00:01',
                'datetime' => '20141231T210001Z',
                'result' => true,
            ),
            array(
                'currentEvent' => $this->getVObjectEventData(),
                'sugarDateTime' => '2015-08-06 09:00:00',
                'datetime' => '20150806T110000',
                'result' => false,
            ),
            array(
                'currentEvent' => $this->getEmptyVObject(),
                'sugarDateTime' => '2014-12-31 21:00:01',
                'datetime' => '20141231T210001Z',
                'result' => true,
            ),
        );
    }

    public function setDueDateProvider()
    {
        return array(
            array(
                'currentEvent' => $this->getVObjectTaskData(),
                'sugarDateTime' => '2014-12-31 21:00:01',
                'datetime' => '20141231T210001Z',
                'result' => true,
            ),
            array(
                'currentEvent' => $this->getVObjectTaskData(),
                'sugarDateTime' => '2015-08-14 10:00:00',
                'datetime' => '20150814T130000',
                'result' => false,
            ),
            array(
                'currentEvent' => $this->getEmptyVObject(),
                'sugarDateTime' => '2014-12-31 21:00:01',
                'datetime' => '20141231T210001Z',
                'result' => true,
            ),
        );
    }

    /**
     * Checking the calculation of params while bean saving
     * @param string $data
     * @param integer $expectedSize
     * @param string $expectedETag
     * @param string $expectedType
     * @param int $expectedFirstOccurrence
     * @param int $expectedLastOccurrence
     * @param string $expectedUID
     *
     * @covers       \CalDavEvent::save
     * @covers       \CalDavEvent::setCalendarEventData
     *
     * @dataProvider saveBeanDataProvider
     */
    public function testSaveBean(
        $data,
        $expectedSize,
        $expectedETag,
        $expectedType,
        $expectedFirstOccurrence,
        $expectedLastOccurrence,
        $expectedUID
    ) {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();
        $calendarID = SugarTestCalDavUtilities::createCalendar($sugarUser, array());
        $event = SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => $data,
            'calendarid' => $calendarID,
            'eventURI' => 'test'
        ));

        $saved = BeanFactory::getBean('CalDavEvents', $event->id, array('use_cache' => false, 'encode' => false));

        $this->assertEquals($expectedSize, $saved->size);
        $this->assertEquals($expectedETag, $saved->etag);
        $this->assertEquals($expectedType, $saved->componenttype);
        $this->assertEquals($expectedFirstOccurrence, $saved->firstoccurence);
        $this->assertEquals($expectedLastOccurrence, $saved->lastoccurence);
        $this->assertEquals($expectedUID, $saved->uid);
        $this->assertEquals($data, $saved->calendardata);

        SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => $data,
            'calendarid' => $calendarID,
            'eventURI' => 'test1'
        ));

        $calendar =
            BeanFactory::getBean('CalDavCalendars', $calendarID, array('use_cache' => false, 'encode' => false));

        $this->assertEquals(2, $calendar->synctoken);
    }

    /**
     * Checking the calculation of the size and ETag
     * @param string $data
     * @param integer $expectedSize
     * @param string $expectedETag
     *
     * @covers       \CalDavEvent::calculateSize
     * @covers       \CalDavEvent::calculateETag
     *
     * @dataProvider sizeAndETagDataProvider
     */
    public function testSizeAndETag($data, $expectedSize, $expectedETag)
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();

        TestReflection::callProtectedMethod($beanMock, 'calculateSize', array($data));
        TestReflection::callProtectedMethod($beanMock, 'calculateETag', array($data));

        $this->assertEquals($expectedSize, $beanMock->size);
        $this->assertEquals($expectedETag, $beanMock->etag);
    }

    /**
     * Checks algorithm for determining the type of component
     * @param string $data
     * @param string $expectedComponent
     * @covers       \CalDavEvent::calculateComponentType
     *
     * @dataProvider componentTypeProvider
     */
    public function testComponentType($data, $expectedComponent)
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();
        TestReflection::callProtectedMethod($beanMock, 'calculateComponentType', array($data));

        $this->assertEquals($expectedComponent, $beanMock->componenttype);
    }

    /**
     * Checks that the necessary methods are invoked
     * @param string $data
     * @covers       \CalDavEvent::setCalendarEventData
     *
     * @dataProvider calendarObjectProvider
     */
    public function testSetCalendarObject($data)
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(array(
                             'calculateSize',
                             'calculateETag',
                             'calculateComponentType',
                             'calculateTimeBoundaries'
                         ))
                         ->getMock();

        $beanMock->expects($this->once())->method('calculateComponentType')->with($data)->willReturn(true);
        $beanMock->expects($this->once())->method('calculateSize')->with($data);
        $beanMock->expects($this->once())->method('calculateETag')->with($data);
        $beanMock->expects($this->once())->method('calculateTimeBoundaries')->with($data);

        $beanMock->setCalendarEventData($data);

        $this->assertEquals($data, $beanMock->calendardata);
    }

    /**
     * Check calculation firstoccurence and lastoccurence
     * @param string $data
     * @param $expectedFirstOccurrence
     * @param $expectedLastOccurrence
     *
     * @covers       \CalDavEvent::calculateTimeBoundaries
     *
     * @dataProvider calendarObjectBoundariesProvider
     */
    public function testCalculateTimeBoundaries($data, $expectedFirstOccurrence, $expectedLastOccurrence)
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();

        TestReflection::callProtectedMethod($beanMock, 'calculateTimeBoundaries', array($data));

        $this->assertEquals($expectedFirstOccurrence, $beanMock->firstoccurence);
        $this->assertEquals($expectedLastOccurrence, $beanMock->lastoccurence);
    }

    /**
     * Test for set calendarid bean property
     * @covers \CalDavEvent::setCalendarId
     */
    public function testSetCalendarId()
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();
        $beanMock->setCalendarId('test');
        $this->assertEquals('test', $beanMock->calendarid);
    }

    /**
     * Test for set uri bean property
     * @covers \CalDavEvent::setCalendarEventURI
     */
    public function testSetCalendarObjectURI()
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();
        $beanMock->setCalendarEventURI('test');
        $this->assertEquals('test', $beanMock->uri);
    }

    /**
     * @param array $beanData
     * @param array $expectedArray
     *
     * @covers       \CalDavEvent::toCalDavArray
     *
     * @dataProvider toCalDavArrayProvider
     */
    public function testToCalDavArray($beanData, $expectedArray)
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();

        foreach ($beanData as $key => $value) {
            $beanMock->$key = $value;
        }

        $result = $beanMock->toCalDavArray();

        $this->assertEquals($expectedArray, $result);
    }

    /**
     * @param array $beanData
     * @param array $expectedChange
     *
     * @covers       \CalDavEvent::addChange
     *
     * @dataProvider addChangeProvider
     */
    public function testAddChange(array $beanData, array $expectedChange)
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(array('getChangesBean', 'getRelatedCalendar'))
                         ->getMock();

        foreach ($beanData as $key => $value) {
            $beanMock->$key = $value;
        }

        $changesMock = $this->getMockBuilder('CalDavChange')
                            ->disableOriginalConstructor()
                            ->setMethods(array('add'))
                            ->getMock();

        $calendarMock = $this->getMockBuilder('CalDavCalendar')
                             ->disableOriginalConstructor()
                             ->setMethods(array('save'))
                             ->getMock();

        $beanMock->expects($this->once())->method('getChangesBean')->willReturn($changesMock);
        $beanMock->expects($this->once())->method('getRelatedCalendar')->willReturn($calendarMock);

        $changesMock->expects($this->once())->method('add')
                    ->with($calendarMock, $expectedChange['uri'], $expectedChange['operation']);

        TestReflection::callProtectedMethod($beanMock, 'addChange', array($expectedChange['operation']));
    }

    /**
     * @param string $vCalendarEventText
     *
     * @covers       \CalDavEvent::getVCalendarEvent
     *
     * @dataProvider getVObjectProvider
     */
    public function testGetVObject($vCalendarEventText)
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();

        $beanMock->calendardata = $vCalendarEventText;

        $result = TestReflection::callProtectedMethod($beanMock, 'getVCalendarEvent', array());

        $this->assertInstanceOf('Sabre\VObject\Component\VCalendar', $result);
    }

    /**
     * @param string $vCalendarEventText
     * @param string $expectedResult
     *
     * @covers       \CalDavEvent::getTitle
     *
     * @dataProvider getTitleProvider
     */
    public function testGetTitle($vCalendarEventText, $expectedResult)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $beanMock->calendardata = $vCalendarEventText;

        $result = $beanMock->getTitle();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $vCalendarEventText
     * @param string $expectedResult
     *
     * @covers       \CalDavEvent::getDescription
     *
     * @dataProvider getDescriptionProvider
     */
    public function testGetDescription($vCalendarEventText, $expectedResult)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = $beanMock->getDescription();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $vCalendarEventText
     * @param string $expectedResult
     *
     * @covers       \CalDavEvent::getStartDate
     *
     * @dataProvider getStartDateProvider
     */
    public function testGetStartDate($vCalendarEventText, $expectedResult)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = $beanMock->getStartDate();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $vCalendarEventText
     * @param string $expectedResult
     *
     * @covers       \CalDavEvent::getEndDate
     *
     * @dataProvider getEndDateProvider
     */
    public function testGetEndDate($vCalendarEventText, $expectedResult)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = $beanMock->getEndDate();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $vCalendarEventText
     * @param string $expectedResult
     *
     * @covers       \CalDavEvent::getTimeZone
     *
     * @dataProvider getTimeZoneProvider
     */
    public function testGetTimeZone($vCalendarEventText, $expectedResult)
    {
        $GLOBALS['current_user']->setPreference('timezone', 'Europe/Berlin');

        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = $beanMock->getTimeZone();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $vCalendarEventText
     * @param string $expectedResult
     *
     * @covers       \CalDavEvent::getLocation
     *
     * @dataProvider getLocationProvider
     */
    public function testGetLocation($vCalendarEventText, $expectedResult)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = $beanMock->getLocation();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $vCalendarEventText
     * @param string $expectedResult
     *
     * @covers       \CalDavEvent::getDuration
     *
     * @dataProvider getDurationProvider
     */
    public function testGetDuration($vCalendarEventText, $expectedResult)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = $beanMock->getDuration();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param int $minutes
     * @param int $expectedResult
     *
     * @covers       \CalDavEvent::getDurationHours
     *
     * @dataProvider getDurationHoursProvider
     */
    public function testGetDurationHours($minutes, $expectedResult)
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(array('getDuration'))
                         ->getMock();

        $beanMock->expects($this->once())->method('getDuration')->willReturn($minutes);

        $result = $beanMock->getDurationHours();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param int $minutes
     * @param int $expectedResult
     *
     * @covers       \CalDavEvent::getDurationMinutes
     *
     * @dataProvider getDurationMinutesProvider
     */
    public function testGetDurationMinutes($minutes, $expectedResult)
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(array('getDuration'))
                         ->getMock();

        $beanMock->expects($this->once())->method('getDuration')->willReturn($minutes);

        $result = $beanMock->getDurationMinutes();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $vCalendarEventText
     * @param string $expectedResult
     *
     * @covers       \CalDavEvent::getVisibility
     *
     * @dataProvider getVisibilityProvider
     */
    public function testGetVisibility($vCalendarEventText, $expectedResult)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = $beanMock->getVisibility();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $vCalendarEventText
     * @param string $expectedResult
     *
     * @covers       \CalDavEvent::getOrganizer
     *
     * @dataProvider getOrganizerProvider
     */
    public function testGetOrganizer($vCalendarEventText, $expectedResult)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = $beanMock->getOrganizer();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $vCalendarEventText
     * @param string $expectedResult
     *
     * @covers       \CalDavEvent::getParticipants
     *
     * @dataProvider getParticipantsProvider
     */
    public function testGetParticipants($vCalendarEventText, $expectedResult)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = $beanMock->getParticipants();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $vCalendarEventText
     * @param string $expectedResult
     *
     * @covers       \CalDavEvent::getReminders
     *
     * @dataProvider getReminderProvider
     */
    public function testGetReminder($vCalendarEventText, $expectedResult)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = $beanMock->getReminders();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $vCalendarEventText
     * @param string $expectedResult
     *
     * @covers       \CalDavEvent::getRRule
     *
     * @dataProvider getRRuleProvider
     */
    public function testGetRRule($vCalendarEventText, $expectedResult)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = $beanMock->getRRule();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $vCalendarEventText
     * @param string $expectedResult
     *
     * @covers       \CalDavEvent::getStatus
     *
     * @dataProvider getStatusProvider
     */
    public function testGetStatus($vCalendarEventText, $expectedResult)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = $beanMock->getStatus();

        $this->assertEquals($expectedResult, $result);
    }

    public function testVObjectToString()
    {
        $vCalendarEventText = $this->getVObjectEventData();
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = $beanMock->vObjectToString();

        $this->assertTrue(is_string($result));
    }

    /**
     * @covers \CalDavEvent::getBean
     */
    public function testGetBean()
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(array('save'))
                         ->getMock();

        $result = $beanMock->getBean();

        $this->assertInstanceOf('Meeting', $result);

        $callsMock = $this->getMockBuilder('Call')
                          ->disableOriginalConstructor()
                          ->setMethods(null)
                          ->getMock();

        $callsMock->module_name = 'Calls';
        $callsMock->id = '1';

        $beanMock->setBean($callsMock);

        $result = $beanMock->getBean();

        $this->assertInstanceOf('Call', $result);
    }

    /**
     * @covers \CalDavEvent::setBean
     */
    public function testSetBean()
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(array('save'))
                         ->getMock();

        $meetingsMock = $this->getMockBuilder('Meeting')
                             ->disableOriginalConstructor()
                             ->setMethods(null)
                             ->getMock();

        $meetingsMock->module_name = 'Meetings';
        $meetingsMock->id = '1';

        $beanMock->setBean($meetingsMock);

        $this->assertEquals($meetingsMock->module_name, $beanMock->parent_type);
        $this->assertEquals($meetingsMock->id, $beanMock->parent_id);
    }

    /**
     * @covers \CalDavEvent::findByBean
     */
    public function testFindByBean()
    {
        $event = SugarTestCalDavUtilities::createEvent();
        $meeting = SugarTestMeetingUtilities::createMeeting();

        $event->setBean($meeting);
        $event->save();

        $caldavBean = \BeanFactory::getBean('CalDavEvents');

        $result = $caldavBean->findByBean($meeting);

        $this->assertEquals($meeting->id, $result->parent_id);
    }

    /**
     * @param string $currentEvent
     * @param string $componentType
     * @param string $expectedClass
     *
     * @covers       \CalDavEvent::setType
     *
     * @dataProvider setComponentProvider
     */
    public function testSetComponent($currentEvent, $componentType, $expectedClass)
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(array('getVCalendarEvent'))
                         ->getMock();

        $vObject = Sabre\VObject\Reader::read($currentEvent);

        $beanMock->expects($this->once())->method('getVCalendarEvent')->willReturn($vObject);

        $beanMock->setType($componentType);

        $components = $vObject->getComponents();

        $this->assertEquals(2, count($components));
        $component = $vObject->select($componentType);
        $this->assertInstanceOf($expectedClass, array_shift($component));
    }

    /**
     * @param string $currentEvent
     * @param string $newValue
     * @param bool $expectedResult
     *
     * @covers       \CalDavEvent::setTitle
     *
     * @dataProvider setTitleProvider
     */
    public function testSetTitle($currentEvent, $newValue, $expectedResult)
    {
        $component = $this->getObjectForSetters($currentEvent);

        $result = $this->beanMock->setTitle($newValue, $component);

        $this->assertEquals($expectedResult, $result);
        $this->assertEquals(1, count($component->select('SUMMARY')));
        $this->assertEquals($newValue, $component->SUMMARY);

    }

    /**
     * @param string $currentEvent
     * @param string $newValue
     * @param bool $expectedResult
     *
     * @covers       \CalDavEvent::setDescription
     *
     * @dataProvider setDescriptionProvider
     */
    public function testSetDescription($currentEvent, $newValue, $expectedResult)
    {
        $component = $this->getObjectForSetters($currentEvent);

        $result = $this->beanMock->setDescription($newValue, $component);

        $this->assertEquals($expectedResult, $result);
        $this->assertEquals(1, count($component->select('DESCRIPTION')));
        $this->assertEquals($newValue, $component->DESCRIPTION);

    }

    /**
     * @param string $currentEvent
     * @param string $newValue
     * @param bool $expectedResult
     *
     * @covers       \CalDavEvent::setLocation
     *
     * @dataProvider setLocationProvider
     */
    public function testSetLocation($currentEvent, $newValue, $expectedResult)
    {
        $component = $this->getObjectForSetters($currentEvent);

        $result = $this->beanMock->setLocation($newValue, $component);

        $this->assertEquals($expectedResult, $result);
        $this->assertEquals(1, count($component->select('LOCATION')));
        $this->assertEquals($newValue, $component->LOCATION);

    }

    /**
     * @param string $currentEvent
     * @param string $newValue
     * @param bool $expectedResult
     *
     * @covers       \CalDavEvent::setStatus
     *
     * @dataProvider setStatusProvider
     */
    public function testSetStatus($currentEvent, $newValue, $expectedResult, $expectedStatus)
    {
        $component = $this->getObjectForSetters($currentEvent);

        $result = $this->beanMock->setStatus($newValue, $component);

        $this->assertEquals($expectedResult, $result);
        $this->assertEquals($expectedStatus, $component->STATUS);
    }

    /**
     * @param string $currentEvent
     * @param int $hours
     * @param int $minutes
     * @param string $expectedDuration
     * @param bool $expectedResult
     *
     * @covers       \CalDavEvent::setDuration
     *
     * @dataProvider setDurationProvider
     */
    public function testSetDuration($currentEvent, $hours, $minutes, $expectedDuration, $expectedResult)
    {
        $component = $this->getObjectForSetters($currentEvent);

        $result = $this->beanMock->setDuration($hours, $minutes, $component);

        $this->assertEquals($expectedResult, $result);
        $this->assertEquals($expectedDuration, $component->DURATION);
    }

    /**
     * @param string $currentEvent
     * @param array $reminderParams
     *
     * @covers       \CalDavEvent::setReminder
     *
     * @dataProvider setReminderProvider
     */
    public function testSetReminder($currentEvent, array $reminderParams)
    {
        $component = $this->getObjectForSetters($currentEvent);

        foreach ($reminderParams as $param) {
            $result = $this->beanMock->setReminder($param['seconds'], $component, $param['action']);
            $this->assertEquals($param['result'], $result);
        }

        $result = $this->beanMock->getReminders();

        foreach ($reminderParams as $param) {
            $this->assertNotEmpty($result[$param['action']]);
            $this->assertEquals($param['seconds'], $result[$param['action']]['duration']);
        }
    }

    /**
     * @param string $currentEvent
     * @param string $dateTime
     * @param string $expectedDateTime
     * @param bool $expectedResult
     *
     * @covers       \CalDavEvent::setStartDate
     *
     * @dataProvider setStartDateProvider
     */
    public function testSetStartDate($currentEvent, $dateTime, $expectedDateTime, $expectedResult)
    {
        $component = $this->getObjectForSetters($currentEvent);

        $result = $this->beanMock->setStartDate($dateTime, $component);

        $this->assertEquals($expectedResult, $result);
        $this->assertEquals($expectedDateTime, $component->DTSTART->getValue());
    }

    /**
     * @param string $currentEvent
     * @param string $dateTime
     * @param string $expectedDateTime
     * @param bool $expectedResult
     *
     * @covers       \CalDavEvent::setEndDate
     *
     * @dataProvider setEndDateProvider
     */
    public function testSetEndDate($currentEvent, $dateTime, $expectedDateTime, $expectedResult)
    {
        $component = $this->getObjectForSetters($currentEvent);

        $result = $this->beanMock->setEndDate($dateTime, $component);

        $this->assertEquals($expectedResult, $result);
        $this->assertEquals($expectedDateTime, $component->DTEND->getValue());
    }

    /**
     * @param string $currentEvent
     * @param string $dateTime
     * @param string $expectedDateTime
     * @param bool $expectedResult
     *
     * @covers       \CalDavEvent::setDueDate
     *
     * @dataProvider setDueDateProvider
     */
    public function testSetDueDate($currentEvent, $dateTime, $expectedDateTime, $expectedResult)
    {
        $component = $this->getObjectForSetters($currentEvent, 'VTODO');

        $result = $this->beanMock->setDueDate($dateTime, $component);

        $this->assertEquals($expectedResult, $result);
        $this->assertEquals($expectedDateTime, $component->DUE->getValue());
    }

    /**
     * Configure mocks for set data tests
     * @param string $currentEvent
     * @param string $type VEVENT of VTODO
     * @return Sabre\VObject\Component
     */
    protected function getObjectForSetters($currentEvent, $type = 'VEVENT')
    {
        $this->beanMock = $this->getMockBuilder('CalDavEvent')
                               ->disableOriginalConstructor()
                               ->setMethods(null)
                               ->getMock();

        $dateTimeHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper')
                               ->disableOriginalConstructor()
                               ->setMethods(null)
                               ->getMock();

        TestReflection::setProtectedValue($this->beanMock, 'dateTimeHelper', $dateTimeHelper);

        $this->beanMock->calendardata = $currentEvent;

        return $this->beanMock->setType($type);
    }

    /**
     * Configure mocks for get data tests
     * @param string $currentEvent
     * @return \CalDavEvent_Mock
     */
    protected function getObjectForGetters($currentEvent)
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();

        $dateTimeHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper')
                               ->disableOriginalConstructor()
                               ->setMethods(null)
                               ->getMock();

        TestReflection::setProtectedValue($beanMock, 'dateTimeHelper', $dateTimeHelper);

        $beanMock->calendardata = $currentEvent;

        return $beanMock;
    }
}
