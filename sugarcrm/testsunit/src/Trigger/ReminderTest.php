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

namespace Sugarcrm\SugarcrmTestsUnit\Trigger;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class ReminderTest
 * @package Sugarcrm\SugarcrmTestsUnit\Trigger
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Trigger\Reminder
 */
class ReminderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers ::remind
     */
    public function testRemind()
    {
        $module = 'dummy-module';
        $beanId = 'dummy-bean-id';
        $userId = 'dummy-user-id';

        $bean = $this->getBeanMock('Call');
        $user = $this->getBeanMock('User');

        $reminder = $this->getReminderMock(array('validate', 'getBean'));

        $reminder->expects($this->at(0))
            ->method('getBean')
            ->with($module, $beanId)
            ->willReturn($bean);

        $reminder->expects($this->at(1))
            ->method('getBean')
            ->with('Users', $userId)
            ->willReturn($user);

        $reminder->expects($this->once())
            ->method('validate')
            ->with($bean, $user)
            ->willReturn(false);

        $reminder->remind($module, $beanId, $userId);
    }

    /**
     * @covers ::validate
     */
    public function testValidate()
    {
        $bean = $this->getBeanMock('Call');
        $user = $this->getBeanMock('User');

        $reminder = $this->getReminderMock();
        $this->assertTrue(TestReflection::callProtectedMethod($reminder, 'validate', array($bean, $user)));
    }

    /**
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getReminderMock($methods = array())
    {
        return $this->getMockBuilder('Sugarcrm\\Sugarcrm\\Trigger\\Reminder')
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
}
