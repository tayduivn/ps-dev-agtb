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

namespace Sugarcrm\SugarcrmTests\Trigger;

use Sugarcrm\Sugarcrm\JobQueue\Manager\Manager;
use Sugarcrm\Sugarcrm\Trigger\Client;
use Sugarcrm\Sugarcrm\Trigger\HookManager;
use Sugarcrm\Sugarcrm\Trigger\ReminderManager\Base;
use Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler;
use Sugarcrm\Sugarcrm\Trigger\ReminderManager\TriggerServer;

/**
 * Class HookManagerTest
 * @package Sugarcrm\SugarcrmTests\Trigger
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Trigger\HookManager
 */
class HookManagerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|HookManager
     */
    protected $hookManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Base
     */
    protected $reminderManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Client
     */
    protected $triggerClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TriggerServer
     */
    protected $triggerServerManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Scheduler
     */
    protected $schedulerManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Manager
     */
    protected $jobQueueManager;

    public function setUp()
    {
        parent::setUp();

    }

    /**
     * @param string $module
     * @param boolean $isSetRemindersCalled
     * @dataProvider providerAfterCallOrMeetingSaveCallsSetReminders
     * @covers ::afterCallOrMeetingSave
     */
    public function testAfterCallOrMeetingSaveCallsSetReminders($module, $isSetRemindersCalled)
    {
        /* @var $bean \PHPUnit_Framework_MockObject_MockObject|\Call|\Meeting|\SugarBean */
        $bean = $this->getMock($module);
        $isUpdate = true;

        $this->mockReminderManager();
        $this->mockHookManager(array('getReminderManager'));

        $this->reminderManager->expects($this->exactly($isSetRemindersCalled ? 1 : 0))
            ->method('setReminders')
            ->with($bean, $isUpdate);

        $this->hookManager->method('getReminderManager')->willReturn($this->reminderManager);

        $this->hookManager->afterCallOrMeetingSave($bean, 'after_save', array('isUpdate' => $isUpdate));
    }

    /**
     * @return array
     */
    public function providerAfterCallOrMeetingSaveCallsSetReminders()
    {
        return array(
            'bean isn\t Call or Meeting' => array('Account', false),
            'bean is Call' => array('Call', true),
            'bean is Meeting' => array('Meeting', true),
        );
    }

    /**
     * @param boolean $isTsConfigured
     * @param boolean $isTsManagerUsed
     * @param boolean $isSchedulerManagerUsed
     * @dataProvider providerAfterCallOrMeetingSaveUsesCorrectReminderManager
     * @covers ::afterCallOrMeetingSave
     */
    public function testAfterCallOrMeetingSaveUsesCorrectReminderManager(
        $isTsConfigured,
        $isTsManagerUsed,
        $isSchedulerManagerUsed
    ) {
        /* @var $bean \PHPUnit_Framework_MockObject_MockObject|\Call|\SugarBean */
        $bean = $this->getMock('Call');

        $this->mockTriggerClient();
        $this->mockTriggerServerManager();
        $this->mockSchedulerManager();
        $this->mockHookManager(array(
            'getTriggerClient',
            'getTriggerServerManager',
            'getSchedulerManager'
        ));

        $this->triggerClient->method('isConfigured')->willReturn($isTsConfigured);

        $this->triggerServerManager->expects($this->exactly($isTsManagerUsed ? 1 : 0))
            ->method('setReminders');

        $this->schedulerManager->expects($this->exactly($isSchedulerManagerUsed ? 1 : 0))
            ->method('setReminders');

        $this->hookManager->method('getTriggerClient')->willReturn($this->triggerClient);
        $this->hookManager->method('getTriggerServerManager')->willReturn($this->triggerServerManager);
        $this->hookManager->method('getSchedulerManager')->willReturn($this->schedulerManager);


        $this->hookManager->afterCallOrMeetingSave($bean, 'after_save', array('isUpdate' => true));
    }

    /**
     * @return array
     */
    public function providerAfterCallOrMeetingSaveUsesCorrectReminderManager()
    {
        return array(
            array(false, false, true),
            array(true, true, false)
        );
    }

    /**
     * @param string $module
     * @param boolean $isDeleteRemindersCalled
     * @dataProvider providerAfterCallOrMeetingDeleteCallsDeleteReminders
     * @covers ::afterCallOrMeetingDelete
     */
    public function testAfterCallOrMeetingDeleteCallsDeleteReminders($module, $isDeleteRemindersCalled)
    {
        /* @var $bean \PHPUnit_Framework_MockObject_MockObject|\Call|\Meeting|\SugarBean */
        $bean = $this->getMock($module);

        $this->mockReminderManager();
        $this->mockHookManager(array('getReminderManager'));

        $this->reminderManager->expects($this->exactly($isDeleteRemindersCalled ? 1 : 0))
            ->method('deleteReminders')
            ->with($bean);

        $this->hookManager->method('getReminderManager')->willReturn($this->reminderManager);

        $this->hookManager->afterCallOrMeetingDelete($bean, 'after_delete', array());
    }

    /**
     * @return array
     */
    public function providerAfterCallOrMeetingDeleteCallsDeleteReminders()
    {
        return array(
            'bean isn\t Call or Meeting' => array('Account', false),
            'bean is Call' => array('Call', true),
            'bean is Meeting' => array('Meeting', true),
        );
    }

    /**
     * @param boolean $isTsConfigured
     * @param boolean $isTsManagerUsed
     * @param boolean $isSchedulerManagerUsed
     * @dataProvider providerAfterCallOrMeetingDeleteUsesCorrectReminderManager
     * @covers ::afterCallOrMeetingDelete
     */
    public function testAfterCallOrMeetingDeleteUsesCorrectReminderManager(
        $isTsConfigured,
        $isTsManagerUsed,
        $isSchedulerManagerUsed
    ) {
        /* @var $bean \PHPUnit_Framework_MockObject_MockObject|\Call|\SugarBean */
        $bean = $this->getMock('Call');

        $this->mockTriggerClient();
        $this->mockTriggerServerManager();
        $this->mockSchedulerManager();
        $this->mockHookManager(array(
            'getTriggerClient',
            'getTriggerServerManager',
            'getSchedulerManager'
        ));

        $this->triggerClient->method('isConfigured')->willReturn($isTsConfigured);

        $this->triggerServerManager->expects($this->exactly($isTsManagerUsed ? 1 : 0))
            ->method('deleteReminders');

        $this->schedulerManager->expects($this->exactly($isSchedulerManagerUsed ? 1 : 0))
            ->method('deleteReminders');

        $this->hookManager->method('getTriggerClient')->willReturn($this->triggerClient);
        $this->hookManager->method('getTriggerServerManager')->willReturn($this->triggerServerManager);
        $this->hookManager->method('getSchedulerManager')->willReturn($this->schedulerManager);


        $this->hookManager->afterCallOrMeetingDelete($bean, 'after_delete', array());
    }

    /**
     * @return array
     */
    public function providerAfterCallOrMeetingDeleteUsesCorrectReminderManager()
    {
        return array(
            array(false, false, true),
            array(true, true, false)
        );
    }

    /**
     * @param string $module
     * @param boolean $isSetRemindersCalled
     * @dataProvider providerAfterCallOrMeetingRestoreCallsSetReminders
     * @covers ::afterCallOrMeetingRestore
     */
    public function testAfterCallOrMeetingRestoreCallsSetReminders($module, $isSetRemindersCalled)
    {
        /* @var $bean \PHPUnit_Framework_MockObject_MockObject|\Call|\Meeting|\SugarBean */
        $bean = $this->getMock($module);

        $this->mockReminderManager();
        $this->mockHookManager(array('getReminderManager'));

        $this->reminderManager->expects($this->exactly($isSetRemindersCalled ? 1 : 0))
            ->method('setReminders')
            ->with($bean, false);

        $this->hookManager->method('getReminderManager')->willReturn($this->reminderManager);

        $this->hookManager->afterCallOrMeetingRestore($bean, 'after_restore', array());
    }

    /**
     * @return array
     */
    public function providerAfterCallOrMeetingRestoreCallsSetReminders()
    {
        return array(
            'bean isn\t Call or Meeting' => array('Account', false),
            'bean is Call' => array('Call', true),
            'bean is Meeting' => array('Meeting', true),
        );
    }

    /**
     * @param boolean $isTsConfigured
     * @param boolean $isTsManagerUsed
     * @param boolean $isSchedulerManagerUsed
     * @dataProvider providerAfterCallOrMeetingRestoreUsesCorrectReminderManager
     * @covers ::afterCallOrMeetingRestore
     */
    public function testAfterCallOrMeetingRestoreUsesCorrectReminderManager(
        $isTsConfigured,
        $isTsManagerUsed,
        $isSchedulerManagerUsed
    ) {
        /* @var $bean \PHPUnit_Framework_MockObject_MockObject|\Call|\SugarBean */
        $bean = $this->getMock('Call');

        $this->mockTriggerClient();
        $this->mockTriggerServerManager();
        $this->mockSchedulerManager();
        $this->mockHookManager(array(
            'getTriggerClient',
            'getTriggerServerManager',
            'getSchedulerManager'
        ));

        $this->triggerClient->method('isConfigured')->willReturn($isTsConfigured);

        $this->triggerServerManager->expects($this->exactly($isTsManagerUsed ? 1 : 0))
            ->method('setReminders');

        $this->schedulerManager->expects($this->exactly($isSchedulerManagerUsed ? 1 : 0))
            ->method('setReminders');

        $this->hookManager->method('getTriggerClient')->willReturn($this->triggerClient);
        $this->hookManager->method('getTriggerServerManager')->willReturn($this->triggerServerManager);
        $this->hookManager->method('getSchedulerManager')->willReturn($this->schedulerManager);


        $this->hookManager->afterCallOrMeetingRestore($bean, 'after_restore', array());
    }

    /**
     * @return array
     */
    public function providerAfterCallOrMeetingRestoreUsesCorrectReminderManager()
    {
        return array(
            array(false, false, true),
            array(true, true, false)
        );
    }

    /**
     * @param string $module
     * @param string $category
     * @param boolean $isTaskCreated
     * @dataProvider providerAfterUserPreferenceSaveCreatesTask
     * @covers ::afterUserPreferenceSave
     */
    public function testAfterUserPreferenceSaveCreatesTask($module, $category, $isTaskCreated)
    {
        $userId = 'dummy-user-id';

        /* @var $bean \PHPUnit_Framework_MockObject_MockObject|\UserPreference|\SugarBean */
        $bean = $this->getMock($module);
        $bean->category = $category;
        $bean->assigned_user_id = $userId;

        $arguments = array('dataChanges' => array('dummy-data-changes'));

        $this->mockJobQueueManager();
        $this->mockHookManager(array('isReminderTimeChanged', 'getJobQueueManager'));

        $this->jobQueueManager->expects($this->exactly($isTaskCreated ? 1 : 0))
            ->method('RecreateUserRemindersJob')
            ->with($userId);

        $this->hookManager->method('isReminderTimeChanged')->willReturn(true);
        $this->hookManager->method('getJobQueueManager')->willReturn($this->jobQueueManager);

        $this->hookManager->afterUserPreferenceSave($bean, 'after_save', $arguments);
    }

    /**
     * @return array
     */
    public function providerAfterUserPreferenceSaveCreatesTask()
    {
        return array(
            'bean isn\t UserPreference' => array('Meeting', 'dummy-category', false),
            'bean is UserPreference and category isn\t "global"' => array(
                'UserPreference',
                'dummy-category',
                false
            ),
            'bean is UserPreference and category is "global"' => array(
                'UserPreference',
                'global',
                true
            )
        );
    }

    /**
     * @param int $before
     * @param int $after
     * @param boolean $isTaskCreated
     * @dataProvider providerAfterUserPreferenceSaveCreatesTaskWhenReminderTimeIsChanged
     * @covers ::afterUserPreferenceSave
     */
    public function testAfterUserPreferenceSaveCreatesTaskWhenReminderTimeIsChanged($before, $after, $isTaskCreated)
    {
        $userId = 'dummy-user-id';

        /* @var $bean \PHPUnit_Framework_MockObject_MockObject|\UserPreference|\SugarBean */
        $bean = $this->getMock('UserPreference');
        $bean->category = 'global';
        $bean->assigned_user_id = $userId;

        $arguments = array(
            'dataChanges' => array(
                'contents' => array(
                    'before' => base64_encode(serialize(array(
                        'reminder_time' => $before
                    ))),
                    'after' => base64_encode(serialize(array(
                        'reminder_time' => $after
                    )))
                )
            )
        );

        $this->mockJobQueueManager();
        $this->mockHookManager(array('getJobQueueManager'));

        $this->jobQueueManager->expects($this->exactly($isTaskCreated ? 1 : 0))
            ->method('RecreateUserRemindersJob')
            ->with($userId);

        $this->hookManager->method('getJobQueueManager')->willReturn($this->jobQueueManager);

        $this->hookManager->afterUserPreferenceSave($bean, 'after_save', $arguments);
    }

    /**
     * @return array
     */
    public function providerAfterUserPreferenceSaveCreatesTaskWhenReminderTimeIsChanged()
    {
        return array(
            'reminder time is same' => array(60, 60, false),
            'reminder time is different' => array(60, 300, true)
        );
    }

    /**
     * @param array $methods
     */
    public function mockHookManager($methods = array())
    {
        $this->hookManager = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Trigger\\HookManager',
            $methods
        );
    }

    /**
     *
     */
    public function mockReminderManager()
    {
        $this->reminderManager = $this
            ->getMockBuilder('Sugarcrm\\Sugarcrm\\Trigger\\ReminderManager\\Base')
            ->setMethods(array('setReminders', 'deleteReminders'))
            ->getMockForAbstractClass();
    }

    /**
     *
     */
    public function mockTriggerClient()
    {
        $this->triggerClient = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Trigger\\Client',
            array('isConfigured')
        );
    }

    /**
     *
     */
    public function mockTriggerServerManager()
    {
        $this->triggerServerManager = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Trigger\\ReminderManager\\TriggerServer',
            array('setReminders', 'deleteReminders')
        );
    }

    /**
     *
     */
    public function mockSchedulerManager()
    {
        $this->schedulerManager = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Trigger\\ReminderManager\\Scheduler',
            array('setReminders', 'deleteReminders')
        );
    }

    /**
     *
     */
    public function mockJobQueueManager()
    {
        $this->jobQueueManager = $this->getMock(
            'Sugarcrm\\Sugarcrm\\JobQueue\\Manager\\Manager',
            array('RecreateUserRemindersJob')
        );
    }
}
