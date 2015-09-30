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
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings as MeetingAdapter;

/**
 * Class for testing Meeting CalDavAdapter
 *
 * @package Sugarcrm\SugarcrmTestsUnit\Dav\Cal\Adapter
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory\Meetings
 */
class CallsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * data for CalDavEvent mock getters
     * @var array
     */
    protected $calDavBeanProperties = array(
        'getTitle' => 'Cal Dav test Title',
        'getDescription' => 'Event description',
        'getStartDate' => '2015-08-06 08:00:00',
        'getEndDate' => '2015-08-06 16:00:00',
        'getLocation' => 'office',
        'getDuration' => '125'
    );

    /**
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings::export
     */
    public function testExport()
    {
        $callBean = $this->getBeanMock('\Call');
        $calDavBean = $this->getCalDavBeanMock();
        $callAdapater = $this->getCallsAdapterMock($callBean);

        $result = $callAdapater->export($callBean, $calDavBean);
        $this->assertTrue($result);
    }

    /**
     * @covers \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings::arrayIndex
     * @dataProvider arrayIndexProvider
     */
    public function testArrayIndex($data, $expected)
    {
        $meetingAdapter = new MeetingAdapter;
        $actualData = TestReflection::callProtectedMethod($meetingAdapter, 'arrayIndex', array('id', $data));
        $this->assertEquals($expected, $actualData);
    }

    /**
     * return data for testArrayIndex fucntion
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
     * return adapter mock
     * @param $nonCachedBeanReturn
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getCallsAdapterMock($nonCachedBeanReturn)
    {
        $adapterMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Calls')
            ->disableOriginalConstructor()
            ->setMethods(array('getNotCachedBean', 'getCurrentUserId', 'getUserCalendars', 'getDateTimeHelper', 'getCalendarEvents'))
            ->getMock();
        $dateTimeHelperMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper')
            ->setMethods(array('getCurrentUserTimeZone'))
            ->getMock();
        $dateTimeHelperMock->method('getCurrentUserTimeZone')->willReturn('UTC');

        $calendarEventsMock = $this->getCalendarEventsMock();

        $adapterMock->method('getNotCachedBean')->willReturn($nonCachedBeanReturn);
        $adapterMock->method('getCurrentUserId')->willReturn(1);
        $adapterMock->method('getDateTimeHelper')->willReturn($dateTimeHelperMock);
        $adapterMock->method('getCalendarEvents')->willReturn($calendarEventsMock);

        $calendars = array();
        $defaultCalendar = new \stdClass();
        $defaultCalendar->id = 1;
        $calendars[] = $defaultCalendar;
        $adapterMock->method('getUserCalendars')->willReturn($calendars);

        return $adapterMock;
    }

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

    public function mockBeanHelpers($bean)
    {
        $dateTimeHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $participantsHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\ParticipantsHelper')
            ->disableOriginalConstructor()
            ->setMethods(array('prepareForDav'))
            ->getMock();

        $recurringHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper')
            ->disableOriginalConstructor()
            ->setMethods(array('getCalendarEventsObject'))
            ->getMock();
        $recurringHelper->method('getCalendarEventsObject')->willReturn($this->getCalendarEventsMock());

        $acceptedMapper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\AcceptedMap')
            ->disableOriginalConstructor()
            ->setMethods(array('getMapping'))
            ->getMock();

        $statusMapper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\EventMap')
            ->disableOriginalConstructor()
            ->setMethods(array('getMapping'))
            ->getMock();

        TestReflection::setProtectedValue($participantsHelper, 'statusMapper', $acceptedMapper);

        TestReflection::setProtectedValue($bean, 'dateTimeHelper', $dateTimeHelper);
        TestReflection::setProtectedValue($bean, 'recurringHelper', $recurringHelper);
        TestReflection::setProtectedValue($bean, 'participantsHelper', $participantsHelper);
        TestReflection::setProtectedValue($bean, 'statusMapper', $statusMapper);
    }
}
