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
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    public function tearDown()
    {
        SugarTestCalDavUtilities::deleteAllCreatedCalendars();
        SugarTestCalDavUtilities::deleteCreatedEvents();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestMeetingUtilities::removeMeetingContacts();
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestContactUtilities::removeCreatedContactsEmailAddresses();
        SugarTestContactUtilities::removeAllCreatedContacts();
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
                'uri'=>'',
            ),
            array(
                'content' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:test
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR',
                'uri'=>'test.ics',
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
                    'data_size' => '2',
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

    /**
     * Load template for event
     * @param string $templateName
     * @param bool $isText
     * @return string | Sabre\VObject\Component\VCalendar
     */
    protected function getEventTemplate($templateName, $isText = true)
    {
        $calendarData = file_get_contents(dirname(__FILE__) . '/EventTemplates/' . $templateName . '.ics');

        if ($isText) {
            return $calendarData;
        }

        $vEvent = VObject\Reader::read($calendarData);

        return $vEvent;
    }

    public function getVObjectProvider()
    {
        return array(
            array('vCalendar' => $this->getEventTemplate('vevent')),
            array('vCalnedar' => $this->getEventTemplate('vtodo')),
            array('vCalendar' => null),
        );
    }

    public function getTitleProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getEventTemplate('vevent'),
                'result' => 'Test event title',
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vtodo'),
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
                'vCalendar' => $this->getEventTemplate('vevent'),
                'result' => 'Test event description',
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vtodo'),
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
                'vCalendar' => $this->getEventTemplate('vevent'),
                'result' => '2015-08-06 08:00:00',
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vtodo'),
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
                'vCalendar' => $this->getEventTemplate('vevent'),
                'result' => 60,
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vtodo'),
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
                'vCalendar' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
DTSTART;VALUE=DATE:20110101
DTEND;VALUE=DATE:20110102
END:VEVENT
END:VCALENDAR',
                'result' => 1440,
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
                'vCalendar' => $this->getEventTemplate('vevent'),
                'result' => '2015-08-06 09:00:00',
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vtodo'),
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
                'vCalendar' => $this->getEventTemplate('vevent'),
                'result' => 'Europe/Berlin',
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vtodo'),
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
                'vCalendar' => $this->getEventTemplate('vevent'),
                'result' => 'office',
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vtodo'),
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
                'vCalendar' => $this->getEventTemplate('vevent'),
                'result' => 'PUBLIC',
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vtodo'),
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
        $id1 = create_guid();

        return array(
            array(
                'vCalendar' => $this->getEventTemplate('vevent'),
                'user' => array(
                    array('email1' => 'test0@test.com', 'new_with_id' => true, 'id' => $id1),
                ),
                'result' => array(
                    'Users' => array(
                        $id1 => array(
                            'email' => 'test0@test.com',
                            'accept_status' => 'accept',
                            'cn' => '',
                            'role' => 'CHAIR',
                        ),
                    ),
                ),
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vtodo'),
                'user' => array(),
                'result' => array(),
            ),
            array(
                'vCalendar' => null,
                'user' => array(),
                'result' => array(),
            ),
        );
    }

    public function getParticipantsProvider()
    {
        $id1 = create_guid();
        $id2 = create_guid();

        return array(
            array(
                'vCalendar' => $this->getEventTemplate('vevent'),
                'users' => array(
                    array('email1' => 'test@test.com', 'new_with_id' => true, 'id' => $id1),
                ),
                'contacts' => array(
                    array('email' => 'test2@test.com', 'new_with_id' => true, 'id' => $id2),
                ),
                'result' => array(
                    'Users' => array(
                        $id1 => array(
                            'email' => 'test@test.com',
                            'accept_status' => 'none',
                            'cn' => '',
                            'role' => 'REQ-PARTICIPANT',
                        )
                    ),
                    'Contacts' => array(
                        $id2 => array(
                            'email' => 'test2@test.com',
                            'accept_status' => 'decline',
                            'cn' => '',
                            'role' => 'OPT-PARTICIPANT',
                        ),
                    )
                ),
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vevent'),
                'users' => array(),
                'contacts' => array(
                    array('email' => 'test2@test.com', 'new_with_id' => true, 'id' => $id2),
                ),
                'result' => array(
                    'Contacts' => array(
                        $id2 => array(
                            'email' => 'test2@test.com',
                            'accept_status' => 'decline',
                            'cn' => '',
                            'role' => 'OPT-PARTICIPANT',
                        ),
                    )
                ),
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vtodo'),
                'users' => array(),
                'contacts' => array(),
                'result' => array(),
            ),
            array(
                'vCalendar' => null,
                'users' => array(),
                'contacts' => array(),
                'result' => array(),
            ),
        );
    }

    public function getReminderProvider()
    {
        $id1 = create_guid();
        $id2 = create_guid();

        return array(
            array(
                'vCalendar' => $this->getEventTemplate('vevent'),
                'users' => array(),
                'result' => array(
                    'DISPLAY' => array(
                        'duration' => 900,
                        'description' => 'Default Mozilla Description',
                        'attendees' => array(),
                    )
                )
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vtodo'),
                'users' => array(),
                'result' => array(),
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vGetReminder1'),
                'users' => array(
                    array('email1' => 'test@test.com', 'new_with_id' => true, 'id' => $id1),
                    array('email1' => 'test1@test.com', 'new_with_id' => true, 'id' => $id2)
                ),
                'result' => array(
                    'DISPLAY' => array(
                        'duration' => 900,
                        'description' => 'alarm test',
                        'attendees' => array(
                            'Users' => array(
                                $id1 => array(
                                    'email' => 'test@test.com',
                                    'accept_status' => 'none',
                                    'cn' => '',
                                    'role' => 'REQ-PARTICIPANT',
                                ),
                            ),
                        ),
                    ),
                    'EMAIL' => array(
                        'duration' => 1200,
                        'description' => 'alarm test',
                        'attendees' => array(
                            'Users' => array(
                                $id1 => array(
                                    'email' => 'test@test.com',
                                    'accept_status' => 'none',
                                    'cn' => '',
                                    'role' => 'REQ-PARTICIPANT',
                                ),
                                $id2 => array(
                                    'email' => 'test1@test.com',
                                    'accept_status' => 'none',
                                    'cn' => '',
                                    'role' => 'REQ-PARTICIPANT',
                                ),
                            ),
                        ),
                    ),
                )
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vGetReminder2'),
                'users' => array(
                    array('email1' => 'test@test.com', 'new_with_id' => true, 'id' => $id1),
                    array('email1' => 'test1@test.com', 'new_with_id' => true, 'id' => $id2)
                ),
                'result' => array(
                    'EMAIL' => array(
                        'duration' => 1200,
                        'description' => 'alarm test',
                        'attendees' => array(
                            'Users' => array(
                                $id1 => array(
                                    'email' => 'test@test.com',
                                    'accept_status' => 'none',
                                    'cn' => '',
                                    'role' => 'REQ-PARTICIPANT',
                                ),
                                $id2 => array(
                                    'email' => 'test1@test.com',
                                    'accept_status' => 'none',
                                    'cn' => '',
                                    'role' => 'REQ-PARTICIPANT',
                                ),
                            ),
                        ),
                    ),
                )
            ),
            array(
                'vCalendar' => null,
                'users' => array(),
                'result' => array(),
            ),
        );
    }

    public function getRRuleProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getEventTemplate('vevent'),
                'method' => 'getRecurringInfo',
                'count' => 1,
            ),
        );
    }

    public function setRRuleProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getEventTemplate('vevent'),
                'method' => 'setRecurringInfo',
                'count' => 1,
            ),
        );
    }

    public function getStatusProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getEventTemplate('vevent'),
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
                'currentEvent' => $this->getEventTemplate('vevent'),
                'component' => 'VEVENT',
                'class' => 'Sabre\VObject\Component\VEvent'
            ),
            array(
                'currentEvent' => $this->getEventTemplate('vempty'),
                'component' => 'VTODO',
                'class' => 'Sabre\VObject\Component\VTodo'
            ),
        );
    }

    public function setTitleProvider()
    {
        return array(
            array(
                'currentEvent' => $this->getEventTemplate('vevent'),
                'newValue' => 'test1',
                'result' => true
            ),
            array(
                'currentEvent' => $this->getEventTemplate('vevent'),
                'newValue' => 'Test event title',
                'result' => false
            ),
            array(
                'currentEvent' => $this->getEventTemplate('vempty'),
                'newValue' => 'test',
                'result' => true
            ),
        );
    }

    public function setDescriptionProvider()
    {
        return array(
            array(
                'currentEvent' => $this->getEventTemplate('vevent'),
                'newValue' => 'test1',
                'result' => true
            ),
            array(
                'currentEvent' => $this->getEventTemplate('vevent'),
                'newValue' => 'Test event description',
                'result' => false
            ),
            array(
                'currentEvent' => $this->getEventTemplate('vempty'),
                'newValue' => 'test',
                'result' => true
            ),
        );
    }

    public function setLocationProvider()
    {
        return array(
            array(
                'currentEvent' => $this->getEventTemplate('vevent'),
                'newValue' => 'test1',
                'result' => true
            ),
            array(
                'currentEvent' => $this->getEventTemplate('vevent'),
                'newValue' => 'office',
                'result' => false
            ),
            array(
                'currentEvent' => $this->getEventTemplate('vempty'),
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
                'currentEvent' => $this->getEventTemplate('vempty'),
                'newValue' => 'Planned',
                'result' => true,
                'newStatus' => 'CONFIRMED'
            ),
            array(
                'currentEvent' => $this->getEventTemplate('vempty'),
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
                'currentEvent' => $this->getEventTemplate('vempty'),
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
                'vCalendar' => $this->getEventTemplate('vevent'),
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
                'vCalendar' => $this->getEventTemplate('vevent'),
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
                'vCalendar' => $this->getEventTemplate('vevent'),
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
                'vCalendar' => $this->getEventTemplate('vempty'),
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
                'currentEvent' => $this->getEventTemplate('vevent'),
                'sugarDateTime' => '2014-12-31 21:00:01',
                'datetime' => '20141231T210001Z',
                'result' => true,
            ),
            array(
                'currentEvent' => $this->getEventTemplate('vevent'),
                'sugarDateTime' => '2015-08-06 08:00:00',
                'datetime' => '20150806T100000',
                'result' => false,
            ),
            array(
                'currentEvent' => $this->getEventTemplate('vempty'),
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
                'currentEvent' => $this->getEventTemplate('vevent'),
                'sugarDateTime' => '2014-12-31 21:00:01',
                'datetime' => '20141231T210001Z',
                'result' => true,
            ),
            array(
                'currentEvent' => $this->getEventTemplate('vevent'),
                'sugarDateTime' => '2015-08-06 09:00:00',
                'datetime' => '20150806T110000',
                'result' => false,
            ),
            array(
                'currentEvent' => $this->getEventTemplate('vempty'),
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
                'currentEvent' => $this->getEventTemplate('vtodo'),
                'sugarDateTime' => '2014-12-31 21:00:01',
                'datetime' => '20141231T210001Z',
                'result' => true,
            ),
            array(
                'currentEvent' => $this->getEventTemplate('vtodo'),
                'sugarDateTime' => '2015-08-14 10:00:00',
                'datetime' => '20150814T130000',
                'result' => false,
            ),
            array(
                'currentEvent' => $this->getEventTemplate('vempty'),
                'sugarDateTime' => '2014-12-31 21:00:01',
                'datetime' => '20141231T210001Z',
                'result' => true,
            ),
        );
    }

    public function deleteParticipantsProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getEventTemplate('vevent'),
                'participants' => array(
                    'mailto:test@test.com' => array(
                        'PARTSTAT' => null,
                        'CN' => null,
                        'ROLE' => '',
                        'davLink' => null,
                    ),
                    'mailto:test1@test.com' => array(
                        'PARTSTAT' => null,
                        'CN' => null,
                        'ROLE' => '',
                        'davLink' => null,
                    ),
                ),
            ),
        );
    }

    public function addParticipantsProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getEventTemplate('vempty'),
                'participants' => array(
                    'mailto:test@test.com' => array(
                        'PARTSTAT' => 'ACCEPTED',
                        'CN' => 'Test Test',
                        'ROLE' => '',
                        'davLink' => null,
                    ),
                    'mailto:test1@test.com' => array(
                        'PARTSTAT' => 'DECLINED',
                        'CN' => 'Test1 Test1',
                        'ROLE' => '',
                        'davLink' => null,
                    ),
                ),
            ),
        );
    }

    public function modifyParticipantsProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getEventTemplate('vevent'),
                'participants' => array(
                    'mailto:test2@test.com' => array(
                        'PARTSTAT' => 'DECLINED',
                        'CN' => 'Test Test',
                        'ROLE' => '',
                        'davLink' => null,
                    ),
                    'mailto:test10@test.com' => array(
                        'PARTSTAT' => 'DECLINED',
                        'CN' => 'Test Test',
                        'ROLE' => '',
                        'davLink' => 'mailto:test@test.com',
                    ),
                    'mailto:test11@test.com' => array(
                        'PARTSTAT' => 'DECLINED',
                        'CN' => 'Test Test',
                        'ROLE' => '',
                        'davLink' => 'mailto:test1@test.com',
                    ),
                ),
            ),
        );
    }

    public function setOrganizerProvider()
    {
        $id1 = 'baba4eca-59f2-f1ad-1f03-55d5d45e3f83';

        return array(
            array(
                'vCalendar' => $this->getEventTemplate('vempty'),
                'user' => array(
                    'email1' => 'test0@test.com',
                    'new_with_id' => true,
                    'id' => $id1,
                    'full_name' => 'SugarUser 756101654',
                    'first_name' => 'SugarUser',
                    'last_name' => '756101654',
                ),
                'davUser' => array(),
                'result' => true,
                'expectedMethods' => array('addParticipants'),
                'expectedArguments' => array(
                    'mailto:test0@test.com' => array(
                        'PARTSTAT' => 'NEEDS-ACTION',
                        'CN' => 'SugarUser 756101654',
                        'ROLE' => '',
                        'davLink' => '',
                        'RSVP' => 'TRUE',
                        'X-SUGARUID' => $id1,
                    ),
                ),
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vparticipants'),
                'user' => array(
                    'email1' => 'test10@test.com',
                    'new_with_id' => true,
                    'id' => $id1,
                    'full_name' => 'SugarUser 756101654',
                    'first_name' => 'SugarUser',
                    'last_name' => '756101654',
                ),
                'davUsers' => array(
                    'email1' => 'test0@test.com',
                    'new_with_id' => true,
                    'id' => $id1,
                    'full_name' => 'SugarUser 756101654',
                    'first_name' => 'SugarUser',
                    'last_name' => '756101654',
                ),
                'result' => true,
                'expectedMethods' => array('modifyParticipants'),
                'expectedArguments' => array(
                    'mailto:test10@test.com' => array(
                        'PARTSTAT' => 'NEEDS-ACTION',
                        'CN' => 'SugarUser 756101654',
                        'ROLE' => 'CHAIR',
                        'davLink' => 'mailto:test0@test.com',
                        'RSVP' => 'TRUE',
                        'X-SUGARUID' => $id1,
                    ),
                ),
            ),
        );
    }

    public function setParticipantsProvider()
    {
        $id1 = create_guid();
        $id2 = create_guid();
        $id3 = create_guid();

        return array(
            array(
                'vCalendar' => $this->getEventTemplate('vempty'),
                'users' => array(),
                'contacts' => array(),
                'davUsers' => array(),
                'result' => false,
                'expectedMethods' => null,
                'expectedArguments' => array(),
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vempty'),
                'users' => array(
                    array(
                        'email1' => 'test@test.com',
                        'new_with_id' => true,
                        'id' => $id1,
                        'full_name' => 'SugarUser 756101654',
                        'first_name' => 'SugarUser',
                        'last_name' => '756101654',
                    ),
                    array(
                        'email1' => 'test1@test.com',
                        'new_with_id' => true,
                        'id' => $id2,
                        'full_name' => 'SugarUser 1735411632',
                        'first_name' => 'SugarUser',
                        'last_name' => '1735411632',
                    )
                ),
                'contacts' => array(),
                'davUsers' => array(),
                'result' => true,
                'expectedMethods' => array('addParticipants'),
                'expectedArguments' => array(
                    'addParticipants' => array(
                        'mailto:test@test.com' => array(
                            'PARTSTAT' => 'NEEDS-ACTION',
                            'CN' => 'SugarUser 756101654',
                            'ROLE' => '',
                            'davLink' => '',
                            'RSVP' => 'TRUE',
                            'X-SUGARUID' => $id1,
                        ),
                        'mailto:test1@test.com' => array(
                            'PARTSTAT' => 'NEEDS-ACTION',
                            'CN' => 'SugarUser 1735411632',
                            'ROLE' => '',
                            'davLink' => '',
                            'RSVP' => 'TRUE',
                            'X-SUGARUID' => $id2,
                        )
                    ),
                ),
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vevent-participants'),
                'users' => array(),
                'contacts' => array(),
                'davUsers' => array(
                    array('email1' => 'test2@test.com', 'new_with_id' => true, 'id' => $id1, 'addToMeeting' => false),
                    array('email1' => 'test1@test.com', 'new_with_id' => true, 'id' => $id2, 'addToMeeting' => false)
                ),
                'result' => true,
                'expectedMethods' => array('deleteParticipants'),
                'expectedArguments' => array(
                    'deleteParticipants' => array(
                        'mailto:test2@test.com' => array(
                            'PARTSTAT' => '',
                            'CN' => '',
                            'ROLE' => '',
                            'davLink' => '',
                            'RSVP' => 'TRUE',
                            'X-SUGARUID' => $id1,
                        ),
                        'mailto:test1@test.com' => array(
                            'PARTSTAT' => '',
                            'CN' => '',
                            'ROLE' => '',
                            'davLink' => '',
                            'RSVP' => 'TRUE',
                            'X-SUGARUID' => $id2,
                        ),
                    ),
                ),
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vparticipants'),
                'users' => array(
                    array(
                        'email1' => 'test12@test.com',
                        'new_with_id' => true,
                        'id' => 'baba4eca-59f2-f1ad-1f03-55d5d45e3f82',
                        'full_name' => 'SugarUser 1735411632',
                        'first_name' => 'SugarUser',
                        'last_name' => '1735411632',
                    ),
                    array(
                        'email1' => 'test1@test.com',
                        'new_with_id' => true,
                        'id' => $id2,
                        'full_name' => 'SugarUser 1735411632',
                        'first_name' => 'SugarUser',
                        'last_name' => '1735411632',
                    ),
                ),
                'contacts' => array(
                    array(
                        'email' => 'test10@test.com',
                        'new_with_id' => true,
                        'id' => $id3,
                        'full_name' => 'SugarContact 1735411632',
                        'first_name' => 'SugarContact',
                        'last_name' => '1735411632',
                    ),
                ),
                'davUsers' => array(
                    array(
                        'email1' => 'test2@test.com',
                        'new_with_id' => true,
                        'id' => 'baba4eca-59f2-f1ad-1f03-55d5d45e3f82',
                        'full_name' => 'SugarUser 1735411632',
                        'first_name' => 'SugarUser',
                        'last_name' => '1735411632',
                        'addToMeeting' => true,
                    ),
                    array(
                        'email1' => 'test1@test.com',
                        'new_with_id' => true,
                        'id' => $id2,
                        'full_name' => 'SugarUser 1735411632',
                        'first_name' => 'SugarUser',
                        'last_name' => '1735411632',
                        'addToMeeting' => true,
                    )
                ),
                'result' => true,
                'expectedMethods' => array('modifyParticipants', 'addParticipants'),
                'expectedArguments' => array(
                    'modifyParticipants' => array(
                        'mailto:test12@test.com' => array(
                            'PARTSTAT' => 'NEEDS-ACTION',
                            'CN' => 'SugarUser 1735411632',
                            'ROLE' => 'OPT-PARTICIPANT',
                            'davLink' => 'mailto:test2@test.com',
                            'RSVP' => 'TRUE',
                            'X-SUGARUID' => 'baba4eca-59f2-f1ad-1f03-55d5d45e3f82',
                        ),
                        'mailto:test1@test.com' => array(
                            'PARTSTAT' => 'NEEDS-ACTION',
                            'CN' => 'SugarUser 1735411632',
                            'ROLE' => 'CHAIR',
                            'davLink' => 'mailto:test1@test.com',
                            'RSVP' => 'TRUE',
                            'X-SUGARUID' => $id2,
                        )
                    ),
                    'addParticipants' => array(
                        'mailto:test10@test.com' => array(
                            'PARTSTAT' => 'NEEDS-ACTION',
                            'CN' => 'SugarContact 1735411632',
                            'ROLE' => '',
                            'davLink' => null,
                            'RSVP' => 'TRUE',
                            'X-SUGARUID' => $id3,
                        ),
                    ),
                ),
            ),
        );
    }

    public function scheduleLocalDeliveryProvider()
    {
        return array(
            array(
                'currentEvent' => '',
                'updatedEvent' => $this->getEventTemplate('vevent-attendee-needaction', false),
            ),
        );
    }

    public function logicHooksFromModulesProvider()
    {
        return array(
            array('Meeting'),
            array('Call'),
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

        $this->assertEquals($expectedSize, $saved->data_size);
        $this->assertEquals($expectedETag, $saved->etag);
        $this->assertEquals($expectedType, $saved->componenttype);
        $this->assertEquals($expectedFirstOccurrence, $saved->firstoccurence);
        $this->assertEquals($expectedLastOccurrence, $saved->lastoccurence);
        $this->assertEquals($expectedUID, $saved->event_uid);
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

        $this->assertEquals($expectedSize, $beanMock->data_size);
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
     * @param string $expectedUri
     * @covers       \CalDavEvent::setCalendarEventData
     *
     * @dataProvider calendarObjectProvider
     */
    public function testSetCalendarObject($data, $expectedUri)
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(array(
                             'calculateSize',
                             'calculateETag',
                             'calculateTimeBoundaries'
                         ))
                         ->getMock();

        $beanMock->expects($this->once())->method('calculateSize')->with($data);
        $beanMock->expects($this->once())->method('calculateETag')->with($data);
        $beanMock->expects($this->once())->method('calculateTimeBoundaries')->with($data);

        $beanMock->setCalendarEventData($data);

        $this->assertEquals($data, $beanMock->calendardata);
        $this->assertEquals($expectedUri, $beanMock->uri);
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

        $result = $beanMock->getVCalendarEvent();

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
     * @param array $users
     * @param array|null $expectedResult
     *
     * @covers       \CalDavEvent::getOrganizer
     *
     * @dataProvider getOrganizerProvider
     */
    public function testGetOrganizer($vCalendarEventText, array $users, $expectedResult)
    {
        foreach ($users as $user) {
            SugarTestUserUtilities::createAnonymousUser(true, 0, $user);
        }

        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = $beanMock->getOrganizer();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $vCalendarEventText
     * @param array $users
     * @param array $contacts
     * @param array|null $expectedResult
     *
     * @covers       \CalDavEvent::getParticipants
     *
     * @dataProvider getParticipantsProvider
     */
    public function testGetParticipants($vCalendarEventText, array $users, array $contacts, $expectedResult)
    {
        foreach ($users as $user) {
            SugarTestUserUtilities::createAnonymousUser(true, 0, $user);
        }

        foreach ($contacts as $contact) {
            SugarTestContactUtilities::createContact($contact['id'], $contact);
        }

        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = $beanMock->getParticipants();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $vCalendarEventText
     * @param array $users
     * @param array $expectedResult
     *
     * @covers       \CalDavEvent::getReminders
     *
     * @dataProvider getReminderProvider
     */
    public function testGetReminder($vCalendarEventText, array $users, array $expectedResult)
    {
        foreach ($users as $user) {
            SugarTestUserUtilities::createAnonymousUser(true, 0, $user);
        }
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = $beanMock->getReminders();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $vCalendarEventText
     * @param string $expectedMethod
     * @param int $callCount
     *
     * @covers       \CalDavEvent::getRRule
     *
     * @dataProvider getRRuleProvider
     */
    public function testGetRRule($vCalendarEventText, $expectedMethod, $callCount)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $recurringHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper')
                                ->disableOriginalConstructor()
                                ->setMethods(array($expectedMethod))
                                ->getMock();

        TestReflection::setProtectedValue($beanMock, 'recurringHelper', $recurringHelper);

        $recurringHelper->expects($this->exactly($callCount))->method($expectedMethod)->with($beanMock);

        $beanMock->getRRule();
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
        $vCalendarEventText = $this->getEventTemplate('vevent');
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
        $meeting->retrieve($meeting->id);

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

        $beanMock->setComponent($componentType);

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
     * @param string $vCalendarEventText
     * @param string $expectedMethod
     * @param int $callCount
     *
     * @covers       \CalDavEvent::setRRule
     *
     * @dataProvider setRRuleProvider
     */
    public function testSetRRule($vCalendarEventText, $expectedMethod, $callCount)
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();

        $beanMock->setCalendarEventData($vCalendarEventText);

        $recurringHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper')
                                ->disableOriginalConstructor()
                                ->setMethods(array($expectedMethod))
                                ->getMock();

        TestReflection::setProtectedValue($beanMock, 'recurringHelper', $recurringHelper);

        $testArray = array('value' => 1);

        $recurringHelper->expects($this->exactly($callCount))->method($expectedMethod)->with($beanMock, $testArray);

        $beanMock->setRRule($testArray);
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
        $component = $this->getObjectForSetters($currentEvent, null, 'VTODO');

        $result = $this->beanMock->setDueDate($dateTime, $component);

        $this->assertEquals($expectedResult, $result);
        $this->assertEquals($expectedDateTime, $component->DUE->getValue());
    }

    /**
     * @param string $currentEvent
     * @param array $participants
     *
     * @covers       \CalDavEvent::modifyParticipants
     *
     * @dataProvider modifyParticipantsProvider
     */
    public function testModifyParticipants($currentEvent, $participants)
    {
        $component = $this->getObjectForSetters($currentEvent);

        TestReflection::callProtectedMethod(
            $this->beanMock,
            'modifyParticipants',
            array($participants, $component, 'ATTENDEE')
        );

        $nodes = $component->select('ATTENDEE');

        foreach ($nodes as $node) {

            $props = $node->parameters();
            $email = $node->getValue();
            $this->assertArrayHasKey($email, $participants);
            $this->assertEquals($participants[$email]['PARTSTAT'], $props['PARTSTAT']);
        }
    }

    /**
     * @param string $currentEvent
     * @param array $participants
     *
     * @covers       \CalDavEvent::addParticipants
     *
     * @dataProvider addParticipantsProvider
     */
    public function testAddParticipants($currentEvent, $participants)
    {
        $component = $this->getObjectForSetters($currentEvent);

        TestReflection::callProtectedMethod(
            $this->beanMock,
            'addParticipants',
            array($participants, $component, 'ATTENDEE')
        );

        $nodes = $component->select('ATTENDEE');

        foreach ($nodes as $node) {
            $props = $node->parameters();
            $email = $node->getValue();
            $this->assertArrayHasKey($email, $participants);
            $this->assertEquals($participants[$email]['PARTSTAT'], $props['PARTSTAT']);
            $this->assertEquals($participants[$email]['CN'], $props['CN']);
            $this->assertEquals($participants[$email]['ROLE'], $props['ROLE']);
        }
    }

    /**
     * @param string $currentEvent
     * @param array $participants
     *
     * @covers       \CalDavEvent::deleteParticipants
     *
     * @dataProvider deleteParticipantsProvider
     */
    public function testDeleteParticipants($currentEvent, $participants)
    {
        $component = $this->getObjectForSetters($currentEvent);

        TestReflection::callProtectedMethod(
            $this->beanMock,
            'deleteParticipants',
            array($participants, $component, 'ATTENDEE')
        );

        $nodes = $component->select('ATTENDEE');

        $emails = array();
        foreach ($nodes as $node) {
            $emails[$node->getValue()] = $node->getValue();
        }

        foreach ($participants as $key => $value) {
            $this->assertArrayNotHasKey($key, $emails);
        }

        $this->assertArrayHasKey('mailto:test2@test.com', $emails);
    }

    /**
     * @param string $currentEvent
     * @param array $users
     * @param array $contacts
     * @param array $davUsers
     * @param bool $expectedResult
     * @param array $expectedArguments
     * @param array|null $expectedMethods List of expected methods
     *
     * @covers       \CalDavEvent::setParticipants
     *
     * @dataProvider setParticipantsProvider
     */
    public function testSetParticipants(
        $currentEvent,
        array $users,
        array $contacts,
        array $davUsers,
        $expectedResult,
        $expectedMethods,
        array $expectedArguments
    ) {
        $component = $this->getObjectForSetters($currentEvent, $expectedMethods, 'VEVENT');

        $meeting = SugarTestMeetingUtilities::createMeeting();

        foreach ($davUsers as $user) {
            $createdUser = SugarTestUserUtilities::createAnonymousUser(true, 0, $user);
            if (!empty($user['addToMeeting'])) {
                SugarTestMeetingUtilities::addMeetingUserRelation($meeting->id, $createdUser->id);
            }
        }

        foreach ($users as $user) {
            $existingUser = BeanFactory::getBean('Users', $user['id']);
            if ($existingUser->id) {
                $existingUser->email1 = $user['email1'];
                $existingUser->save();
            } else {
                $createdUser = SugarTestUserUtilities::createAnonymousUser(true, 0, $user);
                SugarTestMeetingUtilities::addMeetingUserRelation($meeting->id, $createdUser->id);
            }
        }

        foreach ($contacts as $contact) {
            $createdContact = SugarTestContactUtilities::createContact($contact['id'], $contact);
            SugarTestMeetingUtilities::addMeetingContactRelation($meeting->id, $createdContact->id);
        }

        $this->beanMock->parent_type = 'Meetings';
        $this->beanMock->parent_id = $meeting->id;

        if ($expectedMethods) {
            foreach ($expectedMethods as $method) {
                $this->beanMock->expects($this->once())->method($method)->with($expectedArguments[$method], $component);
            }
        } else {
            $this->beanMock->expects($this->never())->method('addParticipants');
            $this->beanMock->expects($this->never())->method('deleteParticipants');
            $this->beanMock->expects($this->never())->method('modifyParticipants');
        }

        $result = $this->beanMock->setParticipants($component);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $currentEvent
     * @param array $user
     * @param array $davUser
     * @param bool $expectedResult
     * @param array $expectedArguments
     * @param array|null $expectedMethods List of expected methods
     *
     * @covers       \CalDavEvent::setOrganizer
     *
     * @dataProvider setOrganizerProvider
     */
    public function testSetOrganizer(
        $currentEvent,
        array $user,
        array $davUser,
        $expectedResult,
        $expectedMethods,
        array $expectedArguments
    ) {
        $component = $this->getObjectForSetters($currentEvent, $expectedMethods, 'VEVENT');

        $createdUser = null;

        if ($davUser) {
            SugarTestUserUtilities::createAnonymousUser(true, 0, $davUser);
            $createdUser = \BeanFactory::getBean('Users', $davUser['id']);
        }

        if ($createdUser) {
            $createdUser->email1 = $user['email1'];
            $createdUser->save();
        } else {
            $createdUser = SugarTestUserUtilities::createAnonymousUser(true, 0, $user);
        }
        $meeting = SugarTestMeetingUtilities::createMeeting('', $createdUser);
        SugarTestMeetingUtilities::addMeetingUserRelation($meeting->id, $createdUser->id);

        $this->beanMock->parent_type = 'Meetings';
        $this->beanMock->parent_id = $meeting->id;

        if ($expectedMethods) {
            foreach ($expectedMethods as $method) {
                $this->beanMock->expects($this->once())->method($method)->with($expectedArguments, $component);
            }
        } else {
            $this->beanMock->expects($this->never())->method('addParticipants');
            $this->beanMock->expects($this->never())->method('deleteParticipants');
            $this->beanMock->expects($this->never())->method('modifyParticipants');
        }

        $result = $this->beanMock->setOrganizer($component);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Configure mocks for set data tests
     * @param string $currentEvent
     * @param $eventMethods
     * @param string $type VEVENT of VTODO
     * @return Sabre\VObject\Component
     */
    protected function getObjectForSetters($currentEvent, $eventMethods = null, $type = 'VEVENT')
    {
        $this->beanMock = $this->getMockBuilder('CalDavEvent')
                               ->disableOriginalConstructor()
                               ->setMethods($eventMethods)
                               ->getMock();

        $dateTimeHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper')
                               ->disableOriginalConstructor()
                               ->setMethods(null)
                               ->getMock();

        $participantsHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper')
                                   ->disableOriginalConstructor()
                                   ->setMethods(null)
                                   ->getMock();

        $recurringHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper')
                                ->disableOriginalConstructor()
                                ->setMethods(null)
                                ->getMock();

        $acceptedMapper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\AcceptedMap')
                               ->disableOriginalConstructor()
                               ->setMethods(array('getMapping'))
                               ->getMock();

        $statusMapper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\EventMap')
                             ->disableOriginalConstructor()
                             ->setMethods(array('getMapping'))
                             ->getMock();

        $searchFactoryMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Factory')
                                  ->disableOriginalConstructor()
                                  ->setMethods(null)
                                  ->getMock();

        TestReflection::setProtectedValue($participantsHelper, 'statusMapper', $acceptedMapper);
        TestReflection::setProtectedValue($participantsHelper, 'searchFactory', $searchFactoryMock);

        TestReflection::setProtectedValue($this->beanMock, 'dateTimeHelper', $dateTimeHelper);
        TestReflection::setProtectedValue($this->beanMock, 'recurringHelper', $recurringHelper);
        TestReflection::setProtectedValue($this->beanMock, 'participantsHelper', $participantsHelper);
        TestReflection::setProtectedValue($this->beanMock, 'statusMapper', $statusMapper);

        $statusMapper->expects($this->any())
                     ->method('getMapping')
                     ->willReturn(TestReflection::getProtectedValue($statusMapper, 'statusMap'));

        $acceptedMapper->expects($this->any())
                       ->method('getMapping')
                       ->willReturn(TestReflection::getProtectedValue($acceptedMapper, 'statusMap'));

        $this->beanMock->calendardata = $currentEvent;

        return $this->beanMock->setComponent($type);
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

        $participantsHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper')
                                   ->disableOriginalConstructor()
                                   ->setMethods(null)
                                   ->getMock();

        $statusMapper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\EventMap')
                             ->disableOriginalConstructor()
                             ->setMethods(array('getMapping'))
                             ->getMock();

        $acceptedMapper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\AcceptedMap')
                               ->disableOriginalConstructor()
                               ->setMethods(array('getMapping'))
                               ->getMock();

        TestReflection::setProtectedValue($participantsHelper, 'statusMapper', $acceptedMapper);

        TestReflection::setProtectedValue($beanMock, 'dateTimeHelper', $dateTimeHelper);
        TestReflection::setProtectedValue($beanMock, 'participantsHelper', $participantsHelper);
        TestReflection::setProtectedValue($beanMock, 'statusMapper', $statusMapper);

        $statusMapper->expects($this->any())
                     ->method('getMapping')
                     ->willReturn(TestReflection::getProtectedValue($statusMapper, 'statusMap'));

        $acceptedMapper->expects($this->any())
                       ->method('getMapping')
                       ->willReturn(TestReflection::getProtectedValue($acceptedMapper, 'statusMap'));

        $beanMock->calendardata = $currentEvent;

        return $beanMock;
    }

    /**
     * test the Bean Sync Counter
     *
     * @group  caldav
     * @covers CalDavEvent::setBeanSyncCounter
     * @covers CalDavEvent::getBeanSyncCounter
     */
    public function testBeanSyncCounter()
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();

        $rand = rand(0, 999);

        $beanMock->module_sync_counter = $rand;

        $this->assertEquals(++$rand, $beanMock->setBeanSyncCounter());
        $this->assertEquals($rand, $beanMock->getBeanSyncCounter());
    }

    /**
     * test the Dav Sync Counter
     *
     * @group  caldav
     * @covers CalDavEvent::setDavSyncCounter
     * @covers CalDavEvent::getDavSyncCounter
     */
    public function testDavSyncCounter()
    {
        $beanMock = $this->getMockBuilder('CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();

        $rand = rand(0, 999);

        $beanMock->sync_counter = $rand;

        $this->assertEquals(++$rand, $beanMock->setDavSyncCounter());
        $this->assertEquals($rand, $beanMock->getDavSyncCounter());
    }

    /**
     * @param string $currentEvent
     * @param Sabre\VObject\Component\VCalendar $updatedEvent
     *
     * @covers       CalDavEvent::scheduleLocalDelivery
     *
     * @dataProvider scheduleLocalDeliveryProvider
     */
    public function testScheduleLocalDelivery($currentEvent, Sabre\VObject\Component\VCalendar $updatedEvent)
    {
        $this->getObjectForSetters($currentEvent, array('getRelatedCalendar'));

        $this->beanMock->setVCalendarEvent($updatedEvent);

        $userMock = $this->getMockBuilder('\User')
                         ->disableOriginalConstructor()
                         ->setMethods(array('getPreference'))
                         ->getMock();
        $userMock->user_name = 'test';

        $GLOBALS['current_user'] = $userMock;

        $this->beanMock->expects($this->any())->method('getCurrentUser')->wilLReturn($userMock);

        $serverHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\ServerHelper')
                             ->disableOriginalConstructor()
                             ->setMethods(array('setUp'))
                             ->getMock();

        $serverMock = $this->getMockBuilder('Sabre\Dav\Server')
                           ->disableOriginalConstructor()
                           ->setMethods(array('getPlugin'))
                           ->getMock();

        TestReflection::setProtectedValue($this->beanMock, 'serverHelper', $serverHelper);

        $serverHelper->expects($this->once())->method('setUp')->willReturn($serverMock);

        $scheduleMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Schedule\Plugin')
                             ->disableOriginalConstructor()
                             ->setMethods(array('calendarObjectSugarChange'))
                             ->getMock();

        $caldavMock = $this->getMockBuilder('Sabre\CalDAV\Plugin')
                           ->disableOriginalConstructor()
                           ->setMethods(null)
                           ->getMock();

        $serverMock->expects($this->at(0))->method('getPlugin')->with('caldav-schedule')->willReturn($scheduleMock);
        $serverMock->expects($this->at(1))->method('getPlugin')->with('caldav')->willReturn($caldavMock);

        $userCalendar = 'calendars/test/default';

        $scheduleMock->expects($this->once())
                     ->method('calendarObjectSugarChange')
                     ->with($updatedEvent, $userCalendar, $currentEvent);

        $this->beanMock->scheduleLocalDelivery();
    }
}
