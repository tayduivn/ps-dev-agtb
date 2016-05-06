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

namespace Sugarcrm\SugarcrmTests\Trigger;

use \Sugarcrm\Sugarcrm\Notification\Emitter\Bean\BeanEmitterInterface;
use \Sugarcrm\Sugarcrm\Notification\EmitterRegistry;
use \Sugarcrm\Sugarcrm\Trigger\Reminder as TriggerReminder;

/**
 * Class ReminderTest
 *
 * @package Sugarcrm\SugarcrmTests\Trigger
 * @covers Sugarcrm\Sugarcrm\Trigger\Reminder
 */
class ReminderTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var BeanEmitterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $emitterInterface = null;

    /** @var EmitterRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $emitterRegistry = null;

    /** @var TriggerReminder|\PHPUnit_Framework_MockObject_MockObject $bean */
    protected $reminder = null;

    /** @var \User|\PHPUnit_Framework_MockObject_MockObject $bean */
    protected $user = null;

    /** @var \User|null */
    protected $currentUser = null;

    /** @var \TimeDate|\PHPUnit_Framework_MockObject_MockObject */
    protected $timeDate = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        \BeanFactory::setBeanClass('Calls', 'Sugarcrm\SugarcrmTests\Trigger\CallCRYS1308');
        \BeanFactory::setBeanClass('Meetings', 'Sugarcrm\SugarcrmTests\Trigger\MeetingCRYS1308');
        \BeanFactory::setBeanClass('Users', 'Sugarcrm\SugarcrmTests\Trigger\UserCRYS1308');
        $this->emitterInterface = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Emitter',
            array('reminder')
        );
        $this->reminder = $this->getMock(
            'Sugarcrm\Sugarcrm\Trigger\Reminder',
            array('getEmitterRegistry')
        );

        $this->user = new \User();
        $this->user->id = create_guid();
        $this->currentUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = $this->user;
        $this->timeDate = $this->getMock('TimeDate');
        $this->emitterRegistry = $this->getMock('Sugarcrm\Sugarcrm\Notification\EmitterRegistry');
        $this->emitterRegistry->method('getModuleEmitter')->willReturn($this->emitterInterface);
        $this->reminder->method('getEmitterRegistry')->willReturn($this->emitterRegistry);
        \SugarTestReflection::setProtectedValue($this->timeDate, 'timedate', new \TimeDate());
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        \BeanFactory::setBeanClass('Calls');
        \BeanFactory::setBeanClass('Meetings');
        \BeanFactory::setBeanClass('Users');
        $GLOBALS['current_user'] = $this->currentUser;
        \SugarTestReflection::setProtectedValue($this->timeDate, 'timedate', null);
        parent::tearDown();
    }

    /**
     * Data provider for testRemindAssignedUser.
     *
     * @see Sugarcrm\SugarcrmTests\Trigger\ReminderTest::testRemindAssignedUser
     * @return array
     */
    public static function remindAssignedUserProvider()
    {
        return array(
            'reminderTimeOutOfBounds' => array(
                'beanClass' => 'Meeting',
                'reminderTime' => 1200,
                'dateTimeFormat' => \TimeDate::DB_DATETIME_FORMAT,
                'dateFormat' => \TimeDate::DB_DATE_FORMAT,
                'timeFormat' => 'H:i:s',
                'dateTimezone' => 'UTC',
                'callReminder' => false,
            ),
            'createMeetingReminder' => array(
                'beanClass' => 'Meeting',
                'reminderTime' => 200,
                'dateTimeFormat' => \TimeDate::DB_DATETIME_FORMAT,
                'dateFormat' => \TimeDate::DB_DATE_FORMAT,
                'timeFormat' => 'H:i:s',
                'dateTimezone' => 'UTC',
                'callReminder' => true,
            ),
            'createCallReminder' => array(
                'beanClass' => 'Call',
                'reminderTime' => 300,
                'dateTimeFormat' => \TimeDate::DB_DATETIME_FORMAT,
                'dateFormat' => \TimeDate::DB_DATE_FORMAT,
                'timeFormat' => 'H:i:s',
                'dateTimezone' => 'UTC',
                'callReminder' => true,
            ),
            'tryWrongReminderTime' => array(
                'beanClass' => 'Call',
                'reminderTime' => -100,
                'dateTimeFormat' => \TimeDate::DB_DATETIME_FORMAT,
                'dateFormat' => \TimeDate::DB_DATE_FORMAT,
                'timeFormat' => 'H:i:s',
                'dateTimezone' => 'UTC',
                'callReminder' => false,
            ),
            'createCallReminderReminderUserDateFormat' => array(
                'beanClass' => 'Call',
                'reminderTime' => 300,
                'dateTimeFormat' => 'Y/m/d H:i',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'H:i',
                'dateTimezone' => 'Europe/Berlin',
                'callReminder' => true,
            ),
        );
    }

    /**
     * Triggers reminder with proper params if user is assigned to bean and bean reminder_time not out of bounds.
     * Does nothing if user is assigned and reminder_time of bean out of bounds.
     *
     * @dataProvider remindAssignedUserProvider
     * @covers Sugarcrm\Sugarcrm\Trigger\Reminder::remind
     * @param string $beanClass
     * @param int $reminderTime
     * @param string $dateTimeFormat user datetime format.
     * @param string $dateFormat user date format.
     * @param string $timeFormat user time format.
     * @param string $dateTimezone user timezone.
     * @param bool $callReminder
     */
    public function testRemindAssignedUser(
        $beanClass,
        $reminderTime,
        $dateTimeFormat,
        $dateFormat,
        $timeFormat,
        $dateTimezone,
        $callReminder
    ) {
        $this->user->setPreference('datef', $dateFormat);
        $this->user->setPreference('timef', $timeFormat);
        $this->user->setPreference('timezone', $dateTimezone);
        $now = new \DateTime('now', new \DateTimeZone($dateTimezone));

        $processedUser = null;
        $processedBean = null;
        /** @var \SugarBean $bean */
        $bean = new $beanClass();
        $bean->id = create_guid();
        $bean->reminder_time = $reminderTime;
        $bean->assigned_user_id = $this->user->id;
        $bean->date_start = $now->format($dateTimeFormat);
        \BeanFactory::registerBean($bean);

        if ($callReminder) {
            $this->emitterInterface->expects($this->once())
                ->method('reminder')
                ->willReturnCallback(function ($processedBean, $processedUser) use ($bean) {
                    $this->assertEquals($bean, $processedBean);
                    $this->assertEquals($processedUser->id, $this->user->id);
                });
        } else {
            $this->emitterInterface->expects($this->never())->method('reminder');
        }

        $this->reminder->remind($bean->module_name, $bean->id, $this->user->id);
        \BeanFactory::unregisterBean($bean);
    }

    /**
     * Data provider for testRemindNotAssignedUser.
     *
     * @see Sugarcrm\SugarcrmTests\Trigger\ReminderTest::testRemindNotAssignedUser
     * @return array
     */
    public static function remindNotAssignedUserProvider()
    {
        return array(
            'reminderTimeOutOfBounds' => array(
                'beanModule' => 'Meetings',
                'reminderTime' => 1200,
                'dateTimezone' => 'Europe/Berlin',
                'dateTimeFormat' => 'Y/m/d H:i',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'H:i',
                'callReminder' => false,
            ),
            'tryWrongReminderTime' => array(
                'beanModule' => 'Meetings',
                'reminderTime' => -100,
                'dateTimezone' => 'UTC',
                'dateTimeFormat' => 'Y/m/d H:i',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'H:i',
                'callReminder' => false,
            ),
        );
    }

    /**
     * Triggers reminder with proper params if user not assigned to bean and user's reminder_time not out of bounds.
     * Does nothing if user is not assigned and user reminder_time out of bounds.
     *
     * @dataProvider remindNotAssignedUserProvider
     * @covers Sugarcrm\Sugarcrm\Trigger\Reminder::remind
     * @param string $beanModule
     * @param int $reminderTime
     * @param string $dateTimezone
     * @param string $dateTimeFormat user datetime format.
     * @param string $dateFormat user date format.
     * @param string $timeFormat user time format.
     * @param bool $callReminder
     */
    public function testRemindNotAssignedUser(
        $beanModule,
        $reminderTime,
        $dateTimezone,
        $dateTimeFormat,
        $dateFormat,
        $timeFormat,
        $callReminder
    ) {
        $now = new \DateTime('now', new \DateTimeZone($dateTimezone));

        $this->user->setPreference('reminder_time', $reminderTime);
        $this->user->setPreference('datef', $dateFormat);
        $this->user->setPreference('timef', $timeFormat);
        $this->user->setPreference('timezone', $dateTimezone);

        /** @var \Meeting|\PHPUnit_Framework_MockObject_MockObject $bean */
        $bean = \BeanFactory::getBean($beanModule);
        $bean->id = create_guid();
        $bean->date_start = $now->format($dateTimeFormat);
        \BeanFactory::registerBean($bean);

        if ($callReminder) {
            $this->emitterInterface->expects($this->once())
                ->method('reminder')
                ->willReturnCallback(function ($processedBean, $processedUser) use ($bean) {
                    $this->assertEquals($bean, $processedBean);
                    $this->assertEquals($processedUser->id, $this->user->id);
                });
        } else {
            $this->emitterInterface->expects($this->never())->method('reminder');
        }

        $this->reminder->remind($bean->module_name, $bean->id, $this->user->id);
        \BeanFactory::unregisterBean($bean);
    }
}

/**
 * Class UserCRYS1308
 *
 * @package Sugarcrm\SugarcrmTests\Trigger
 */
class UserCRYS1308 extends \User
{
    /**
     * {@inheritdoc}
     *
     * @param string $id
     * @param bool|true $encode
     * @param bool|true $deleted
     * @return UserCRYS1308
     */
    public function retrieve($id = '-1', $encode = true, $deleted = true)
    {
        $this->id = $id;
        return $this;
    }
}

/**
 * Class MeetingCRYS1308
 *
 * @package Sugarcrm\SugarcrmTests\Trigger
 */
class MeetingCRYS1308 extends \Meeting
{

}

/**
 * Class CallCRYS1308
 *
 * @package Sugarcrm\SugarcrmTests\Trigger
 */
class CallCRYS1308 extends \Call
{

}
