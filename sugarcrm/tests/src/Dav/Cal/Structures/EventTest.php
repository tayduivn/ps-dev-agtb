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

namespace Sugarcrm\SugarcrmTests\Dav\Cal\Structures;

use Sabre\VObject;
use Sabre\VObject\Component\VEvent;
use SugarTestReflection;

/**
 * Class EventTest
 * @package Sugarcrm\SugarcrmTestsUnit\Dav\Cal
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event
 */
class EventTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Load event from template
     * @param string $templateName
     * @return VEvent
     */
    protected function getEvent($templateName)
    {
        $calendarData =
            file_get_contents(dirname(__FILE__) . '/../../Base/Helper/EventsTemplates/' . $templateName . '.ics');
        $vCalendar = VObject\Reader::read($calendarData);

        return $vCalendar->getBaseComponent();
    }

    /**
     * Provide data for testIsAllDay.
     *
     * @return array
     */
    public function isAllDayProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('allday'),
                'result' => true,
            ),
            array(
                'vEvent' => $this->getEvent('vevent'),
                'result' => false,
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
                'result' => false,
            ),
        );
    }

    public function getTitleProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'result' => 'Test event title',
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
                'result' => null,
            ),
        );
    }

    public function getDescriptionProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'result' => 'Test event description',
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
                'result' => null,
            ),
        );
    }

    public function getLocationProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'result' => 'office',
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
                'result' => null,
            ),
        );
    }

    public function getVisibilityProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'result' => 'PUBLIC',
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
                'result' => null,
            ),
        );
    }

    public function getStatusProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'result' => 'CONFIRMED',
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
                'result' => null,
            ),
        );
    }

    public function getEndDateProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'result' => new \SugarDateTime('2015-08-06 11:00:00', new \DateTimeZone('Europe/Berlin')),
            ),
            array(
                'vEvent' => $this->getEvent('datetime1'),
                'result' => new \SugarDateTime('2015-08-06 11:00:00', new \DateTimeZone('UTC')),
            ),
            array(
                'vEvent' => $this->getEvent('datetime3'),
                'result' => new \SugarDateTime('2015-08-06 08:00:00', new \DateTimeZone('UTC')),
            ),
            array(
                'vEvent' => $this->getEvent('duration1'),
                'result' => new \SugarDateTime('20110101T130000', new \DateTimeZone('UTC')),
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
                'result' => null,
            ),
        );
    }

    public function getDurationProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'result' => 60,
            ),
            array(
                'vEvent' => $this->getEvent('duration'),
                'result' => 21900,
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
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

    public function getRemindersProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'count' => 1,
                'result' => array(
                    array(
                        'getAction' => 'DISPLAY',
                        'getTrigger' => - 900,
                        'getDescription' => 'Default Mozilla Description',
                    )
                )
            ),
            array(
                'vEvent' => $this->getEvent('reminder'),
                'count' => 2,
                'result' => array(
                    array(
                        'getAction' => 'DISPLAY',
                        'getTrigger' => 900,
                        'getDescription' => 'alarm test',
                    ),
                    array(
                        'getAction' => 'EMAIL',
                        'getTrigger' => - 1200,
                        'getDescription' => null,
                    )
                )
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
                'count' => 0,
                'result' => array(),
            ),
        );
    }

    public function setTitleProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'newValue' => 'test1',
                'result' => true
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
                'newValue' => 'test',
                'result' => true
            ),
        );
    }

    public function setDescriptionProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'newValue' => 'test1',
                'result' => true
            ),
            array(
                'vEvent' => $this->getEvent('vevent'),
                'newValue' => 'Test event description',
                'result' => false
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
                'newValue' => 'test',
                'result' => true
            ),
        );
    }

    public function setStartDateProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'sugarDateTime' => new \SugarDateTime('20150806T110000', new \DateTimeZone('UTC')),
                'datetime' => '20150806T130000',
                'timezone' => 'Europe/Berlin',
                'result' => true,
            ),
            array(
                'vEvent' => $this->getEvent('vevent'),
                'sugarDateTime' => new \SugarDateTime('20150806T080000', new \DateTimeZone('UTC')),
                'datetime' => '20150806T100000',
                'timezone' => 'Europe/Berlin',
                'result' => false,
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
                'sugarDateTime' => new \SugarDateTime('20141231T210001', new \DateTimeZone('UTC')),
                'datetime' => '20141231T210001Z',
                'timezone' => 'UTC',
                'result' => true,
            ),
        );
    }

    public function setEndOfEventProvider()
    {
        $dtEnd = new \SugarDateTime('20150806T110000', new \DateTimeZone('UTC'));

        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'endDate' => null,
                'duration' => 3600,
                'expectedMethods' => array(
                    'deleteProperty' => array('DTEND'),
                    'setStringProperty' => array('DURATION', 'PT1H')
                ),
            ),
            array(
                'vEvent' => $this->getEvent('vevent'),
                'endDate' => $dtEnd,
                'duration' => 3600,
                'expectedMethods' => array('setDateTimeProperty' => array('DTEND', $dtEnd)),
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
                'endDate' => $dtEnd,
                'duration' => 3660,
                'expectedMethods' => array('setDateTimeProperty' => array('DTEND', $dtEnd)),
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
                'endDate' => null,
                'duration' => 3660,
                'expectedMethods' => array(
                    'deleteProperty' => array('DTEND'),
                    'setStringProperty' => array('DURATION', 'PT1H1M')
                ),
            ),
        );
    }

    public function setEndDateProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'sugarDateTime' => new \SugarDateTime('2015-08-06 11:00:01', new \DateTimeZone('UTC')),
                'datetime' => '20150806T130001',
                'timezone' => 'Europe/Berlin',
                'result' => true,
            ),
            array(
                'vEvent' => $this->getEvent('vevent'),
                'sugarDateTime' => new \SugarDateTime('2015-08-06 09:00:00', new \DateTimeZone('UTC')),
                'datetime' => '20150806T110000',
                'timezone' => 'Europe/Berlin',
                'result' => false,
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
                'sugarDateTime' => new \SugarDateTime('2015-08-06 10:00:00', new \DateTimeZone('UTC')),
                'datetime' => '20150806T100000Z',
                'timezone' => 'UTC',
                'result' => true,
            ),
        );
    }

    public function setDurationProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('duration1'),
                'hours' => 2,
                'minutes' => 0,
                'newValue' => 'PT2H',
                'result' => true,
            ),
            array(
                'vEvent' => $this->getEvent('duration1'),
                'hours' => 0,
                'minutes' => 60,
                'newValue' => 'PT1H',
                'result' => false,
            ),
            array(
                'vEvent' => $this->getEvent('duration2'),
                'hours' => 1,
                'minutes' => 30,
                'newValue' => null,
                'result' => true,
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
                'hours' => 2,
                'minutes' => 30,
                'newValue' => 'PT2H30M',
                'result' => true,
            ),
        );
    }

    public function setLocationProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'newValue' => 'test1',
                'result' => true
            ),
            array(
                'vEvent' => $this->getEvent('vevent'),
                'newValue' => 'office',
                'result' => false
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
                'newValue' => 'office',
                'result' => true
            ),
        );
    }

    public function setStatusProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'newValue' => 'CANCELLED',
                'result' => true
            ),
            array(
                'vEvent' => $this->getEvent('vevent'),
                'newValue' => 'CONFIRMED',
                'result' => false
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
                'newValue' => 'CONFIRMED',
                'result' => true
            ),
        );
    }

    public function addReminderProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'params' => array(
                    array(
                        'seconds' => 1200,
                        'action' => 'DISPLAY',
                        'description' => 'test',
                    ),
                ),
            ),
            array(
                'vEvent' => $this->getEvent('vevent'),
                'params' => array(
                    array(
                        'seconds' => 900,
                        'action' => 'EMAIL',
                        'description' => '',
                    ),
                ),
            ),
        );
    }

    public function deleteReminderProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('reminder'),
            ),
        );
    }

    public function getParticipantsProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'links' => array(
                    'test@sugarcrm.com' => array(
                        'beanName' => '',
                        'beanId' => ''
                    ),
                    'sally@example.com' => array(
                        'beanName' => 'Users',
                        'beanId' => 'a1'
                    ),
                    'test@test.com' => array(
                        'beanName' => 'Users',
                        'beanId' => 'a2'
                    ),
                    'test1@test.com' => array(
                        'beanName' => 'Contacts',
                        'beanId' => 'a3'
                    ),
                    'test3@test.com' => array(
                        'beanName' => 'Leads',
                        'beanId' => 'a4'
                    ),
                ),
                'organizer' => array(
                    'getStatus' => 'ACCEPTED',
                    'isOrganizer' => true,
                    'getDisplayName' => '',
                    'getRole' => 'CHAIR',
                    'getEmail' => 'test@sugarcrm.com',
                    'getBeanName' => '',
                    'getBeanId' => '',
                ),
                'participants' => array(
                    array(
                        'getStatus' => 'NEEDS-ACTION',
                        'isOrganizer' => false,
                        'getDisplayName' => '',
                        'getRole' => 'REQ-PARTICIPANT',
                        'getEmail' => 'sally@example.com',
                        'getBeanName' => 'Users',
                        'getBeanId' => 'a1',
                    ),
                    array(
                        'getStatus' => 'ACCEPTED',
                        'isOrganizer' => false,
                        'getDisplayName' => 'Test Test',
                        'getRole' => 'CHAIR',
                        'getEmail' => 'test@test.com',
                        'getBeanName' => 'Users',
                        'getBeanId' => 'a2',
                    ),
                    array(
                        'getStatus' => 'DECLINED',
                        'isOrganizer' => false,
                        'getDisplayName' => 'Test1 Test1',
                        'getRole' => 'OPT-PARTICIPANT',
                        'getEmail' => 'test1@test.com',
                        'getBeanName' => 'Contacts',
                        'getBeanId' => 'a3',
                    ),
                    array(
                        'getStatus' => 'DECLINED',
                        'isOrganizer' => false,
                        'getDisplayName' => 'Test3 Test3',
                        'getRole' => 'OPT-PARTICIPANT',
                        'getEmail' => 'test3@test.com',
                        'getBeanName' => 'Leads',
                        'getBeanId' => 'a4',
                    ),
                ),
                'count' => 4,
            ),
            array(
                'vEvent' => $this->getEvent('vemptyevent'),
                'links' => array(),
                'organizer' => array(),
                'participants' => array(),
                'count' => 0,
            ),
        );
    }

    public function getOrganizerProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'participants' =>
                    array(
                        'getStatus' => 'ACCEPTED',
                        'isOrganizer' => true,
                        'getDisplayName' => '',
                        'getRole' => 'CHAIR',
                        'getEmail' => 'test@sugarcrm.com',
                        'getBeanName' => '',
                    ),
            ),
        );
    }

    public function deleteParticipantProvider()
    {
        return array(
            array(
                'vEvent' => $this->getEvent('vevent'),
                'participant' => 'new@new.com',
                'result' => false,
                'newCount' => 4
            ),
            array(
                'vEvent' => $this->getEvent('vevent'),
                'participant' => 'sally@example.com',
                'result' => true,
                'newCount' => 3
            ),
        );
    }

    /**
     * @param VEvent $VEvent
     * @param string $expectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::getTitle
     *
     * @dataProvider getTitleProvider
     */
    public function testGetTitle(VEvent $VEvent, $expectedResult)
    {
        $eventMock = $this->getEventMock($VEvent);
        $result = $eventMock->getTitle();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param VEvent $VEvent
     * @param string $expectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::getDescription
     *
     * @dataProvider getDescriptionProvider
     */
    public function testGetDescription(VEvent $VEvent, $expectedResult)
    {
        $eventMock = $this->getEventMock($VEvent);
        $result = $eventMock->getDescription();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param VEvent $VEvent
     * @param string $expectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::getLocation
     *
     * @dataProvider getLocationProvider
     */
    public function testGetLocation(VEvent $VEvent, $expectedResult)
    {
        $eventMock = $this->getEventMock($VEvent);
        $result = $eventMock->getLocation();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param VEvent $VEvent
     * @param string $expectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::getVisibility
     *
     * @dataProvider getVisibilityProvider
     */
    public function testGetVisibility(VEvent $VEvent, $expectedResult)
    {
        $eventMock = $this->getEventMock($VEvent);
        $result = $eventMock->getVisibility();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param VEvent $VEvent
     * @param string $expectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::getStatus
     *
     * @dataProvider getStatusProvider
     */
    public function testGetStatus(VEvent $VEvent, $expectedResult)
    {
        $eventMock = $this->getEventMock($VEvent);
        $result = $eventMock->getStatus();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param VEvent $VEvent
     * @param string $expectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::getEndDate
     *
     * @dataProvider getEndDateProvider
     */
    public function testGetEndDate(VEvent $VEvent, $expectedResult)
    {
        $eventMock = $this->getEventMock($VEvent);
        $result = $eventMock->getEndDate();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param VEvent $VEvent
     * @param string $expectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::getDuration
     *
     * @dataProvider getDurationProvider
     */
    public function testGetDuration(VEvent $VEvent, $expectedResult)
    {
        $eventMock = $this->getEventMock($VEvent);
        $result = $eventMock->getDuration();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param int $minutes
     * @param int $expectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::getDurationHours
     *
     * @dataProvider getDurationHoursProvider
     */
    public function testGetDurationHours($minutes, $expectedResult)
    {
        $eventMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event')
                          ->disableOriginalConstructor()
                          ->setMethods(array('getDuration'))
                          ->getMock();

        $eventMock->expects($this->once())->method('getDuration')->willReturn($minutes);

        $result = $eventMock->getDurationHours();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param int $minutes
     * @param int $expectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::getDurationMinutes
     *
     * @dataProvider getDurationMinutesProvider
     */
    public function testGetDurationMinutes($minutes, $expectedResult)
    {
        $eventMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event')
                          ->disableOriginalConstructor()
                          ->setMethods(array('getDuration'))
                          ->getMock();

        $eventMock->expects($this->once())->method('getDuration')->willReturn($minutes);

        $result = $eventMock->getDurationMinutes();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param VEvent $VEvent
     * @param int $expectedCount
     * @param array $expectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::getReminders
     *
     * @dataProvider getRemindersProvider
     */
    public function testGetReminders(VEvent $VEvent, $expectedCount, array $expectedResult)
    {
        $eventMock = $this->getEventMock($VEvent);

        $result = $eventMock->getReminders();

        $this->assertEquals($expectedCount, count($result));

        foreach ($expectedResult as $i => $expectedReminder) {
            foreach ($expectedReminder as $method => $value) {
                $this->assertEquals($value, $result[$i]->$method());
            }
        }
    }

    /**
     * @param VEvent $VEvent
     * @param string $newValue
     * @param bool $expectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::setTitle
     *
     * @dataProvider setTitleProvider
     */
    public function testSetTitle(VEvent $VEvent, $newValue, $expectedResult)
    {
        $eventMock = $this->getEventMock($VEvent);
        $result = $eventMock->setTitle($newValue);

        $this->assertEquals($expectedResult, $result);

        $event = SugarTestReflection::getProtectedValue($eventMock, 'event');
        $this->assertEquals(1, count($event->select('SUMMARY')));
        $this->assertEquals($newValue, $eventMock->getTitle());

    }

    /**
     * @param VEvent $VEvent
     * @param string $newValue
     * @param bool $expectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::setDescription
     *
     * @dataProvider setDescriptionProvider
     */
    public function testSetDescription(VEvent $VEvent, $newValue, $expectedResult)
    {
        $eventMock = $this->getEventMock($VEvent);
        $result = $eventMock->setDescription($newValue);

        $this->assertEquals($expectedResult, $result);

        $event = SugarTestReflection::getProtectedValue($eventMock, 'event');
        $this->assertEquals(1, count($event->select('DESCRIPTION')));
        $this->assertEquals($newValue, $eventMock->getDescription());

    }

    /**
     * @param VEvent $VEvent
     * @param string $dateTime
     * @param string $expectedDateTime
     * @param string $expectedTimeZone
     * @param bool $expectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::setStartDate
     *
     * @dataProvider setStartDateProvider
     */
    public function testSetStartDate(VEvent $VEvent, $dateTime, $expectedDateTime, $expectedTimeZone, $expectedResult)
    {
        $eventMock = $this->getEventMock($VEvent);

        $result = $eventMock->setStartDate($dateTime);
        $this->assertEquals($expectedResult, $result);

        $event = SugarTestReflection::getProtectedValue($eventMock, 'event');
        $this->assertEquals($expectedDateTime, $event->DTSTART->getValue());
        $timezone = $event->DTSTART->getDateTime()->getTimezone()->getName();
        $this->assertEquals($expectedTimeZone, $timezone);
    }

    /**
     * @param VEvent $VEvent
     * @param string $dtEnd
     * @param int $duration
     * @param array $expectedMethods
     *
     * @dataProvider setEndOfEventProvider
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::setEndOfEvent
     */
    public function testSetEndOfEvent(VEvent $VEvent, $dtEnd, $duration, $expectedMethods)
    {
        $eventMock = $this->getEventMock($VEvent, array('setDateTimeProperty', 'deleteProperty', 'setStringProperty'));

        foreach ($expectedMethods as $method => $params) {
            $eventMock->expects($this->once())->method($method)->withConsecutive($params);
        }
        SugarTestReflection::callProtectedMethod($eventMock, 'setEndOfEvent', array($duration, $dtEnd));
    }

    /**
     * @param VEvent $VEvent
     * @param \SugarDateTime $dateTime
     * @param string $expectedDateTime
     * @param string $expectedTimeZone
     * @param bool $expectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::setEndDate
     *
     * @dataProvider setEndDateProvider
     */
    public function testSetEndDate(VEvent $VEvent, $dateTime, $expectedDateTime, $expectedTimeZone, $expectedResult)
    {
        $eventMock = $this->getEventMock($VEvent);
        $result = $eventMock->setEndDate($dateTime);

        $this->assertEquals($expectedResult, $result);

        $event = SugarTestReflection::getProtectedValue($eventMock, 'event');
        $this->assertEquals($expectedDateTime, $event->DTEND->getValue());
        $timezone = $event->DTEND->getDateTime()->getTimezone()->getName();
        $this->assertEquals($expectedTimeZone, $timezone);
    }

    /**
     * @param VEvent $VEvent
     * @param int $hours
     * @param int $minutes
     * @param string $expectedDuration
     * @param bool $expectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::setDuration
     *
     * @dataProvider setDurationProvider
     */
    public function testSetDuration(VEvent $VEvent, $hours, $minutes, $expectedDuration, $expectedResult)
    {
        $eventMock = $this->getEventMock($VEvent);
        $result = $eventMock->setDuration($hours, $minutes);

        $this->assertEquals($expectedResult, $result);

        $event = SugarTestReflection::getProtectedValue($eventMock, 'event');
        $this->assertEquals($expectedDuration, $event->DURATION);
    }

    /**
     * @param VEvent $VEvent
     * @param string $newValue
     * @param bool $expectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::setLocation
     *
     * @dataProvider setLocationProvider
     */
    public function testSetLocation(VEvent $VEvent, $newValue, $expectedResult)
    {
        $eventMock = $this->getEventMock($VEvent);

        $result = $eventMock->setLocation($newValue);

        $this->assertEquals($expectedResult, $result);

        $event = SugarTestReflection::getProtectedValue($eventMock, 'event');
        $this->assertEquals($newValue, $event->LOCATION);

    }

    /**
     * @param VEvent $VEvent
     * @param string $newValue
     * @param bool $expectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::setStatus
     *
     * @dataProvider setStatusProvider
     */
    public function testSetStatus(VEvent $VEvent, $newValue, $expectedResult)
    {
        $eventMock = $this->getEventMock($VEvent);

        $result = $eventMock->setStatus($newValue);

        $this->assertEquals($expectedResult, $result);
        $event = SugarTestReflection::getProtectedValue($eventMock, 'event');
        $this->assertEquals($newValue, $event->STATUS);
    }

    /**
     * @param VEvent $VEvent
     * @param array $reminderParams
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::addReminder
     *
     * @dataProvider addReminderProvider
     */
    public function testAddReminder(VEvent $VEvent, array $reminderParams)
    {
        $eventMock = $this->getEventMock($VEvent);

        foreach ($reminderParams as $reminder) {
            $result = $eventMock->addReminder($reminder['seconds'], $reminder['action'], $reminder['description']);
            $this->assertInstanceOf('Sugarcrm\Sugarcrm\Dav\Cal\Structures\Reminder', $result);
            $this->assertEquals($reminder['action'], $result->getAction());
            $this->assertEquals(0 - $reminder['seconds'], $result->getTrigger());
        }
    }

    /**
     * @param VEvent $VEvent
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::deleteReminder
     *
     * @dataProvider deleteReminderProvider
     */
    public function testDeleteReminder(VEvent $VEvent)
    {
        $eventMock = $this->getEventMock($VEvent);

        $reminders = $eventMock->getReminders();
        $reminder = $reminders[1];

        $eventMock->deleteReminder($reminder);

        $reminders = $eventMock->getReminders();

        $this->assertEquals(1, count($reminders));

    }

    /**
     * @param VEvent $VEvent
     * @param array $links
     * @param array $expectedOrganizer
     * @param array $expectedParticipants
     * @param int $participantsCount
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::getParticipants
     *
     * @dataProvider getParticipantsProvider
     */
    public function testGetParticipants(VEvent $VEvent, array $links, array $expectedOrganizer, array $expectedParticipants, $participantsCount)
    {
        $eventMock = $this->getEventMock($VEvent, null, $links);
        $participants = $eventMock->getParticipants();

        $iCount = count($participants);
        $this->assertEquals($participantsCount, $iCount);

        for ($i = 0; $i < $iCount; $i ++) {
            $participant = $participants[$i];
            $expectedParticipant = $expectedParticipants[$i];
            $this->assertInstanceOf('Sugarcrm\Sugarcrm\Dav\Cal\Structures\Participant', $participant);

            foreach ($expectedParticipant as $method => $value) {
                $this->assertEquals($value, $participant->$method());
            }
        }

        $organizer = $eventMock->getOrganizer();

        foreach ($expectedOrganizer as $method => $value) {
            $this->assertEquals($value, $organizer->$method());
        }
    }

    /**
     * @param VEvent $VEvent
     * @param array $expectedOrganizer
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::getOrganizer
     *
     * @dataProvider getOrganizerProvider
     */
    public function testGetOrganizer(VEvent $VEvent, array $expectedOrganizer)
    {
        $eventMock = $this->getEventMock($VEvent);
        $organizer = $eventMock->getOrganizer();

        foreach ($expectedOrganizer as $method => $value) {
            $this->assertEquals($value, $organizer->$method());
        }
    }

    /**
     * Test for checking all day events.
     *
     * @param VEvent $VEvent
     * @param bool $expectedResult
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::isAllDay
     *
     * @dataProvider isAllDayProvider
     */
    public function testIsAllDay(VEvent $VEvent, $expectedResult)
    {
        $eventMock = $this->getEventMock($VEvent);
        $this->assertEquals($expectedResult, $eventMock->isAllDay());
    }

    /**
     * @param VEvent $VEvent
     * @param string $email
     * @param bool $expectedResult
     * @param int $expectedCount
     *
     * @covers       \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event::deleteParticipant
     *
     * @dataProvider deleteParticipantProvider
     */
    public function testDeleteParticipant(VEvent $VEvent, $email, $expectedResult, $expectedCount)
    {
        $eventMock = $this->getEventMock($VEvent);
        $result = $eventMock->deleteParticipant($email);

        $this->assertEquals($expectedResult, $result);
        $this->assertEquals($expectedCount, count($eventMock->getParticipants()));
    }

    /**
     * Create mock object for event
     * @param VEvent $VEvent
     * @param mixed $mockMethods
     * @param array $links
     * @return \Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event mock object
     */
    protected function getEventMock(VEvent $VEvent, $mockMethods = null, array $links = array())
    {
        $eventMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Cal\Structures\Event')
                          ->setConstructorArgs(array($VEvent, -1, $links))
                          ->setMethods($mockMethods)
                          ->getMock();

        $dateTimeHelper = $this->getMockBuilder('Sugarcrm\Sugarcrm\Dav\Base\Helper\DateTimeHelper')
                               ->disableOriginalConstructor()
                               ->setMethods(null)
                               ->getMock();

        SugarTestReflection::setProtectedValue($eventMock, 'dateTimeHelper', $dateTimeHelper);

        return $eventMock;
    }
}
