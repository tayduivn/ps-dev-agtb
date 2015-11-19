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

namespace Sugarcrm\SugarcrmTestsUnit\Trigger;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\Trigger\HookManager;

/**
 * Class HookManagerTest
 * @package Sugarcrm\SugarcrmTestsUnit\Trigger
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Trigger\HookManager
 */
class HookManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $bean
     * @param boolean $setRemindersCalled
     * @dataProvider providerAfterCallOrMeetingSave
     * @covers ::afterCallOrMeetingSave
     */
    public function testAfterCallOrMeetingSave($bean, $setRemindersCalled)
    {
        $isUpdate = false;

        $reminderManager = $this->getBaseReminderManagerMock(array('setReminders'));
        $reminderManager
            ->expects($this->exactly($setRemindersCalled ? 1 : 0))
            ->method('setReminders')
            ->with($bean, $isUpdate);

        $hookManager = $this->getHookManagerMock(array('getReminderManager'));
        $hookManager->method('getReminderManager')->willReturn($reminderManager);
        $hookManager->afterCallOrMeetingSave($bean, 'after_save',
            array('isUpdate' => $isUpdate, 'dataChanges' => array()));
    }

    /**
     * (bean, is Base::setReminders called)
     * @return array
     */
    public function providerAfterCallOrMeetingSave()
    {
        return array(
            'bean is not Call or Meeting' => array($this->getBeanMock('Account'), false),
            'bean is Call' => array($this->getBeanMock('Call'), true),
            'bean is Meeting' => array($this->getBeanMock('Meeting'), true)
        );
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $bean
     * @param boolean $deleteRemindersCalled
     * @dataProvider providerAfterCallOrMeetingDelete
     * @covers ::afterCallOrMeetingDelete
     */
    public function testAfterCallOrMeetingDelete($bean, $deleteRemindersCalled)
    {
        $reminderManager = $this->getBaseReminderManagerMock(array('deleteReminders'));
        $reminderManager
            ->expects($this->exactly($deleteRemindersCalled ? 1 : 0))
            ->method('deleteReminders')
            ->with($bean);

        $hookManager = $this->getHookManagerMock(array('getReminderManager'));
        $hookManager->method('getReminderManager')->willReturn($reminderManager);

        $hookManager->afterCallOrMeetingDelete($bean, 'after_delete', array('id' => 'dummy-id'));
    }

    /**
     * (bean, is Base::deleteReminders called)
     * @return array
     */
    public function providerAfterCallOrMeetingDelete()
    {
        return array(
            'bean is not Call or Meeting' => array($this->getBeanMock('Account'), false),
            'bean is Call' => array($this->getBeanMock('Call'), true),
            'bean is Meeting' => array($this->getBeanMock('Meeting'), true)
        );
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $bean
     * @param boolean $setRemindersCalled
     * @dataProvider providerAfterCallOrMeetingRestore
     * @covers ::afterCallOrMeetingRestore
     */
    public function testAfterCallOrMeetingRestore($bean, $setRemindersCalled)
    {
        $reminderManager = $this->getBaseReminderManagerMock(array('setReminders'));
        $reminderManager
            ->expects($this->exactly($setRemindersCalled ? 1 : 0))
            ->method('setReminders')
            ->with($bean, false);

        $hookManager = $this->getHookManagerMock(array('getReminderManager'));
        $hookManager->method('getReminderManager')->willReturn($reminderManager);
        $hookManager->afterCallOrMeetingRestore($bean, 'after_restore', array('id' => 'dummy-id'));
    }

    /**
     * (bean, is Base::setReminders called)
     * @return array
     */
    public function providerAfterCallOrMeetingRestore()
    {
        return array(
            'bean is not Call or Meeting' => array($this->getBeanMock('Account'), false),
            'bean is Call' => array($this->getBeanMock('Call'), true),
            'bean is Meeting' => array($this->getBeanMock('Meeting'), true)
        );
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $bean
     * @param boolean $isReminderTimeChanged
     * @param boolean $submitJobCalled
     * @dataProvider providerAfterUserPreferenceSave
     * @covers ::afterUserPreferenceSave
     */
    public function testAfterUserPreferenceSave($bean, $isReminderTimeChanged, $submitJobCalled)
    {
        $userId = 'dummy-user-id';

        $hookManager = $this->getHookManagerMock(array(
            'isReminderTimeChanged',
            'submitRecreateUserRemindersJob'
        ));

        $hookManager->method('isReminderTimeChanged')->willReturn($isReminderTimeChanged);

        $hookManager->expects($this->exactly($submitJobCalled ? 1 : 0))
            ->method('submitRecreateUserRemindersJob')
            ->with($userId);

        $hookManager->afterUserPreferenceSave($bean, 'after_save',
            array('isUpdate' => true, 'dataChanges' => array()));
    }

    /**
     * (call or meeting bean, is reminder_time was changed, is ::submitRecreateUserRemindersJob called)
     * @return array
     */
    public function providerAfterUserPreferenceSave()
    {
        $userId = 'dummy-user-id';
        return array(
            'bean is not UserPreference' => array(
                $this->getBeanMock('Account'), false, false
            ),
            'bean category is not global' => array(
                $this->getBeanMock('UserPreference', array(
                        'category' => 'dummy category',
                        'assigned_user_id' => $userId)
                ), false, false
            ),
            'reminder_time was not changed' => array(
                $this->getBeanMock('UserPreference', array(
                    'category' => 'global',
                    'assigned_user_id' => $userId
                )), false, false
            ),
            'reminder_time was changed' => array(
                $this->getBeanMock('UserPreference', array(
                    'category' => 'global',
                    'assigned_user_id' => $userId
                )), true, true
            )
        );
    }

    /**
     * @covers ::submitRecreateUserRemindersJob
     */
    public function testSubmitRecreateUserRemindersJob()
    {
        $userId = 'dummy-user-id';

        $manager = $this->getSugarMock('Sugarcrm\\Sugarcrm\\JobQueue\\Manager\\Manager', array(
            'RecreateUserRemindersJob'
        ));

        $manager->expects($this->once())
            ->method('RecreateUserRemindersJob')
            ->with($userId);

        $hookManager = $this->getHookManagerMock(array('getJobQueueManager'));
        $hookManager->method('getJobQueueManager')->willReturn($manager);

        TestReflection::callProtectedMethod($hookManager, 'submitRecreateUserRemindersJob', array($userId));
    }

    /**
     * @param array $dataChanges
     * @param array $decodePreferencesMap
     * @param boolean $expected
     * @dataProvider providerIsReminderTimeChanged
     * @covers ::isReminderTimeChanged
     */
    public function testIsReminderTimeChanged($dataChanges, $decodePreferencesMap, $expected)
    {
        $hookManager = $this->getHookManagerMock(array('decodePreferences'));
        $hookManager->method('decodePreferences')
            ->will($this->returnValueMap($decodePreferencesMap));

        $this->assertEquals($expected,
            TestReflection::callProtectedMethod($hookManager, 'isReminderTimeChanged', array($dataChanges)));
    }

    /**
     * ($dataChanges array, ::decodePreferences return values map, expected result)
     * @return array
     */
    public function providerIsReminderTimeChanged()
    {
        return array(
            '"contents" is not set' => array(
                array(), array(), false
            ),
            '["contents"]["before"] is not array' => array(
                array(
                    'contents' => array(
                        'before' => 'contents_before',
                        'after' => 'contents_after'
                    )
                ), array(
                    array('contents_before', 'dummy string'),
                    array('contents_after', 'dummy string')
                ), false
            ),
            '["contents"]["after"] is not array' => array(
                array(
                    'contents' => array(
                        'before' => 'contents_before',
                        'after' => 'contents_after'
                    )
                ), array(
                    array('contents_before', array()),
                    array('contents_after', 'dummy string')
                ), false
            ),
            '["contents"]["before"]["reminder_time"] is not set' => array(
                array(
                    'contents' => array(
                        'before' => 'contents_before',
                        'after' => 'contents_after'
                    )
                ), array(
                    array('contents_before', array()),
                    array('contents_after', array())
                ), false
            ),
            '["contents"]["after"]["reminder_time"] is not set' => array(
                array(
                    'contents' => array(
                        'before' => 'contents_before',
                        'after' => 'contents_after'
                    )
                ), array(
                    array('contents_before', array('reminder_time' => 1)),
                    array('contents_after', array())
                ), false
            ),
            '["contents"]["before"]["reminder_time"] is equals ["contents"]["after"]["reminder_time"]' => array(
                array(
                    'contents' => array(
                        'before' => 'contents_before',
                        'after' => 'contents_after'
                    )
                ), array(
                    array('contents_before', array('reminder_time' => 1)),
                    array('contents_after', array('reminder_time' => 1))
                ), false
            ),
            '["contents"]["before"]["reminder_time"] is not equals ["contents"]["after"]["reminder_time"]' => array(
                array(
                    'contents' => array(
                        'before' => 'contents_before',
                        'after' => 'contents_after'
                    )
                ), array(
                    array('contents_before', array('reminder_time' => 1)),
                    array('contents_after', array('reminder_time' => 2))
                ), true
            )
        );
    }

    /**
     * @covers ::getTriggerClient
     */
    public function testGetTriggerClient()
    {
        $hookManager = $this->getHookManagerMock();
        $this->assertInstanceOf('\Sugarcrm\Sugarcrm\Trigger\Client',
            TestReflection::callProtectedMethod($hookManager, 'getTriggerClient'));
    }

    /**
     * @param boolean $isTsConfigured
     * @param boolean $getTSManagerCalled
     * @param boolean $getSchedulerManagerCalled
     * @dataProvider providerGetReminderManager
     * @covers ::getReminderManager
     */
    public function testGetReminderManager($isTsConfigured, $getTSManagerCalled, $getSchedulerManagerCalled, $expected)
    {
        $triggerClient = $this->getTriggerClientMock(array('isConfigured'));
        $triggerClient->method('isConfigured')->willReturn($isTsConfigured);

        $hookManager = $this->getHookManagerMock(array(
            'getTriggerClient',
            'getTriggerServerManager',
            'getSchedulerManager'
        ));

        $hookManager->method('getTriggerClient')->willReturn($triggerClient);

        $hookManager->expects($this->exactly($getTSManagerCalled ? 1 : 0))
            ->method('getTriggerServerManager')
            ->willReturn('TriggerServer');

        $hookManager->expects($this->exactly($getSchedulerManagerCalled ? 1 : 0))
            ->method('getSchedulerManager')
            ->willReturn('Scheduler');

        $this->assertEquals($expected,
            TestReflection::callProtectedMethod($hookManager, 'getReminderManager'));
    }

    /**
     * (is trigger server configured,
     *   is ::getTriggerServerManager() called,
     *   is ::getSchedulerManager() called,
     *   expected returned value)
     * @return array
     */
    public function providerGetReminderManager()
    {
        return array(
            'trigger server is configured' => array(true, true, false, 'TriggerServer'),
            'trigger server is not configured' => array(false, false, true, 'Scheduler')
        );
    }

    /**
     * @param string $className
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getSugarMock($className, $methods = null)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param string $className
     * @param array $properties
     * @param array|null $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getBeanMock($className, $properties = array(), $methods = null)
    {
        $bean = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        $bean->object_name = $className;

        foreach ($properties as $name => $value) {
            $bean->$name = $value;
        }
        return $bean;
    }

    /**
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getHookManagerMock($methods = array())
    {
        return $this->getMockBuilder('Sugarcrm\\Sugarcrm\\Trigger\\HookManager')
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getBaseReminderManagerMock($methods = array())
    {
        return $this->getMockBuilder('Sugarcrm\\Sugarcrm\\Trigger\\ReminderManager\\Base')
            ->setMethods($methods)
            ->getMockForAbstractClass();
    }

    /**
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getTriggerClientMock($methods = array())
    {
        return $this->getMockBuilder('Sugarcrm\\Sugarcrm\\Trigger\\Client')
            ->setMethods($methods)
            ->getMock();
    }
}
