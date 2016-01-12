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
require_once 'tests/SugarTestAddresseeUtilities.php';
require_once 'modules/CalDav/EventCollection.php';

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

use Sabre\VObject;

/**
 * CalDav bean tests
 * Class CalDavTest
 *
 *
 * @coversDefaultClass \CalDavEventCollection
 */
class CalDavEventCollectionTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \CalDavEventCollection
     */
    protected $beanMock;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        \BeanFactory::setBeanClass('Meetings', 'MeetingCRYS1322');
    }

    public function tearDown()
    {
        SugarTestCalDavUtilities::deleteAllCreatedCalendars();
        SugarTestCalDavUtilities::deleteCreatedEvents();

        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestMeetingUtilities::removeMeetingContacts();
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestAddresseeUtilities::removeAllCreatedAddresses();

        \BeanFactory::setBeanClass('Meetings');
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

    public function syncDataProvider()
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
            array(
                'content' => '',
                'size' => 0,
                'ETag' => 'd41d8cd98f00b204e9800998ecf8427e',
                'type' => null,
                'firstoccurence' => null,
                'lastoccurence' => null,
                'uid' => null,
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
UID:test1
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR',
            ),
            array(
                'content' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:test
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
                    'calendar_id' => '2',
                    'data_size' => '2',
                    'calendar_data' => '22',
                    'component_type' => 'VEVENT',
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
                'beanData' => array('id' => 1, 'calendar_id' => 1, 'deleted' => 0, 'uri' => 'uri'),
                'expectedChange' => array('calendar_id' => 1, 'operation' => 2, 'uri' => 'uri'),
            ),
            array(
                'beanData' => array('id' => null, 'calendar_id' => 1, 'deleted' => 0, 'uri' => 'uri'),
                'expectedChange' => array('calendar_id' => 1, 'operation' => 1, 'uri' => 'uri')
            ),
            array(
                'beanData' => array('id' => 1, 'calendar_id' => 1, 'deleted' => 1, 'uri' => 'uri'),
                'expectedChange' => array('calendar_id' => 1, 'operation' => 3, 'uri' => 'uri')
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

    public function getRRuleProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEventTemplate('vevent'),
                'instance' => 'Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule',
                'result' => array(
                    'getFrequency' => 'DAILY',
                    'getInterval' => 1,
                    'getCount' => null,
                    'getUntil' => new \SugarDateTime('20150813T080000Z', new \DateTimeZone('UTC')),
                    'getByDay' => array(),
                )
            ),
            array(
                'vEvent' => $this->getEventTemplate('recurring-byday-cnt2'),
                'instance' => 'Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule',
                'result' => array(
                    'getFrequency' => 'WEEKLY',
                    'getInterval' => 2,
                    'getCount' => 5,
                    'getUntil' => null,
                    'getByDay' => array('WE', 'TH'),
                )
            ),
            array(
                'vEvent' => $this->getEventTemplate('vemptyevent'),
                'instance' => null,
                'result' => array(),
            ),
            array(
                'vEvent' => $this->getEventTemplate('recurring-extended'),
                'instance' => null,
                'instance' => 'Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule',
                'result' => array(
                    'getFrequency' => 'YEARLY',
                    'getInterval' => 2,
                    'getByDay' => array('SU'),
                    'getByMonth' => array(1, 3),
                )
            ),
        );
    }

    public function setRRuleProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEventTemplate('recurring-extended'),
                'recurringParams' => array(
                    'setFrequency' => 'YEARLY',
                    'setInterval' => 2,
                    'setByDay' => array('SU'),
                    'setByMonth' => array(1, 3),
                ),
                'result' => false,
                'newParams' => array(
                    'getFrequency' => 'YEARLY',
                    'getInterval' => 2,
                    'getByDay' => array('SU'),
                    'getByMonth' => array(1, 3),
                ),
            ),
            array(
                'vEvent' => $this->getEventTemplate('vemptyevent'),
                'recurringParams' => array(
                    'setFrequency' => 'DAILY',
                    'setInterval' => 1,
                    'setBySetPos' => array(1),
                    'setCount' => null,
                    'setByDay' => array(),
                ),
                'result' => true,
                'newParams' => array(
                    'getFrequency' => 'DAILY',
                    'getInterval' => 1,
                    'getCount' => null,
                    'getUntil' => null,
                    'getByDay' => array(),
                    'getBySetPos' => array(1),
                ),
            ),
            array(
                'vEvent' => $this->getEventTemplate('vevent'),
                'recurringParams' => array(
                    'setFrequency' => 'DAILY',
                    'setInterval' => 2,
                    'setCount' => null,
                    'setByDay' => array(),
                ),
                'result' => true,
                'newParams' => array(
                    'getFrequency' => 'DAILY',
                    'getInterval' => 2,
                    'getCount' => null,
                    'getUntil' => null,
                    'getByDay' => array(),
                ),
            ),
            array(
                'vEvent' => $this->getEventTemplate('vevent'),
                'recurringParams' => array(
                    'setFrequency' => 'DAILY',
                    'setUntil' => new \SugarDateTime('20150813T110000', new \DateTimeZone('Europe/Minsk')),
                    'setByDay' => array(),
                ),
                'result' => false,
                'newParams' => array(
                    'getFrequency' => 'DAILY',
                    'getInterval' => 1,
                    'getCount' => null,
                    'getUntil' => new \SugarDateTime('20150813T080000', new \DateTimeZone('UTC')),
                    'getByDay' => array(),
                ),
            ),
            array(
                'vEvent' => $this->getEventTemplate('vevent'),
                'recurringParams' => array(
                    'setFrequency' => 'DAILY',
                    'setUntil' => new \SugarDateTime('20150813T080000', new \DateTimeZone('UTC')),
                    'setByDay' => array('TU'),
                ),
                'result' => true,
                'newParams' => array(
                    'getFrequency' => 'DAILY',
                    'getInterval' => 1,
                    'getCount' => null,
                    'getUntil' => new \SugarDateTime('20150813T080000', new \DateTimeZone('UTC')),
                    'getByDay' => array('TU'),
                ),
            ),
        );
    }

    public function getAllChildrenProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getEventTemplate('recurring'),
                'childrenCount' => 3,
                'children' => array(
                    array(
                        'getRecurrenceID' => new \SugarDateTime('20150902T090000', new DateTimeZone('Europe/Minsk'))
                    ),
                    array(
                        'getRecurrenceID' => new \SugarDateTime('20150903T090000', new DateTimeZone('Europe/Minsk'))
                    ),
                    array(
                        'getRecurrenceID' => new \SugarDateTime('20150904T090000', new DateTimeZone('Europe/Minsk'))
                    ),
                )
            ),
            array(
                'vCalendar' => $this->getEventTemplate('recurring-deleted'),
                'childrenCount' => 3,
                'children' => array(
                    array(
                        'getRecurrenceID' => new \SugarDateTime('20151110T090000', new DateTimeZone('Europe/Minsk'))
                    ),
                    array(
                        'getRecurrenceID' => new \SugarDateTime('20151111T090000', new DateTimeZone('Europe/Minsk'))
                    ),
                    array(
                        'getRecurrenceID' => new \SugarDateTime('20151112T090000', new DateTimeZone('Europe/Minsk'))
                    ),
                )
            ),
            array(
                'vCalendar' => null,
                'childrenCount' => 0,
                'children' => array(),
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vevent-not-recurring'),
                'childrenCount' => 0,
                'children' => array(),
            ),
        );
    }

    public function getDeletedChildrenRecurrenceIdsProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getEventTemplate('recurring-deleted'),
                'childrenCount' => 2,
                'children' => array(
                    new \SugarDateTime('20151111T090000', new DateTimeZone('Europe/Minsk')),
                    new \SugarDateTime('20151110T090000', new DateTimeZone('Europe/Minsk'))
                ),
            ),
        );
    }

    public function addChildProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getEventTemplate('recurring-deleted'),
                'recurringId' => new \SugarDateTime('20151113T090000', new DateTimeZone('Europe/Minsk')),
                'restoreDeleted' => false,
                'eventState' => 1,
            ),
            array(
                'vCalendar' => $this->getEventTemplate('recurring-deleted'),
                'recurringId' => new \SugarDateTime('20151111T090000', new DateTimeZone('Europe/Minsk')),
                'restoreDeleted' => false,
                'eventState' => 1,
            ),
            array(
                'vCalendar' => $this->getEventTemplate('recurring-deleted'),
                'recurringId' => new \SugarDateTime('20151211T090000', new DateTimeZone('Europe/Minsk')),
                'restoreDeleted' => false,
                'eventState' => 1,
            ),
        );
    }

    public function getAllChildrenRecurrenceIdsProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getEventTemplate('recurring'),
                'childrenCount' => 3,
                'children' => array(
                    new \SugarDateTime('20150902T090000', new DateTimeZone('Europe/Minsk')),
                    new \SugarDateTime('20150903T090000', new DateTimeZone('Europe/Minsk')),
                    new \SugarDateTime('20150904T090000', new DateTimeZone('Europe/Minsk'))
                ),
            ),
            array(
                'vCalendar' => null,
                'childrenCount' => 0,
                'children' => array(),
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vevent-not-recurring'),
                'childrenCount' => 0,
                'children' => array(),
            ),
        );
    }

    public function getCustomizedChildrenRecurrenceIdsProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getEventTemplate('recurring'),
                'childrenCount' => 1,
                'children' => array(
                    new \SugarDateTime('20150904T090000', new DateTimeZone('Europe/Minsk'))
                ),
            ),
            array(
                'vCalendar' => null,
                'childrenCount' => 0,
                'children' => array(),
            ),
            array(
                'vCalendar' => $this->getEventTemplate('vevent-not-recurring'),
                'childrenCount' => 0,
                'children' => array(),
            ),
        );
    }

    public function getParentProvider()
    {
        return array(
            array(
                'vCalendar' => $this->getEventTemplate('recurring'),
            ),
            array(
                'vCalendar' => null,
            ),
        );
    }

    public function removeDeletedProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEventTemplate('recurring-deleted'),
            ),
        );
    }

    public function mapParticipantsToBeansProvider()
    {
        $ids = array();
        for ($i = 0; $i < 12; $i ++) {
            $ids[] = \create_guid();
        }

        return array(
            array(
                'vEvent' => $this->getEventTemplate('vevent'),
                'sugarUsers' => array(
                    'Contacts' => array(
                        $ids[0] => array('email' => 'test0@test.com'),
                    ),
                    'Users' => array(),
                    'Leads' => array(
                        $ids[2] => array('email' => 'test0@test.com'),
                        $ids[3] => array('email' => 'test2@test.com'),
                    ),
                    'Addresses' => array(
                        $ids[4] => array('email' => 'test1@test.com'),
                    )
                ),
                'links' => array(
                    'test0@test.com' => array('beanName' => 'Contacts', 'beanId' => $ids[0]),
                    'test2@test.com' => array('beanName' => 'Leads', 'beanId' => $ids[3]),
                    'test1@test.com' => array('beanName' => 'Addresses', 'beanId' => $ids[4]),
                    'test@test.com' => array('beanName' => 'Addresses', 'beanId' => null),
                ),
                'newName' => array()
            ),
            array(
                'vEvent' => $this->getEventTemplate('vevent-with-displayname'),
                'sugarUsers' => array(
                    'Contacts' => array(),
                    'Users' => array(
                        $ids[5] => array('email1' => 'test@test.com', 'id' => $ids[5], 'new_with_id' => true),
                        $ids[6] => array('email1' => 'test1@test.com', 'id' => $ids[6], 'new_with_id' => true),
                    ),
                    'Leads' => array(
                        $ids[7] => array('email' => 'test1@test.com'),
                        $ids[8] => array('email' => 'test0@test.com'),
                    ),
                    'Addresses' => array(
                        $ids[9] => array('email' => 'test1@test.com'),
                    )
                ),
                'links' => array(
                    'test@test.com' => array('beanName' => 'Users', 'beanId' => $ids[5]),
                    'test1@test.com' => array('beanName' => 'Leads', 'beanId' => $ids[7]),
                    'test0@test.com' => array('beanName' => 'Leads', 'beanId' => $ids[8]),
                    'test2@test.com' => array('beanName' => 'Addresses', 'beanId' => null),
                ),
                'newName' => array(
                    'test2@test.com' => 'TestFirstName3 TestLastName3'
                ),
            ),
            array(
                'vEvent' => $this->getEventTemplate('recurring'),
                'sugarUsers' => array(
                    'Contacts' => array(),
                    'Users' => array(
                        $ids[10] => array('email1' => 'test@test.com', 'id' => $ids[10], 'new_with_id' => true),
                    ),
                    'Leads' => array(
                        $ids[11] => array('email' => 'test3@test.com'),
                    ),
                    'Addresses' => array(),
                ),
                'links' => array(
                    'test@test.com' => array('beanName' => 'Users', 'beanId' => $ids[10]),
                    'test3@test.com' => array('beanName' => 'Leads', 'beanId' => $ids[11]),
                    'test0@test.com' => array('beanName' => 'Addresses', 'beanId' => null),
                    'test1@test.com' => array('beanName' => 'Addresses', 'beanId' => null),
                    'test2@test.com' => array('beanName' => 'Addresses', 'beanId' => null),
                ),
                'newName' => array(
                    'test2@test.com' => 'Ms. Mimi Finkelstein'
                ),
            ),
        );
    }

    public function sugarChildrenOrderProvider()
    {
        $id1 = create_guid();
        $id2 = create_guid();
        $id3 = create_guid();

        return array(
            array(
                'ids' => array($id1, $id2, $id3),
                'result' => true,
                'getResult' => array($id1, $id2, $id3),
            ),
            array(
                'ids' => array($id1, $id2, 4, $id3),
                'result' => false,
                'getResult' => array(),
            ),
        );
    }

    /**
     * @param string $vEventText
     * @param array $beansToCreate
     * @param array $expectedLink
     * @param array $newName
     *
     * @covers       \CalDavEventCollection::mapParticipantsToBeans
     *
     * @dataProvider mapParticipantsToBeansProvider
     */
    public function testMapParticipantsToBeans($vEventText, $beansToCreate, $expectedLink, $newName)
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser();

        foreach ($beansToCreate['Contacts'] as $id => $params) {
            SugarTestContactUtilities::createContact($id, $params);
        }

        foreach ($beansToCreate['Leads'] as $id => $params) {
            SugarTestLeadUtilities::createLead($id, $params);
        }

        foreach ($beansToCreate['Users'] as $id => $params) {
            SugarTestUserUtilities::createAnonymousUser(true, 0, $params);
        }

        foreach ($beansToCreate['Addresses'] as $id => $params) {
            SugarTestAddresseeUtilities::createAddressee($id, $params);
        }

        $calendarID = SugarTestCalDavUtilities::createCalendar($sugarUser);
        $event = SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => $vEventText,
            'calendarid' => $calendarID,
            'eventURI' => 'test'
        ));

        $result = TestReflection::callProtectedMethod($event, 'mapParticipantsToBeans');

        foreach ($expectedLink as $email => &$data) {
            if ($data['beanId'] === null) {
                $this->assertArrayHasKey($email, $result);
                SugarTestAddresseeUtilities::setCreatedAddressee([$result[$email]['beanId']]);
                $data['beanId'] = $result[$email]['beanId'];

                $focus = \BeanFactory::getBean('Addresses', $result[$email]['beanId']);
                $focus->emailAddress->getPrimaryAddress($focus);

                $this->assertEquals($email, $focus->emailAddress->getPrimaryAddress($focus));

                if (isset($newName[$email])) {
                    $this->assertEquals($newName[$email], $focus->full_name);
                }
            }
        }

        $this->assertEquals($expectedLink, $result);
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
     * @covers       \CalDavEventCollection::save
     * @covers       \CalDavEventCollection::setCalendarEventData
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
        $this->assertEquals($expectedType, $saved->component_type);
        $this->assertEquals($expectedFirstOccurrence, $saved->first_occurence);
        $this->assertEquals($expectedLastOccurrence, $saved->last_occurence);
        $this->assertEquals($expectedUID, $saved->event_uid);
        $this->assertEquals($data, $saved->calendar_data);

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
     * Checking the calculation of params while bean saving
     * @param string $data
     * @param integer $expectedSize
     * @param string $expectedETag
     * @param string $expectedType
     * @param int $expectedFirstOccurrence
     * @param int $expectedLastOccurrence
     * @param string $expectedUID
     *
     * @covers       \CalDavEventCollection::sync
     *
     * @dataProvider syncDataProvider
     */
    public function testSync(
        $data,
        $expectedSize,
        $expectedETag,
        $expectedType,
        $expectedFirstOccurrence,
        $expectedLastOccurrence,
        $expectedUID
    ) {
        $beanMock = $this->getMockBuilder('CalDavEventCollection')
                         ->disableOriginalConstructor()
                         ->setMethods(array('setCalDavParent'))
                         ->getMock();

        $beanMock->expects($this->once())->method('setCalDavParent');

        $beanMock->setData($data);
        $beanMock->sync();

        $this->assertEquals($expectedSize, $beanMock->data_size);
        $this->assertEquals($expectedETag, $beanMock->etag);
        $this->assertEquals($expectedType, $beanMock->component_type);
        $this->assertEquals($expectedFirstOccurrence, $beanMock->first_occurence);
        $this->assertEquals($expectedLastOccurrence, $beanMock->last_occurence);
        $this->assertEquals($expectedUID, $beanMock->event_uid);
        $this->assertEquals($data, $beanMock->calendar_data);
    }

    /**
     * @covers \CalDavEventCollection::setCalDavParent
     * @covers \CalDavEventCollection::isImportable
     */
    public function testSetCalDavParent()
    {
        $eventData = $this->getEventTemplate('vevent');

        $sugarUser = SugarTestUserUtilities::createAnonymousUser();
        $calendarID = SugarTestCalDavUtilities::createCalendar($sugarUser, array());
        $event = SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => $eventData,
            'calendarid' => $calendarID,
            'eventURI' => 'test'
        ));

        $schedulingUser = SugarTestUserUtilities::createAnonymousUser();
        $schedulingCalendarID = SugarTestCalDavUtilities::createCalendar($schedulingUser, array());

        SugarTestCalDavUtilities::createSchedulingObject($schedulingUser, 'test1', $eventData);

        $event1 = \BeanFactory::getBean('CalDavEvents');
        $event1->uri = 'test1';
        $event1->setData($eventData);
        $event1->calendar_id = $schedulingCalendarID;

        $result = TestReflection::callProtectedMethod($event1, 'setCalDavParent');

        $this->assertTrue($result);
        $this->assertEquals('CalDavEvents', $event1->parent_type);
        $this->assertTrue($event->isImportable());
        $this->assertFalse($event1->isImportable());
    }

    /**
     * Checking the calculation of the size and ETag
     * @param string $data
     * @param integer $expectedSize
     * @param string $expectedETag
     *
     * @covers       \CalDavEventCollection::calculateSize
     * @covers       \CalDavEventCollection::calculateETag
     *
     * @dataProvider sizeAndETagDataProvider
     */
    public function testSizeAndETag($data, $expectedSize, $expectedETag)
    {
        $beanMock = $this->getMockBuilder('CalDavEventCollection')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();

        $beanMock->setData($data);

        TestReflection::callProtectedMethod($beanMock, 'calculateSize');
        TestReflection::callProtectedMethod($beanMock, 'calculateETag');

        $this->assertEquals($expectedSize, $beanMock->data_size);
        $this->assertEquals($expectedETag, $beanMock->etag);
    }

    /**
     * Checks algorithm for determining the type of component
     * @param string $data
     * @param string $expectedComponent
     * @covers       \CalDavEventCollection::calculateComponentType
     *
     * @dataProvider componentTypeProvider
     */
    public function testComponentType($data, $expectedComponent)
    {
        $beanMock = $this->getMockBuilder('CalDavEventCollection')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();
        TestReflection::callProtectedMethod($beanMock, 'calculateComponentType', array($data));

        $this->assertEquals($expectedComponent, $beanMock->component_type);
    }

    /**
     * Checks that the necessary methods are invoked
     * @param string $data
     * @covers       \CalDavEventCollection::setCalendarEventData
     *
     * @dataProvider calendarObjectProvider
     */
    public function testSetCalendarObject($data)
    {
        $beanMock = $this->getMockBuilder('CalDavEventCollection')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();


        $beanMock->setData($data);

        $this->assertEquals($data, $beanMock->calendar_data);
    }

    /**
     * Check calculation firstoccurence and lastoccurence
     * @param string $data
     * @param $expectedFirstOccurrence
     * @param $expectedLastOccurrence
     *
     * @covers       \CalDavEventCollection::calculateTimeBoundaries
     *
     * @dataProvider calendarObjectBoundariesProvider
     */
    public function testCalculateTimeBoundaries($data, $expectedFirstOccurrence, $expectedLastOccurrence)
    {
        $beanMock = $this->getMockBuilder('CalDavEventCollection')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();

        $beanMock->setData($data);

        TestReflection::callProtectedMethod($beanMock, 'calculateTimeBoundaries');

        $this->assertEquals($expectedFirstOccurrence, $beanMock->first_occurence);
        $this->assertEquals($expectedLastOccurrence, $beanMock->last_occurence);
    }

    /**
     * Test for set calendarid bean property
     * @covers \CalDavEventCollection::setCalendarId
     */
    public function testSetCalendarId()
    {
        $beanMock = $this->getMockBuilder('CalDavEventCollection')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();
        $beanMock->setCalendarId('test');
        $this->assertEquals('test', $beanMock->calendar_id);
    }

    /**
     * Test for set uri bean property
     * @covers \CalDavEventCollection::setCalendarEventURI
     */
    public function testSetCalendarObjectURI()
    {
        $beanMock = $this->getMockBuilder('CalDavEventCollection')
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
     * @covers       \CalDavEventCollection::toCalDavArray
     *
     * @dataProvider toCalDavArrayProvider
     */
    public function testToCalDavArray($beanData, $expectedArray)
    {
        $beanMock = $this->getMockBuilder('CalDavEventCollection')
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
     * @covers       \CalDavEventCollection::addChange
     *
     * @dataProvider addChangeProvider
     */
    public function testAddChange(array $beanData, array $expectedChange)
    {
        $beanMock = $this->getMockBuilder('CalDavEventCollection')
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
     * @covers       \CalDavEventCollection::getVCalendar
     *
     * @dataProvider getVObjectProvider
     */
    public function testGetVCalendar($vCalendarEventText)
    {
        $beanMock = $this->getMockBuilder('CalDavEventCollection')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();

        $beanMock->calendar_data = $vCalendarEventText;

        $result = TestReflection::callProtectedMethod($beanMock, 'getVCalendar');

        $this->assertInstanceOf('Sabre\VObject\Component\VCalendar', $result);
    }

    /**
     * @param string $vCalendarEventText
     * @param string $expectedResult
     *
     * @covers       \CalDavEventCollection::getTimeZone
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
     * @param string $expectedInstance
     * @param array $expectedRules
     *
     * @covers       \CalDavEventCollection::getRRule
     *
     * @dataProvider getRRuleProvider
     */
    public function testGetRRule($vCalendarEventText, $expectedInstance, array $expectedRules)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = $beanMock->getRRule();

        if ($expectedInstance) {
            $this->assertInstanceOf($expectedInstance, $result);
        } else {
            $this->assertNull($result);
        }

        foreach ($expectedRules as $method => $value) {
            $this->assertEquals($value, $result->$method());
        }
    }

    /**
     * @covers \CalDavEventCollection::getBean
     */
    public function testGetBean()
    {
        $beanMock = $this->getMockBuilder('CalDavEventCollection')
                         ->disableOriginalConstructor()
                         ->setMethods(array('save'))
                         ->getMock();

        $result = $beanMock->getBean();

        $this->assertNull($result);

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
     * @covers \CalDavEventCollection::setBean
     */
    public function testSetBean()
    {
        $beanMock = $this->getMockBuilder('CalDavEventCollection')
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
     * @param string $vCalendarEventText
     * @param array $recurringParams
     * @param bool $expectedResult
     * @param array $expectedParams
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::setRRule
     *
     * @dataProvider setRRuleProvider
     */
    public function testSetRRule($vCalendarEventText, array $recurringParams, $expectedResult, $expectedParams)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);
        $rRule = new \Sugarcrm\Sugarcrm\Dav\Cal\Structures\RRule();

        foreach ($recurringParams as $method => $value) {
            $rRule->$method($value);
        }

        $result = $beanMock->setRRule($rRule);

        $this->assertEquals($expectedResult, $result);

        $rRule = $beanMock->getRRule();

        foreach ($expectedParams as $method => $value) {
            $this->assertEquals($value, $rRule->$method());
        }
    }

    /**
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::setRRule
     */
    public function testDeletedRRule()
    {
        $beanMock = $this->getObjectForGetters($this->getEventTemplate('recurring-byday-cnt2'));

        $result = $beanMock->setRRule(null);

        $this->assertTrue($result);

        $rRule = $beanMock->getRRule();
        $children = $beanMock->getAllChildrenRecurrenceIds();
        $this->assertNull($rRule);
        $this->assertEmpty($children);
    }

    /**
     * @param $vCalendarEventText
     *
     * @covers       \CalDavEventCollection::getParent
     *
     * @dataProvider getParentProvider
     */
    public function testGetParent($vCalendarEventText)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = $beanMock->getParent();

        $this->assertInstanceOf('Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event', $result);
    }

    /**
     * @param string $vCalendarEventText
     * @param int $childrenCount
     * @param array $expectedChildren
     *
     * @covers       \CalDavEventCollection::getAllChildren
     *
     * @dataProvider getAllChildrenProvider
     */
    public function testGetAllChildren($vCalendarEventText, $childrenCount, array $expectedChildren)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $children = TestReflection::callProtectedMethod($beanMock, 'getAllChildren');

        $this->assertEquals($childrenCount, count($children));

        $children = array_values($children);
        foreach ($expectedChildren as $index => $child) {
            foreach ($child as $method => $value) {
                $this->assertEquals($value, $children[$index]->$method());
            }
        }
    }

    /**
     * @param string $vCalendarEventText
     * @param int $childrenCount
     * @param array $expectedChildren
     *
     * @covers       \CalDavEventCollection::getDeletedChildrenRecurrenceIds
     *
     * @dataProvider getDeletedChildrenRecurrenceIdsProvider
     */
    public function testGetDeletedChildrenRecurrenceIds($vCalendarEventText, $childrenCount, array $expectedChildren)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);
        $children = $beanMock->getDeletedChildrenRecurrenceIds();
        $this->assertEquals($childrenCount, count($children));
        foreach ($expectedChildren as $child) {
            $this->assertEquals($child, $children[$child->getTimestamp()]);
        }

    }

    /**
     * @param string $vCalendarEventText
     * @param int $childrenCount
     * @param array $expectedChildren
     *
     * @covers       \CalDavEventCollection::getAllChildrenRecurrenceIds
     *
     * @dataProvider getAllChildrenRecurrenceIdsProvider
     */
    public function testGetAllChildrenRecurrenceIds($vCalendarEventText, $childrenCount, array $expectedChildren)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $children = $beanMock->getAllChildrenRecurrenceIds();

        $this->assertEquals($childrenCount, count($children));

        foreach ($expectedChildren as $child) {
            $this->assertEquals($child, $children[$child->getTimestamp()]);
        }
    }

    /**
     * @param string $vCalendarEventText
     * @param int $childrenCount
     * @param array $expectedChildren
     *
     * @covers       \CalDavEventCollection::getCustomizedChildrenRecurrenceIds
     *
     * @dataProvider getCustomizedChildrenRecurrenceIdsProvider
     */
    public function testGetCustomizedChildrenRecurrenceIds($vCalendarEventText, $childrenCount, array $expectedChildren)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $children = $beanMock->getCustomizedChildrenRecurrenceIds();

        $this->assertEquals($childrenCount, count($children));

        foreach ($expectedChildren as $child) {
            $this->assertEquals($child, $children[$child->getTimestamp()]);
        }
    }

    /**
     * @covers \CalDavEventCollection::getChild
     */
    public function testEditExistingNotCustomChild()
    {
        $event = $this->getEventTemplate('recurring');

        $beanMock = $this->getObjectForGetters($event);
        $recurrenceId = new \SugarDateTime('20150902T090000', new DateTimeZone('Europe/Minsk'));
        $child = $beanMock->getChild($recurrenceId);

        $this->assertEquals(true, $child->isVirtual());
        $child->setTitle('test');
        $this->assertEquals(true, $child->isCustomized());
    }

    /**
     * @covers \CalDavEventCollection::getChild
     */
    public function testEditExistingCustomChild()
    {
        $event = $this->getEventTemplate('recurring');

        $beanMock = $this->getObjectForGetters($event);
        $recurrenceId = new \SugarDateTime('20150904T090000', new DateTimeZone('Europe/Minsk'));
        $child = $beanMock->getChild($recurrenceId);

        $this->assertEquals(true, $child->isCustomized());
        $child->setTitle('test');
        $this->assertEquals(true, $child->isCustomized());
    }

    /**
     * @covers \CalDavEventCollection::getChild
     * @covers \CalDavEventCollection::addChild
     */
    public function testEditNotExistingChild()
    {
        $event = $this->getEventTemplate('recurring');

        $beanMock = $this->getObjectForGetters($event);
        $recurrenceId = new \SugarDateTime('20150908T090000', new DateTimeZone('Europe/Minsk'));
        $child = $beanMock->getChild($recurrenceId);

        $this->assertNull($child);
    }

    /**
     * @covers \CalDavEventCollection::getChild
     */
    public function testEditDeletedChild()
    {
        $event = $this->getEventTemplate('recurring-deleted');

        $beanMock = $this->getObjectForGetters($event);
        $recurrenceId = new \SugarDateTime('20151110T090000', new DateTimeZone('Europe/Minsk'));
        $child = $beanMock->getChild($recurrenceId);

        $this->assertNull($child);
    }

    /**
     * @covers \CalDavEventCollection::getChild
     */
    public function testEditDeletedChildWithRestore()
    {
        $event = $this->getEventTemplate('recurring-deleted');

        $beanMock = $this->getObjectForGetters($event);
        $recurrenceId = new \SugarDateTime('20151110T090000', new DateTimeZone('Europe/Minsk'));
        $child = $beanMock->getChild($recurrenceId, true);

        $this->assertEquals(true, $child->isCustomized());
    }

    /**
     * @param string $vCalendarEventText
     * @param \SugarDateTime $recurringId
     * @param bool $restoreDeleted
     * @param int $eventState
     *
     * @covers       \CalDavEventCollection::addChild
     *
     * @dataProvider addChildProvider
     */
    public function testAddChild($vCalendarEventText, $recurringId, $restoreDeleted, $eventState)
    {
        $beanMock = $this->getObjectForGetters($vCalendarEventText);

        $result = TestReflection::callProtectedMethod($beanMock, 'addChild', array($recurringId, $restoreDeleted));

        $this->assertInstanceOf('Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event', $result);

        $state = TestReflection::getProtectedValue($result, 'state');

        $this->assertEquals($eventState, $state);
    }

    /**
     * @param string $vCalendarEventText
     * @covers       \CalDavEventCollection::removeFromDeleted
     *
     * @dataProvider removeDeletedProvider
     */
    public function testRemoveDeleted($vCalendarEventText)
    {
        $eventMock = $this->getObjectForGetters($vCalendarEventText);
        $result = TestReflection::callProtectedMethod(
            $eventMock,
            'removeFromDeleted',
            array(new \SugarDateTime('20151110T090000', new \DateTimeZone('Europe/Minsk')))
        );
        $this->assertTrue($result);
    }

    /**
     * @param array $ids
     * @param bool $expectedResult
     * @param array $expectedGet
     *
     * @covers       \CalDavEventCollection::setSugarChildrenOrder
     * @covers       \CalDavEventCollection::getSugarChildrenOrder
     *
     * @dataProvider sugarChildrenOrderProvider
     */
    public function testSugarChildrenOrder(array $ids, $expectedResult, array $expectedGet)
    {
        $beanMock = $this->getMockBuilder('CalDavEventCollection')
                         ->disableOriginalConstructor()
                         ->setMethods(null)
                         ->getMock();

        $result = $beanMock->setSugarChildrenOrder($ids);

        $this->assertEquals($expectedResult, $result);

        $result = $beanMock->getSugarChildrenOrder();

        $this->assertEquals($expectedGet, $result);
    }

    /**
     * @covers \CalDavEventCollection::scheduleLocalDelivery
     */
    public function testScheduleLocalDelivery()
    {
        $sugarUser = SugarTestUserUtilities::createAnonymousUser(true, 0, array('email1' => 'test10@test.com'));
        $GLOBALS['current_user'] = $sugarUser;
        $calendarID = SugarTestCalDavUtilities::createCalendar($sugarUser, array());

        $attendee1 = SugarTestUserUtilities::createAnonymousUser(true, 0, array('email1' => 'test11@test.com'));
        $attendeeCalendar1 = SugarTestCalDavUtilities::createCalendar($attendee1, array());

        $attendee2 = SugarTestUserUtilities::createAnonymousUser(true, 0, array('email1' => 'test12@test.com'));
        $attendeeCalendar2 = SugarTestCalDavUtilities::createCalendar($attendee2, array());

        $event = SugarTestCalDavUtilities::createEvent(array(
            'calendardata' => $this->getEventTemplate('vevent-attendee-needaction'),
            'calendarid' => $calendarID,
            'eventURI' => 'test'
        ), true);

        $GLOBALS['current_user'] = $attendee1;

        $parent = $event->getParent();
        $participants = $parent->getParticipants();

        $participant = $participants[$parent->findParticipantsByEmail('test11@test.com')];
        $participant->setStatus('ACCEPTED');
        $event->save();
        $this->checkScheduleStatus($event, $attendeeCalendar1, 'test11@test.com', 'ACCEPTED');
        $this->checkScheduleStatus($event, $attendeeCalendar2, 'test11@test.com', 'ACCEPTED');

        $participant = $participants[$parent->findParticipantsByEmail('test12@test.com')];
        $participant->setStatus('DECLINED');
        $event->save();

        $this->checkScheduleStatus($event, $attendeeCalendar1, 'test11@test.com', 'ACCEPTED');
        $this->checkScheduleStatus($event, $attendeeCalendar2, 'test11@test.com', 'ACCEPTED');
        $this->checkScheduleStatus($event, $attendeeCalendar1, 'test12@test.com', 'DECLINED');
        $this->checkScheduleStatus($event, $attendeeCalendar2, 'test12@test.com', 'DECLINED');
    }

    /**
     * Check attendee status
     * @param CalDavEventCollection $event
     * @param string $calendarId
     * @param string $attendeeURI
     * @param string $expectedStatus
     * @throws SugarQueryException
     */
    protected function checkScheduleStatus(\CalDavEventCollection $event, $calendarId, $attendeeURI, $expectedStatus)
    {
        $query = new \SugarQuery();
        $query->from($event);
        $query->where()->equals('calendar_id', $calendarId);
        $query->where()->equals('event_uid', $event->event_uid);
        $foundEvent = array_shift($event->fetchFromQuery($query));
        $parent = $foundEvent->getParent();
        $participants = $parent->getParticipants();
        $found = $participants[$parent->findParticipantsByEmail($attendeeURI)];
        $this->assertEquals($expectedStatus, $found->getStatus());
    }

    /**
     * Configure mocks for get data tests
     * @param string $currentEvent
     * @param array $mockMethods
     * @return \CalDavEvent_Mock
     */
    protected function getObjectForGetters($currentEvent, $mockMethods = null)
    {
        $beanMock = $this->getMockBuilder('CalDavEventCollection')
                         ->disableOriginalConstructor()
                         ->setMethods($mockMethods)
                         ->getMock();

        $dateTimeHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper')
                               ->disableOriginalConstructor()
                               ->setMethods(null)
                               ->getMock();

        TestReflection::setProtectedValue($beanMock, 'dateTimeHelper', $dateTimeHelper);

        $beanMock->calendar_data = $currentEvent;

        return $beanMock;
    }

    /**
     * @covers CalDavEventCollection::getSynchronizationObject
     */
    public function testGetSynchronizationObject()
    {
        $event = SugarTestCalDavUtilities::createEvent();

        $syncBean = $event->getSynchronizationObject();
        $this->assertEquals($event->id, $syncBean->event_id);

        $syncBean = $event->getSynchronizationObject();
        $this->assertEquals($event->id, $syncBean->event_id);
    }

    /**
     * Test prepare for invite
     *
     * @covers \CalDavEventCollection prepareForInvite
     */
    public function testPrepareForInvite()
    {
        $meetingMock = \BeanFactory::getBean('Meetings', '1b6705f9-d098-130f-39aa-5671685940de');

        $result = \CalDavEventCollection::prepareForInvite($meetingMock);
        $this->assertContains('METHOD:REQUEST', $result);
        $this->assertContains('X-SUGAR-ID:1b6705f9-d098-130f-39aa-5671685940de', $result);
        $this->assertContains('X-SUGAR-NAME:Meetings', $result);
    }

    /**
     * @param $oldData
     * @param $currentData
     * @param $expectedChangedFields
     * @param $expectedInvites
     *
     * @dataProvider prepareForImportProvider
     * @covers       \CalDavEventCollection::getDiffStructure
     */
    public function testGetDiffStructure($oldData, $currentData, $expectedBeanParams, $expectedChangedFields, $expectedInvites)
    {
        $event = $this->getObjectForGetters($currentData, array('mapParticipantsToBeans'));

        $importData = $event->getDiffStructure($oldData);
        if ($importData) {
            foreach ($importData as $key => $data) {
                list($beanData, $changedFields, $invites) = $data;
                $this->assertEquals($expectedBeanParams[$key], $beanData);
                $this->assertEquals($expectedChangedFields[$key], $changedFields);
                $this->assertEquals($expectedInvites[$key], $invites);
            }
        } else {
            $this->assertEmpty($importData);
        }
    }

    /**
     * @return array
     */
    public function prepareForImportProvider()
    {
        return array(
            array(
                'oldEvent' => $this->getEventTemplate('vevent'),
                'currentEvent' => $this->getEventTemplate('vevent'),
                'expectedBean' => null,
                'changedFields' => null,
                'expectedInvites' => null,
            ),
            array(
                'oldEvent' => '',
                'currentEvent' => $this->getEventTemplate('vevent-diff-recurring'),
                'expectedBean' => array(
                    array(
                        null,
                        null,
                        null,
                        null,
                        true,
                    ),
                    array(
                        null,
                        array(),
                        (new SugarDateTime('20160106T110000', new DateTimeZone('Europe/Minsk')))->asDb(),
                        false,
                        false,
                    )
                ),
                'changedFields' => array(
                    array(
                        'title' => array('test event 1'),
                        'description' => array(null),
                        'location' => array(null),
                        'status' => array(null),
                        'date_start' => array(
                            (new SugarDateTime('20160105T110000', new DateTimeZone('Europe/Minsk')))->asDb()
                        ),
                        'date_end' => array(
                            (new SugarDateTime('20160105T120000', new DateTimeZone('Europe/Minsk')))->asDb()
                        ),
                        'rrule' => array(
                            'frequency' => array('DAILY'),
                            'interval' => array(1),
                            'count' => array(10),
                            'until' => array(null),
                            'byday' => array(array()),
                            'action' => 'added',
                        )
                    ),
                    array(
                        'title' => array('test event 2'),
                        'description' => array(null),
                        'location' => array('office'),
                        'status' => array(null),
                        'date_start' => array(
                            (new SugarDateTime('20160106T110000', new DateTimeZone('Europe/Minsk')))->asDb()
                        ),
                        'date_end' => array(
                            (new SugarDateTime('20160106T120000', new DateTimeZone('Europe/Minsk')))->asDb()
                        ),
                    ),
                ),
                'expectedInvites' => array(
                    array(
                        'added' => array(
                            array(
                                null,
                                null,
                                null,
                                '/amze5odc4ntc5mze5odc4nqzhqkf3bxlx0byyian9ss8xna6rivu-whvwr-twk49m/principal/',
                                'Dmitry Dolbik',
                            ),
                            array(
                                null,
                                null,
                                'NEEDS-ACTION',
                                'sally@example.com',
                                'Sally Bronsen',
                            ),
                        ),
                    ),
                    array(
                        'added' => array(
                            array(
                                null,
                                null,
                                null,
                                '/amze5odc4ntc5mze5odc4nqzhqkf3bxlx0byyian9ss8xna6rivu-whvwr-twk49m/principal/',
                                'Dmitry Dolbik',
                            ),
                            array(
                                null,
                                null,
                                'NEEDS-ACTION',
                                'sugar.vegan.phone@example.edu',
                                'Doyle Brow',
                            ),
                            array(
                                null,
                                null,
                                'NEEDS-ACTION',
                                'sally@example.com',
                                'Sally Bronsen',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'oldEvent' => $this->getEventTemplate('vevent-diff'),
                'currentEvent' => $this->getEventTemplate('vevent-diff-child'),
                'expectedBean' => array(
                    array(
                        null,
                        array(),
                        (new SugarDateTime('20160106T110000', new DateTimeZone('Europe/Minsk')))->asDb(),
                        false,
                        false,
                    )
                ),
                'changedFields' => array(
                    array(
                        'title' => array('test event 2', 'test event'),
                        'location' => array('office', null),
                    ),
                ),
                'expectedInvites' => array(
                    array(
                        'added' => array(
                            array(
                                null,
                                null,
                                'NEEDS-ACTION',
                                'sugar.vegan.phone@example.edu',
                                'Doyle Brow',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'oldEvent' => $this->getEventTemplate('vevent-diff'),
                'currentEvent' => $this->getEventTemplate('vevent-diff-recurring'),
                'expectedBean' => array(
                    array(
                        null,
                        null,
                        null,
                        null,
                        false,
                    ),
                    array(
                        null,
                        array(),
                        (new SugarDateTime('20160106T110000', new DateTimeZone('Europe/Minsk')))->asDb(),
                        false,
                        false,
                    )
                ),
                'changedFields' => array(
                    array(
                        'title' => array('test event 1', 'test event'),
                    ),
                    array(
                        'title' => array('test event 2', 'test event'),
                        'location' => array('office', null),
                    ),
                ),
                'expectedInvites' => array(
                    array(),
                    array(
                        'added' => array(
                            array(
                                null,
                                null,
                                'NEEDS-ACTION',
                                'sugar.vegan.phone@example.edu',
                                'Doyle Brow',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'oldEvent' => '',
                'currentEvent' => $this->getEventTemplate('vevent'),
                'expectedBean' => array(
                    array(
                        null,
                        null,
                        null,
                        null,
                        true,
                    )
                ),
                'changedFields' => array(
                    array(
                        'title' => array('Test event title'),
                        'description' => array('Test event description'),
                        'location' => array('office'),
                        'status' => array(null),
                        'date_start' => array(
                            (new SugarDateTime('2015-08-06 10:00:00', new DateTimeZone('Europe/Berlin')))->asDb()
                        ),
                        'date_end' => array(
                            (new SugarDateTime('2015-08-06 11:00:00', new DateTimeZone('Europe/Berlin')))->asDb()
                        ),
                        'rrule' => array(
                            'frequency' => array('DAILY'),
                            'interval' => array(1),
                            'count' => array(null),
                            'until' => array((new SugarDateTime('20150813T080000Z', new DateTimeZone('UTC')))->asDb()),
                            'byday' => array(array()),
                            'action' => 'added',
                        )
                    ),
                ),
                'expectedInvites' => array(
                    array(
                        'added' => array(
                            array(
                                null,
                                null,
                                'ACCEPTED',
                                'test0@test.com',
                                'Test0',
                            ),
                            array(
                                null,
                                null,
                                'NEEDS-ACTION',
                                'test@test.com',
                                null
                            ),
                            array(
                                null,
                                null,
                                'ACCEPTED',
                                'test1@test.com',
                                null,
                            ),
                            array(
                                null,
                                null,
                                'DECLINED',
                                'test2@test.com',
                                null,
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'oldEvent' => $this->getEventTemplate('vevent'),
                'currentEvent' => $this->getEventTemplate('vevent-after-edit'),
                'expectedBean' => array(
                    array(
                        null,
                        null,
                        null,
                        null,
                        false,
                    )
                ),
                'changedFields' => array(
                    array(
                        'title' => array('Test event title 1', 'Test event title'),
                        'description' => array(null, 'Test event description'),
                        'status' => array('CONFIRMED', null),
                        'location' => array('home', 'office'),
                        'date_start' => array(
                            (new SugarDateTime('2015-08-06 09:00:00', new DateTimeZone('Europe/Berlin')))->asDb(),
                            (new SugarDateTime('2015-08-06 10:00:00', new DateTimeZone('Europe/Berlin')))->asDb(),
                        ),
                        'rrule' => array(
                            'frequency' => array('WEEKLY', 'DAILY'),
                            'interval' => array(2, 1),
                            'count' => array(10, null),
                            'until' => array(
                                null,
                                (new SugarDateTime('20150813T080000Z', new DateTimeZone('UTC')))->asDb()
                            ),
                            'byday' => array(array('MO'), array()),
                            'action' => 'updated'
                        ),
                    ),
                ),
                'expectedInvites' => array(
                    array(
                        'added' => array(
                            array(
                                null,
                                null,
                                'ACCEPTED',
                                'test5@test.com',
                                null
                            ),
                        ),
                        'deleted' => array(
                            array(
                                null,
                                null,
                                'NEEDS-ACTION',
                                'test@test.com',
                                null
                            ),
                            array(
                                null,
                                null,
                                'ACCEPTED',
                                'test1@test.com',
                                null,
                            ),
                        ),
                        'changed' => array(
                            array(
                                null,
                                null,
                                'ACCEPTED',
                                'test2@test.com',
                                null
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
}

/**
 * Stub class for Meeting bean
 */
class MeetingCRYS1322 extends Meeting
{
    public function retrieve($id)
    {
        $this->populateFromRow(array(
            'name' => 'Meeting1102415055',
            'date_entered' => '2015-12-16 13:34:54',
            'date_modified' => '2015-12-16 13:34:54',
            'modified_user_id' => '531fc10a-78e6-157a-cced-567168676bd3',
            'created_by' => '531fc10a-78e6-157a-cced-567168676bd3',
            'description' => null,
            'deleted' => '0',
            'location' => null,
            'duration_hours' => '0',
            'duration_minutes' => '15',
            'date_start' => '2015-12-16 13:34:54',
            'date_end' => '2015-12-16 13:49:54',
            'parent_type' => null,
            'status' => 'Planned',
            'type' => 'Sugar',
            'parent_id' => null,
            'reminder_time' => '-1',
            'email_reminder_time' => '-1',
            'email_reminder_sent' => '0',
            'sequence' => '0',
            'repeat_type' => null,
            'repeat_interval' => '1',
            'repeat_dow' => null,
            'repeat_until' => null,
            'repeat_count' => null,
            'repeat_parent_id' => null,
            'recurring_source' => null,
            'assigned_user_id' => '531fc10a-78e6-157a-cced-567168676bd3',
        ));

        $this->id = $id;

        $this->fetched_row = $this->toArray(true);

        return $this;
    }
}
