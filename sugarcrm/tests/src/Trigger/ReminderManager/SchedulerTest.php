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

    /**
     * @covers ::deleteReminders
     */
    public function testDeleteReminders()
    {
        $eventBeanTag = 'SomeDummyBeanTag';
        $jobList = array();
        for ($i = 0; $i < 3; $i++) {
            $job = $this->getMockBean('SchedulersJobs', 'SchedulersJobs', array('mark_deleted'));
            $job->expects($this->once())->method('mark_deleted')
                ->with($this->equalTo($job->id));
            $jobList[] = $job;
        }

        $eventBean = $this->getMockBean('Calls', 'Call');

        $jobBean = $this->getMockBean('SchedulersJobs', 'SchedulersJobs', array('fetchFromQuery'));

        $sugarQueryWhere = $this->getMock('SugarQuery_Builder_Andwhere', array('contains'), array(), '', false);
        $sugarQueryWhere->expects($this->once())->method('contains')
            ->with($this->equalTo('job_group'), $this->equalTo($eventBeanTag));

        $sugarQuery = $this->getMock('getSugarQuery', array('from', 'where'));
        $sugarQuery->expects($this->once())->method('from')
            ->with($this->equalTo($jobBean));
        $sugarQuery->expects($this->once())->method('where')
            ->willReturn($sugarQueryWhere);

        $jobBean->expects($this->once())->method('fetchFromQuery')
            ->with($this->equalTo($sugarQuery))
            ->willReturn($jobList);

        $this->mockSchedulerManager(array('makeTag', 'getSchedulersJob', 'getSugarQuery'));
        $this->schedulerManager->expects($this->atLeastOnce())->method('makeTag')
            ->with($this->equalTo($eventBean))
            ->willReturn($eventBeanTag);

        $this->schedulerManager->expects($this->once())->method('getSchedulersJob')
            ->willReturn($jobBean);
        $this->schedulerManager->expects($this->once())->method('getSugarQuery')
            ->willReturn($sugarQuery);

        $this->schedulerManager->deleteReminders($eventBean);
    }

    /**
     * @covers ::addReminderForUser
     */
    public function testAddReminderForUser()
    {
        $reminderTime = new \DateTime();
        $reminderTimeFormatted = $reminderTime->format('Y-m-d\TH:i:s');
        $eventBean = $this->getMockBean('Calls', 'Call');
        $userBean = $this->getMockBean('Users', 'User');
        $tags = array(
            'bean' => 'bean-tag',
            'user' => 'user-tag',
        );
        $triggerArgs = array(
            'module' => $eventBean->module_name,
            'beanId' => $eventBean->id,
            'userId' => $userBean->id
        );

        $catchedJob = null;

        $timeDateHandler = $this->getMock('TimeDate', array('asDb'));
        $timeDateHandler->expects($this->atLeastOnce())->method('asDb')
            ->with($this->equalTo($reminderTime), $this->equalTo(false))
            ->willReturn($reminderTimeFormatted);

        $sugarJobQueue = $this->getMock('SugarJobQueue', array('submitJob'));
        $sugarJobQueue->expects($this->atLeastOnce())->method('submitJob')
            ->with($this->callback(function ($job) use (&$catchedJob) {
                $catchedJob = $job;
                return true;
            }));

        $this->mockSchedulerManager(array('getSugarJobQueue', 'makeTag', 'getTimeDate', 'prepareTriggerArgs'));
        $this->schedulerManager->expects($this->once())
            ->method('prepareTriggerArgs')
            ->with($this->equalTo($eventBean), $this->equalTo($userBean))
            ->willReturn($triggerArgs);

        $this->schedulerManager->expects($this->atLeastOnce())->method('getTimeDate')
            ->willReturn($timeDateHandler);
        $this->schedulerManager->expects($this->atLeastOnce())->method('getSugarJobQueue')
            ->willReturn($sugarJobQueue);
        $this->schedulerManager->expects($this->any())
            ->method('makeTag')
            ->with($this->logicalOr($this->equalTo($eventBean), $this->equalTo($userBean)))
            ->will($this->returnValueMap(array(
                array($eventBean, $tags['bean']),
                array($userBean, $tags['user']),
            )));

        $this->schedulerManager->addReminderForUser($eventBean, $userBean, $reminderTime);

        $this->assertContains($eventBean->name, (string)$catchedJob->name);
        $this->assertEquals(Scheduler::CALLBACK_CLASS, $catchedJob->target);
        $this->assertTrue($catchedJob->requeue);
        $this->assertContains($tags['bean'], explode(':', $catchedJob->job_group));
        $this->assertContains($tags['user'], explode(':', $catchedJob->job_group));
        $this->assertEquals($reminderTimeFormatted, $catchedJob->execute_time);
        $this->assertEquals($triggerArgs, (array)json_decode($catchedJob->data));
    }

    /**
     * @covers ::addReminderForUser
     */
    public function testMakeTag()
    {
        $schedulerManager = new Scheduler();
        $bean = $this->getMockBean('Calls', 'Call');
        $this->assertEquals(
            md5(strtolower($bean->object_name) . '-' . $bean->id),
            \SugarTestReflection::callProtectedMethod($schedulerManager, 'makeTag', array($bean))
        );
    }

    /**
     * @param $module
     * @param $originalClassName
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockBean($module, $originalClassName, $methods = array())
    {
        static $idNum = 0;
        $idNum++;
        $bean = $this->getMock($originalClassName, $methods);
        $bean->id = 'dummy-bean-id' . $idNum;
        $bean->module_name = $module;
        $bean->object_name = $originalClassName;
        $bean->name = 'dummy bean name';
        return $bean;
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
