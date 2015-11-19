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

/**
 * Class ReminderJobTest
 * @package Sugarcrm\SugarcrmTestsUnit\Trigger
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Trigger\Job\ReminderJob
 */
class ReminderJobTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::run
     */
    public function testRun()
    {
        $module = 'dummy-module';
        $beanId = 'dummy-bean-id';
        $userId = 'dummy-user-id';

        $data = json_encode(array(
            'module' => $module,
            'beanId' => $beanId,
            'userId' => $userId
        ));

        $reminder = $this->getSugarMock('Sugarcrm\\Sugarcrm\\Trigger\\Reminder', array(
            'remind'
        ));

        $reminder->expects($this->once())
            ->method('remind')
            ->with($module, $beanId, $userId);

        $job = $this->getSugarMock('Sugarcrm\\Sugarcrm\\Trigger\\Job\\ReminderJob', array(
            'getReminder'
        ));

        $job->method('getReminder')->willReturn($reminder);

        $this->assertTrue($job->run($data));
    }

    /**
     * @param string $class
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getSugarMock($class, $methods = array())
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
