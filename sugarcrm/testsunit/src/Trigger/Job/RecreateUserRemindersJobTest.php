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

namespace Sugarcrm\SugarcrmTestsUnit\Trigger\Job;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class RecreateUserRemindersJob
 * @package Sugarcrm\SugarcrmTestsUnit\Trigger\Job
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Trigger\JobRecreateUserRemindersJob
 */
class RecreateUserRemindersJobTest extends \PHPUnit_Framework_TestCase
{
    const TEST_DATE = '2015-11-08 10:30:00';

    /**
     * @covers ::run
     */
    public function testRun()
    {
        $userId = 'dummy-user-id';
        $calls = array('calls');
        $meetings = array('meetings');
        $user = $this->getBeanMock('User');

        $loadBeansMap = array(
            array('Calls', $userId, $calls),
            array('Meetings', $userId, $meetings)
        );

        $job = $this->getRecreateUserRemindersJobMock(array(
            'loadBeans',
            'getBean',
            'recreateReminders',
        ), array($userId));

        $job->expects($this->exactly(2))
            ->method('loadBeans')
            ->will($this->returnValueMap($loadBeansMap));

        $job->method('getBean')
            ->with('Users', $userId)
            ->willReturn($user);

        $job->expects($this->once())
            ->method('recreateReminders')
            ->with(array('calls', 'meetings'), $user);

        $this->assertEquals(\SchedulersJob::JOB_SUCCESS, $job->run());
    }

    /**
     * @param array $beans
     * @param \PHPUnit_Framework_MockObject_MockObject $user
     * @param array $addReminderForUserMap
     * @param int $addReminderForUserCallsCount
     * @dataProvider providerRecreateReminders
     * @covers ::recreateReminders
     */
    public function testRecreateReminders($beans, $user, $addReminderForUserMap, $addReminderForUserCallsCount)
    {
        $reminderManager = $this->getBaseMock(array(
            'addReminderForUser',
            'deleteReminders'
        ));

        $reminderManager->expects($this->once())
            ->method('deleteReminders')
            ->with($user);

        $reminderManager->expects($this->exactly($addReminderForUserCallsCount))
            ->method('addReminderForUser')
            ->will($this->returnValueMap($addReminderForUserMap));

        $job = $this->getRecreateUserRemindersJobMock(array('getReminderManager'));
        $job->method('getReminderManager')->willReturn($reminderManager);

        TestReflection::callProtectedMethod($job, 'recreateReminders', array($beans, $user));
    }

    /**
     * (beans array,
     *   user mock,
     *   Base::addReminderForUser() call map,
     *   Base::addReminderForUser() calls count)
     * @return array
     */
    public function providerRecreateReminders()
    {
        $user = $this->getBeanMock('User');
        $bean1 = $this->getBeanMock('SugarBean');
        $bean2 = $this->getBeanMock('SugarBean');
        $bean3 = $this->getBeanMock('SugarBean');
        return array(
            'with empty beans' => array(
                array(), $user, array(), 0
            ),
            'with 1 bean' => array(
                array($bean1),
                $user,
                array(
                    array($bean1, $user)
                ),
                1
            ),
            'with 3 bean' => array(
                array($bean1, $bean2, $bean3),
                $user,
                array(
                    array($bean1, $user),
                    array($bean2, $user),
                    array($bean3, $user)
                ),
                3
            )
        );
    }

    /**
     * @param boolean $isTSConfigured
     * @param boolean $geTSReminderManagerCalled
     * @param boolean $getSchedulerReminderManagerCalled
     * @dataProvider providerGetReminderManager
     * @covers ::getReminderManager
     */
    public function testGetReminderManager($isTSConfigured, $geTSReminderManagerCalled, $getSchedulerReminderManagerCalled)
    {
        $triggerServerClient = $this->getTriggerClientMock(array('isConfigured'));
        $triggerServerClient->method('isConfigured')->willReturn($isTSConfigured);

        $job = $this->getRecreateUserRemindersJobMock(array(
            'getTriggerClient',
            'getTriggerServerManager',
            'getSchedulerManager'
        ));

        $job->method('getTriggerClient')->willReturn($triggerServerClient);
        $job->expects($this->exactly($geTSReminderManagerCalled ? 1 : 0))
            ->method('getTriggerServerManager');
        $job->expects($this->exactly($getSchedulerReminderManagerCalled ? 1 : 0))
            ->method('getSchedulerManager');

        TestReflection::callProtectedMethod($job, 'getReminderManager');
    }

    /**
     * (is trigger server configured,
     * is ::getTriggerServerReminderManager called
     * is ::getSchedulerReminderManager called)
     * @return array
     */
    public function providerGetReminderManager()
    {
        return array(
            'trigger server is not configured' => array(false, false, true),
            'trigger server is configured' => array(true, true, false)
        );
    }

    /**
     * @covers ::loadBeans
     */
    public function testLoadBeans()
    {
        $module = 'dummy-module';
        $userId = 'dummy-user-id';

        $resultObjects = array(
            $this->getBeanMock('Call'),
            $this->getBeanMock('Call')
        );

        $query = $this->getSugarMock('SugarQuery');

        $bean = $this->getBeanMock('Call', array(), array('fetchFromQuery'));
        $bean->expects($this->once())
            ->method('fetchFromQuery')
            ->with($query)
            ->willReturn($resultObjects);


        $job = $this->getRecreateUserRemindersJobMock(array(
            'makeLoadBeansSugarQuery',
            'getBean'
        ));

        $job->method('makeLoadBeansSugarQuery')
            ->with($bean, $userId)
            ->willReturn($query);

        $job->method('getBean')
            ->with($module)
            ->willReturn($bean);

        $result = TestReflection::callProtectedMethod($job, 'loadBeans', array($module, $userId));

        $this->assertEquals($resultObjects, $result);
    }

    /**
     * @covers ::makeLoadBeansSugarQuery
     */
    public function testMakeBeansSugarQuery()
    {
        $bean = $this->getSugarMock('Call');
        $userId = 'dummy-user-id';

        $timeDate = $this->getSugarMock('TimeDate', array('asDb'));
        $timeDate->expects($this->once())
            ->method('asDb')
            ->with(new \DateTime())
            ->willReturn(static::TEST_DATE);

        $sugarJoin = $this->getSugarMock('SugarQuery_Builder_Join', array('joinName'));
        $sugarJoin->method('joinName')->willReturn('jt0_users');

        $sugarQuery = $this->getSugarMock('SugarQuery', array(
            'from',
            'join',
            'where',
            'equals',
            'queryAnd',
            'notEquals',
            'gt'
        ));

        $sugarQuery->expects($this->once())
            ->method('from')
            ->with($bean);

        $sugarQuery->expects($this->once())
            ->method('join')
            ->with('users')
            ->willReturn($sugarJoin);

        $sugarQuery->expects($this->once())
            ->method('where')
            ->willReturnSelf();

        $sugarQuery->expects($this->once())
            ->method('equals')
            ->with('jt0_users.id', $userId)
            ->willReturnSelf();

        $sugarQuery->expects($this->once())
            ->method('notEquals')
            ->with('assigned_user_id', $userId)
            ->willReturnSelf();

        $sugarQuery->expects($this->once())
            ->method('gt')
            ->with('date_start', static::TEST_DATE)
            ->willReturnSelf();

        $sugarQuery->expects($this->once())
            ->method('queryAnd')
            ->willReturnSelf();

        $job = $this->getRecreateUserRemindersJobMock(array(
            'getSugarQuery',
            'getTimeDate'
        ));

        $job->method('getSugarQuery')->willReturn($sugarQuery);
        $job->method('getTimeDate')->willReturn($timeDate);

        $this->assertEquals($sugarQuery,
            TestReflection::callProtectedMethod($job, 'makeLoadBeansSugarQuery', array($bean, $userId)));
    }

    /**
     * @covers ::getTriggerClient
     */
    public function testGetTriggerClient()
    {
        $job = $this->getRecreateUserRemindersJobMock();
        $this->assertInstanceOf('\Sugarcrm\Sugarcrm\Trigger\Client',
            TestReflection::callProtectedMethod($job, 'getTriggerClient'));
    }

    /**
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getRecreateUserRemindersJobMock($methods = array(), $constructorArgs = array())
    {
        if (empty($constructorArgs)) {
            $constructorArgs = array(null);
        }
        return $this->getMockBuilder('\Sugarcrm\Sugarcrm\Trigger\Job\RecreateUserRemindersJob')
            ->setConstructorArgs($constructorArgs)
            ->setMethods($methods)
            ->getMock();
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
    public function getTriggerClientMock($methods = array())
    {
        return $this->getMockBuilder('Sugarcrm\\Sugarcrm\\Trigger\\Client')
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getTriggerServerManagerMock($methods = array())
    {
        return $this->getMockBuilder('Sugarcrm\\Sugarcrm\\Trigger\\ReminderManager\\TriggerServer')
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getSchedulerManagerMock($methods = array())
    {
        return $this->getMockBuilder('Sugarcrm\\Sugarcrm\\Trigger\\ReminderManager\\Scheduler')
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getBaseMock($methods = array())
    {
        return $this->getMockBuilder('Sugarcrm\\Sugarcrm\\Trigger\\ReminderManager\\Base')
            ->setMethods($methods)
            ->getMockForAbstractClass();
    }
}
