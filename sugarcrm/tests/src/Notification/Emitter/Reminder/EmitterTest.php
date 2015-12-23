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

namespace Sugarcrm\SugarcrmTests\Notification\Emitter\Reminder;

use Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Emitter as ReminderEmitter;
use Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event as ReminderEvent;
use Sugarcrm\Sugarcrm\Notification\Dispatcher as NotificationDispatcher;

/**
 * Class EmitterTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Emitter
 */
class EmitterTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var ReminderEmitter|\PHPUnit_Framework_MockObject_MockObject */
    protected $emitter = null;

    /** @var NotificationDispatcher|\PHPUnit_Framework_MockObject_MockObject */
    protected $dispatcher = null;

    /** @var \User|\PHPUnit_Framework_MockObject_MockObject */
    protected $user = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->dispatcher = $this->getMock('Sugarcrm\Sugarcrm\Notification\Dispatcher');
        $this->user = $this->getMock('User');
        $this->user->id = create_guid();
        $this->emitter = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Emitter',
            array('getDispatcher')
        );
        $this->emitter->method('getDispatcher')->willReturn($this->dispatcher);
    }

    /**
     * Check if string representation of emitter is Reminder.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Emitter::__toString
     */
    public function testToString()
    {
        $this->assertEquals('Reminder', (string)$this->emitter);
    }

    /**
     * Data provider for testGetEventPrototypeByStringNotReminder.
     *
     * @see EmitterTest::testGetEventPrototypeByStringThrowsIsEventNotReminder
     * @return array
     */
    public static function getEventPrototypeByStringThrowsIsEventNotReminderProvider()
    {
        return array(
            'notReminder' => array(
                'eventName' => 'test' . rand(1000, 1999),
            ),
            'reminderUpperCase' => array(
                'eventName' => 'Reminder',
            ),
        );
    }

    /**
     * Check if function throw LogicException if event type not reminder.
     *
     * @dataProvider getEventPrototypeByStringThrowsIsEventNotReminderProvider
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Emitter::getEventPrototypeByString
     * @expectedException \LogicException
     * @param string $eventName
     */
    public function testGetEventPrototypeByStringThrowsIsEventNotReminder($eventName)
    {
        $this->emitter->getEventPrototypeByString($eventName);
    }

    /**
     * Check if method returns Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event object.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Emitter::getEventPrototypeByString
     */
    public function testGetEventPrototypeByStringReturnsCorrectObject()
    {
        $this->assertInstanceOf(
            'Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event',
            $this->emitter->getEventPrototypeByString('reminder')
        );
    }

    /**
     * Should returns array with "reminders".
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Emitter::getEventStrings
     */
    public function testGetEventStrings()
    {
        $this->assertEquals(array('reminder'), $this->emitter->getEventStrings());
    }

    /**
     * Data provider for testReminderDispatchesProperEvent.
     *
     * @see EmitterTest::testReminderDispatchesProperEvent
     * @return array
     */
    public static function reminderDispatchesProperEventProvider()
    {
        return array(
            'dispatchCall' => array(
                'beanModule' => 'Call',
            ),
            'dispatchMeeting' => array(
                'beanModule' => 'Meeting',
            ),
        );
    }

    /**
     * Reminder Event should be dispatched with provided bean and user. Also event type should be reminder.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Emitter::reminder
     * @dataProvider reminderDispatchesProperEventProvider
     * @param string $beanModule
     */
    public function testReminderDispatchesProperEvent($beanModule)
    {
        /** @var ReminderEvent|null $event */
        $event = null;
        /** @var \Call|\Meeting|\PHPUnit_Framework_MockObject_MockObject $bean */
        $bean = $this->getMock($beanModule);

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($arg) use (&$event) {
                $event = $arg;
            });

        $this->emitter->reminder($bean, $this->user);
        $this->assertInstanceOf('Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event', $event);
        $this->assertEquals($bean, $event->getBean());
        $this->assertEquals($this->user, $event->getUser());
    }

    /**
     * Method should throw Exceptions if upcoming events not Calls or Meetings.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Emitter::reminder
     * @expectedException \LogicException
     */
    public function testReminderThrowsOnWrongBeanType()
    {
        /** @var \SugarBean|\PHPUnit_Framework_MockObject_MockObject $bean */
        $bean = $this->getMock('SugarBean');
        $this->emitter->reminder($bean, $this->user);
    }
}
