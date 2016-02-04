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

namespace Sugarcrm\SugarcrmTests\clients\base\api;

use ReminderApi;

/**
 * Class ReminderApiTest
 * @package Sugarcrm\SugarcrmTests\clients\base\api
 * @coversDefaultClass ReminderApi
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
     * remind method should throw on invalid arguments
     *
     * @param array $args
     * @dataProvider providerRemindThrowsWithoutRequiredArgs
     * @covers ReminderApi::remind
     * @expectedException \SugarApiExceptionMissingParameter
     */
    public function testRemindThrowsWithoutRequiredArgs($args)
    {
        $this->reminderApi->remind($this->serviceMock, $args);
    }

    /**
     * @return array
     */
    public static function providerRemindThrowsWithoutRequiredArgs()
    {
        return array(
            'throws if "module" isn\'t presented' => array(
                array(),
            ),
            'throws if "beanId" isn\'t presented' => array(
                array(
                    'module' => 'dummy-module',
                ),
            ),
            'throws if "userId" isn\'t presented' => array(
                array(
                    'module' => 'dummy-module',
                    'beanId' => 'dummy-bean-id',
                ),
            ),
        );
    }

    /**
     * remind method should throw on invalid module value
     *
     * @dataProvider providerRemindThrowsWithoutRequiredArgs
     * @covers ReminderApi::remind
     * @expectedException \SugarApiExceptionInvalidParameter
     */
    public function testRemindThrowsOnIncorrectModule()
    {
        $args = array(
            'module' => 'dummy-module',
            'beanId' => 'dummy-bean-id',
            'userId' => '1',
        );
        $this->reminderApi->remind($this->serviceMock, $args);
    }

    /**
     * remind method should pass correct data to Reminder
     *
     * @param array $args
     * @dataProvider providerRemindCallsRemindWithCorrectArgs
     * @covers ReminderApi::remind
     */
    public function testRemindCallsRemindWithCorrectArgs($args)
    {
        $reminder = $this->getMock('Sugarcrm\\Sugarcrm\\Trigger\\Reminder', array('remind'));
        $this->reminderApi->method('getReminder')->willReturn($reminder);

        $reminder->expects($this->once())
            ->method('remind')
            ->with(
                $this->equalTo($args['module']),
                $this->equalTo($args['beanId']),
                $this->equalTo($args['userId'])
            );

        $this->reminderApi->remind($this->serviceMock, $args);
    }

    /**
     * @return array
     */
    public static function providerRemindCallsRemindWithCorrectArgs()
    {
        return array(
            'calls Reminder::remind() with Calls in args' => array(
                array(
                    'module' => 'Calls',
                    'beanId' => 'dummy-bean-id',
                    'userId' => 'dummy-user-id',
                ),
            ),
            'calls Reminder::remind() with Meetings in args' => array(
                array(
                    'module' => 'Meetings',
                    'beanId' => 'dummy-bean-id',
                    'userId' => 'dummy-user-id',
                ),
            ),
        );
    }
}
