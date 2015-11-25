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

namespace Sugarcrm\SugarcrmTests\clients\base\api;

/**
 * Class ReminderApiTest
 * @package Sugarcrm\SugarcrmTests\clients\base\api
 * @coversDefaultClass \ReminderApi
 */
class ReminderApiTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\ReminderApi
     */
    protected $reminderApi;

    /**
     * @var \RestService
     */
    protected $serviceMock;

    public function setUp()
    {
        parent::setUp();
        $this->reminderApi = $this->getMock('ReminderApi', array('getReminder'));
        $this->serviceMock = \SugarTestRestUtilities::getRestServiceMock();
    }

    /**
     * @param array $args
     * @dataProvider providerRemindThrowsWithoutRequiredArgs
     * @covers ::remind
     * @expectedException \SugarApiExceptionMissingParameter
     */
    public function testRemindThrowsWithoutRequiredArgs($args)
    {
        $this->reminderApi->remind($this->serviceMock, $args);
    }

    /**
     * @return array
     */
    public function providerRemindThrowsWithoutRequiredArgs()
    {
        return array(
            'throws if "module" isn\'t presented' => array(
                array()
            ),
            'throws if "beanId" isn\'t presented' => array(
                array('module' => 'dummy-module')
            ),
            'throws if "userId" isn\'t presented' => array(
                array('module' => 'dummy-module', 'beanId' => 'dummy-bean-id')
            )
        );
    }

    /**
     * @covers ::remind
     * @expectedException \SugarApiExceptionMissingParameter
     */
    public function testRemindThrowsIfModuleIsNotCallOrMeeting()
    {
        $args = array(
            'module' => 'dummy-module',
            'beanId' => 'dummy-bean-id',
            'userId' => 'dummy-user-id'
        );
        $this->reminderApi->remind($this->serviceMock, $args);

    }

    /**
     * @param array $args
     * @param array $expectedCallArgs
     * @dataProvider providerRemindCallsRemindWithCorrectArgs
     * @covers ::remind
     */
    public function testRemindCallsRemindWithCorrectArgs($args, $expectedCallArgs)
    {
        $reminder = $this->getMock('Sugarcrm\\Sugarcrm\\Trigger\\Reminder', array('remind'));

        $reminder->expects($this->once())
            ->method('remind')
            ->with($expectedCallArgs[0], $expectedCallArgs[1], $expectedCallArgs[2]);

        $this->reminderApi->method('getReminder')->willReturn($reminder);

        $this->reminderApi->remind($this->serviceMock, $args);
    }

    /**
     * @return array
     */
    public function providerRemindCallsRemindWithCorrectArgs()
    {
        return array(
            'calls Reminder::remind() with Calls in args' => array(
                array('module' => 'Calls', 'beanId' => 'dummy-bean-id', 'userId' => 'dummy-user-id'),
                array('Calls', 'dummy-bean-id', 'dummy-user-id')
            ),
            'calls Reminder::remind() with Meetings in args' => array(
                array('module' => 'Meetings', 'beanId' => 'dummy-bean-id', 'userId' => 'dummy-user-id'),
                array('Meetings', 'dummy-bean-id', 'dummy-user-id')
            ),
        );
    }
}
