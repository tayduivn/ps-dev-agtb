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

/**
 * CalDav bean tests
 * Class CalDavTest
 */
class CalDavEventTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        parent::setUp();
    }

    public function tearDown()
    {
        SugarTestCalDavUtilities::deleteAllCreatedCalendars();
        SugarTestCalDavUtilities::deleteCreatedEvents();
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
}
