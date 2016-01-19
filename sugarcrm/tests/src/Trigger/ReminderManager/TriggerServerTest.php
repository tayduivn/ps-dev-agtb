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

namespace Sugarcrm\SugarcrmTests\Trigger\ReminderManager;

use Sugarcrm\Sugarcrm\Trigger\Client as TriggerClient;
use Sugarcrm\Sugarcrm\Trigger\ReminderManager\TriggerServer as TriggerServerManager;

/**
 * Class TriggerServerTest
 *
 * @package Sugarcrm\SugarcrmTests\Trigger\ReminderManager
 * @covers \Sugarcrm\Sugarcrm\Trigger\ReminderManager\TriggerServer
 */
class TriggerServerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|TriggerClient */
    protected $triggerClient;

    /** @var \PHPUnit_Framework_MockObject_MockObject|TriggerServerManager */
    protected $triggerServerManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->triggerClient = $this->getMock('Sugarcrm\Sugarcrm\Trigger\Client');
        $this->triggerServerManager = $this->getMock(
            'Sugarcrm\Sugarcrm\Trigger\ReminderManager\TriggerServer',
            array('getTriggerClient')
        );

        $this->triggerServerManager->method('getTriggerClient')->willReturn($this->triggerClient);
    }

    /**
     * Data provider for testDeleteRemindersDeletesTriggersOnServer.
     *
     * @see Sugarcrm\SugarcrmTests\Trigger\ReminderManager\TriggerServerTest::testDeleteRemindersDeletesTriggersOnServer
     * @return array
     */
    public static function deleteRemindersDeletesTriggersOnServerProvider()
    {
        $meetingId = create_guid();
        $callId = create_guid();

        return array(
            'deleteTagsForMeeting' => array(
                'beanClass' => 'Meeting',
                'beanId' => $meetingId,
                'expectedTags' => array('meeting-' . $meetingId),
            ),
            'deleteTagsForCall' => array(
                'beanClass' => 'Call',
                'beanId' => $callId,
                'expectedTags' => array('call-' . $callId),
            ),
        );
    }

    /**
     * Should build tags array and call deleteByTags method with generated array.
     *
     * @dataProvider deleteRemindersDeletesTriggersOnServerProvider
     * @covers Sugarcrm\Sugarcrm\Trigger\ReminderManager\TriggerServer::deleteReminders
     * @param string $beanClass
     * @param string $beanId
     * @param array $expectedTags
     */
    public function testDeleteRemindersDeletesTriggersOnServer($beanClass, $beanId, $expectedTags)
    {
        /** @var \Meeting|\Call|\PHPUnit_Framework_MockObject_MockObject $bean */
        $bean = $this->getMock($beanClass);
        $bean->id = $beanId;
        $bean->name = 'Name' . rand(1000, 1999);

        $this->triggerClient->expects($this->once())
            ->method('deleteByTags')
            ->with($expectedTags);

        $this->triggerServerManager->deleteReminders($bean);
    }

    /**
     * Data provider for testAddReminderForUser.
     *
     * @see Sugarcrm\SugarcrmTests\Trigger\ReminderManager\TriggerServerTest::testAddReminderForUser
     * @return array
     */
    public static function addReminderForUserProvider()
    {
        $meetingId = create_guid();
        $callId = create_guid();
        $userId = create_guid();
        return array(
            'pushMeetingReminder' => array(
                'userId' => $userId,
                'beanClass' => 'Meeting',
                'beanId' => $meetingId,
                'reminderTime' => '2016-01-08 11:28:19',
                'reminderTimeZone' => 'UTC',
                'expectedParams' => array(
                    'id' => $meetingId . '-' . $userId,
                    'formattedTime' => '2016-01-08T11:28:19',
                    'method' => 'post',
                    'uri' => 'rest/v10/reminder',
                    'args' => array(
                        'module' => 'Meetings',
                        'beanId' => $meetingId,
                        'userId' => $userId,
                    ),
                    'tags' => array(
                        'meeting-' . $meetingId,
                        'user-' . $userId,
                    ),
                ),
            ),
            'pushCallReminder' => array(
                'userId' => $userId,
                'beanClass' => 'Call',
                'beanId' => $callId,
                'reminderTime' => '2016-01-02 15:00:05',
                'reminderTimeZone' => 'Europe/Berlin',
                'expectedParams' => array(
                    'id' => $callId . '-' . $userId,
                    'formattedTime' => '2016-01-02T14:00:05',
                    'method' => 'post',
                    'uri' => 'rest/v10/reminder',
                    'args' => array(
                        'module' => 'Calls',
                        'beanId' => $callId,
                        'userId' => $userId,
                    ),
                    'tags' => array(
                        'call-' . $callId,
                        'user-' . $userId,
                    ),
                ),
            ),
        );
    }

    /**
     * Should prepare tags and trigger arguments. After that call push method with built tags and arguments.
     *
     * @dataProvider addReminderForUserProvider
     * @covers Sugarcrm\Sugarcrm\Trigger\ReminderManager\TriggerServer::addReminderForUser
     * @param string $userId
     * @param string $beanClass
     * @param string $beanId
     * @param string $reminderTime
     * @param string $reminderTimeZone
     * @param array $expectedParams
     */
    public function testAddReminderForUser(
        $userId,
        $beanClass,
        $beanId,
        $reminderTime,
        $reminderTimeZone,
        $expectedParams
    ) {
        /** @var \Meeting|\Call|\PHPUnit_Framework_MockObject_MockObject $bean */
        $bean = $this->getMock($beanClass);
        $bean->id = $beanId;
        $bean->name = 'Name' . rand(1000, 1999);

        /** @var \User|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMock('User');
        $user->id = $userId;

        $reminder = \DateTime::createFromFormat('Y-m-d H:i:s', $reminderTime, new \DateTimeZone($reminderTimeZone));
        $expectedReminder = clone $reminder;

        $this->triggerClient->expects($this->once())
            ->method('push')
            ->with(
                $this->equalTo($expectedParams['id']),
                $this->equalTo($expectedParams['formattedTime']),
                $this->equalTo($expectedParams['method']),
                $this->equalTo($expectedParams['uri']),
                $this->equalTo($expectedParams['args']),
                $this->equalTo($expectedParams['tags'])
            );

        $this->triggerServerManager->addReminderForUser($bean, $user, $reminder);
        $this->assertEquals($expectedReminder, $reminder);
    }
}
