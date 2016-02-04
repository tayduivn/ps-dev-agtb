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

class CalendarDataCRYS1207Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        BeanFactory::setBeanClass('CalDavCalendars', 'CalDavCalendarsCRYS1207');
        BeanFactory::setBeanClass('Users', 'UsersCRYS1207');
    }

    public function tearDown()
    {
        BeanFactory::setBeanClass('CalDavCalendars');
        BeanFactory::setBeanClass('Users');
    }

    /**
     * Test method getCalendarObjects if calendar not found.
     *
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::getCalendarObjects
     */
    public function testGetCalendarObjectsNoFoundCalendar()
    {
        $calendarId = 'cal_not_found_id';
        $sugarQueryMock = $this->getMock('SugarQuery', array('execute'));

        $calendarDataMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData', array('getSugarQuery', 'getDateTime', 'getCurrentUser'));
        $sugarQueryMock->expects($this->never())->method('execute');
        $this->assertEquals(array(), $calendarDataMock->getCalendarObjects($calendarId));
    }

    /**
     * Provider for testGetCalendarObjects.
     *
     * @see testGetCalendarObjects
     * @return array
     */
    public function getCalendarObjectsProvider()
    {
        return array(
            'gettingObjectswithoutInterval' => array(
                'date' => '',
                'calendarId' => 'cal_id',
                'interval' => '0',
                'resultExecute' => array(
                    array(
                        'id' => '1',
                        'uri' => '_uri_',
                        'date_modified' => '2015-11-30',
                        'etag' => '??',
                        'calendar_id' => 'cal_id',
                        'data_size' => '8bit',
                        'calendar_data' => 'CalendarData',
                        'component_type' => 'ComponentType',
                    )
                ),
                'expected' => array(
                    array(
                        'id' => '1',
                        'uri' => '_uri_',
                        'lastmodified' => 1448841600,
                        'etag' => '"??"',
                        'calendarid' => 'cal_id',
                        'size' => '8bit',
                        'calendardata' => 'CalendarData',
                        'component' => 'componenttype'
                    ),
                ),
                'expectedDate' => 0
            ),
            'gettingObjectswithInterval' => array(
                'date' => '2015-10-30 12:12:12',
                'calendarId' => 'c9ca048b-6194-47e5-85b3-5657117d86a7',
                'interval' => '6 month',
                'resultExecute' => array(
                    array(
                        'id' => '11556963-7d57-dad2-9d40-56571157b76f',
                        'uri' => 'test',
                        'date_modified' => '2015-11-26 14:05:06',
                        'etag' => '6745fe5be1f72508792e1e09dd13b18b',
                        'calendar_id' => 'c9ca048b-6194-47e5-85b3-5657117d86a7',
                        'data_size' => '94',
                        'calendar_data' => "BEGIN:VCALENDAR\nBEGIN:VEVENT\nDTSTART;VALUE=DATE:20150826\nDURATION:P2D\nEND:VEVENT\nEND:VCALENDAR",
                        'component_type' => 'VEVENT',
                    ),
                ),
                'expected' => array(
                    array(
                        'id' => '11556963-7d57-dad2-9d40-56571157b76f',
                        'uri' => 'test',
                        'lastmodified' => 1448546706,
                        'etag' => '"6745fe5be1f72508792e1e09dd13b18b"',
                        'calendarid' => 'c9ca048b-6194-47e5-85b3-5657117d86a7',
                        'size' => '94',
                        'calendardata' => "BEGIN:VCALENDAR\nBEGIN:VEVENT\nDTSTART;VALUE=DATE:20150826\nDURATION:P2D\nEND:VEVENT\nEND:VCALENDAR",
                        'component' => 'vevent'
                    ),
                ),
                'expectedDate' => 1430395932, // expectedDate = date - interval
            ),
        );
    }

    /**
     * Test method getCalendarObjects if calendar found because interval is zero or present.
     *
     * @param string $date
     * @param string $calendarId
     * @param string $interval
     * @param array $resultExecute
     * @param array $expected
     * @param int $expectedDate
     *
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::getCalendarObjects
     * @dataProvider getCalendarObjectsProvider
     */
    public function testGetCalendarObjects(
        $date,
        $calendarId,
        $interval,
        $resultExecute,
        $expected,
        $expectedDate
    )
    {
        $dateTimeMock = new DateTime($date, new DateTimeZone('UTC'));

        $currentUserMock            = $this->getMock('User');
        $sugarQueryBuilderWhereMock = $this->getMock('SugarQuery_Builder_Where', array(), array(), '', false);
        $sugarQueryMock             = $this->getMock('SugarQuery', array(), array(), '', false);
        $eventMock                  = $this->getMock('CalDavEventCollection', array(), array(), '', false);

        $calendarDataMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData', array('getEventsBean', 'getSugarQuery', 'getCurrentUser', 'getDateTime'));
        $calendarDataMock->method('getEventsBean')->willReturn($eventMock);
        $calendarDataMock->method('getSugarQuery')->willReturn($sugarQueryMock);
        $calendarDataMock->method('getCurrentUser')->willReturn($currentUserMock);
        $calendarDataMock->method('getDateTime')->willReturn($dateTimeMock);

        $sugarQueryMock->expects($this->once())->method('from')->with($this->equalTo($eventMock));
        $sugarQueryMock->method('where')->willReturn($sugarQueryBuilderWhereMock);

        $sugarQueryBuilderWhereMock->expects($this->once())->method('equals')->with($this->equalTo('calendar_id'), $this->equalTo($calendarId));

        if ($interval == '0') {
            $sugarQueryBuilderWhereMock->expects($this->never())->method('gte')->with($this->equalTo('last_occurence'));
        } else {
            $sugarQueryBuilderWhereMock->expects($this->once())->method('gte')->with($this->equalTo('last_occurence'), $this->equalTo($expectedDate));
        }

        $currentUserMock->method('getPreference')->will($this->returnValueMap(array(array('caldav_interval', 'global', $interval))));
        $sugarQueryMock->method('execute')->willReturn($resultExecute);

        $this->assertEquals($expected, $calendarDataMock->getCalendarObjects($calendarId));

    }

    /**
     * Provider for testGetMultipleCalendarObjects.
     *
     * @return array
     */
    public function getMultipleCalendarObjectsProvider()
    {
        return array(
            array(
                'calendarId' => null,
                'uris' => array(),
                'resultExecute' => array(),
                'expectedObject' => array(),
            ),
            array(
                'calendarId' => 'c9ca048b-6194-47e5-85b3-5657117d86a7',
                'uris' => array('__URI__'),
                'resultExecute' => array(),
                'expectedObject' => array(),
            ),
            array(
                'calendarId' => 'c9ca048b-6194-47e5-85b3-5657117d86a7',
                'uris' => array('__URI_1__', '__URI_2__', '__URI_3__'),
                'resultExecute' => array(
                    array(
                        'id' => '11556963-7d57-dad2-9d40-56571157b76f',
                        'uri' => '__URI_1__',
                        'date_modified' => '2015-11-26 14:05:06',
                        'etag' => '6745fe5be1f72508792e1e09dd13b18b',
                        'calendar_id' => 'c9ca048b-6194-47e5-85b3-5657117d86a7',
                        'data_size' => '94',
                        'calendar_data' => "BEGIN:VCALENDAR\nBEGIN:VEVENT\nDTSTART;VALUE=DATE:20150826\nDURATION:P2D\nEND:VEVENT\nEND:VCALENDAR",
                        'component_type' => 'VEVENT',
                    ),
                    array(
                        'id' => '69edda45-54cc-463a-19b7-56571a8eecdb',
                        'uri' => '__URI_1__',
                        'date_modified' => '2015-11-26 14:44:30',
                        'etag' => 'ecc90eda365fea22a8743b854514f537',
                        'calendar_id' => 'c9ca048b-6194-47e5-85b3-5657117d86a7',
                        'data_size' => '94',
                        'calendar_data' => "BEGIN:VCALENDAR\nBEGIN:VEVENT\nDTSTART;VALUE=DATE:20150626\nDURATION:P2D\nEND:VEVENT\nEND:VCALENDAR",
                        'component_type' => 'VEVENT',
                    ),
                ),
                'expectedObject' => array(
                    'id' => '11556963-7d57-dad2-9d40-56571157b76f',
                    'uri' => '__URI_1__',
                    'lastmodified' => 1448546706,
                    'etag' => '"6745fe5be1f72508792e1e09dd13b18b"',
                    'calendarid' => 'c9ca048b-6194-47e5-85b3-5657117d86a7',
                    'size' => '94',
                    'calendardata' => "BEGIN:VCALENDAR\nBEGIN:VEVENT\nDTSTART;VALUE=DATE:20150826\nDURATION:P2D\nEND:VEVENT\nEND:VCALENDAR",
                    'component' => 'vevent',
                ),
            ),
            array(
                'calendarId' => 'c9ca048b-6194-47e5-85b3-5657117d86a7',
                'uris' => array('__URI_2__', '__URI_3__'),
                'resultExecute' => array(
                    array(
                        'id' => '11556963-7d57-dad2-9d40-56571157b76f',
                        'uri' => 'test',
                        'date_modified' => '2015-11-26 14:05:06',
                        'etag' => '6745fe5be1f72508792e1e09dd13b18b',
                        'calendar_id' => 'c9ca048b-6194-47e5-85b3-5657117d86a7',
                        'data_size' => '94',
                        'calendar_data' => "BEGIN:VCALENDAR\nBEGIN:VEVENT\nDTSTART;VALUE=DATE:20150826\nDURATION:P2D\nEND:VEVENT\nEND:VCALENDAR",
                        'component_type' => 'VEVENT',
                    ),
                ),
                'expectedObject' => array(
                    'id' => '11556963-7d57-dad2-9d40-56571157b76f',
                    'uri' => 'test',
                    'lastmodified' => 1448546706,
                    'etag' => '"6745fe5be1f72508792e1e09dd13b18b"',
                    'calendarid' => 'c9ca048b-6194-47e5-85b3-5657117d86a7',
                    'size' => '94',
                    'calendardata' => "BEGIN:VCALENDAR\nBEGIN:VEVENT\nDTSTART;VALUE=DATE:20150826\nDURATION:P2D\nEND:VEVENT\nEND:VCALENDAR",
                    'component' => 'vevent',
                ),
            ),
        );
    }

    /**
     * Test method getMultipleCalendarObjects.
     *
     * @param string|null $calendarId
     * @param array $uris
     * @param array $resultExecute
     * @param array $expectedObject
     *
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::getMultipleCalendarObjects
     * @dataProvider getMultipleCalendarObjectsProvider
     */
    public function testGetMultipleCalendarObjects(
        $calendarId,
        array $uris,
        array $resultExecute,
        array $expectedObject
    )
    {
        $sugarQueryBuilderWhereMock = $this->getMock('SugarQuery_Builder_Where', array(), array(), '', false);
        $sugarQueryMock             = $this->getMock('SugarQuery', array(), array(), '', false);
        $eventMock                  = $this->getMock('CalDavEventCollection', array(), array(), '', false);

        $calendarDataMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData', array('getEventsBean', 'getSugarQuery'));
        $calendarDataMock->method('getEventsBean')->willReturn($eventMock);
        $calendarDataMock->method('getSugarQuery')->willReturn($sugarQueryMock);

        if ($calendarId) {
            $sugarQueryMock->expects($this->once())->method('from')->with($eventMock);
            $sugarQueryMock->method('where')->willReturn($sugarQueryBuilderWhereMock);

            $sugarQueryBuilderWhereMock->expects($this->at(0))->method('equals')->with($this->equalTo('calendar_id'), $this->equalTo($calendarId));

            if (count($uris) == 1) {
                $sugarQueryBuilderWhereMock->expects($this->at(1))->method('equals')->with($this->equalTo('caldav_events.uri'), $this->equalTo($uris[0]));
                $sugarQueryMock->method('limit')->with($this->equalTo(1));
            } else {
                $sugarQueryBuilderWhereMock->method('in')->with($this->equalTo('caldav_events.uri'), $this->equalTo($uris));
            }

            $sugarQueryMock->method('execute')->willReturn($resultExecute);
        } else {
            $sugarQueryMock->expects($this->never())->method('execute')->willReturn($resultExecute);
        }

        $this->assertEquals($expectedObject, $calendarDataMock->getCalendarObject($calendarId, array_shift($uris)));
    }

    /**
     * Test query to database in method getCalendarObjects. If interval is zero.

     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::getCalendarObjects
     */
    public function testSugarQueryForCalDavEventsAddsFieldsToWhere()
    {
        $eventBean = \BeanFactory::getBean('CalDavEvents');
        $eventBean->doLocalDelivery = false;

        $query = new \SugarQuery();
        $query->from($eventBean);
        $query->where()->equals('calendar_id', '');
        $query->where()->gte('last_occurence', '');
        $query->where()->equals('caldav_events.uri', '');

        $sql = $query->execute('raw');

        $this->assertContains('caldav_events.calendar_id = ', $sql);
        $this->assertContains('caldav_events.last_occurence >= ', $sql);
        $this->assertContains('caldav_events.uri = ', $sql);
    }

    /**
     * Provider for testGetSchedulingObject.
     *
     * @return array
     */
    public function getSchedulingObjectProvider()
    {
        return array(
            array(
                'principalUri' => 'principals/users/user1',
                'expected' => array('result'),
            ),
            array(
                'principalUri' => 'principals/contacts/user1',
                'expected' => array(),
            ),
            array(
                'principalUri' => 'principals/users',
                'expected' => array(),
            ),
            array(
                'principalUri' => 'user1',
                'expected' => array('result'),
            ),
        );
    }

    /**
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::getSchedulingObject
     * @dataProvider getSchedulingObjectProvider
     */
    public function testGetSchedulingObject($principalUri, $expected)
    {
        $calendarDataMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData', array('getSchedulingByUri'));

        if (empty($expected)) {
            $calendarDataMock->expects($this->never())->method('getSchedulingByUri');
        } else {
            $calendarDataMock
                ->expects($this->once())
                ->method('getSchedulingByUri')
                ->with($this->equalTo('test'), $this->equalTo('c9ca048b-6194-47e5-85b3-5657117d86a7'))
                ->willReturn(array('result'));
        }

        $this->assertEquals($expected, $calendarDataMock->getSchedulingObject($principalUri, 'test'));
    }

    public function getCalendarsForUserProvider()
    {
        return array(
            array(
                'principalUri' => 'principals/users/user1',
                'resultExecute' => array(
                    array(
                        'id' => '11000bf0-07d9-7df9-a3b7-565f0eeae669',
                        'name' => 'myNewCalendar',
                        'date_entered' => '2015-12-02 15:33:34',
                        'date_modified' => '2015-12-02 15:33:34',
                        'modified_user_id' => 'c9ca048b-6194-47e5-85b3-5657117d86a7',
                        'created_by' => 'c9ca048b-6194-47e5-85b3-5657117d86a7',
                        'description' => 'new description',
                        'deleted' => '0',
                        'uri' => 'default',
                        'synctoken' => 0,
                        'calendarorder' => 0,
                        'calendarcolor' => null,
                        'timezone' => "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//Sabre//Sabre VObject 3.4.7//EN\nCALSCALE:GREGORIAN\nBEGIN:VTIMEZONE\nTZID:Europe/Minsk\nEND:VTIMEZONE\nEND:VCALENDAR",
                        'components' => 'VEVENT,VTODO',
                        'transparent' => '0',
                        'assigned_user_id' => 'c9ca048b-6194-47e5-85b3-5657117d86a7',
                    ),
                ),
                'expected' => array(
                    array(
                        'id' => '11000bf0-07d9-7df9-a3b7-565f0eeae669',
                        'uri' => 'default',
                        '{DAV:}displayname' => 'myNewCalendar',
                        '{urn:ietf:params:xml:ns:caldav}calendar-description' => 'new description',
                        '{urn:ietf:params:xml:ns:caldav}calendar-timezone' => "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//Sabre//Sabre VObject 3.4.7//EN\nCALSCALE:GREGORIAN\nBEGIN:VTIMEZONE\nTZID:Europe/Minsk\nEND:VTIMEZONE\nEND:VCALENDAR",
                        '{http://apple.com/ns/ical/}calendar-order' => 0,
                        '{http://apple.com/ns/ical/}calendar-color' => null,
                        '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set' => new \Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet(explode(',', 'VEVENT,VTODO')),
                        '{http://calendarserver.org/ns/}getctag' => 'http://sabre.io/ns/sync/0',
                        '{http://sabredav.org/ns}sync-token' => 0,
                        '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp' => new \Sabre\CalDAV\Xml\Property\ScheduleCalendarTransp('opaque'),
                        'principaluri' => 'principals/users/user1',
                    )
                ),
            ),
            array(
                'principalUri' => 'principals/users/user1',
                'resultExecute' => array(),
                'expected' => array(
                    array(
                        'id' => '109f318f-0080-26d6-b96e-565f0fa1b8a4',
                        'uri' => 'default',
                        '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set' => new \Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet(explode(',', 'VEVENT,VTODO')),
                        '{http://calendarserver.org/ns/}getctag' => 'http://sabre.io/ns/sync/0',
                        '{http://sabredav.org/ns}sync-token' => 0,
                        '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp' => new \Sabre\CalDAV\Xml\Property\ScheduleCalendarTransp('opaque'),
                        'principaluri' => 'principals/users/user1',
                        '{DAV:}displayname' => 'myCalendar',
                        '{urn:ietf:params:xml:ns:caldav}calendar-description' => 'description',
                        '{urn:ietf:params:xml:ns:caldav}calendar-timezone' => "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//Sabre//Sabre VObject 3.4.7//EN\nCALSCALE:GREGORIAN\nBEGIN:VTIMEZONE\nTZID:Europe/Minsk\nEND:VTIMEZONE\nEND:VCALENDAR",
                        '{http://apple.com/ns/ical/}calendar-order' => 0,
                        '{http://apple.com/ns/ical/}calendar-color' => null,
                    ),
                ),
            ),
            array(
                'principalUri' => 'principals/users/user2',
                'resultExecute' => null,
                'expected' => array(),
            )
        );
    }

    /**
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::getCalendarsForUser
     * @dataProvider getCalendarsForUserProvider
     */
    public function testGetCalendarsForUser($principalUri, $resultExecute, $expected)
    {
        $calendarDataMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData', array('getSugarQuery', 'createDefaultForUser'));
        $sugarQueryMock = $this->getMock('SugarQuery', array('execute'));

        if ($expected) {
            $calendarDataMock->expects($this->once())->method('getSugarQuery')->willReturn($sugarQueryMock);
            $sugarQueryMock->method('execute')->willReturn($resultExecute);
        } else {
            $sugarQueryMock->expects($this->never())->method('execute');
        }

        $actual = $calendarDataMock->getCalendarsForUser($principalUri);

        if ($expected) {
            $this->assertEquals(
                $expected[0]['{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set']->getValue(),
                $actual[0]['{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set']->getValue()
            );

            $this->assertEquals(
                $expected[0]['{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp']->getValue(),
                $actual[0]['{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp']->getValue()
            );

            unset(
                $expected[0]['{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set'],
                $expected[0]['{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp'],
                $actual[0]['{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set'],
                $actual[0]['{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp']
            );
        }

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test query to database in method getCalendarsForUser.
     *
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::getCalendarsForUser
     */
    public function testSugarQueryForCalDavCalendarsAddsFieldsToWhere()
    {
        $calendarBean = \BeanFactory::getBean('CalDavCalendars');

        $query = new \SugarQuery();
        $query->from($calendarBean);
        $query->where()->equals('assigned_user_id', '');
        $sql = $query->execute('raw');

        $this->assertContains('assigned_user_id = ', $sql);
    }
}

class CalDavCalendarsCRYS1207 extends CalDavCalendar
{
    public function retrieve($id = '-1', $encode = true, $deleted = true)
    {
        if ($id == 'cal_not_found_id') {
            return null;
        }

        $this->id = $id;
        return $this;
    }

    public function createDefaultForUser($user)
    {
        return array(
            'id' => '109f318f-0080-26d6-b96e-565f0fa1b8a4',
            'name' => 'myCalendar',
            'date_entered' => '2015-12-02 15:33:34',
            'date_modified' => '2015-12-02 15:33:34',
            'modified_user_id' => $user->id,
            'created_by' => $user->id,
            'description' => 'description',
            'deleted' => '0',
            'uri' => 'default',
            'synctoken' => 0,
            'calendarorder' => 0,
            'calendarcolor' => null,
            'timezone' => "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//Sabre//Sabre VObject 3.4.7//EN\nCALSCALE:GREGORIAN\nBEGIN:VTIMEZONE\nTZID:Europe/Minsk\nEND:VTIMEZONE\nEND:VCALENDAR",
            'components' => 'VEVENT,VTODO',
            'transparent' => '0',
            'assigned_user_id' => $user->id,
        );
    }
}

class UsersCRYS1207 extends User
{
    public function retrieve($id, $encode = true, $deleted = true)
    {
        if ($id == 'c9ca048b-6194-47e5-85b3-5657117d86a7') {
            $this->id = $id;
            return $this;
        }

        return null;
    }

    public function retrieve_user_id($user_name)
    {
        if ($user_name == 'user1') {
            return 'c9ca048b-6194-47e5-85b3-5657117d86a7';
        }

        return false;
    }
}
