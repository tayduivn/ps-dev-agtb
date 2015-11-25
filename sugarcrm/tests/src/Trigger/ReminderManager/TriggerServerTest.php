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

namespace Sugarcrm\SugarcrmTests\Trigger\ReminderManager;

use Sugarcrm\Sugarcrm\Trigger\Client;
use Sugarcrm\Sugarcrm\Trigger\ReminderManager\TriggerServer;

/**
 * Class TriggerServer
 * @package Sugarcrm\SugarcrmTests\Trigger\ReminderManager
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Trigger\ReminderManager\TriggerServer
 */
class TriggerServerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \DateTime
     */
    protected $dateStart;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TriggerServer
     */
    protected $triggerServerManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Client
     */
    protected $triggerClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Call|\Meeting|\SugarBean
     */
    protected $bean;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\User
     */
    protected $user;

    public function setUp()
    {
        parent::setUp();
        $this->dateStart = new \DateTime();
        $this->triggerClient = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Trigger\\Client',
            array('push', 'delete', 'deleteByTags')
        );
    }

    /**
     * @param boolean $isUpdate
     * @param boolean $isDeleteRemindersCalled
     * @dataProvider providerSetRemindersDeletesTriggersWhenIsUpdateIsTrue
     * @covers ::setReminders
     */
    public function testSetRemindersDeletesTriggersWhenIsUpdateIsTrue($isUpdate, $isDeleteRemindersCalled)
    {
        $this->mockBean('SugarBean');
        $this->mockUser();
        $this->mockTriggerServerManager(array('deleteReminders', 'addReminders'));

        $this->triggerServerManager->expects($this->exactly($isDeleteRemindersCalled ? 1 : 0))
            ->method('deleteReminders')
            ->with($this->bean);

        $this->triggerServerManager->setReminders($this->bean, $isUpdate);
    }

    /**
     * @return array
     */
    public function providerSetRemindersDeletesTriggersWhenIsUpdateIsTrue()
    {
        return array(
            '$isUpdate is false' => array(false, false),
            '$isUpdate is true' => array(true, true)
        );
    }

    /**
     * @param boolean $isAuthor
     * @param int $userReminderTime
     * @param int $beanReminderTime
     * @param int $expected
     * @dataProvider providerSetRemindersUsesProperReminderTime
     * @covers ::setReminders
     */
    public function testSetRemindersUsesProperReminderTime($isAuthor, $userReminderTime, $beanReminderTime, $expected)
    {
        $this->mockBean('SugarBean');
        $this->mockUser($userReminderTime);

        $this->bean->users_arr = array($this->user->id);
        $this->bean->reminder_time = $beanReminderTime;
        $this->bean->assigned_user_id = $isAuthor ? $this->user->id : 'dummy-other-user-id';

        $current = $this->dateStart->getTimestamp();

        $this->triggerClient->expects($this->any())
            ->method('push')
            ->will($this->returnCallback(function ($id, $time, $method, $url, $args, $tags) use ($expected, $current) {
                $reminderTime = $current - strtotime($time);
                \PHPUnit_Framework_Assert::assertEquals($expected, $reminderTime);
            }));

        $this->mockTriggerServerManager(array(
            'loadUsers',
            'getTriggerClient'
        ));

        $this->triggerServerManager->method('loadUsers')->willReturn(array($this->user));
        $this->triggerServerManager->method('getTriggerClient')->willReturn($this->triggerClient);

        $this->triggerServerManager->setReminders($this->bean, false);
    }

    /**
     * @return array
     */
    public function providerSetRemindersUsesProperReminderTime()
    {
        return array(
            'returns bean reminder time when user is author' => array(true, 60, 300, 300),
            'returns user reminder time when user isn\'t author' => array(false, 60, 300, 60),
        );
    }

    /**
     * @param int $reminderTime
     * @param boolean $isPushCalled
     * @dataProvider providerSetRemindersPushesTriggerWhenReminderTimeIsGreatThanZero
     * @covers ::setReminders
     */
    public function testSetRemindersPushesTriggerWhenReminderTimeIsGreatThanZero($reminderTime, $isPushCalled)
    {
        $this->mockBean('SugarBean');
        $this->mockUser();

        $this->bean->users_arr = array($this->user->id);

        $this->triggerClient->expects($this->exactly($isPushCalled ? 1 : 0))->method('push');

        $this->mockTriggerServerManager(array(
            'loadUsers',
            'getTriggerClient',
            'getReminderTime'
        ));

        $this->triggerServerManager->method('loadUsers')->willReturn(array($this->user));
        $this->triggerServerManager->method('getTriggerClient')->willReturn($this->triggerClient);
        $this->triggerServerManager->method('getReminderTime')->willReturn($reminderTime);

        $this->triggerServerManager->setReminders($this->bean, false);
    }

    /**
     * @return array
     */
    public function providerSetRemindersPushesTriggerWhenReminderTimeIsGreatThanZero()
    {
        return array(
            'doesn\'t push trigger when reminder time is less than zero' => array(-1, false),
            'doesn\'t push trigger when reminder time equals zero' => array(0, false),
            'pushes trigger when reminder time is great than zero' => array(60, true),
        );
    }

    /**
     * @covers ::setReminders
     */
    public function testSetRemindersPushesTriggerToServerWithCorrectArguments()
    {
        $this->mockBean('SugarBean');
        $this->mockUser();

        $reminderTime = 60;

        $this->bean->users_arr = array($this->user->id);
        $this->bean->object_name = 'bean';

        $id = $this->bean->id . '-' . $this->user->id;
        $executeTimestamp = $this->dateStart->getTimestamp() - $reminderTime;
        $executeTime = date('Y-m-d\TH:i:s', $executeTimestamp);
        $args = array(
            'module' => $this->bean->module_name,
            'beanId' => $this->bean->id,
            'userId' => $this->user->id
        );
        $tags = array(
            'bean-' . $this->bean->id,
            'user-' . $this->user->id
        );

        $this->triggerClient->expects($this->once())
            ->method('push')
            ->with($id, $executeTime, 'post', TriggerServer::CALLBACK_URL, $args, $tags);

        $this->mockTriggerServerManager(array(
            'loadUsers',
            'getTriggerClient',
            'getReminderTime'
        ));

        $this->triggerServerManager->method('loadUsers')->willReturn(array($this->user));
        $this->triggerServerManager->method('getTriggerClient')->willReturn($this->triggerClient);
        $this->triggerServerManager->method('getReminderTime')->willReturn($reminderTime);

        $this->triggerServerManager->setReminders($this->bean, false);
    }

    /**
     * @param int $usersCount
     * @param int $pushCallsCount
     * @dataProvider providerSetRemindersPushesTriggerToServerDependsOnUsersCount
     * @covers ::setReminders
     */
    public function testSetRemindersPushesTriggerToServerDependsOnUsersCount($usersCount, $pushCallsCount)
    {
        $this->mockBean('SugarBean');

        $reminderTime = 60;

        $users = array();
        $this->bean->users_arr = array();
        for ($i = 0; $i < $usersCount; $i++) {
            /* @var $item \PHPUnit_Framework_MockObject_MockObject|\Call|\Meeting|\SugarBean */
            $item = $this->getMock('User', array('getPreference'));
            $item->id = 'dummy-user-id-' . $i;
            $item->method('getPreference')->willReturn($reminderTime);
            $this->bean->users_arr[] = $item->id;
            $users[$i] = $item;
        }

        $this->triggerClient->expects($this->exactly($pushCallsCount))
            ->method('push');

        $this->mockTriggerServerManager(array(
            'loadUsers',
            'getTriggerClient'
        ));

        $this->triggerServerManager->method('loadUsers')->willReturn($users);
        $this->triggerServerManager->method('getTriggerClient')->willReturn($this->triggerClient);

        $this->triggerServerManager->setReminders($this->bean, false);
    }

    /**
     * @return array
     */
    public function providerSetRemindersPushesTriggerToServerDependsOnUsersCount()
    {
        return array(
            'users count is zero' => array(0, 0),
            'users count is 1' => array(1, 1),
            'users count is 3' => array(3, 3)
        );
    }

    /**
     * @covers ::deleteReminders
     */
    public function testDeleteRemindersDeletesTriggersOnServer()
    {
        $this->mockBean('SugarBean');
        $this->bean->object_name = 'bean';

        $tag = 'bean-' . $this->bean->id;

        $this->triggerClient->expects($this->once())
            ->method('deleteByTags')
            ->with(array($tag));

        $this->mockTriggerServerManager(array(
            'getTriggerClient'
        ));

        $this->triggerServerManager->method('getTriggerClient')->willReturn($this->triggerClient);

        $this->triggerServerManager->deleteReminders($this->bean);
    }

    /**
     * @covers ::addReminderForUser
     */
    public function testAddReminderForUserPushesTriggerToServerForSpecifiedUserOfBean()
    {
        $reminderTime = 60;

        $this->mockBean('SugarBean');
        $this->mockUser($reminderTime);

        $this->bean->users_arr = array($this->user->id);

        $this->mockTriggerServerManager(array(
            'createReminder'
        ));

        $this->triggerServerManager->expects($this->once())
            ->method('createReminder')
            ->with($this->bean, $this->user, $reminderTime);

        $this->triggerServerManager->addReminderForUser($this->bean, $this->user);
    }

    /**
     * @param string $module
     */
    protected function mockBean($module)
    {
        $this->bean = $this->getMock($module);
        $this->bean->id = 'dummy-bean-id';
        $this->bean->name = 'dummy bean name';
        $this->bean->date_start = $this->dateStart->format('Y-m-d H:i:s');
    }

    protected function mockUser($reminderTime = 60)
    {
        $this->user = $this->getMock('User', array('getPreference'));
        $this->user->id = 'dummy-user-id';
        $this->user->method('getPreference')->willReturn($reminderTime);
    }

    protected function mockTriggerServerManager($methods = array())
    {
        $this->triggerServerManager = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Trigger\\ReminderManager\\TriggerServer',
            $methods
        );
    }
}
