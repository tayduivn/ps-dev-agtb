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

namespace Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Schedule;

/**
 * Class PluginTest
 * @package            Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Schedule
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Cal\Schedule\Plugin
 */
class PluginTest extends DAVServerMock
{
    public $setupCalDAV = true;
    public $setupCalDAVScheduling = true;
    public $setupACL = true;
    public $autoLogin = 'user1';

    public $caldavCalendars = array(
        array(
            'principaluri' => 'principals/user1',
            'uri' => 'cal',
        ),
        array(
            'principaluri' => 'principals/user2',
            'uri' => 'cal',
        ),
        array(
            'principaluri' => 'principals/user3',
            'uri' => 'cal',
        ),
    );

    public function setUp()
    {
        $this->calendarObjectUri = '/calendars/user1/cal/object.ics';
        parent::setUp();
    }

    public function scheduleLocalDeliveryProvider()
    {
        return array(
            array(
                'source' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR',
                'expectedObjectForUser1' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE;SCHEDULE-STATUS=1.2:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR',
                'expectedObjectForUser2' => 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject 3.4.5//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user1.sabredav@sabredav.org
ATTENDEE;PARTSTAT=NEEDS-ACTION:mailto:user2.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR'
            )
        );
    }

    public function calendarObjectSugarChangeProvider()
    {
        return array(
            array(
                'oldObject' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user2.sabredav@sabredav.org
ATTENDEE;PARTSTAT=ACCEPTED:mailto:user2.sabredav@sabredav.org
ATTENDEE:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user3.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR',
                'newObject' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER:mailto:user2.sabredav@sabredav.org
ATTENDEE;PARTSTAT=ACCEPTED:mailto:user2.sabredav@sabredav.org
ATTENDEE;PARTSTAT=ACCEPTED:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user3.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR',
                'expectedObject' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
DTSTART:20140811T230000Z
ORGANIZER;SCHEDULE-STATUS=1.2:mailto:user2.sabredav@sabredav.org
ATTENDEE;PARTSTAT=ACCEPTED:mailto:user2.sabredav@sabredav.org
ATTENDEE;PARTSTAT=ACCEPTED:mailto:user1.sabredav@sabredav.org
ATTENDEE:mailto:user3.sabredav@sabredav.org
END:VEVENT
END:VCALENDAR',
            )
        );
    }

    /**
     * @param string $newObject
     * @param string $expectedObjectForUser1
     * @param string $expectedObjectForUser2
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Schedule\Plugin::scheduleLocalDelivery
     *
     * @dataProvider scheduleLocalDeliveryProvider
     */
    public function testScheduleLocalDelivery($newObject, $expectedObjectForUser1, $expectedObjectForUser2)
    {
        $this->deliver(null, $newObject);
        $this->assertItemsInInbox('user2', 1);
        $this->assertVObjEquals($expectedObjectForUser1, $newObject);
        $this->assertItemsInCalendar('user2', $expectedObjectForUser2);
    }

    /**
     * @param string $oldObject
     * @param string $newObject
     * @param string $expectedObject
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Schedule\Plugin::calendarObjectSugarChange
     *
     * @dataProvider calendarObjectSugarChangeProvider
     */
    public function testCalendarObjectSugarChange($oldObject, $newObject, $expectedObject)
    {
        $this->putPath('/calendars/user2/cal/object.ics', $oldObject);
        $newCalendar = \Sabre\VObject\Reader::read($newObject);
        $this->caldavSchedulePlugin->calendarObjectSugarChange($newCalendar, $oldObject, 'user1');

        $this->assertItemsInInbox('user2', 1);
        $this->assertItemsInInbox('user1', 0);

        $this->assertVObjEquals($expectedObject, $newCalendar->serialize());
    }

    /**
     * @param string $user
     * @param int $count
     */
    public function assertItemsInInbox($user, $count)
    {
        $inboxNode = $this->server->tree->getNodeForPath('calendars/' . $user . '/inbox');
        $this->assertEquals($count, count($inboxNode->getChildren()));
    }

    /**
     * @param string $user
     * @param string $expectedObject
     */
    public function assertItemsInCalendar($user, $expectedObject)
    {
        $calendarNode = $this->server->tree->getNodeForPath('calendars/' . $user . '/cal');
        $node = $calendarNode->getChildren();
        $objectData = $node[0];
        $this->assertVObjEquals($expectedObject, $objectData->get());
    }

    /**
     * @param string $expected
     * @param string $actual
     */
    public function assertVObjEquals($expected, $actual)
    {
        $format = function ($data) {
            $data = preg_replace('/PRODID:-\/\/Sabre\/\/Sabre VObject \d+?.\d+?.\d+?\/\/EN/', '', $data);
            $data = trim($data, "\r\n");
            $data = str_replace("\r", "", $data);
            $data = str_replace("\n ", "", $data);

            return $data;
        };
        $this->assertEquals(
            $format($expected),
            $format($actual)
        );
    }
}
