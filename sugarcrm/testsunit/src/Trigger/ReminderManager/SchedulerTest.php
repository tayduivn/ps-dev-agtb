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

use Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class SchedulerTest
 * @package Sugarcrm\SugarcrmTestsUnit\Trigger\ReminderManager
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler
 */
class SchedulerTest extends \PHPUnit_Framework_TestCase
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
        $manager = $this->getSchedulerMock(array(
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
        $manager = $this->getSchedulerMock(array(
            'deleteByJobGroup',
            'makeTag'
        ));
        $manager->expects($this->once())
            ->method('deleteByJobGroup')
            ->with($tag);

        $manager->method('makeTag')
            ->with($bean)
            ->willReturn($tag);

        $manager->deleteReminders($bean);
    }

    /**
     * @param int $reminderTime
     * @param boolean $jobAddingMethodsCalled
     * @dataProvider providerAddReminderForUser
     * @covers ::addReminderForUser
     */
    public function testAddReminderForUser($reminderTime, $jobAddingMethodsCalled)
    {
        $bean = $this->getBeanMock('SugarBean');
        $user = $this->getBeanMock('User');
        $job = $this->getSugarMock('SchedulersJob');

        $queue = $this->getSugarMock('SugarJobQueue', array('submitJob'));
        $queue->expects($this->exactly($jobAddingMethodsCalled ? 1 : 0))
            ->method('submitJob')
            ->with($job);

        $manager = $this->getSchedulerMock(array(
            'getSugarJobQueue',
            'getReminderTime',
            'createSchedulersJob'
        ));

        $manager->method('getSugarJobQueue')
            ->willReturn($queue);

        $manager->method('getReminderTime')
            ->with($bean, $user)
            ->willReturn($reminderTime);

        $manager->expects($this->exactly($jobAddingMethodsCalled ? 1 : 0))
            ->method('createSchedulersJob')
            ->with($bean, $user, $reminderTime)
            ->willReturn($job);

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
     * @param \PHPUnit_Framework_MockObject_MockObject $bean
     * @param array $loadUsersMap
     * @param array $getReminderTimeMap
     * @param array $jobsMap
     * @param int $jobQueueCallsCount
     * @param array $jobQueueMap
     * @dataProvider providerAddReminders
     * @covers ::addReminders
     */
    public function testAddReminders($bean, $loadUsersMap, $getReminderTimeMap, $jobsMap, $jobQueueCallsCount, $jobQueueMap)
    {
        $queue = $this->getSugarMock('SugarJobQueue', array('submitJob'));
        $queue->expects($this->exactly($jobQueueCallsCount))
            ->method('submitJob')
            ->will($this->returnValueMap($jobQueueMap));

        $manager = $this->getSchedulerMock(array(
            'getSugarJobQueue',
            'loadUsers',
            'getReminderTime',
            'createSchedulersJob'
        ));

        $manager->method('getSugarJobQueue')->willReturn($queue);
        $manager->method('loadUsers')->will($this->returnValueMap($loadUsersMap));
        $manager->method('getReminderTime')->will($this->returnValueMap($getReminderTimeMap));
        $manager->method('createSchedulersJob')->will($this->returnValueMap($jobsMap));

        TestReflection::callProtectedMethod($manager, 'addReminders', array($bean));
    }

    /**
     * (call or meeting bean,
     *  users map to call ::getBean mock,
     *  jobs map to call ::createSchedulersJob mock
     *  SugarJobQueue::submitJob calls count
     *  jobs map to call SugarJobQueue::submitJob
     * )
     * @return array
     */
    public function providerAddReminders()
    {
        $data = array();

        //bean without users
        $bean = $this->getBeanMock('SugarBean', array('users_arr' => array('dummy-users-array-0')));
        $loadUsersMap = array(
            array(array('dummy-users-array-0'), array())
        );
        $getReminderTimeMap = array();
        $jobsMap = array();
        $queueMap = array();
        $data['bean without users'] =
            array($bean, $loadUsersMap, $getReminderTimeMap, $jobsMap, 0, $queueMap);

        //bean with 1 user with one reminder time set
        $bean = $this->getBeanMock('SugarBean', array('users_arr' => array('dummy-users-array-1')));
        $user1 = $this->getBeanMock('User');
        $loadUsersMap = array(
            array(array('dummy-users-array-1'), array($user1))
        );
        $getReminderTimeMap = array(
            array($bean, $user1, 600)
        );
        $job1 = $this->getSugarMock('SchedulersJob');
        $jobsMap = array(
            array($bean, $user1, 600, $job1)
        );
        $queueMap = array(
            array($job1)
        );
        $data['bean with 1 user with one reminder time set'] =
            array($bean, $loadUsersMap, $getReminderTimeMap, $jobsMap, 1, $queueMap);

        //bean with 3 users with 3 reminder time set
        $bean = $this->getBeanMock('SugarBean', array('users_arr' => array('dummy-users-array-2')));
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
        $job1 = $this->getSugarMock('SchedulersJob');
        $job2 = $this->getSugarMock('SchedulersJob');
        $job3 = $this->getSugarMock('SchedulersJob');
        $jobsMap = array(
            array($bean, $user1, 60, $job1),
            array($bean, $user2, 300, $job2),
            array($bean, $user3, 600, $job3)
        );
        $queueMap = array(
            array($job1),
            array($job2),
            array($job3)
        );
        $data['bean with 3 users with 3 reminder time set'] =
            array($bean, $loadUsersMap, $getReminderTimeMap, $jobsMap, 3, $queueMap);

        //bean with 3 users with 1 reminder time set
        $bean = $this->getBeanMock('SugarBean', array('users_arr' => array('dummy-users-array-3')));
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
        $job1 = $this->getSugarMock('SchedulersJob');
        $jobsMap = array(
            array($bean, $user3, 600, $job1)
        );
        $queueMap = array(
            array($job1)
        );
        $data['bean with 3 users with 1 reminder time set'] =
            array($bean, $loadUsersMap, $getReminderTimeMap, $jobsMap, 1, $queueMap);

        return $data;
    }

    /**
     * @covers ::createSchedulersJob
     */
    public function testCreateSchedulersJob()
    {
        $beanTag = 'dummy-bean-tag';
        $userTag = 'dummy-user-tag';
        $tag = 'dummy-bean-tag:dummy-user-tag';

        $args = array('dummy-args');
        $reminderTime = 600;
        $executeTime = static::TEST_DATE_T;
        $job = $this->getSugarMock('SchedulersJob');
        $bean = $this->getBeanMock('Call', array(
            'name' => 'Bean name',
            'date_start' => static::TEST_DATE
        ));
        $user = $this->getBeanMock('User');
        $timeDate = $this->getSugarMock('TimeDate', array('asDb'));
        $timeDate->method('asDb')->willReturn($executeTime);

        $manager = $this->getSchedulerMock(array(
            'getSchedulersJob',
            'makeTag',
            'prepareTriggerArgs',
            'prepareReminderDateTime',
            'getTimeDate'
        ));

        $manager->method('getSchedulersJob')->willReturn($job);

        $manager->method('makeTag')
            ->will($this->returnValueMap(array(
                array($bean, $beanTag),
                array($user, $userTag)
            )));

        $manager->method('prepareTriggerArgs')->willReturn($args);
        $manager->method('getTimeDate')->willReturn($timeDate);

        $manager->expects($this->once())
            ->method('prepareReminderDateTime')
            ->with(static::TEST_DATE, $reminderTime)
            ->willReturn(new \DateTime(static::TEST_DATE));

        $result = TestReflection::callProtectedMethod($manager, 'createSchedulersJob',
            array($bean, $user, $reminderTime));
        $this->assertInstanceOf(get_class($job), $result);
        $this->assertEquals('Reminder Job Bean name', $result->name);
        $this->assertEquals($tag, $result->job_group);
        $this->assertEquals(json_encode($args), $result->data);
        $this->assertEquals(Scheduler::CALLBACK_CLASS, $result->target);
        $this->assertEquals($executeTime, $result->execute_time);
        $this->assertEquals(true, $result->requeue);
    }

    /**
     * @covers ::deleteByJobGroup
     */
    public function testDeleteByJobGroup()
    {
        $group = 'dummy-group';

        $id1 = 'dummy-job-1';
        $id2 = 'dummy-job-2';

        $job1 = $this->getBeanMock('SchedulersJob', array('id' => $id1), array('mark_deleted'));
        $job2 = $this->getBeanMock('SchedulersJob', array('id' => $id2), array('mark_deleted'));

        $resultObjects = array($job1, $job2);

        $query = $this->getSugarMock('SugarQuery');

        $bean = $this->getBeanMock('SchedulersJob', array(), array('fetchFromQuery'));
        $bean->expects($this->once())
            ->method('fetchFromQuery')
            ->with($query)
            ->willReturn($resultObjects);

        $manager = $this->getSchedulerMock(array(
            'getBean',
            'makeLoadRemindersByJobGroupSugarQuery'
        ));

        $manager->method('getBean')->willReturn($bean);

        $manager->method('makeLoadRemindersByJobGroupSugarQuery')
            ->with($bean, $group)
            ->willReturn($query);

        $job1->expects($this->once())
            ->method('mark_deleted')
            ->with($id1);

        $job2->expects($this->once())
            ->method('mark_deleted')
            ->with($id2);

        TestReflection::callProtectedMethod($manager, 'deleteByJobGroup', array($group));
    }

    /**
     * @covers ::makeLoadRemindersByJobGroupSugarQuery
     */
    public function testMakeLoadRemindersByJobGroupSugarQuery()
    {
        $bean = $this->getSugarMock('SchedulersJob');
        $group = 'dummy-user-id';

        $sugarQuery = $this->getSugarMock('SugarQuery', array(
            'from',
            'where',
            'contains'
        ));

        $sugarQuery->expects($this->once())
            ->method('from')
            ->with($bean);

        $sugarQuery->expects($this->once())
            ->method('where')
            ->willReturnSelf();

        $sugarQuery->expects($this->once())
            ->method('contains')
            ->with('job_group', $group);

        $manager = $this->getSchedulerMock(array('getSugarQuery'));

        $manager->method('getSugarQuery')->willReturn($sugarQuery);

        $this->assertEquals($sugarQuery, TestReflection::callProtectedMethod(
            $manager,
            'makeLoadRemindersByJobGroupSugarQuery',
            array($bean, $group)
        ));
    }

    /**
     * @covers ::makeTag
     */
    public function testMakeTag()
    {
        $tag = 'dummy-tag';
        $hash = 'dummy-hash';
        $bean = $this->getSugarMock('Call');

        $manager = $this->getSchedulerMock(array('hashTag', 'parentMakeTag'));
        $manager->expects($this->once())
            ->method('parentMakeTag')
            ->with($bean)
            ->willReturn($tag);

        $manager->expects($this->once())
            ->method('hashTag')
            ->with($tag)
            ->willReturn($hash);

        $this->assertEquals($hash,
            TestReflection::callProtectedMethod($manager, 'makeTag', array($bean)));
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
     * @param string $className
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getSugarMock($className, $methods = null)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getSchedulerMock($methods = array())
    {
        return $this->getMockBuilder('Sugarcrm\\Sugarcrm\\Trigger\\ReminderManager\\Scheduler')
            ->setMethods($methods)
            ->getMock();
    }
}
