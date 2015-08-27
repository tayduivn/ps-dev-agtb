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

namespace Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Backend;

use Sabre\CalDAV;

/**
 * Class DataTest
 * @package Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Backend
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData
 */
class CalendarDataTest extends \PHPUnit_Framework_TestCase
{
    public function createCalendarObjectProvider()
    {
        return array(
            array(
                'calendarUri' => 'uri.isc',
                'calendarID' => 1,
                'content' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
uid:test
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR',
                'ETag' => '"c3d48c3c99615a99a764be4fc95c9ca9"',
            ),
        );
    }

    public function createUnsupportedCalendarObjectProvider()
    {
        return array(
            array(
                'content' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
uid:test
RRULE:FREQ=MONTHLY;BYMONTHDAY=17,18,19,22,27,30,-1
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR',
            ),
        );
    }

    public function updateCalendarObjectProvider()
    {
        return array(
            array(
                'calendarUri' => 'uri.isc',
                'calendarID' => 1,
                'content' => 'BEGIN:VCALENDAR
BEGIN:VEVENT
uid:test
DTSTART;VALUE=DATE:20160101
END:VEVENT
END:VCALENDAR',
            ),
        );
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::deleteCalendar
     *
     * @expectedException \Sabre\DAV\Exception\Forbidden
     */
    public function testDeleteCalendar()
    {
        $calendarMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                              ->disableOriginalConstructor()
                              ->setMethods(null)
                              ->getMock();

        $calendarMock->deleteCalendar(1);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::createCalendar
     *
     * @expectedException \Sabre\DAV\Exception\Forbidden
     */
    public function testCreateCalendar()
    {
        $calendarMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                              ->disableOriginalConstructor()
                              ->setMethods(null)
                              ->getMock();


        $calendarMock->createCalendar('principals/testuser', 'testcalendar', array());
    }

    /**
     * @param string $calendarId
     * @param string $objectUri
     * @param string $calendarData
     * @param string $expectedETag
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::createCalendarObject
     *
     * @dataProvider createCalendarObjectProvider
     */
    public function testCreateCalendarObject($calendarId, $objectUri, $calendarData, $expectedETag)
    {
        $calendarMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                              ->disableOriginalConstructor()
                              ->setMethods(array('getEventsBean'))
                              ->getMock();

        $eventMock = $this->getMockBuilder('CalDavEvent')
                          ->disableOriginalConstructor()
                          ->setMethods(array('save', 'setCalendarEventURI', 'setCalendarId'))
                          ->getMock();

        $eventMock->expects($this->once())->method('setCalendarEventURI')->with($objectUri);
        $eventMock->expects($this->once())->method('setCalendarId')->with($calendarId);

        $calendarMock->expects($this->once())->method('getEventsBean')->willReturn($eventMock);

        $result = $calendarMock->createCalendarObject($calendarId, $objectUri, $calendarData);

        $this->assertEquals($expectedETag, $result);
    }

    /**
     * @param string $calendarData
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::createCalendarObject
     *
     * @dataProvider createUnsupportedCalendarObjectProvider
     *
     * @expectedException \Sabre\DAV\Exception\NotImplemented
     */
    public function testCreateUnsupportedCalendarObject($calendarData)
    {
        $calendarMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                             ->disableOriginalConstructor()
                             ->setMethods(null)
                             ->getMock();

        $calendarMock->createCalendarObject(1, 'uri', $calendarData);
    }

    /**
     * @param string $calendarData
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::updateCalendarObject
     *
     * @dataProvider createUnsupportedCalendarObjectProvider
     *
     * @expectedException \Sabre\DAV\Exception\NotImplemented
     */
    public function testUpdateUnsupportedCalendarObject($calendarData)
    {
        $calendarMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                             ->disableOriginalConstructor()
                             ->setMethods(null)
                             ->getMock();

        $calendarMock->updateCalendarObject(1, 'uri', $calendarData);
    }

    /**
     * @param $calendarId
     * @param $objectUri
     * @param $calendarData
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData::updateCalendarObject
     *
     * @dataProvider updateCalendarObjectProvider
     */
    public function testUpdateCalendarObject($calendarId, $objectUri, $calendarData)
    {
        $calendarMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Backend\CalendarData')
                              ->disableOriginalConstructor()
                              ->setMethods(array('getEventsBean'))
                              ->getMock();

        $eventMock = $this->getMockBuilder('CalDavEvent')
                          ->disableOriginalConstructor()
                          ->setMethods(array('save', 'getByURI'))
                          ->getMock();

        $eventMock->id = $calendarId;

        $calendarMock->expects($this->once())->method('getEventsBean')->willReturn($eventMock);

        $eventMock->expects($this->once())->method('save');
        $eventMock->expects($this->once())->method('getByURI')->willReturn($eventMock);

        $calendarMock->updateCalendarObject($calendarId, $objectUri, $calendarData);
    }
}
