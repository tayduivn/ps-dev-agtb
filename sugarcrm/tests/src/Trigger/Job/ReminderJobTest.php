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

namespace Sugarcrm\SugarcrmTests\Trigger\Job;

use Sugarcrm\Sugarcrm\Trigger\Job\ReminderJob;
use Sugarcrm\Sugarcrm\Trigger\Reminder;

/**
 * Class ReminderJobTest
 *
 * @package Sugarcrm\SugarcrmTests\Trigger\Job
 * @covers Sugarcrm\Sugarcrm\Trigger\Job\ReminderJob
 */
class ReminderJobTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var ReminderJob|\PHPUnit_Framework_MockObject_MockObject */
    protected $job = null;

    /** @var Reminder|\PHPUnit_Framework_MockObject_MockObject */
    protected $reminder = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->reminder = $this->getMock('Sugarcrm\Sugarcrm\Trigger\Reminder');
        $this->job = $this->getMock('Sugarcrm\Sugarcrm\Trigger\Job\ReminderJob', array('getReminder'));
        $this->job->method('getReminder')->willReturn($this->reminder);
    }

    /**
     * Data provider for testRunRemindWithProperArguments.
     *
     * @see Sugarcrm\SugarcrmTests\Trigger\Job\ReminderJobTest::testRunRemindWithProperArguments
     * @return array
     */
    public static function runRemindWithProperArgumentsProvider()
    {
        $meetingId = create_guid();
        $userId = create_guid();
        $callId = create_guid();
        $beanId = create_guid();
        return array(
            'passCallAndUserBeanToReminder' => array(
                'data' => json_encode(array(
                    'module' => 'Calls',
                    'beanId' => $callId,
                    'userId' => $userId,
                )),
                'expectedModuleName' => 'Calls',
                'expectedBeanId' => $callId,
                'expectedUserId' => $userId,
            ),
            'passMeetingAndUserBeanToReminder' => array(
                'data' => json_encode(array(
                    'module' => 'Meetings',
                    'beanId' => $meetingId,
                    'userId' => $userId,
                )),
                'expectedModuleName' => 'Meetings',
                'expectedBeanId' => $meetingId,
                'expectedUserId' => $userId,
            ),
            'passSugarBeanAndUserBeanToReminder' => array(
                'data' => json_encode(array(
                    'module' => 'SugarBean',
                    'beanId' => $beanId,
                    'userId' => $userId,
                )),
                'expectedModuleName' => 'SugarBean',
                'expectedBeanId' => $beanId,
                'expectedUserId' => $userId,
            ),
        );
    }

    /**
     * Should pass bean with loaded user's bean to remind method.
     *
     * @dataProvider runRemindWithProperArgumentsProvider
     * @covers Sugarcrm\Sugarcrm\Trigger\Job\ReminderJob::run
     * @param string $data
     * @param string $expectedModuleName
     * @param string $expectedBeanId
     * @param string $expectedUserId
     */
    public function testRunRemindWithProperArguments($data, $expectedModuleName, $expectedBeanId, $expectedUserId)
    {
        $this->reminder->expects($this->once())
            ->method('remind')
            ->with($expectedModuleName, $expectedBeanId, $expectedUserId);

        $this->assertTrue($this->job->run($data));
    }
}
