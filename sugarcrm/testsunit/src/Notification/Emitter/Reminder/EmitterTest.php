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

namespace Sugarcrm\SugarcrmTestsUnit\Notification\Emitter\Reminder;

use Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Emitter;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Emitter
 */
class EmitterTest extends \PHPUnit_Framework_TestCase
{
    const NS_REMINDER_EVENT = 'Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Reminder\\Event';

    const NS_REMINDER_EMITTER = 'Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Reminder\\Emitter';

    const NS_DISPATCHER = 'Sugarcrm\\Sugarcrm\\Notification\\Dispatcher';

    /**
     * Test that getEventStrings have event name reminder.
     *
     * @covers ::getEventStrings
     */
    public function testIsEventStringsHaveReminder()
    {
        $emitter = new Emitter();

        $this->assertContains('reminder', $emitter->getEventStrings());
    }

    /**
     * Test that getEventPrototypeByString return reminder event.
     *
     * @covers ::getEventPrototypeByString
     */
    public function testInitReminderEvent()
    {
        $emitter = new Emitter();

        $this->assertInstanceOf(self::NS_REMINDER_EVENT, $emitter->getEventPrototypeByString('reminder'));
    }

    /**
     * Test that getEventPrototypeByString throw LogicException if invalid event name given.
     *
     * @covers ::getEventPrototypeByString
     * @expectedException \LogicException
     */
    public function testInitInvalidEvent()
    {
        $emitter = new Emitter();

        $emitter->getEventPrototypeByString('invalid-event-name'.microtime());
    }

    /**
     * Testing dispatching event reminder
     *
     * @covers ::reminder
     */
    public function testReminder()
    {
        $bean = $this->getMock('Call', array(), array(), '', false);
        $bean->id = 'call-id-'.microtime();

        $user = $this->getMock('User', array(), array(), '', false);
        $user->id = 'user-id-'.microtime();

        $event = $this->getMock(self::NS_REMINDER_EVENT, array('setBean', 'setUser'));
        $event->expects($this->once())->method('setBean')
            ->with($this->equalTo($bean))
            ->will($this->returnSelf());

        $event->expects($this->once())->method('setUser')
            ->with($this->equalTo($user))
            ->will($this->returnSelf());

        $dispatcher = $this->getMock(self::NS_DISPATCHER, array('dispatch'));
        $dispatcher->expects($this->once())->method('dispatch')
            ->with($this->equalTo($event));

        $emitter = $this->getMock(self::NS_REMINDER_EMITTER, array('getDispatcher', 'getEventPrototypeByString'));
        $emitter->expects($this->atLeastOnce())->method('getDispatcher')
            ->willReturn($dispatcher);
        $emitter->expects($this->atLeastOnce())->method('getEventPrototypeByString')
            ->with($this->equalTo('reminder'))
            ->willReturn($event);

        $emitter->reminder($bean, $user);
    }
}
