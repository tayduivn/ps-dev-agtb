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

use Sugarcrm\Sugarcrm\Trigger\Job\RecreateUserRemindersJob;
use Sugarcrm\Sugarcrm\Trigger\ReminderManager\Base;

/**
 * Class RecreateUserRemindersJobTest
 * @package Sugarcrm\SugarcrmTests\Trigger\Job
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Trigger\Job\RecreateUserRemindersJob
 */
class RecreateUserRemindersJobTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $userId;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RecreateUserRemindersJob
     */
    protected $recreateUserRemindersJob;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Call
     */
    protected $call;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Meeting
     */
    protected $meeting;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\User
     */
    protected $user;

    public function setUp()
    {
        parent::setUp();
        $this->userId = create_guid();
        $this->call = $this->getMock('Call', array('fetchFromQuery'));
        $this->meeting = $this->getMock('Meeting', array('fetchFromQuery'));
        $tomorrow = new \DateTime('tomorrow');
        $this->call->date_start = $tomorrow->format('Y-m-d\TH:i:s');
        $this->call->reminder_time = 10;
        $this->meeting->date_start = $tomorrow->format('Y-m-d\TH:i:s');
        $this->meeting->reminder_time = 10;
        $this->user = $this->getMock('User', array('getPreference'));
        $this->user->method('getPreference')
            ->will($this->returnValueMap(array(
                array('reminder_time', '10')
            )));
    }

    /**
     * @covers ::run
     */
    public function testRunReturnsJobSuccess()
    {
        $this->call->method('fetchFromQuery')->willReturn(array($this->call));
        $this->meeting->method('fetchFromQuery')->willReturn(array($this->meeting));

        $reminderManager = $this->getBaseReminderManager();

        $this->mockRecreateUserRemindersMock(array(
            'getReminderManager',
            'getBean'
        ));

        $this->recreateUserRemindersJob->method('getReminderManager')->willReturn($reminderManager);
        $this->mockGetBean();

        $result = $this->recreateUserRemindersJob->run();

        $this->assertEquals(\SchedulersJob::JOB_SUCCESS, $result);
    }

    /**
     * @covers :: run
     */
    public function testRunCallsFetchFromQueryWithCorrectSugarQuery()
    {
        $userId = $this->userId;
        $date = (new \DateTime())->format('Y-m-d H:i');

        $timeDate = $this->getMock('Timedate', array('asDb'));
        $timeDate->method('asDb')->willReturn($date);

        $call = $this->call;
        $this->call->method('fetchFromQuery')
            ->will($this->returnCallback(function (\SugarQuery $query) use ($call, $userId, $date) {
                $sql = $query->compileSql();

                $where = "WHERE calls.deleted = 0 " .
                    "AND (jt0_users.id = '$userId' " .
                    "AND calls.assigned_user_id != '$userId' " .
                    "AND calls.date_start > '$date')";

                \PHPUnit_Framework_Assert::assertContains('FROM calls', $sql);
                \PHPUnit_Framework_Assert::assertContains($where, $sql);
                return array($call);
            }));

        $meeting = $this->meeting;
        $this->meeting->method('fetchFromQuery')
            ->will($this->returnCallback(function (\SugarQuery $query) use ($meeting, $userId, $date) {
                $sql = $query->compileSql();

                $where = "WHERE meetings.deleted = 0 " .
                    "AND (jt0_users.id = '$userId' " .
                    "AND meetings.assigned_user_id != '$userId' " .
                    "AND meetings.date_start > '$date')";

                \PHPUnit_Framework_Assert::assertContains('FROM meeting', $sql);
                \PHPUnit_Framework_Assert::assertContains($where, $sql);
                return array($meeting);
            }));

        $reminderManager = $this->getBaseReminderManager();

        $this->mockRecreateUserRemindersMock(array(
            'getReminderManager',
            'getBean',
            'getTimeDate'
        ));

        $this->recreateUserRemindersJob->method('getReminderManager')->willReturn($reminderManager);
        $this->recreateUserRemindersJob->method('getTimeDate')->willReturn($timeDate);
        $this->mockGetBean();

        $this->recreateUserRemindersJob->run();
    }

    /**
     * @param boolean $isTsConfigured
     * @param boolean $isTsManagerUsed
     * @param boolean $isSchedulerManagerUsed
     * @dataProvider providerRunUsesCorrectReminderManager
     * @covers ::run
     */
    public function testRunUsesCorrectReminderManager($isTsConfigured, $isTsManagerUsed, $isSchedulerManagerUsed)
    {
        $this->call->method('fetchFromQuery')->willReturn(array($this->call));
        $this->meeting->method('fetchFromQuery')->willReturn(array($this->meeting));

        $tsClient = $this->getMock('Sugarcrm\\Sugarcrm\\Trigger\\Client', array('isConfigured'));
        $tsClient->method('isConfigured')->willReturn($isTsConfigured);

        $triggerServerManager = $this->getMockBuilder('Sugarcrm\\Sugarcrm\\Trigger\\ReminderManager\\TriggerServer')
            ->setMethods(array('deleteReminders', 'addReminderForUser'))
            ->getMock();

        $triggerServerManager->expects($this->exactly($isTsManagerUsed ? 1 : 0))
            ->method('deleteReminders');

        $triggerServerManager->expects($this->exactly($isTsManagerUsed ? 2 : 0))// one call and one meeting
        ->method('addReminderForUser');

        $schedulerManager = $this->getMockBuilder('Sugarcrm\\Sugarcrm\\Trigger\\ReminderManager\\Scheduler')
            ->setMethods(array('deleteReminders', 'addReminderForUser'))
            ->getMock();

        $schedulerManager->expects($this->exactly($isSchedulerManagerUsed ? 1 : 0))
            ->method('deleteReminders');

        $schedulerManager->expects($this->exactly($isSchedulerManagerUsed ? 2 : 0))// one call and one meeting
        ->method('addReminderForUser');

        $this->mockRecreateUserRemindersMock(array(
            'getTriggerServerManager',
            'getSchedulerManager',
            'getTriggerClient',
            'getBean'
        ));

        $this->recreateUserRemindersJob->method('getTriggerServerManager')->willReturn($triggerServerManager);
        $this->recreateUserRemindersJob->method('getSchedulerManager')->willReturn($schedulerManager);
        $this->recreateUserRemindersJob->method('getTriggerClient')->willReturn($tsClient);
        $this->mockGetBean();

        $this->recreateUserRemindersJob->run();
    }

    /**
     * @return array
     */
    public function providerRunUsesCorrectReminderManager()
    {
        return array(
            'trigger server manager is used when trigger client is configured' => array(true, true, false),
            'scheduler manager is used when trigger client isn\'t configured' => array(false, false, true)
        );
    }

    /**
     * @covers ::run
     */
    public function testRunCallsDeleteRemindersWithCorrectUser()
    {
        $this->call->method('fetchFromQuery')->willReturn(array($this->call));
        $this->meeting->method('fetchFromQuery')->willReturn(array($this->meeting));

        $reminderManager = $this->getBaseReminderManager();

        $reminderManager->expects($this->once())
            ->method('deleteReminders')
            ->with($this->user);

        $this->mockRecreateUserRemindersMock(array(
            'getReminderManager',
            'getBean'
        ));

        $this->recreateUserRemindersJob->method('getReminderManager')->willReturn($reminderManager);
        $this->mockGetBean();

        $this->recreateUserRemindersJob->run();
    }

    public function testRunCallsAddReminderForUserWithCorrectBeanAndUser()
    {
        $this->call->method('fetchFromQuery')->willReturn(array($this->call));
        $this->meeting->method('fetchFromQuery')->willReturn(array($this->meeting));

        $reminderManager = $this->getBaseReminderManager();

        $reminderManager->expects($this->at(1))
            ->method('addReminderForUser')
            ->with($this->call, $this->user);

        $reminderManager->expects($this->at(2))
            ->method('addReminderForUser')
            ->with($this->meeting, $this->user);

        $this->mockRecreateUserRemindersMock(array(
            'getReminderManager',
            'getBean'
        ));

        $this->recreateUserRemindersJob->method('getReminderManager')->willReturn($reminderManager);
        $this->mockGetBean();

        $this->recreateUserRemindersJob->run();
    }

    /**
     * @param int $beansCount
     * @param int $addReminderForUserCallsCount
     * @dataProvider providerRunCallsAddReminderForUserDependsOnBeansCount
     * @covers ::run
     */
    public function testRunCallsAddReminderForUserDependsOnBeansCount($beansCount, $addReminderForUserCallsCount)
    {
        $beans = array();
        $tomorrow = new \DateTime('tomorrow');

        for ($i = 0; $i < $beansCount; $i++) {
            $call = $this->getMock('Call', array('fetchFromQuery'));
            $call->reminder_time = 10;
            $call->date_start = $tomorrow->format('Y-m-d\TH:i:s');
            $beans[] = $call;
        }

        $this->call->method('fetchFromQuery')->willReturn($beans);
        $this->meeting->method('fetchFromQuery')->willReturn(array());

        $reminderManager = $this->getBaseReminderManager();

        $reminderManager->expects($this->exactly($addReminderForUserCallsCount))
            ->method('addReminderForUser');

        $this->mockRecreateUserRemindersMock(array(
            'getReminderManager',
            'getBean'
        ));

        $this->recreateUserRemindersJob->method('getReminderManager')->willReturn($reminderManager);
        $this->mockGetBean();

        $this->recreateUserRemindersJob->run();
    }

    /**
     * @return array
     */
    public function providerRunCallsAddReminderForUserDependsOnBeansCount()
    {
        return array(
            'beans count is zero' => array(0, 0),
            'beans count is 1' => array(1, 1),
            'beans count is 3' => array(3, 3)
        );
    }

    /**
     * Mocks ::getBean()
     */
    protected function mockGetBean()
    {
        $this->recreateUserRemindersJob->method('getBean')->will($this->returnValueMap(array(
            array('Calls', null, $this->call),
            array('Meetings', null, $this->meeting),
            array('Users', $this->userId, $this->user)
        )));
    }

    /**
     * @param string[] $methods
     */
    protected function mockRecreateUserRemindersMock($methods = array())
    {
        $this->recreateUserRemindersJob = $this
            ->getMockBuilder('Sugarcrm\\Sugarcrm\\Trigger\\Job\\RecreateUserRemindersJob')
            ->setConstructorArgs(array($this->userId))
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Base
     */
    protected function getBaseReminderManager()
    {
        return $this->getMockBuilder('Sugarcrm\\Sugarcrm\\Trigger\\ReminderManager\\Base')
            ->setMethods(array('deleteReminders', 'addReminderForUser'))
            ->getMockForAbstractClass();
    }
}
