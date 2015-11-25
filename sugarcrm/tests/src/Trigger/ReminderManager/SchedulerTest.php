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

namespace Sugarcrm\SugarcrmTests\Trigger\ReminderManager;

use Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler;

/**
 * Class SchedulerTest
 * @package Sugarcrm\SugarcrmTests\Trigger\ReminderManager
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler
 */
class SchedulerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \DateTime
     */
    protected $dateStart;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Scheduler
     */
    protected $schedulerManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Call|\Meeting|\SugarBean
     */
    protected $bean;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\User
     */
    protected $user;

    public function setUp()
    {
        parent::setUp();
        $this->dateStart = new \DateTime();
    }

    /**
     * @param boolean $isUpdate
     * @param boolean $isDeleteRemindersCalled
     * @dataProvider providerSetRemindersDeletesSchedulersJobsWhenIsUpdateIsTrue
     * @covers ::setReminders
     */
    public function testSetRemindersDeletesSchedulersJobsWhenIsUpdateIsTrue($isUpdate, $isDeleteRemindersCalled)
    {
        $this->mockBean('SugarBean');
        $this->mockUser();
        $this->mockSchedulerManager(array('deleteReminders', 'addReminders'));

        $this->schedulerManager->expects($this->exactly($isDeleteRemindersCalled ? 1 : 0))
            ->method('deleteReminders')
            ->with($this->bean);

        $this->schedulerManager->setReminders($this->bean, $isUpdate);
    }

    /**
     * @return array
     */
    public function providerSetRemindersDeletesSchedulersJobsWhenIsUpdateIsTrue()
    {
        return array(
            '$isUpdate is false' => array(false, false),
            '$isUpdate is true' => array(true, true)
        );
    }

    /**
     * @param boolean $isAuthor
     * @param int $userReminderTime
     * @param int $beanReminderTime
     * @param int $expected
     * @dataProvider providerSetRemindersUsesProperReminderTime
     * @covers ::setReminders
     */
    public function testSetRemindersUsesProperReminderTime($isAuthor, $userReminderTime, $beanReminderTime, $expected)
    {
        $this->mockBean('SugarBean');
        $this->mockUser($userReminderTime);

        $this->bean->users_arr = array($this->user->id);
        $this->bean->reminder_time = $beanReminderTime;
        $this->bean->assigned_user_id = $isAuthor ? $this->user->id : 'dummy-other-user-id';

        $current = $this->dateStart->getTimestamp();

        $jobQueue = $this->getMock('SugarJobQueue', array('submitJob'));
        $jobQueue->expects($this->any())
            ->method('submitJob')
            ->will($this->returnCallback(function (\SchedulersJob $job) use ($expected, $current) {
                $reminderTime = $current - strtotime($job->execute_time);
                \PHPUnit_Framework_Assert::assertEquals($expected, $reminderTime);
            }));

        $this->mockSchedulerManager(array(
            'loadUsers',
            'getSugarJobQueue'
        ));

        $this->schedulerManager->method('loadUsers')->willReturn(array($this->user));
        $this->schedulerManager->method('getSugarJobQueue')->willReturn($jobQueue);

        $this->schedulerManager->setReminders($this->bean, false);
    }

    /**
     * @return array
     */
    public function providerSetRemindersUsesProperReminderTime()
    {
        return array(
            'returns bean reminder time when user is author' => array(true, 60, 300, 300),
            'returns user reminder time when user isn\'t author' => array(false, 60, 300, 60),
        );
    }

    /**
     * @param int $reminderTime
     * @param boolean $isSubmitJobCalled
     * @dataProvider providerSetRemindersAddsSchedulersJobsWhenReminderTimeIsGreatThanZero
     * @covers ::setReminders
     */
    public function testSetRemindersAddsSchedulersJobsWhenReminderTimeIsGreatThanZero($reminderTime, $isSubmitJobCalled)
    {
        $this->mockBean('SugarBean');
        $this->mockUser();

        $this->bean->users_arr = array($this->user->id);

        $jobQueue = $this->getMock('SugarJobQueue', array('submitJob'));
        $jobQueue->expects($this->exactly($isSubmitJobCalled ? 1 : 0))->method('submitJob');

        $this->mockSchedulerManager(array(
            'loadUsers',
            'getSugarJobQueue',
            'getReminderTime'
        ));

        $this->schedulerManager->method('loadUsers')->willReturn(array($this->user));
        $this->schedulerManager->method('getSugarJobQueue')->willReturn($jobQueue);
        $this->schedulerManager->method('getReminderTime')->willReturn($reminderTime);

        $this->schedulerManager->setReminders($this->bean, false);
    }

    /**
     * @return array
     */
    public function providerSetRemindersAddsSchedulersJobsWhenReminderTimeIsGreatThanZero()
    {
        return array(
            'doesn\'t submit job when reminder time is less than zero' => array(-1, false),
            'doesn\'t submit job when reminder time equals zero' => array(0, false),
            'submits job when reminder time is great than zero' => array(60, true),
        );
    }

    /**
     * @covers ::setReminders
     */
    public function testSetRemindersCreatesCorrectSchedulerJob()
    {
        $this->mockBean('SugarBean');
        $this->mockUser();

        $reminderTime = 60;

        $this->bean->users_arr = array($this->user->id);
        $this->bean->object_name = 'bean';

        $current = $this->dateStart->getTimestamp();
        $data = json_encode(array(
            'module' => $this->bean->module_name,
            'beanId' => $this->bean->id,
            'userId' => $this->user->id
        ));
        $jobGroup = md5('bean-' . $this->bean->id) . ':' . md5('user-' . $this->user->id);

        $jobQueue = $this->getMock('SugarJobQueue', array('submitJob'));
        $jobQueue->expects($this->any())
            ->method('submitJob')
            ->will($this->returnCallback(function (\SchedulersJob $job) use (
                $current,
                $reminderTime,
                $data,
                $jobGroup
            ) {
                $actualReminderTime = $current - strtotime($job->execute_time);

                \PHPUnit_Framework_Assert::assertEquals('Reminder Job dummy bean name', $job->name);
                \PHPUnit_Framework_Assert::assertEquals($jobGroup, $job->job_group);
                \PHPUnit_Framework_Assert::assertJson($job->data);
                \PHPUnit_Framework_Assert::assertJsonStringEqualsJsonString($data, $job->data);
                \PHPUnit_Framework_Assert::assertEquals(Scheduler::CALLBACK_CLASS, $job->target);
                \PHPUnit_Framework_Assert::assertEquals($reminderTime, $actualReminderTime);
                \PHPUnit_Framework_Assert::assertTrue($job->requeue);
            }));

        $this->mockSchedulerManager(array(
            'loadUsers',
            'getSugarJobQueue',
            'getReminderTime'
        ));

        $this->schedulerManager->method('loadUsers')->willReturn(array($this->user));
        $this->schedulerManager->method('getSugarJobQueue')->willReturn($jobQueue);
        $this->schedulerManager->method('getReminderTime')->willReturn($reminderTime);

        $this->schedulerManager->setReminders($this->bean, false);
    }

    /**
     * @param int $usersCount
     * @param int $submitJobCallsCount
     * @dataProvider providerSetRemindersAddsSchedulersJobsToJobQueueDependsOnUsersCount
     * @covers ::setReminders
     */
    public function testSetRemindersAddsSchedulersJobsToJobQueueDependsOnUsersCount($usersCount, $submitJobCallsCount)
    {
        $this->mockBean('SugarBean');

        $reminderTime = 60;

        $users = array();
        $this->bean->users_arr = array();
        for ($i = 0; $i < $usersCount; $i++) {
            /* @var $item \PHPUnit_Framework_MockObject_MockObject|\Call|\Meeting|\SugarBean */
            $item = $this->getMock('User', array('getPreference'));
            $item->id = 'dummy-user-id-' . $i;
            $item->method('getPreference')->willReturn($reminderTime);
            $this->bean->users_arr[] = $item->id;
            $users[$i] = $item;
        }

        $jobQueue = $this->getMock('SugarJobQueue', array('submitJob'));
        $jobQueue->expects($this->exactly($submitJobCallsCount))
            ->method('submitJob');

        $this->mockSchedulerManager(array(
            'loadUsers',
            'getSugarJobQueue'
        ));

        $this->schedulerManager->method('loadUsers')->willReturn($users);
        $this->schedulerManager->method('getSugarJobQueue')->willReturn($jobQueue);

        $this->schedulerManager->setReminders($this->bean, false);
    }

    /**
     * @return array
     */
    public function providerSetRemindersAddsSchedulersJobsToJobQueueDependsOnUsersCount()
    {
        return array(
            'users count is zero' => array(0, 0),
            'users count is 1' => array(1, 1),
            'users count is 3' => array(3, 3)
        );
    }

    /**
     * @covers ::deleteReminders
     */
    public function testDeleteRemindersGenerateProperSugarQueryToFindSchedulersJobs()
    {
        $this->mockBean('SugarBean');
        $this->bean->object_name = 'bean';

        $group = md5('bean-dummy-bean-id');

        $job = $this->getMock('SchedulersJob', array('fetchFromQuery', 'mark_deleted'));
        $job->method('fetchFromQuery')
            ->will($this->returnCallback(function (\SugarQuery $query) use ($job, $group) {
                $sql = $query->compileSql();

                $where = "WHERE job_queue.deleted = 0 " .
                    "AND job_queue.job_group LIKE '%$group%'";

                \PHPUnit_Framework_Assert::assertContains('FROM job_queue', $sql);
                \PHPUnit_Framework_Assert::assertContains($where, $sql);
                return array($job);
            }));


        $this->mockSchedulerManager(array(
            'getBean'
        ));

        $this->schedulerManager->method('getBean')->willReturn($job);

        $this->schedulerManager->deleteReminders($this->bean);
    }

    /**
     * @param int $jobsCount
     * @param int $markDeletedCallsCount
     * @dataProvider providerDeleteRemindersDeletesSchedulersJobsDependsOnJobsCount
     * @covers ::deleteReminders
     */
    public function testDeleteRemindersDeletesSchedulersJobsDependsOnJobsCount($jobsCount, $markDeletedCallsCount)
    {
        $this->mockBean('SugarBean');

        $job = $this->getMock('SchedulersJob', array('fetchFromQuery', 'mark_deleted'));

        $objects = array();
        for ($i = 0; $i < $jobsCount; $i++) {
            $objects[] = $job;
        }

        $job->method('fetchFromQuery')->willReturn($objects);
        $job->expects($this->exactly($markDeletedCallsCount))->method('mark_deleted');

        $this->mockSchedulerManager(array(
            'getBean'
        ));

        $this->schedulerManager->method('getBean')->willReturn($job);

        $this->schedulerManager->deleteReminders($this->bean);
    }

    /**
     * @return array
     */
    public function providerDeleteRemindersDeletesSchedulersJobsDependsOnJobsCount()
    {
        return array(
            'schedulers jobs count is zero' => array(0, 0),
            'schedulers jobs count is 1' => array(1, 1),
            'schedulers jobs count is 3' => array(3, 3)
        );
    }

    /**
     * @covers ::addReminderForUser
     */
    public function testAddReminderForUserAddsSchedulersJobToJobQueueForSpecifiedUserOfBean()
    {
        $reminderTime = 60;

        $this->mockBean('SugarBean');
        $this->mockUser($reminderTime);

        $this->bean->users_arr = array($this->user->id);

        $jobQueue = $this->getMock('SugarJobQueue', array('submitJob'));

        $this->mockSchedulerManager(array(
            'getSugarJobQueue',
            'createSchedulersJob'
        ));

        $this->schedulerManager->method('getSugarJobQueue')->willReturn($jobQueue);
        $this->schedulerManager->expects($this->once())
            ->method('createSchedulersJob')
            ->with($this->bean, $this->user, $reminderTime);

        $this->schedulerManager->addReminderForUser($this->bean, $this->user);
    }

    /**
     * @param string $module
     */
    protected function mockBean($module)
    {
        $this->bean = $this->getMock($module);
        $this->bean->id = 'dummy-bean-id';
        $this->bean->name = 'dummy bean name';
        $this->bean->date_start = $this->dateStart->format('Y-m-d H:i:s');
    }

    protected function mockUser($reminderTime = 60)
    {
        $this->user = $this->getMock('User', array('getPreference'));
        $this->user->id = 'dummy-user-id';
        $this->user->method('getPreference')->willReturn($reminderTime);
    }

    protected function mockSchedulerManager($methods = array())
    {
        $this->schedulerManager = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Trigger\\ReminderManager\\Scheduler',
            $methods
        );
    }
}
