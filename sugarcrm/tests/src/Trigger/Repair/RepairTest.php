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

namespace Sugarcrm\SugarcrmTests\Trigger\Repair;

use Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event as ReminderEvent;
use Sugarcrm\Sugarcrm\Trigger\Repair\Repair;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * Testing is correctly fetch beans for repair and mechanism of repairing(re-creation notification job).
 *
 * @covers \Sugarcrm\Sugarcrm\Trigger\Repair\Repair
 */
class RepairTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var string[] */
    protected $allEmitterModules = array('ReminderModule1', 'ReminderModule2', 'Module3', 'Module4',);

    /** @var \Sugarcrm\Sugarcrm\Trigger\Repair\Repair|\PHPUnit_Framework_MockObject_MockObject */
    protected $repair = null;

    /** @var \Sugarcrm\Sugarcrm\Notification\EmitterRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $emitterRegistry = null;

    /** @var \User|\PHPUnit_Framework_MockObject_MockObject */
    protected $userBean = null;

    /** @var \Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler|\PHPUnit_Framework_MockObject_MockObject */
    protected $schedulerManager = null;

    /** @var \Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler|\PHPUnit_Framework_MockObject_MockObject */
    protected $triggerClient = null;

    /** @var \Sugarcrm\Sugarcrm\Trigger\ReminderManager\TriggerServer|\PHPUnit_Framework_MockObject_MockObject */
    protected $triggerServer = null;

    /** @var \Call|\PHPUnit_Framework_MockObject_MockObject */
    protected $callBean = null;

    /** @var \Sugarcrm\Sugarcrm\Notification\EmitterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $reminderEmitter1 = null;

    /** @var \Sugarcrm\Sugarcrm\Notification\EmitterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $reminderEmitter2 = null;

    /** @var \Sugarcrm\Sugarcrm\Notification\EmitterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $emitter3 = null;

    /** @var \Sugarcrm\Sugarcrm\Notification\EmitterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $emitter4 = null;

    /** @var ReminderEvent|\PHPUnit_Framework_MockObject_MockObject */
    protected $remindEvent = null;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->remindEvent = new ReminderEvent();

        $this->reminderEmitter1 = $this->getMock('Sugarcrm\Sugarcrm\Notification\EmitterInterface');
        $this->reminderEmitter2 = $this->getMock('Sugarcrm\Sugarcrm\Notification\EmitterInterface');
        $this->emitter3 = $this->getMock('Sugarcrm\Sugarcrm\Notification\EmitterInterface');
        $this->emitter4 = $this->getMock('Sugarcrm\Sugarcrm\Notification\EmitterInterface');

        $this->callBean = $this->getMockBuilder('Call')
            ->disableOriginalConstructor()
            ->getMock();

        $this->emitterRegistry = $this->getMock('Sugarcrm\Sugarcrm\Notification\EmitterRegistry');
        $this->emitterRegistry
            ->method('getModuleEmitters')
            ->willReturn($this->allEmitterModules);

        $this->reminderEmitter1->method('getEventStrings')
            ->willReturn(array('someEvent1', Repair::EVENT_STRING_REMINDER, 'someEvent2'));
        $this->reminderEmitter1->method('getEventPrototypeByString')
            ->will($this->returnValueMap(array(
                array(Repair::EVENT_STRING_REMINDER, $this->remindEvent)
            )));
        $this->reminderEmitter2->method('getEventStrings')
            ->willReturn(array('someEvent1', Repair::EVENT_STRING_REMINDER, 'someEvent2'));
        $this->reminderEmitter2->method('getEventPrototypeByString')
            ->will($this->returnValueMap(array(
                array(Repair::EVENT_STRING_REMINDER, $this->remindEvent)
            )));
        $this->emitter3->method('getEventStrings')->willReturn(array('someEvent3', Repair::EVENT_STRING_REMINDER));
        $this->emitter3->method('getEventPrototypeByString')->willReturn(new \StdClass);
        $this->emitter4->method('getEventStrings')->willReturn(array('someEvent3', 'someEvent4'));
        $this->emitter4->method('getEventPrototypeByString')->willReturn(new \StdClass);

        $this->emitterRegistry
            ->method('getModuleEmitter')
            ->will($this->returnValueMap(array(
                array($this->allEmitterModules[0], $this->reminderEmitter1),
                array($this->allEmitterModules[1], $this->reminderEmitter2),
                array($this->allEmitterModules[2], $this->emitter3),
                array($this->allEmitterModules[3], $this->emitter4),
            )));

        $this->triggerServer = $this->getMock('Sugarcrm\Sugarcrm\Trigger\ReminderManager\TriggerServer');
        $this->triggerClient = $this->getMock('Sugarcrm\Sugarcrm\Trigger\Client');
        $this->schedulerManager = $this->getMock('Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler');

        $this->userBean = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->getMock();
        $this->userBean->id = Uuid::uuid1();

        $this->repair = $this->getMock(
            'Sugarcrm\Sugarcrm\Trigger\Repair\Repair',
            array(
                'getSchedulerManager',
                'getTriggerServerManager',
                'loadUsers',
                'getEmitterRegistry',
                'getBeanIterator',
            )
        );
        $this->repair->method('getSchedulerManager')->willReturn($this->schedulerManager);
        $this->repair->method('getTriggerServerManager')->willReturn($this->triggerServer);
        $this->repair->method('getEmitterRegistry')->willReturn($this->emitterRegistry);
        $this->repair->method('loadUsers')->willReturn(array($this->userBean));

        $this->callBean->id = Uuid::uuid1();
        $this->callBean->users_arr = array(Uuid::uuid1());
        $this->callBean->reminder_time = 1;
        $this->callBean->date_start = date('Y-m-d H:i:s', strtotime('+2 day'));
        $this->callBean->assigned_user_id = $this->userBean->id;

        \SugarTestReflection::setProtectedValue('Sugarcrm\Sugarcrm\Trigger\Client', 'instance', $this->triggerClient);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        \SugarTestReflection::setProtectedValue('Sugarcrm\Sugarcrm\Trigger\Client', 'instance', null);
        parent::tearDown();
    }

    /**
     * Testing is fetched beans for all modules that have ReminderEvent in emitter.
     *
     * @covers \Sugarcrm\Sugarcrm\Trigger\Repair\Repair::getBeans
     */
    public function testGetBeans()
    {
        /** @var array $beansExpected */
        $beansExpected = array(
            $this->allEmitterModules[0] => array(
                $this->allEmitterModules[0] . rand(1000, 9999),
                $this->allEmitterModules[0] . rand(1000, 9999),
                $this->allEmitterModules[0] . rand(1000, 9999),
            ),
            $this->allEmitterModules[1] => array(
                $this->allEmitterModules[1] . rand(1000, 9999),
                $this->allEmitterModules[1] . rand(1000, 9999),
                $this->allEmitterModules[1] . rand(1000, 9999),
            ),
        );

        /** @var array $beansExpectedList */
        $beansExpectedList = call_user_func_array('array_merge', array_values($beansExpected));

        $reminderEmitterModules = array();
        $this->repair->expects($this->exactly(2))
            ->method('getBeanIterator')
            ->withConsecutive(
                array($this->equalTo($this->allEmitterModules[0])),
                array($this->equalTo($this->allEmitterModules[1]))
            )
            ->will($this->returnCallback(function ($module) use (&$reminderEmitterModules, $beansExpected) {
                $reminderEmitterModules[] = $module;
                return new \ArrayIterator($beansExpected[$module]);
            }));

        $beans = array();
        foreach ($this->repair->getBeans() as $bean) {
            $beans[] = $bean;
        }

        $this->assertContains($this->allEmitterModules[0], $reminderEmitterModules);
        $this->assertContains($this->allEmitterModules[1], $reminderEmitterModules);
        $this->assertNotContains($this->allEmitterModules[2], $reminderEmitterModules);
        $this->assertNotContains($this->allEmitterModules[3], $reminderEmitterModules);

        $this->assertCount(count($beansExpectedList), $beans);
        $this->assertArraySubset($beans, $beansExpectedList);
    }

    /**
     * Data provider for testDeletingSchedulerJobs.
     *
     * @see RepairTest::testDeletingSchedulerJobs
     * @return array
     */
    public static function triggerClientForDeletingSchedulerJobs()
    {
        return array(
            'ifTriggerClientConfigured' => array(
                'isConfigured' => true,
                'triggerServerUsed' => true,
                'schedulerUsed' => false
            ),
            'ifTriggerClientNotConfigured' => array(
                'isConfigured' => false,
                'triggerServerUsed' => false,
                'schedulerUsed' => true
            )
        );
    }

    /**
     * Testing is will be deleted legacy scheduler jobs and created necessary.
     *
     * @covers \Sugarcrm\Sugarcrm\Trigger\Repair\Repair::rebuild
     * @dataProvider triggerClientForDeletingSchedulerJobs
     * @param boolean $isConfigured
     * @param boolean $triggerServerUsed
     * @param boolean $schedulerUsed
     */
    public function testDeletingSchedulerJobs($isConfigured, $triggerServerUsed, $schedulerUsed)
    {
        $this->triggerClient->method('isConfigured')->willReturn($isConfigured);

        if ($triggerServerUsed) {
            $this->triggerServer->expects($this->once())
                ->method('deleteReminders')
                ->with($this->equalTo($this->callBean));
            $this->triggerServer->expects($this->once())
                ->method('addReminderForUser')
                ->with($this->equalTo($this->callBean), $this->equalTo($this->userBean));
        } else {
            $this->triggerServer->expects($this->never())->method('deleteReminders');
            $this->triggerServer->expects($this->never())->method('addReminderForUser');
        }

        $this->schedulerManager->expects($this->once())
            ->method('deleteReminders')
            ->with($this->equalTo($this->callBean));
        if ($schedulerUsed) {
            $this->schedulerManager->expects($this->once())
                ->method('addReminderForUser')
                ->with($this->equalTo($this->callBean), $this->equalTo($this->userBean));
        } else {
            $this->schedulerManager->expects($this->never())->method('addReminderForUser');
        }

        $this->repair->rebuild($this->callBean);
    }
}
