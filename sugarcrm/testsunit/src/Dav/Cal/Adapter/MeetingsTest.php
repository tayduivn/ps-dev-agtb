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

namespace Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Adapter;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Dav;

/**
 * Class for testing Meeting CalDavAdapter
 *
 * @package Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Adapter
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory\Meetings
 */
class MeetingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data for CalDavEvent mock getters.
     *
     * @var array
     */
    protected $calDavBeanProperties = array(
        'getTitle'       => 'Cal Dav test Title',
        'getDescription' => 'Event description',
        'getStartDate'   => '2015-08-06 08:00:00',
        'getEndDate'     => '2015-08-06 16:00:00',
        'getLocation'    => 'office',
        'getDuration'    => '125',
    );

    /**
     * Test import from CalDav.
     *
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings::import
     */
    public function testImport()
    {
        /**@var \Meeting $meetingBean */
        $meetingBean = $this->getBeanMock('\Meeting');
        $calDavBean = $this->getCalDavBeanMock();
        $meetings = $this->getMeetingAdapterMock($calDavBean, $meetingBean);

        $result = $meetings->import($meetingBean, $calDavBean);

        $this->assertTrue($result);

        $this->assertEquals($meetingBean->name, $this->calDavBeanProperties['getTitle']);
        $this->assertEquals($meetingBean->description, $this->calDavBeanProperties['getDescription']);
        $this->assertEquals($meetingBean->date_start, $this->calDavBeanProperties['getStartDate']);
        $this->assertEquals($meetingBean->date_end, $this->calDavBeanProperties['getEndDate']);
        $this->assertEquals($meetingBean->location, $this->calDavBeanProperties['getLocation']);
        $this->assertEquals($meetingBean->duration_hours, round($this->calDavBeanProperties['getDuration'] / 60));
        $this->assertEquals($meetingBean->duration_minutes, $this->calDavBeanProperties['getDuration'] % 60);

        $result = $meetings->import($meetingBean, $calDavBean);
        $this->assertFalse($result);

    }

    /**
     * Test export to CalDav.
     *
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings::export
     */
    public function testExport()
    {
        $meetingBean = $this->getBeanMock('\Meeting');
        $calDavBean = $this->getCalDavBeanMock();
        $meetings = $this->getMeetingAdapterMock($meetingBean, $calDavBean);

        $result = $meetings->export($meetingBean, $calDavBean);
        $this->assertFalse($result);
    }

    /**
     * Testing array according to a specified key
     *
     * @param array $data
     * @param array $expected
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings::arrayIndex
     * @dataProvider arrayIndexProvider
     */
    public function testArrayIndex($data, $expected)
    {
        $meetingAdapter = new Dav\Cal\Adapter\Meetings();
        $actualData = TestReflection::callProtectedMethod($meetingAdapter, 'arrayIndex', array('id', $data));
        $this->assertEquals($expected, $actualData);
    }

    /**
     * Return data for testArrayIndex function.
     *
     * @return array
     */
    public function arrayIndexProvider()
    {
        return array(
            array(
                array(
                    array('id' => '123', 'data' => 'abc'),
                    array('id' => '345', 'data' => 'def'),
                ),
                array(
                    '123' => array('id' => '123', 'data' => 'abc'),
                    '345' => array('id' => '345', 'data' => 'def')
                )
            ),
            array(
                array(
                    (object)array('id' => '123', 'data' => 'abc'),
                    (object)array('id' => '345', 'data' => 'def'),
                ),
                array(
                    '123' => (object)array('id' => '123', 'data' => 'abc'),
                    '345' => (object)array('id' => '345', 'data' => 'def')
                )
            ),
        );
    }

    /**
     * Test return until date.
     *
     * @param $userDateFormat
     * @param $date
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings::getUntilDate
     * @dataProvider untilDateProvider
     */
    public function testGetUntilDate($userDateFormat, $date)
    {
        $expectedDate = '2010-12-23';
        $adapterMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings')
            ->disableOriginalConstructor()
            ->setMethods(array('getUserDateFormat'))
            ->getMock();
        $adapterMock->method('getUserDateFormat')->willReturn($userDateFormat);
        $actualDate = TestReflection::callProtectedMethod($adapterMock, 'getUntilDate', array($date));
        $this->assertEquals($expectedDate, $actualDate);
    }

    /**
     * Data provider for testGetUntilDate.
     */
    public function untilDateProvider()
    {
        return array(
            array('m/d/Y', '12/23/2010'),
            array('d/m/Y', '23/12/2010'),
            array('Y/m/d', '2010/12/23'),
            array('m-d-Y', '12-23-2010'),
            array('d-m-Y', '23-12-2010'),
            array('Y-m-d', '2010-12-23'),
            array('m.d.Y', '12.23.2010'),
            array('d.m.Y', '23.12.2010'),
            array('Y.m.d', '2010.12.23')
        );
    }

    /**
     * Test of a setting recurring.
     *
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings::setRecurring
     */
    public function testSetRecurring()
    {
        $sugarBeanMock = $this->getBeanMock('\Meeting');
        $calDavBeanMock = $this->getCalDavBeanMock();
        $meetings = $this->getMeetingAdapterMock($calDavBeanMock, $sugarBeanMock);
        $meetings->method('getUserDateFormat')->willReturn('m/d/Y');
        $meetings->method('getUntilDate')->willReturn('2010-10-23');

        $calDavBeanMock->parent_id = true;
        $sugarBeanMock->repeat_until = '12/23/2010';
        $recurringRule = array(
            'children' => array(),
            'deleted' => array()
        );

        $meetings->expects($this->once())->method('getUntilDate');

        TestReflection::callProtectedMethod($meetings, 'setRecurring', array($recurringRule, $sugarBeanMock, $calDavBeanMock));
        $this->assertEquals('2010-10-23', $sugarBeanMock->repeat_until);
    }

    /**
     * Return adapter mock.
     *
     * @param $nonCachedBeanReturn
     * @param $bean
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMeetingAdapterMock($nonCachedBeanReturn, $bean)
    {
        $adapterMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'getNotCachedBean',
                'getCurrentUserId',
                'getUserCalendars',
                'getDateTimeHelper',
                'getCalendarEvents',
                'getUserDateFormat',
                'getUntilDate',
                'getEventMap',
                'isRecurringRulesChangedForBean'
            ))
            ->getMock();
        $dateTimeHelperMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper')
            ->setMethods(array('getCurrentUserTimeZone'))
            ->getMock();
        $dateTimeHelperMock->method('getCurrentUserTimeZone')->willReturn('UTC');

        $calendarEventsMock = $this->getCalendarEventsMock();

        $eventMapMock = $this->getMock('Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\EventMap', array('isAvailableStatusFromBean'));
        $eventMapMock->method('isAvailableStatusFromBean')->with($bean)->willReturn(true);
        $eventMapMock->method('getStatusesByEvent')->with($nonCachedBeanReturn)->willReturn(array('status'));

        $adapterMock->method('getNotCachedBean')->willReturn($nonCachedBeanReturn);
        $adapterMock->method('getCurrentUserId')->willReturn(1);
        $adapterMock->method('getDateTimeHelper')->willReturn($dateTimeHelperMock);
        $adapterMock->method('getCalendarEvents')->willReturn($calendarEventsMock);
        $adapterMock->method('getEventMap')->willReturn($eventMapMock);
        $adapterMock->method('isRecurringRulesChangedForBean')->willReturn(false);

        $calendars = array();
        $defaultCalendar = new \stdClass();
        $defaultCalendar->id = 1;
        $calendars[] = $defaultCalendar;
        $adapterMock->method('getUserCalendars')->willReturn($calendars);

        return $adapterMock;
    }

    /**
     * Return mock CalendarEvents object
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCalendarEventsMock()
    {
        $calendarEventsMock = $this->getMockBuilder('\CalendarEvents')
            ->disableOriginalConstructor()
            ->setMethods(array('getChildrenQuery', 'isEventRecurring'))
            ->getMock();
        $queryMock = $this->getMockBuilder('\SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $calendarEventsMock->method('getChildrenQuery')->willReturn($queryMock);
        $calendarEventsMock->method('isEventRecurring')->willReturn(false);
        return $calendarEventsMock;

    }

    /**
     * Return mock Meeting object
     *
     * @param string $beanClass
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getBeanMock($beanClass)
    {
        $beanMock = $this->getMockBuilder($beanClass)
            ->disableOriginalConstructor()
            ->setMethods(array('fetchFromQuery'))
            ->getMock();
        $beanMock->method('fetchFromQuery')->willReturn(array());
        return $beanMock;
    }

    /**
     * Return mock CalDavEvent object
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCalDavBeanMock()
    {
        $defaultMethods = array(
            'getRRule',
            'getParticipants',
            'getReminders',
            'getCurrentUser',
        );
        $calDavFunctions = array_merge(array_keys($this->calDavBeanProperties), $defaultMethods);
        $beanMock = $this->getMockBuilder('\CalDavEvent')
            ->disableOriginalConstructor()
            ->setMethods($calDavFunctions)
            ->getMock();
        foreach ($this->calDavBeanProperties as $methodName => $returnedValue) {
            $beanMock->method($methodName)->willReturn($returnedValue);
        }

        $userBean = $this->getMockBuilder('\CalDavEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getPreference'))
            ->getMock();

        $beanMock->method('getRRule')->willReturn(array());
        $beanMock->method('getParticipants')->willReturn(false);
        $beanMock->method('getReminders')->willReturn(false);
        $beanMock->method('getCurrentUser')->willReturn($userBean);
        $beanMock->method('cleanBean')->willReturn(array());

        $this->mockBeanHelpers($beanMock);
        return $beanMock;
    }

    /**
     * Helper mock SugarBean
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $bean
     */
    public function mockBeanHelpers($bean)
    {
        $participantsHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper')
            ->disableOriginalConstructor()
            ->setMethods(array('prepareForDav'))
            ->getMock();

        $recurringHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper')
            ->disableOriginalConstructor()
            ->setMethods(array('getCalendarEventsObject'))
            ->getMock();
        $recurringHelper->method('getCalendarEventsObject')->willReturn($this->getCalendarEventsMock());

        TestReflection::setProtectedValue($bean, 'dateTimeHelper', new Dav\Base\Helper\DateTimeHelper());
        TestReflection::setProtectedValue($bean, 'recurringHelper', $recurringHelper);
        TestReflection::setProtectedValue($bean, 'participantsHelper', $participantsHelper);
    }
}
