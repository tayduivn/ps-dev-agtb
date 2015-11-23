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

namespace Sugarcrm\SugarcrmTestsUnit\Trigger\ReminderManager;

use Sugarcrm\Sugarcrm\Trigger\ReminderManager\TriggerServer;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class TriggerServerTest
 * @package Sugarcrm\SugarcrmTestsUnit\Trigger\ReminderManager
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Trigger\ReminderManager\TriggerServer
 */
class TriggerServerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_DATE = '2015-11-08 10:30:00';
    const TEST_DATE_T = '2015-11-08T10:30:00';

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $bean
     * @param boolean $isUpdate
     * @param boolean $deleteRemindersCalled
     * @dataProvider providerSetReminders
     * @covers ::setReminders
     */
    public function testSetReminders($bean, $isUpdate, $deleteRemindersCalled)
    {
        $manager = $this->getTriggerServerMock(array(
            'deleteReminders',
            'addReminders'
        ));

        $manager->expects($this->exactly($deleteRemindersCalled ? 1 : 0))
            ->method('deleteReminders')
            ->with($bean);
        $manager->expects($this->once())
            ->method('addReminders')
            ->with($bean);

        $manager->setReminders($bean, $isUpdate);
    }

    /**
     * (bean, isUpdate, is ::deleteReminders called)
     * @return array
     */
    public function providerSetReminders()
    {
        return array(
            'isUpdate is false' => array($this->getBeanMock('Call'), false, false),
            'isUpdate is true' => array($this->getBeanMock('Call'), true, true)
        );
    }

    /**
     * @covers ::deleteReminders
     */
    public function testDeleteReminders()
    {
        $tag = 'dummy-tag';
        $bean = $this->getBeanMock('Call');
        $manager = $this->getTriggerServerMock(array(
            'deleteByTag',
            'makeTag'
        ));
        $manager->expects($this->once())
            ->method('deleteByTag')
            ->with($tag);

        $manager->method('makeTag')
            ->with($bean)
            ->willReturn($tag);

        $manager->deleteReminders($bean);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $bean
     * @param array $loadUsersMap
     * @param array $getReminderTimeMap
     * @param array $createReminderMap
     * @param int $createReminderCallsCount
     * @dataProvider providerAddReminders
     * @covers ::addReminders
     */
    public function testAddReminders($bean, $loadUsersMap, $getReminderTimeMap, $createReminderMap, $createReminderCallsCount)
    {
        $manager = $this->getTriggerServerMock(array(
            'loadUsers',
            'getReminderTime',
            'createReminder'
        ));

        $manager->method('loadUsers')->will($this->returnValueMap($loadUsersMap));
        $manager->method('getReminderTime')->will($this->returnValueMap($getReminderTimeMap));
        $manager->expects($this->exactly($createReminderCallsCount))
            ->method('createReminder')
            ->will($this->returnValueMap($createReminderMap));

        TestReflection::callProtectedMethod($manager, 'addReminders', array($bean));
    }

    /**
     * (call or meeting bean,
     *  users map for ::getBean mock,
     *  reminders map for ::getReminderTime mock,
     *  map for ::createReminder mock,
     *  expected ::createReminder calls count)
     * @return array
     */
    public function providerAddReminders()
    {
        $data = array();

        // bean without users
        $bean = $this->getBeanMock('Call', array('users_arr' => array('dummy-users-array-0')));
        $loadUsersMap = array(
            array(array('dummy-users-array-0'), array())
        );
        $getReminderTimeMap = array();
        $createTSReminderMap = array();
        $data['bean without users'] = array($bean, $loadUsersMap, $getReminderTimeMap, $createTSReminderMap, 0);

        // bean with one user with one reminder time set
        $bean = $this->getBeanMock('Call', array('users_arr' => array('dummy-users-array-1')));
        $user1 = $this->getBeanMock('User');
        $loadUsersMap = array(
            array(array('dummy-users-array-1'), array($user1))
        );
        $getReminderTimeMap = array(
            array($bean, $user1, 600)
        );
        $createTSReminderMap = array(
            array($bean, $user1, 600)
        );
        $data['bean with one user with one reminder time set'] =
            array($bean, $loadUsersMap, $getReminderTimeMap, $createTSReminderMap, 1);

        // bean with 3 users with 3 reminders time set
        $bean = $this->getBeanMock('Call', array('users_arr' => array('dummy-users-array-2')));
        $user1 = $this->getBeanMock('User');
        $user2 = $this->getBeanMock('User');
        $user3 = $this->getBeanMock('User');
        $loadUsersMap = array(
            array(array('dummy-users-array-2'), array($user1, $user2, $user3))
        );
        $getReminderTimeMap = array(
            array($bean, $user1, 60),
            array($bean, $user2, 300),
            array($bean, $user3, 600)
        );
        $createTSReminderMap = array(
            array($bean, $user1, 60),
            array($bean, $user2, 300),
            array($bean, $user3, 600)
        );
        $data['bean with 3 users with 3 reminders time set'] =
            array($bean, $loadUsersMap, $getReminderTimeMap, $createTSReminderMap, 3);

        // bean with 3 users with 1 reminders time set
        $bean = $this->getBeanMock('Call', array('users_arr' => array('dummy-users-array-3')));
        $user1 = $this->getBeanMock('User');
        $user2 = $this->getBeanMock('User');
        $user3 = $this->getBeanMock('User');
        $loadUsersMap = array(
            array(array('dummy-users-array-3'), array($user1, $user2, $user3))
        );
        $getReminderTimeMap = array(
            array($bean, $user1, -1),
            array($bean, $user2, 0),
            array($bean, $user3, 600)
        );
        $createTSReminderMap = array(
            array($bean, $user3, 600)
        );
        $data['bean with 3 users with 1 reminders time set'] =
            array($bean, $loadUsersMap, $getReminderTimeMap, $createTSReminderMap, 1);

        return $data;
    }

    /**
     * @param int $reminderTime
     * @param boolean $triggerAddingMethodsCalled
     * @dataProvider providerAddReminderForUser
     * @covers ::addReminderForUser
     */
    public function testAddReminderForUser($reminderTime, $triggerAddingMethodsCalled)
    {
        $bean = $this->getBeanMock('SugarBean');
        $user = $this->getBeanMock('User');

        $manager = $this->getTriggerServerMock(array(
            'getReminderTime',
            'createReminder'
        ));

        $manager->method('getReminderTime')
            ->with($bean, $user)
            ->willReturn($reminderTime);

        $manager->expects($this->exactly($triggerAddingMethodsCalled ? 1 : 0))
            ->method('createReminder')
            ->with($bean, $user, $reminderTime);

        TestReflection::callProtectedMethod($manager, 'addReminderForUser', array($bean, $user));
    }

    /**
     * (user's reminder time, is SchedulersJob created and added to SugarJobQueue)
     * @return array
     */
    public function providerAddReminderForUser()
    {
        return array(
            'reminder time is "-1"' => array(-1, false),
            'reminder time is "0"' => array(0, false),
            'reminder time is greater than "0"' => array(600, true)
        );
    }

    /**
     * @covers ::createTriggerServerReminder
     */
    public function testCreateTriggerServerReminder()
    {
        $args = array('dummy-args');
        $tags = array('dummy-tags');

        $bean = $this->getBeanMock('Call', array('id' => 'call-id', 'date_start' => static::TEST_DATE));
        $user = $this->getBeanMock('User', array('id' => 'user-id'));
        $reminderTime = 600;

        $triggerClient = $this->getTriggerClientMock(array('push'));
        $triggerClient->expects($this->once())
            ->method('push')
            ->with(
                'call-id-user-id',
                static::TEST_DATE_T,
                'post',
                TriggerServer::CALLBACK_URL,
                $args,
                $tags
            );

        $manager = $this->getTriggerServerMock(array(
            'prepareReminderDateTime',
            'prepareTriggerArgs',
            'prepareTags',
            'getTriggerClient'
        ));

        $manager->expects($this->once())
            ->method('prepareReminderDateTime')
            ->with(static::TEST_DATE, $reminderTime)
            ->willReturn(new \DateTime(static::TEST_DATE));

        $manager->method('prepareTriggerArgs')->willReturn($args);
        $manager->method('prepareTags')->willReturn($tags);
        $manager->method('getTriggerClient')->willReturn($triggerClient);

        TestReflection::callProtectedMethod($manager, 'createReminder',
            array($bean, $user, $reminderTime));
    }

    /**
     * @covers ::deleteByTag
     */
    public function testDeleteByTag()
    {
        $tag = 'dummy-tag';
        $triggerClient = $this->getTriggerClientMock(array('deleteByTags'));
        $triggerClient->expects($this->once())
            ->method('deleteByTags')
            ->with(array($tag));

        $manager = $this->getTriggerServerMock(array('getTriggerClient'));
        $manager->method('getTriggerClient')->willReturn($triggerClient);

        TestReflection::callProtectedMethod($manager, 'deleteByTag', array($tag));
    }

    /**
     * @covers ::prepareTags
     */
    public function testPrepareTags()
    {
        $bean = $this->getBeanMock('SugarBean');
        $user = $this->getBeanMock('User');

        $beanTag = 'bean-tag';
        $userTag = 'user-tag';

        $tagsMap = array(
            array($bean, $beanTag),
            array($user, $userTag)
        );

        $expected = array('bean-tag', 'user-tag');

        $manager = $this->getTriggerServerMock(array('makeTag'));
        $manager->method('makeTag')->will($this->returnValueMap($tagsMap));
        $this->assertEquals($expected,
            TestReflection::callProtectedMethod($manager, 'prepareTags', array($bean, $user)));
    }

    /**
     * @covers ::getTriggerClient
     */
    public function testGetTriggerClient()
    {
        $manager = $this->getTriggerServerMock();
        $this->assertInstanceOf('\Sugarcrm\Sugarcrm\Trigger\Client',
            TestReflection::callProtectedMethod($manager, 'getTriggerClient'));
    }

    /**
     * @param string $className
     * @param array $properties
     * @param array|null $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getBeanMock($className, $properties = array(), $methods = null)
    {
        $bean = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        $bean->object_name = $className;

        foreach ($properties as $name => $value) {
            $bean->$name = $value;
        }
        return $bean;
    }

    /**
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getTriggerServerMock($methods = array())
    {
        return $this->getMockBuilder('Sugarcrm\\Sugarcrm\\Trigger\\ReminderManager\\TriggerServer')
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getTriggerClientMock($methods = array())
    {
        return $this->getMockBuilder('Sugarcrm\\Sugarcrm\\Trigger\\Client')
            ->setMethods($methods)
            ->getMock();
    }
}
