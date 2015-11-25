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

namespace Sugarcrm\SugarcrmTests\Trigger\Job;

use Sugarcrm\Sugarcrm\Trigger\Job\ReminderJob;
use Sugarcrm\Sugarcrm\Trigger\Reminder;

/**
 * Class ReminderJobTest
 * @package Sugarcrm\SugarcrmTests\Trigger\Job
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Trigger\Job\ReminderJob
 */
class ReminderJobTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ReminderJob
     */
    protected $job;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Reminder
     */
    protected $reminder;

    public function setUp()
    {
        parent::setUp();
        $this->job = $this->getMock('Sugarcrm\\Sugarcrm\\Trigger\\Job\\ReminderJob', array('getReminder'));
        $this->reminder = $this->getMock('Sugarcrm\\Sugarcrm\\Trigger\\Reminder', array('remind'));
    }

    /**
     * @covers ::run
     */
    public function testRunReturnsTrue()
    {
        $data = json_encode(array(
            'module' => 'Calls',
            'beanId' => 'dummy-bean-id',
            'userId' => 'dummy-user-id'
        ));

        $this->job->method('getReminder')->willReturn($this->reminder);

        $this->assertTrue($this->job->run($data));
    }

    /**
     * @param string $data
     * @param string $module
     * @param string $beanId
     * @param string $userId
     * @dataProvider providerRunCallsRemindWithProperArguments
     * @covers ::run
     */
    public function testRunCallsRemindWithProperArguments($data, $module, $beanId, $userId)
    {
        $this->reminder->expects($this->once())
            ->method('remind')
            ->with($module, $beanId, $userId);

        $this->job->method('getReminder')->willReturn($this->reminder);
        $this->job->run($data);
    }

    /**
     * @return array
     */
    public function providerRunCallsRemindWithProperArguments()
    {
        return array(
            'module is Calls' => array(
                json_encode(array(
                    'module' => 'Calls',
                    'beanId' => 'dummy-call-id',
                    'userId' => 'dummy-user-id'
                )),
                'Calls',
                'dummy-call-id',
                'dummy-user-id'
            ),
            'module is Meetings' => array(
                json_encode(array(
                    'module' => 'Meetings',
                    'beanId' => 'dummy-meeting-id',
                    'userId' => 'dummy-user-id'
                )),
                'Meetings',
                'dummy-meeting-id',
                'dummy-user-id'
            ),
        );
    }
}
