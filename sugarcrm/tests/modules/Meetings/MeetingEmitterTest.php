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

require_once 'modules/Meetings/Emitter.php';

/**
 * @coversDefaultClass MeetingEmitter
 */
class MeetingsEmitterTest extends Sugar_PHPUnit_Framework_TestCase
{
    const NS_REMINDER_EMITTER = 'Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Reminder\\Emitter';

    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown()
    {
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @covers ::getEventPrototypeByString
     */
    public function testGetEventPrototypeByString()
    {
        $expectEvent = 'ExpectEvent'.microtime();
        $expectEventName = 'reminder-Event-Name'.microtime();

        $reminderEmitter = $this->getMock(self::NS_REMINDER_EMITTER, array('getEventPrototypeByString'));
        $reminderEmitter->expects($this->atLeastOnce())->method('getEventPrototypeByString')
            ->with($this->equalTo($expectEventName))
            ->willReturn($expectEvent);

        $meetingEmitter = new MeetingEmitter($reminderEmitter);

        $event = $meetingEmitter->getEventPrototypeByString($expectEventName);

        $this->assertEquals($expectEvent, $event);
    }

    /**
     * Checking method is correctly throw calling.
     *
     * @covers ::__call
     */
    public function testThrowMethod()
    {
        $call = SugarTestMeetingUtilities::createMeeting();
        $user = SugarTestUserUtilities::createAnonymousUser(false);
        $user->id = microtime();

        $reminderEmitter = $this->getMock(self::NS_REMINDER_EMITTER, array('reminder'));
        $reminderEmitter->expects($this->once())->method('reminder')
            ->with($this->equalTo($call), $this->equalTo($user));

        $meetingEmitter = new MeetingEmitter($reminderEmitter);

        $meetingEmitter->reminder($call, $user);
    }

    /**
     * @covers ::getEventStrings
     */
    public function testGetEventStrings()
    {
        $reminderEmitter = $this->getMock(self::NS_REMINDER_EMITTER, array('getEventStrings'));
        $reminderEmitter->expects($this->atLeastOnce())->method('getEventStrings')->willReturn(array('reminder'));

        $meetingEmitter = new MeetingEmitter($reminderEmitter);

        $eventStrings = $meetingEmitter->getEventStrings();

        $this->assertContains('reminder', $eventStrings);
    }
}
