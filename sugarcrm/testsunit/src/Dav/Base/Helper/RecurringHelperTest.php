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

namespace Sugarcrm\SugarcrmTestsUnit\Dav\Base\Helper;

use Sabre\VObject;
use Sabre\VObject\Component\VCalendar as EventComponent;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class RecurringHelperTest
 * @package            Sugarcrm\SugarcrmTestsUnit\Dav\Base\Helper
 *
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper
 */
class RecurringHelperTest extends \PHPUnit_Framework_TestCase
{
    protected function getEventTemplateObject($template, $isText = false)
    {
        $calendarData = file_get_contents(dirname(__FILE__) . '/EventsTemplates/' . $template . '.ics');

        if ($isText) {
            return $calendarData;
        }

        $vEvent = VObject\Reader::read($calendarData);

        return $vEvent;
    }

    public function isRecurringProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEventTemplateObject('vempty'),
                'result' => false,
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('recurring'),
                'result' => true,
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('notrecurring'),
                'result' => false,
            ),
        );
    }

    public function isUnsupportedProvider()
    {
        return array(
            array(
                'rrule' => array(
                    'FREQ' => 'DAILY',
                ),
                'result' => false,
            ),
            array(
                'rrule' => array(
                    'FREQ' => 'MINUTELY',
                ),
                'result' => true,
            ),
            array(
                'rrule' => array(
                    'BYMONTHDAY' => array(1, 2),
                ),
                'result' => true,
            ),
        );
    }

    public function getRecurringInfoProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEventTemplateObject('vempty'),
                'result' => null,
                'children' => array(),
                'deleted' => array(),
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('recurring'),
                'result' => array(
                    'type' => 'Daily',
                    'interval' => 1,
                    'until' => '2015-09-04 06:00:00',
                ),
                'children' => array(
                    '2015-09-01 06:00:00' => array(
                        'title' => 'First Reccuring test'
                    ),
                    '2015-09-02 06:00:00' => array(
                        'title' => 'Reccuring test 1'
                    ),
                    '2015-09-03 06:00:00' => array(
                        'title' => 'Reccuring test'
                    ),
                    '2015-09-04 06:00:00' => array(
                        'title' => 'Reccuring test'
                    ),
                ),
                'deleted' => array(),
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('notrecurring'),
                'result' => null,
                'children' => array(),
                'deleted' => array(),
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('recurring-bymonthday'),
                'result' => null,
                'children' => array(),
                'deleted' => array(),
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('recurring-byday'),
                'result' => array(
                    'type' => 'Weekly',
                    'interval' => 1,
                    'dow' => '12345',
                    'until' => '2015-08-28 12:00:00'
                ),
                'children' => array(
                    '2015-08-26 12:00:00' => array(
                        'title' => 'new meeting'
                    ),
                    '2015-08-27 12:00:00' => array(
                        'title' => 'new meeting'
                    ),
                    '2015-08-28 12:00:00' => array(
                        'title' => 'new meeting'
                    ),
                ),
                'deleted' => array(),
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('recurring-until-date'),
                'result' => array(
                    'type' => 'Daily',
                    'interval' => 1,
                    'until' => '2015-10-04 08:00:00'
                ),
                'children' => array(
                    '2015-10-01 11:00:00' => array(
                        'title' => 'Test001'
                    ),
                    '2015-10-02 11:00:00' => array(
                        'title' => 'Test001'
                    ),
                    '2015-10-03 11:00:00' => array(
                        'title' => 'Test001'
                    ),
                    '2015-10-04 11:00:00' => array(
                        'title' => 'Test001'
                    ),
                ),
                'deleted' => array(),
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('recurring-weekly'),
                'result' => array(
                    'type' => 'Weekly',
                    'interval' => 1,
                    'dow' => '6',
                    'until' => '2015-10-15 12:00:00'
                ),
                'children' => array(
                    '2015-09-26 12:00:00' => array(
                        'title' => 'new meeting'
                    ),
                    '2015-10-03 12:00:00' => array(
                        'title' => 'new meeting'
                    ),
                    '2015-10-10 12:00:00' => array(
                        'title' => 'new meeting'
                    ),
                ),
                'deleted' => array(),
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('recurring-byday-cnt2'),
                'result' => array(
                    'type' => 'Weekly',
                    'interval' => 2,
                    'count' => 5,
                    'dow' => '34',
                ),
                'children' => array(
                    '2015-08-19 12:00:00' => array(
                        'title' => 'new2'
                    ),
                    '2015-08-20 12:00:00' => array(
                        'title' => 'new2'
                    ),
                    '2015-09-02 12:00:00' => array(
                        'title' => 'new2'
                    ),
                    '2015-09-03 12:00:00' => array(
                        'title' => 'new2'
                    ),
                    '2015-09-16 12:00:00' => array(
                        'title' => 'new2'
                    ),
                ),
                'deleted' => array(),
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('recurring-with-deleted'),
                'result' => array(
                    'type' => 'Daily',
                    'interval' => 1,
                    'until' => '2015-09-26 14:00:00',
                ),
                'children' => array(
                    '2015-09-22 14:00:00' => array(
                        'title' => 'Test recurring when delete'
                    ),
                    '2015-09-23 14:00:00' => array(
                        'title' => 'Test recurring when delete'
                    ),
                    '2015-09-25 14:00:00' => array(
                        'title' => 'Test recurring when delete'
                    ),
                ),
                'deleted' => array(
                    '2015-09-24 14:00:00',
                    '2015-09-26 14:00:00',
                ),
            ),
        );
    }

    public function setRecurringInfoProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEventTemplateObject('vempty', true),
                'recurringInfo' => array(),
                'childBeans' => array(),
                'sugarBean' => array(),
                'isRecurring' => false,
                'result' => false,
                'eventResult' => null,
                'methods' => array(
                    'getCalendarEventsObject' => 1,
                    'isEventRecurring' => 0,
                    'updateRecurringChildren' => array('count'=>0, 'return' => false),
                ),
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('recurring-bymonthday', true),
                'recurringInfo' => array(
                    'type' => 'Daily',
                    'interval' => 1,
                ),
                'childBeans' => array(),
                'sugarBean' => array(
                    'name' => 'Test Bean',
                    'repeat_type' => 'Daily',
                    'date_start' => '2015-09-01 10:00',
                    'repeat_count' => '1',
                ),
                'isRecurring' => false,
                'result' => false,
                'eventResult' => null,
                'methods' => array(
                    'getCalendarEventsObject' => 1,
                    'isEventRecurring' => 1,
                    'updateRecurringChildren' => array('count'=>0, 'return' => false),
                ),
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('vempty', true),
                'recurringInfo' => array(
                    'type' => 'Weekly',
                    'interval' => '2',
                    'count' => '1',
                    'until' => '2015-10-01',
                    'dow' => '136'
                ),
                'childBeans' => array(
                    'a1' => array(
                        'name' => 'Test Bean'
                    ),
                    'a2' => array(
                        'name' => 'Test Bean 1'
                    ),
                    'a3' => array(
                        'name' => 'Test Bean'
                    )
                ),
                'sugarBean' => array(
                    'name' => 'Test Bean',
                    'repeat_type' => 'Daily',
                    'date_start' => '2015-09-01 10:00',
                    'repeat_interval' => '2',
                    'repeat_count' => '1',
                    'repeat_until' => '2015-10-01',
                    'repeat_dow' => '136'
                ),
                'isRecurring' => true,
                'result' => true,
                'eventResult' => $this->getEventTemplateObject('recurring-simple'),
                'methods' => array(
                    'getCalendarEventsObject' => 1,
                    'isEventRecurring' => 1,
                    'updateRecurringChildren' => array('count'=>0, 'return' => false),
                ),
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('recurring', true),
                'recurringInfo' => array(
                    'type' => 'Daily',
                    'until' => '2015-09-04',
                ),
                'childBeans' => array(),
                'sugarBean' => array(
                    'repeat_type' => 'Daily',
                    'repeat_until' => '2015-09-04',
                ),
                'isRecurring' => true,
                'result' => false,
                'eventResult' => $this->getEventTemplateObject('recurring'),
                'methods' => array(
                    'getCalendarEventsObject' => 1,
                    'isEventRecurring' => 1,
                    'updateRecurringChildren' => array('count'=>0, 'return' => false),
                ),
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('recurring', true),
                'recurringInfo' => array(
                    'type' => 'Daily',
                    'until' => '2015-09-04',
                ),
                'childBeans' => array(
                    'a1' => array(
                        'name' => 'Test Bean'
                    ),
                    'a2' => array(
                        'name' => 'Test Bean 1'
                    ),
                    'a3' => array(
                        'name' => 'Test Bean'
                    )
                ),
                'sugarBean' => array(
                    'repeat_type' => 'Daily',
                    'repeat_until' => '2015-10-04',
                ),
                'isRecurring' => true,
                'result' => true,
                'eventResult' => null,
                'methods' => array(
                    'getCalendarEventsObject' => 1,
                    'isEventRecurring' => 1,
                    'updateRecurringChildren' => array('count'=>1, 'return' => true),
                ),
            ),
        );
    }

    public function updateRecurringChildrenProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEventTemplateObject('recurring', true),
                'sugarBean' => array(),
                'childBeans' => array(),
                'expectedEvents' => $this->getEventTemplateObject('recurring'),
                'childrenCount' => 4,
                'result' => false,
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('recurring', true),
                'sugarBean' => array(
                    'name' => 'Test Bean',
                ),
                'childBeans' => array(
                    'a1' => array(
                        'name' => 'Reccuring test 1',
                        'date_start' => '2015-09-02 06:00:00',
                        'date_end' => '2015-09-02 07:00:00',
                    ),
                    'a2' => array(
                        'name' => 'Test Bean',
                        'date_start' => '2015-09-03 06:00:00',
                        'date_end' => '2015-09-03 07:00:00',
                    ),
                ),
                'expectedEvents' => $this->getEventTemplateObject('recurring-cleaning'),
                'childrenCount' => 4,
                'result' => true,
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('recurring', true),
                'sugarBean' => array(
                    'name' => 'Test Bean',
                    'location' => 'Test location',
                    'description' => 'Test bean description',
                    'date_start' => '2015-09-01 06:00:00',
                ),
                'childBeans' => array(
                    'a1' => array(
                        'name' => 'Test Bean',
                        'description' => 'Test bean description 1',
                        'location' => 'Test location 2',
                        'date_start' => '2015-09-02 06:00:00',
                        'date_end' => '2015-09-02 07:00:00',
                    ),
                    'a2' => array(
                        'name' => 'Test Bean 1',
                        'description' => 'Test bean description',
                        'location' => 'Test location 1',
                        'date_start' => '2015-09-03 06:00:00',
                        'date_end' => '2015-09-03 08:00:00',
                    ),
                ),
                'expectedEvents' => $this->getEventTemplateObject('recurring-children'),
                'childrenCount' => 4,
                'result' => true,
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('recurring', true),
                'sugarBean' => array(
                    'name' => 'Test Bean',
                    'description' => 'Test bean description',
                    'date_start' => '2015-09-01 06:00:00',
                ),
                'childBeans' => array(
                    'a3' => array(
                        'name' => 'Test Bean',
                        'description' => 'New event',
                        'date_start' => '2015-09-04 16:00:00',
                        'date_end' => '2015-09-04 17:00:00',
                    ),
                ),
                'expectedEvents' => $this->getEventTemplateObject('recurring-children-2'),
                'childrenCount' => 4,
                'result' => true,
            ),
            array(
                'vEvent' => $this->getEventTemplateObject('recurring-simple-day', true),
                'sugarBean' => array(
                    'name' => 'Test Bean',
                    'description' => 'Test bean description',
                    'date_start' => '2015-09-01 06:00:00',
                ),
                'childBeans' => array(
                    'a3' => array(
                        'name' => 'Test Bean',
                        'description' => 'New event',
                        'date_start' => '2015-09-04 06:00:00',
                        'date_end' => '2015-09-04 06:00:00',
                    ),
                ),
                'expectedEvents' => $this->getEventTemplateObject('recurring-simple-day-new'),
                'childrenCount' => 3,
                'result' => true,
            ),

        );
    }

    /**
     * @param EventComponent $event
     * @param $expectedResult
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper::isRecurring
     *
     * @dataProvider isRecurringProvider
     */
    public function testIsRecurring(EventComponent $event, $expectedResult)
    {
        $recurringMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper')
                              ->disableOriginalConstructor()
                              ->setMethods(null)
                              ->getMock();

        $eventMock = $this->getMockBuilder('\CalDavEvent')
                          ->disableOriginalConstructor()
                          ->setMethods(null)
                          ->getMock();

        $result = TestReflection::callProtectedMethod($recurringMock, 'isRecurring', array($eventMock, $event));

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param EventComponent $event
     * @param array|null $expectedResult
     * @param array $expectedChildren
     * @param array $expectedDeleted
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper::getRecurringInfo
     *
     * @dataProvider getRecurringInfoProvider
     */
    public function testGetRecurringInfo(
        EventComponent $event,
        $expectedResult,
        array $expectedChildren,
        array $expectedDeleted
    ) {
        $recurringMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper')
                              ->disableOriginalConstructor()
                              ->setMethods(array('getEventBean'))
                              ->getMock();

        $eventMock = $this->getMockBuilder('\CalDavEvent')
                          ->disableOriginalConstructor()
                          ->setMethods(array('getCurrentUser'))
                          ->getMock();

        $userMock = $this->getMockBuilder('\User')
                         ->disableOriginalConstructor()
                         ->setMethods(array('getPreference'))
                         ->getMock();

        $dateTimeHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper')
                               ->disableOriginalConstructor()
                               ->setMethods(null)
                               ->getMock();

        $intervalMapper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\IntervalMap')
                               ->disableOriginalConstructor()
                               ->setMethods(array('getMapping'))
                               ->getMock();

        $intervalMapper->expects($this->any())
                       ->method('getMapping')
                       ->willReturn(TestReflection::getProtectedValue($intervalMapper, 'statusMap'));

        TestReflection::setProtectedValue($recurringMock, 'intervalMapper', $intervalMapper);
        TestReflection::setProtectedValue($recurringMock, 'dateTimeHelper', $dateTimeHelper);

        $eventMock->expects($this->any())->method('getCurrentUser')->willReturn($userMock);
        $userMock->expects($this->any())->method('getPreference')->with('timezone')->willReturn('UTC');

        $eventMock->setCalendarEventData($event->serialize());

        $currentStep = 0;

        foreach ($expectedChildren as $key => $override) {
            $bean = $this->getMockBuilder('\CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(array('getCurrentUser'))
                         ->getMock();

            TestReflection::setProtectedValue($bean, 'dateTimeHelper', $dateTimeHelper);

            $bean->expects($this->any())->method('getCurrentUser')->willReturn($userMock);
            $recurringMock->expects($this->at($currentStep))->method('getEventBean')->willReturn($bean);
            $currentStep ++;
        }

        $result = $recurringMock->getRecurringInfo($eventMock);

        if ($expectedChildren) {
            $this->assertEquals(array_keys($expectedChildren), array_keys($result['children']));

            foreach ($result['children'] as $dateStart => $child) {
                $this->assertInstanceOf('CalDavEvent', $child);
                $expected = $expectedChildren[$dateStart];
                $this->assertEquals($expected['title'], $child->getTitle());
            }
        }

        if ($expectedDeleted) {
            $this->assertEquals($expectedDeleted, $result['deleted']);
        }

        unset($result['children']);
        unset($result['deleted']);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $currentEvent
     * @param array $recurringInfo
     * @param array $childBeans
     * @param array $sugarBean
     * @param bool $isRecurring
     * @param bool $expectedResult
     * @param string $eventResult
     * @param array $expectedMethods
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper::setRecurringInfo
     *
     * @dataProvider setRecurringInfoProvider
     */
    public function testSetRecurringInfo(
        $currentEvent,
        array $recurringInfo,
        array $childBeans,
        array $sugarBean,
        $isRecurring,
        $expectedResult,
        $eventResult,
        array $expectedMethods
    ) {

        $dateTimeHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper')
                               ->disableOriginalConstructor()
                               ->setMethods(null)
                               ->getMock();

        $recurringMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper')
                              ->disableOriginalConstructor()
                              ->setMethods(array('getCalendarEventsObject', 'updateRecurringChildren'))
                              ->getMock();

        $eventMock = $this->getMockBuilder('\CalDavEvent')
                          ->disableOriginalConstructor()
                          ->setMethods(array('getBean', 'getCurrentUser'))
                          ->getMock();

        $userMock = $this->getMockBuilder('\User')
                         ->disableOriginalConstructor()
                         ->setMethods(array('getPreference'))
                         ->getMock();

        $intervalMapper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\IntervalMap')
                               ->disableOriginalConstructor()
                               ->setMethods(array('getMapping'))
                               ->getMock();

        $intervalMapper->expects($this->any())
                       ->method('getMapping')
                       ->willReturn(TestReflection::getProtectedValue($intervalMapper, 'statusMap'));

        TestReflection::setProtectedValue($recurringMock, 'intervalMapper', $intervalMapper);

        $eventMock->setCalendarEventData($currentEvent);

        $meetingsMock = $this->getMockBuilder('\Meeting')
                             ->disableOriginalConstructor()
                             ->setMethods(null)
                             ->getMock();

        if ($sugarBean) {
            foreach ($sugarBean as $key => $value) {
                $meetingsMock->$key = $value;
            }

            $recurringInfo['parent'] = $meetingsMock;
        }

        $recurringInfo['children'] = $childBeans;

        $calendarEventsMock = $this->getMockBuilder('\CalendarEvents')
                                   ->disableOriginalConstructor()
                                   ->setMethods(array('isEventRecurring'))
                                   ->getMock();

        $eventMock->expects($this->any())->method('getBean')->willReturn($meetingsMock);
        $eventMock->expects($this->any())->method('getCurrentUser')->willReturn($userMock);

        $userMock->expects($this->any())->method('getPreference')->with('timezone')->willReturn('UTC');

        $recurringMock->expects($this->exactly($expectedMethods['getCalendarEventsObject']))
                      ->method('getCalendarEventsObject')
                      ->willReturn($calendarEventsMock);
        $recurringMock->expects($this->exactly($expectedMethods['updateRecurringChildren']['count']))
                      ->method('updateRecurringChildren')
                      ->willReturn($expectedMethods['updateRecurringChildren']['return']);

        $calendarEventsMock->expects($this->exactly($expectedMethods['isEventRecurring']))
                           ->method('isEventRecurring')
                           ->willReturn($isRecurring);

        TestReflection::setProtectedValue($recurringMock, 'dateTimeHelper', $dateTimeHelper);

        $result = $recurringMock->setRecurringInfo($eventMock, $recurringInfo);

        $this->assertEquals($expectedResult, $result);

        if ($eventResult) {

            $this->removeDynamicProperties($eventResult);
            $this->removeDynamicProperties($eventMock->getVCalendarEvent());
            $this->assertEquals($eventResult->serialize(), $eventMock->getVCalendarEvent()->serialize());
        }
    }

    /**
     * @param $currentEvent
     * @param $sugarBean
     * @param $childBeans
     * @param $expectedEvents
     * @param $childrenCount
     * @param $expectedResult
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper::updateRecurringChildren
     *
     * @dataProvider updateRecurringChildrenProvider
     */
    public function testUpdateRecurringChildren(
        $currentEvent,
        $sugarBean,
        $childBeans,
        $expectedEvents,
        $childrenCount,
        $expectedResult
    ) {
        $recurringMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper')
                              ->disableOriginalConstructor()
                              ->setMethods(array('getEventBean'))
                              ->getMock();
        $meetingsMock = $this->getMockBuilder('\Meeting')
                             ->disableOriginalConstructor()
                             ->setMethods(array('fetchFromQuery'))
                             ->getMock();

        $dateTimeHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper')
                               ->disableOriginalConstructor()
                               ->setMethods(array('getCurrentUser'))
                               ->getMock();

        $recurringEventMock = $this->getMockBuilder('\CalDavEvent')
                                   ->disableOriginalConstructor()
                                   ->setMethods(array('getBean', 'getCurrentUser'))
                                   ->getMock();

        $intervalMapper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Mapper\Status\IntervalMap')
                               ->disableOriginalConstructor()
                               ->setMethods(array('getMapping'))
                               ->getMock();

        $intervalMapper->expects($this->any())
                       ->method('getMapping')
                       ->willReturn(TestReflection::getProtectedValue($intervalMapper, 'statusMap'));

        $userMock = $this->getMockBuilder('\User')
                         ->disableOriginalConstructor()
                         ->setMethods(array('getPreference'))
                         ->getMock();

        $recurringEventMock->setCalendarEventData($currentEvent);

        $dateTimeHelper->expects($this->any())->method('getCurrentUser')->willReturn($userMock);

        foreach ($sugarBean as $key => $value) {
            $meetingsMock->$key = $value;
        }

        for ($i = 0; $i < $childrenCount; $i ++) {
            $bean = $this->getMockBuilder('\CalDavEvent')
                         ->disableOriginalConstructor()
                         ->setMethods(array('getCurrentUser'))
                         ->getMock();

            TestReflection::setProtectedValue($bean, 'dateTimeHelper', $dateTimeHelper);
            TestReflection::setProtectedValue($bean, 'statusMapper', $intervalMapper);

            $bean->expects($this->any())->method('getCurrentUser')->willReturn($userMock);
            $recurringMock->expects($this->at($i))->method('getEventBean')->willReturn($bean);
        }

        $fetchedBeans = array();
        $currentMock = 0;
        foreach ($childBeans as $key => $value) {
            $fetchedBean = $this->getMockBuilder('\Meeting')
                                ->disableOriginalConstructor()
                                ->setMethods(null)
                                ->getMock();

            foreach ($value as $beanKey => $beanValue) {
                $fetchedBean->$beanKey = $beanValue;
            }
            $fetchedBeans[$key] = $fetchedBean;
            $currentMock ++;
        }

        TestReflection::setProtectedValue($recurringMock, 'dateTimeHelper', $dateTimeHelper);
        TestReflection::setProtectedValue($recurringEventMock, 'dateTimeHelper', $dateTimeHelper);
        TestReflection::setProtectedValue($recurringEventMock, 'statusMapper', $intervalMapper);
        TestReflection::setProtectedValue($recurringMock, 'intervalMapper', $intervalMapper);

        $result = TestReflection::callProtectedMethod(
            $recurringMock,
            'updateRecurringChildren',
            array($meetingsMock, $recurringEventMock, $fetchedBeans)
        );

        $this->removeDynamicProperties($expectedEvents);
        $this->removeDynamicProperties($recurringEventMock->getVCalendarEvent());

        $this->assertEquals($expectedEvents->serialize(), $recurringEventMock->getVCalendarEvent()->serialize());

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Delete dynamic properties from Calendar for normal compare
     *
     * @param \Sabre\VObject\Component\VCalendar $vEvent
     */
    protected function removeDynamicProperties($vEvent)
    {
        $components = $vEvent->getComponents();
        foreach ($components as $component) {
            if ($component->DTSTAMP) {
                $component->remove('DTSTAMP');
            }

            if ($component->UID) {
                $component->remove('UID');
            }
        }
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper::getCalendarEventsObject
     */
    public function testGetCalendarEventsObject()
    {
        $recurringMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper')
                              ->disableOriginalConstructor()
                              ->setMethods(null)
                              ->getMock();

        $result = TestReflection::callProtectedMethod($recurringMock, 'getCalendarEventsObject');

        $this->assertInstanceOf('CalendarEvents', $result);
    }

    /**
     * @param array $rRule
     * @param bool $expectedResult
     *
     * @covers       Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper::isUnsupported
     *
     * @dataProvider isUnsupportedProvider
     */
    public function testIsUnsupported(array $rRule, $expectedResult)
    {
        $recurringMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\RecurringHelper')
                              ->disableOriginalConstructor()
                              ->setMethods(null)
                              ->getMock();

        $result = $recurringMock->isUnsupported($rRule);

        $this->assertEquals($expectedResult, $result);

    }
}
