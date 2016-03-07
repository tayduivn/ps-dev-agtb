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

namespace Sugarcrm\SugarcrmTests\Trigger\ReminderManager;

use Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler;

/**
 * Class SchedulerTest
 *
 * @package Sugarcrm\SugarcrmTests\Trigger\ReminderManager
 * @covers Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler
 */
class SchedulerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var Scheduler|\PHPUnit_Framework_MockObject_MockObject */
    protected $schedulerManager = null;

    /** @var \Call|\PHPUnit_Framework_MockObject_MockObject */
    protected $call = null;

    /** @var \User|\PHPUnit_Framework_MockObject_MockObject */
    protected $user = null;

    /** @var \SugarQuery|\PHPUnit_Framework_MockObject_MockObject */
    protected $sugarQuery = null;

    /** @var \SchedulersJob|\PHPUnit_Framework_MockObject_MockObject */
    protected $jobBean = null;

    /** @var \SugarJobQueue|\PHPUnit_Framework_MockObject_MockObject */
    protected $sugarJobQueue = null;

    /** @var \SugarQuery_Builder_Andwhere|\PHPUnit_Framework_MockObject_MockObject */
    protected $sugarQueryWhere = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->call = $this->getMock('Call');
        $this->call->id = create_guid();
        $this->call->name = 'Call' . rand(1000, 1999);

        $this->user = $this->getMock('User');
        $this->user->id = create_guid();
        $this->user->name = 'User' . rand(1000, 1999);

        $this->jobBean = $this->getMock('SchedulersJob');
        $this->sugarJobQueue = $this->getMock('SugarJobQueue');
        $this->sugarQueryWhere = $this->getMock('SugarQuery_Builder_Andwhere', array(), array(), '', false);
        $this->sugarQuery = $this->getMock('SugarQuery');

        $this->schedulerManager = $this->getMock(
            'Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler',
            array(
                'getSchedulersJob',
                'getSugarQuery',
                'getSugarJobQueue',
            )
        );
        $this->sugarQuery->method('where')->willReturn($this->sugarQueryWhere);
        $this->schedulerManager->method('getSchedulersJob')->willReturn($this->jobBean);
        $this->schedulerManager->method('getSugarQuery')->willReturn($this->sugarQuery);
        $this->schedulerManager->method('getSugarJobQueue')->willReturn($this->sugarJobQueue);
    }

    /**
     * Should fetch all jobs by group and deletes them.
     *
     * @covers Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler::deleteReminders
     */
    public function testDeleteReminders()
    {
        /** @var string $eventBeanTag */
        $eventBeanTag = md5(strtolower($this->call->object_name) . '-' . $this->call->id);
        $jobList = array();
        for ($i = 0; $i < 3; $i++) {
            /** @var \SchedulersJob|\PHPUnit_Framework_MockObject_MockObject $job */
            $job = $this->getMock('SchedulersJob');
            $job->id = create_guid();
            $job->expects($this->once())
                ->method('mark_deleted')
                ->with($this->equalTo($job->id));
            $jobList[] = $job;
        }

        $this->sugarQueryWhere->expects($this->once())
            ->method('contains')
            ->with(
                $this->equalTo('job_group'),
                $this->equalTo($eventBeanTag)
            );

        $this->sugarQuery->expects($this->once())
            ->method('from')
            ->with($this->equalTo($this->jobBean));

        $this->jobBean->expects($this->once())
            ->method('fetchFromQuery')
            ->with($this->equalTo($this->sugarQuery))
            ->willReturn($jobList);

        $this->schedulerManager->deleteReminders($this->call);
    }

    /**
     * Data provider for testAddReminderForUser.
     *
     * @see Sugarcrm\SugarcrmTests\Trigger\ReminderManager\SchedulerTest::testAddReminderForUser
     * @return array
     */
    public static function addReminderForUserProvider()
    {
        return array(
            'createReminderForUserAtRightTime' => array(
                'jobReminderTime' => '2015-02-02 12:45:32',
                'reminderTimeZone' => 'Europe/Berlin',
                'expectedTime' => '2015-02-02 11:45:32',
            ),
            'createReminderForUserUTC' => array(
                'jobReminderTime' => '2015-02-02 12:45:32',
                'reminderTimeZone' => 'UTC',
                'expectedTime' => '2015-02-02 12:45:32',
            ),
        );
    }

    /**
     * Should create reminder job with proper params and add it to the job queue.
     *
     * @dataProvider addReminderForUserProvider
     * @covers Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler::addReminderForUser
     * @param string $jobReminderTime
     * @param string $reminderTimeZone
     * @param string $expectedTime
     */
    public function testAddReminderForUser($jobReminderTime, $reminderTimeZone, $expectedTime)
    {
        $reminderTime = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            $jobReminderTime,
            new \DateTimeZone($reminderTimeZone)
        );
        $expectedReminder = clone $reminderTime;

        $expectedJobGroup = sprintf(
            "%s:%s",
            md5(strtolower($this->call->object_name) . '-' . $this->call->id),
            md5(strtolower($this->user->object_name) . '-' . $this->user->id)
        );

        $expectedTriggerArgs = json_encode(
            array(
                'module' => $this->call->module_name,
                'beanId' => $this->call->id,
                'userId' => $this->user->id,
            )
        );
        /** @var null|\SchedulersJob $caughtJob */
        $caughtJob = null;

        $this->sugarJobQueue->expects($this->once())
            ->method('submitJob')
            ->with($this->callback(function ($job) use (&$caughtJob) {
                $caughtJob = $job;
                return true;
            }));

        $this->schedulerManager->addReminderForUser($this->call, $this->user, $reminderTime);
        $this->assertContains($this->call->name, (string)$caughtJob->name);
        $this->assertEquals(Scheduler::CALLBACK_CLASS, $caughtJob->target);
        $this->assertTrue($caughtJob->requeue);
        $this->assertEquals($expectedJobGroup, $caughtJob->job_group);
        $this->assertEquals($expectedTime, $caughtJob->execute_time);
        $this->assertEquals($expectedTriggerArgs, $caughtJob->data);
        $this->assertEquals($expectedReminder, $reminderTime);
    }
}
