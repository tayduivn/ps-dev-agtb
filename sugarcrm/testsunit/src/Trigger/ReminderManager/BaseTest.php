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

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class BaseTest
 * @package Sugarcrm\SugarcrmTestsUnit\Trigger\ReminderManager
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Trigger\ReminderManager\Base
 */
class BaseTest extends \PHPUnit_Framework_TestCase
{
    const TEST_DATE = '2015-11-08 10:30:00';
    const TEST_DATE_T = '2015-11-08T10:30:00';

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $bean
     * @param string $expected
     * @dataProvider providerMakeTag
     * @covers ::makeTag
     */
    public function testMakeTag($bean, $expected)
    {
        $manager = $this->getBaseMock();
        $this->assertEquals(
            $expected,
            TestReflection::callProtectedMethod($manager, 'makeTag', array($bean))
        );
    }

    /**
     * (bean, expected tag)
     * @return array
     */
    public function providerMakeTag()
    {
        return array(
            'bean is Call' => array($this->getBeanMock('Call', array('id' => 'dummy-id')), 'call-dummy-id'),
            'bean is Meeting' => array($this->getBeanMock('Meeting', array('id' => 'dummy-id')), 'meeting-dummy-id'),
            'bean is User' => array($this->getBeanMock('User', array('id' => 'dummy-id')), 'user-dummy-id'),
            'bean is Account' => array($this->getBeanMock('Account', array('id' => 'dummy-id')), 'account-dummy-id')
        );
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $bean
     * @param boolean $getPreferencesCalled
     * @param mixed $userId
     * @param int $getPreferenceReturn
     * @param int $expected
     * @dataProvider providerGetReminderTime
     * @covers ::getReminderTime
     */
    public function testGetReminderTime($bean, $userId, $getPreferencesCalled, $getPreferenceReturn, $expected)
    {
        $user = $this->getBeanMock('User', array('id' => $userId), array('getPreference'));
        $user->expects($this->exactly($getPreferencesCalled ? 1 : 0))
            ->method('getPreference')
            ->with('reminder_time')
            ->willReturn($getPreferenceReturn);

        $manager = $this->getBaseMock();

        $this->assertEquals(
            $expected,
            TestReflection::callProtectedMethod($manager, 'getReminderTime', array($bean, $user))
        );
    }

    /**
     * (call or meeting bean,
     *  users bean,
     *  is User::getPreference() called,
     *  User::getPreference() return value,
     *  expected reminder time)
     * @return array
     */
    public function providerGetReminderTime()
    {
        return array(
            'user is author' => array(
                $this->getBeanMock('Call', array('assigned_user_id' => 1, 'reminder_time' => 600)),
                1, false, 300, 600
            ),
            'user is not author' => array(
                $this->getBeanMock('Call', array('assigned_user_id' => 1, 'reminder_time' => 600)),
                2, true, 300, 300
            )
        );
    }

    /**
     * @param int $reminderTime
     * @param string $result
     * @dataProvider providerPrepareReminderDateTime
     * @covers ::prepareReminderDateTime
     */
    public function testPrepareReminderDateTime($reminderTime, $result)
    {
        $manager = $this->getBaseMock();

        $this->assertEquals($result, TestReflection::callProtectedMethod(
            $manager,
            'prepareReminderDateTime',
            array(static::TEST_DATE, $reminderTime)
        ));
    }

    /**
     * (users timezone, users reminder time in seconds, returned datetime string)
     * @return array
     */
    public function providerPrepareReminderDateTime()
    {
        return array(
            array(1800, $this->getPrepareReminderDateTimeResult(1800)),
            array(60, $this->getPrepareReminderDateTimeResult(60))
        );
    }

    /**
     * @param int $reminder
     * @return \DateTimeZone
     */
    private function getPrepareReminderDateTimeResult($reminder)
    {
        $now = new \DateTime(static::TEST_DATE, new \DateTimeZone('UTC'));
        $now->modify('- ' . $reminder . ' seconds');
        return $now;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $bean
     * @param \PHPUnit_Framework_MockObject_MockObject $user
     * @param array $expected
     * @dataProvider providerPrepareTriggerArgs
     * @covers ::prepareTriggerArgs
     */
    public function testPrepareTriggerArgs($bean, $user, $expected)
    {
        $manager = $this->getBaseMock();
        $this->assertEquals($expected,
            TestReflection::callProtectedMethod($manager, 'prepareTriggerArgs', array($bean, $user)));
    }

    /**
     * (call or meeting bean, user bean, expected args)
     * @return array
     */
    public function providerPrepareTriggerArgs()
    {
        return array(
            'bean is Call' => array(
                $this->getBeanMock('Call', array('id' => 1, 'module_name' => 'Calls')),
                $this->getBeanMock('User', array('id' => 1, 'module_name' => 'Users')),
                array(
                    'module' => 'Calls',
                    'beanId' => 1,
                    'userId' => 1
                )
            ),
            'bean is Meeting' => array(
                $this->getBeanMock('Meeting', array('id' => 2, 'module_name' => 'Meetings')),
                $this->getBeanMock('User', array('id' => 3, 'module_name' => 'Users')),
                array(
                    'module' => 'Meetings',
                    'beanId' => 2,
                    'userId' => 3
                )
            )
        );
    }

    /**
     * @covers ::loadUsers
     */
    public function testLoadUsers()
    {
        $usersIds = array(1, 2, 3);

        $resultObjects = array(
            $this->getBeanMock('User'),
            $this->getBeanMock('User'),
            $this->getBeanMock('User')
        );

        $query = $this->getSugarMock('SugarQuery');

        $bean = $this->getBeanMock('User', array(), array('fetchFromQuery'));
        $bean->expects($this->once())
            ->method('fetchFromQuery')
            ->with($query)
            ->willReturn($resultObjects);

        $manager = $this->getBaseMock(array(
            'getBean',
            'makeLoadUsersSugarQuery'
        ));

        $manager->method('getBean')
            ->with('Users')
            ->willReturn($bean);

        $manager->method('makeLoadUsersSugarQuery')
            ->with($bean, $usersIds)
            ->willReturn($query);

        $result = TestReflection::callProtectedMethod($manager, 'loadUsers', array($usersIds));
        $this->assertEquals($resultObjects, $result);
    }

    /**
     * @covers ::makeLoadUsersSugarQuery
     */
    public function testMakeLoadUsersSugarQuery()
    {
        $bean = $this->getSugarMock('User');
        $usersIds = array(1, 2, 3);

        $sugarQuery = $this->getSugarMock('SugarQuery', array(
            'from',
            'where',
            'in'
        ));

        $sugarQuery->expects($this->once())
            ->method('from')
            ->with($bean);

        $sugarQuery->expects($this->once())
            ->method('where')
            ->willReturnSelf();

        $sugarQuery->expects($this->once())
            ->method('in')
            ->with('id', $usersIds);

        $manager = $this->getBaseMock(array(
            'getSugarQuery'
        ));

        $manager->method('getSugarQuery')->willReturn($sugarQuery);

        $this->assertEquals($sugarQuery, TestReflection::callProtectedMethod(
            $manager,
            'makeLoadUsersSugarQuery',
            array($bean, $usersIds)
        ));
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
}
