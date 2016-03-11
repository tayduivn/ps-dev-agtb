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

namespace Sugarcrm\SugarcrmTests\Trigger;

use Sugarcrm\Sugarcrm\Trigger\ReminderManager as TriggerReminderManager;
use Sugarcrm\Sugarcrm\JobQueue\Manager\Manager as JobQueueManager;
use Sugarcrm\Sugarcrm\Trigger\Client as TriggerClient;

/**
 * Class HookManagerTest
 *
 * @package Sugarcrm\SugarcrmTests\Trigger
 * @covers Sugarcrm\Sugarcrm\Trigger\HookManager
 */
class HookManagerTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var JobQueueManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $jobQueueManager = null;

    /** @var TriggerReminderManager\TriggerServer|\PHPUnit_Framework_MockObject_MockObject */
    protected $triggerServerManager = null;

    /** @var \Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler|\PHPUnit_Framework_MockObject_MockObject */
    protected $schedulerManager = null;

    /** @var \Sugarcrm\Sugarcrm\Trigger\HookManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $hookManager = null;

    /** @var TriggerClient|\PHPUnit_Framework_MockObject_MockObject $triggerClient */
    protected $triggerClient = null;

    /** @var \User */
    protected $currentUser = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        \BeanFactory::setBeanClass('Users', 'Sugarcrm\SugarcrmTests\Trigger\UserCRYS1307');
        UserCRYS1307::$fetchFromQueryArguments = array();
        UserCRYS1307::$fetchFromQueryReturn = array();
        $this->triggerClient = $this->getMock('Sugarcrm\Sugarcrm\Trigger\Client');
        $this->schedulerManager = $this->getMock('Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler');
        $this->triggerServerManager = $this->getMock('Sugarcrm\Sugarcrm\Trigger\ReminderManager\TriggerServer');

        $this->hookManager = $this->getMock(
            'Sugarcrm\Sugarcrm\Trigger\HookManager',
            array(
                'getTriggerClient',
                'getTriggerServerManager',
                'getSchedulerManager',
                'getJobQueueManager',
            )
        );
        $this->jobQueueManager = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Manager\Manager',
            array('RecreateUserRemindersJob')
        );

        \SugarTestReflection::setProtectedValue('Sugarcrm\Sugarcrm\Trigger\Client', 'instance', $this->triggerClient);
        $this->hookManager->method('getTriggerServerManager')->willReturn($this->triggerServerManager);
        $this->hookManager->method('getSchedulerManager')->willReturn($this->schedulerManager);
        $this->hookManager->method('getJobQueueManager')->willReturn($this->jobQueueManager);

        $this->currentUser = $GLOBALS['current_user'];
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        \BeanFactory::setBeanClass('Users');
        TriggerClient::getInstance(true);
        $GLOBALS['current_user'] = $this->currentUser;
        parent::tearDown();
    }

    /**
     * Data provider for afterCallOrMeetingSaveProvider.
     *
     * @see Sugarcrm\SugarcrmTests\Trigger\testAfterCallOrMeetingSave
     * @return array
     */
    public static function afterCallOrMeetingSaveProvider()
    {
        $users = array(
            0 => array(
                'id' => create_guid(),
                'reminderTime' => 900,
                'datef' => 'Y/m/d',
                'timef' => 'H:i',
                'timezone' => 'Europe/Berlin',
            ),
            1 => array(
                'id' => create_guid(),
                'reminderTime' => 1800,
                'datef' => 'Y/m/d',
                'timef' => 'H:i',
                'timezone' => 'Europe/Berlin',
            ),
            2 => array(
                'id' => create_guid(),
                'reminderTime' => 2400,
                'datef' => 'Y/m/d',
                'timef' => 'H:i',
                'timezone' => 'Europe/Berlin',
            ),
            3 => array(
                'id' => create_guid(),
                'reminderTime' => 0,
                'datef' => 'Y/m/d',
                'timef' => 'H:i',
                'timezone' => 'Europe/Berlin',
            ),
            4 => array(
                'id' => create_guid(),
                'reminderTime' => -1,
                'datef' => 'Y/m/d',
                'timef' => 'H:i',
                'timezone' => 'Europe/Berlin',
            ),
        );
        return array(
            'notCallOrMeetingBean' => array(
                'coveredMethod' => 'afterCallOrMeetingSave',
                'triggerClientConfigured' => false,
                'moduleName' => 'Account',
                'beanUsersData' => array(),
                'beanStartDate' => null,
                'beanAssignedUser' => array(),
                'beanReminderTime' => null,
                'arguments' => array('isUpdate' => true),
                'expectedLoadUsers' => false,
                'expectedDeleteReminders' => false,
                'expectedReminders' => array(),
            ),
            'callBeanUpdate' => array(
                'coveredMethod' => 'afterCallOrMeetingSave',
                'triggerClientConfigured' => false,
                'moduleName' => 'Call',
                'beanUsersData' => $users,
                'beanStartDate' => '2025-12-31 16:00:00',
                'beanAssignedUser' => $users[0]['id'],
                'beanReminderTime' => 1200,
                'arguments' => array('isUpdate' => true),
                'expectedLoadUsers' => true,
                'expectedDeleteReminders' => true,
                'expectedReminders' => array(
                    1 => array(
                        'idUser' => $users[0]['id'],
                        'reminderTime' => '2025-12-31 15:40:00',
                    ),
                    2 => array(
                        'idUser' => $users[1]['id'],
                        'reminderTime' => '2025-12-31 15:40:00',
                    ),
                    3 => array(
                        'idUser' => $users[2]['id'],
                        'reminderTime' => '2025-12-31 15:40:00',
                    ),
                ),
            ),
            'callBeanCreateUserDateFormat' => array(
                'coveredMethod' => 'afterCallOrMeetingSave',
                'triggerClientConfigured' => true,
                'moduleName' => 'Call',
                'beanUsersData' => $users,
                'beanStartDate' => '2025/12/31 16:00',
                'beanAssignedUser' => $users[1]['id'],
                'beanReminderTime' => 1200,
                'arguments' => array('isUpdate' => false),
                'expectedLoadUsers' => true,
                'expectedDeleteReminders' => false,
                'expectedReminders' => array(
                    array(
                        'idUser' => $users[0]['id'],
                        'reminderTime' => '2025-12-31 14:40:00',
                    ),
                    array(
                        'idUser' => $users[1]['id'],
                        'reminderTime' => '2025-12-31 14:40:00',
                    ),
                    array(
                        'idUser' => $users[2]['id'],
                        'reminderTime' => '2025-12-31 14:40:00',
                    ),
                ),
            ),
            'callBeanUpdateInPast' => array(
                'coveredMethod' => 'afterCallOrMeetingSave',
                'triggerClientConfigured' => false,
                'moduleName' => 'Call',
                'beanUsersData' => $users,
                'beanStartDate' =>  '2014-12-01 17:00:00',
                'beanAssignedUser' => $users[0]['id'],
                'beanReminderTime' => 1200,
                'arguments' => array('isUpdate' => true),
                'expectedLoadUsers' => true,
                'expectedDeleteReminders' => true,
                'expectedReminders' => array(),
            ),
            'meetingBeanUpdate' => array(
                'coveredMethod' => 'afterCallOrMeetingSave',
                'triggerClientConfigured' => true,
                'moduleName' => 'Call',
                'beanUsersData' => $users,
                'beanStartDate' => '2025-12-31 17:00:00',
                'beanAssignedUser' => $users[0]['id'],
                'beanReminderTime' => 1200,
                'arguments' => array('isUpdate' => true),
                'expectedLoadUsers' => true,
                'expectedDeleteReminders' => true,
                'expectedReminders' => array(
                    1 => array(
                        'idUser' => $users[0]['id'],
                        'reminderTime' => '2025-12-31 16:40:00',
                    ),
                    2 => array(
                        'idUser' => $users[1]['id'],
                        'reminderTime' => '2025-12-31 16:40:00',
                    ),
                    3 => array(
                        'idUser' => $users[2]['id'],
                        'reminderTime' => '2025-12-31 16:40:00',
                    ),
                ),
            ),
            'meetingBeanCreate' => array(
                'coveredMethod' => 'afterCallOrMeetingSave',
                'triggerClientConfigured' => false,
                'moduleName' => 'Call',
                'beanUsersData' => $users,
                'beanStartDate' => '2025/12/31 14:00',
                'beanAssignedUser' => $users[1]['id'],
                'beanReminderTime' => 1200,
                'arguments' => array('isUpdate' => false),
                'expectedLoadUsers' => true,
                'expectedDeleteReminders' => false,
                'expectedReminders' => array(
                    array(
                        'idUser' => $users[0]['id'],
                        'reminderTime' => '2025-12-31 12:40:00',
                    ),
                    array(
                        'idUser' => $users[1]['id'],
                        'reminderTime' => '2025-12-31 12:40:00',
                    ),
                    array(
                        'idUser' => $users[2]['id'],
                        'reminderTime' => '2025-12-31 12:40:00',
                    ),
                ),
            ),
            'meetingBeanUpdateInPast' => array(
                'coveredMethod' => 'afterCallOrMeetingSave',
                'triggerClientConfigured' => true,
                'moduleName' => 'Call',
                'beanUsersData' => $users,
                'beanStartDate' => '2012/12/12 13:00',
                'beanAssignedUser' => $users[0]['id'],
                'beanReminderTime' => 1200,
                'arguments' => array('isUpdate' => true),
                'expectedLoadUsers' => true,
                'expectedDeleteReminders' => true,
                'expectedReminders' => array(),
            ),
            'notCallOrMeetingBeanRestore' => array(
                'coveredMethod' => 'afterCallOrMeetingRestore',
                'triggerClientConfigured' => false,
                'moduleName' => 'Account',
                'beanUsersData' => array(),
                'beanStartDate' => null,
                'beanAssignedUser' => array(),
                'beanReminderTime' => null,
                'arguments' => array('isUpdate' => true),
                'expectedLoadUsers' => false,
                'expectedDeleteReminders' => false,
                'expectedReminders' => array(),
            ),
            'callBeanUpdateRestore' => array(
                'coveredMethod' => 'afterCallOrMeetingRestore',
                'triggerClientConfigured' => true,
                'moduleName' => 'Call',
                'beanUsersData' => $users,
                'beanStartDate' => '2025/12/31 14:00',
                'beanAssignedUser' => $users[0]['id'],
                'beanReminderTime' => 1200,
                'arguments' => array('isUpdate' => true),
                'expectedLoadUsers' => true,
                'expectedDeleteReminders' => false,
                'expectedReminders' => array(
                    array(
                        'idUser' => $users[0]['id'],
                        'reminderTime' => '2025-12-31 12:40:00',
                    ),
                    array(
                        'idUser' => $users[1]['id'],
                        'reminderTime' => '2025-12-31 12:40:00',
                    ),
                    array(
                        'idUser' => $users[2]['id'],
                        'reminderTime' => '2025-12-31 12:40:00',
                    ),
                ),
            ),
            'callBeanCreateRestore' => array(
                'coveredMethod' => 'afterCallOrMeetingRestore',
                'triggerClientConfigured' => false,
                'moduleName' => 'Call',
                'beanUsersData' => $users,
                'beanStartDate' => '2025-12-31 13:00:00',
                'beanAssignedUser' => $users[1]['id'],
                'beanReminderTime' => 1200,
                'arguments' => array('isUpdate' => false),
                'expectedLoadUsers' => true,
                'expectedDeleteReminders' => false,
                'expectedReminders' => array(
                    array(
                        'idUser' => $users[0]['id'],
                        'reminderTime' => '2025-12-31 12:40:00',
                    ),
                    array(
                        'idUser' => $users[1]['id'],
                        'reminderTime' => '2025-12-31 12:40:00',
                    ),
                    array(
                        'idUser' => $users[2]['id'],
                        'reminderTime' => '2025-12-31 12:40:00',
                    ),
                ),
            ),
            'callBeanUpdateInPastRestore' => array(
                'coveredMethod' => 'afterCallOrMeetingRestore',
                'triggerClientConfigured' => true,
                'moduleName' => 'Call',
                'beanUsersData' => $users,
                'beanStartDate' => '2012-12-10 11:00:00',
                'beanAssignedUser' => $users[0]['id'],
                'beanReminderTime' => 1200,
                'arguments' => array('isUpdate' => true),
                'expectedLoadUsers' => true,
                'expectedDeleteReminders' => false,
                'expectedReminders' => array(),
            ),
            'meetingBeanUpdateRestore' => array(
                'coveredMethod' => 'afterCallOrMeetingRestore',
                'triggerClientConfigured' => false,
                'moduleName' => 'Call',
                'beanUsersData' => $users,
                'beanStartDate' => '2025-10-15 16:00:00',
                'beanAssignedUser' => $users[0]['id'],
                'beanReminderTime' => 1200,
                'arguments' => array('isUpdate' => true),
                'expectedLoadUsers' => true,
                'expectedDeleteReminders' => false,
                'expectedReminders' => array(
                    array(
                        'idUser' => $users[0]['id'],
                        'reminderTime' => '2025-10-15 15:40:00',
                    ),
                    array(
                        'idUser' => $users[1]['id'],
                        'reminderTime' => '2025-10-15 15:40:00',
                    ),
                    array(
                        'idUser' => $users[2]['id'],
                        'reminderTime' => '2025-10-15 15:40:00',
                    ),
                ),
            ),
            'meetingBeanCreateRestore' => array(
                'coveredMethod' => 'afterCallOrMeetingRestore',
                'triggerClientConfigured' => false,
                'moduleName' => 'Call',
                'beanUsersData' => $users,
                'beanStartDate' => '2025/08/15 16:00',
                'beanAssignedUser' => $users[1]['id'],
                'beanReminderTime' => 1200,
                'arguments' => array('isUpdate' => false),
                'expectedLoadUsers' => true,
                'expectedDeleteReminders' => false,
                'expectedReminders' => array(
                    array(
                        'idUser' => $users[0]['id'],
                        'reminderTime' => '2025-08-15 13:40:00',
                    ),
                    array(
                        'idUser' => $users[1]['id'],
                        'reminderTime' => '2025-08-15 13:40:00',
                    ),
                    array(
                        'idUser' => $users[2]['id'],
                        'reminderTime' => '2025-08-15 13:40:00',
                    ),
                ),
            ),
            'meetingBeanUpdateInPastRestore' => array(
                'coveredMethod' => 'afterCallOrMeetingRestore',
                'triggerClientConfigured' => true,
                'moduleName' => 'Call',
                'beanUsersData' => $users,
                'beanStartDate' => '2012-08-16 12:00:00',
                'beanAssignedUser' => $users[0]['id'],
                'beanReminderTime' => 1200,
                'arguments' => array('isUpdate' => true),
                'expectedLoadUsers' => true,
                'expectedDeleteReminders' => false,
                'expectedReminders' => array(),
            ),
        );
    }

    /**
     * Should not do nothing if bean is not Call or Meeting.
     * If bean is creation should create reminders for each user.
     * If bean is creation should create reminders for each user and delete old reminders.
     * If bean in past should not do nothing.
     * In restore event should only create reminders without delete old.
     *
     * @dataProvider afterCallOrMeetingSaveProvider
     * @covers Sugarcrm\Sugarcrm\Trigger\HookManager::afterCallOrMeetingSave
     * @covers Sugarcrm\Sugarcrm\Trigger\HookManager::afterCallOrMeetingRestore
     * @param string $coveredMethod
     * @param bool $triggerClientConfigured
     * @param string $moduleName
     * @param array $beanUsersData
     * @param array $beanStartDate
     * @param string $beanAssignedUser
     * @param string $beanReminderTime
     * @param array $arguments
     * @param bool $expectedLoadUsers
     * @param bool $expectedDeleteReminders
     * @param array $expectedReminders
     */
    public function testAfterCallOrMeetingSave(
        $coveredMethod,
        $triggerClientConfigured,
        $moduleName,
        $beanUsersData,
        $beanStartDate,
        $beanAssignedUser,
        $beanReminderTime,
        $arguments,
        $expectedLoadUsers,
        $expectedDeleteReminders,
        $expectedReminders
    ) {
        $expectedFetchedQueryArguments = array();
        $this->triggerClient->method('isConfigured')->willReturn($triggerClientConfigured);
        if ($triggerClientConfigured) {
            $reminderManager = $this->triggerServerManager;
            $this->schedulerManager->expects($this->never())->method('deleteReminders');
            $this->schedulerManager->expects($this->never())->method('addReminderForUser');
        } else {
            $reminderManager = $this->schedulerManager;
            $this->triggerServerManager->expects($this->never())->method('deleteReminders');
            $this->triggerServerManager->expects($this->never())->method('addReminderForUser');
        }

        /** @var \Call|\Meeting|\SugarBean|\PHPUnit_Framework_MockObject_MockObject $bean */
        $bean = $this->getMock($moduleName);
        if ($beanStartDate) {
            $bean->date_start = $beanStartDate;
        }
        if ($beanReminderTime) {
            $bean->reminder_time = $beanReminderTime;
        }

        $beanUsers = array();

        foreach ($beanUsersData as $userData) {
            /** @var \User|\PHPUnit_Framework_MockObject_MockObject $user */
            $user = $this->getMock('User');
            $user->id = $userData['id'];

            $user->method('getPreference')
                ->willReturnMap(array(
                    array('reminder_time', 'global', $userData['reminderTime']),
                    array('datef', 'global', $userData['datef']),
                    array('timef', 'global', $userData['timef']),
                    array('timezone', 'global', $userData['timezone']),
                ));
            $beanUsers[$user->id] = $user;
            $bean->users_arr[] = $user->id;
        }

        if ($beanAssignedUser) {
            $bean->assigned_user_id = $beanAssignedUser;
            $GLOBALS['current_user'] = $beanUsers[$beanAssignedUser];
        }

        if ($expectedLoadUsers) {
            UserCRYS1307::$fetchFromQueryReturn = $beanUsers;
            $usersBean = \BeanFactory::getBean('Users');
            $expectedQuery = new \SugarQuery();
            $expectedQuery->from($usersBean);
            $expectedQuery->where()->in('id', array_keys($beanUsers));
            $expectedFetchedQueryArguments = array($expectedQuery);
        }
        if (!$expectedDeleteReminders) {
            $reminderManager->expects($this->never())->method('deleteReminders');
        } else {
            $reminderManager->expects($this->once())
                ->method('deleteReminders')
                ->with($bean);
        }

        if (!$expectedReminders) {
            $reminderManager->expects($this->never())->method('addReminderForUser');
        } else {
            foreach ($expectedReminders as $index => $remindersData) {
                $expectedReminder = new \DateTime($remindersData['reminderTime'], new \DateTimeZone('UTC'));

                $reminderManager->expects($this->at($index))
                    ->method('addReminderForUser')
                    ->with($bean, $beanUsers[$remindersData['idUser']], $expectedReminder);
            }
        }

        $this->hookManager->$coveredMethod($bean, 'update' . rand(1000, 1999), $arguments);
        $this->assertEquals($expectedFetchedQueryArguments, UserCRYS1307::$fetchFromQueryArguments);
    }

    /**
     * Data provider for testAfterCallOrMeetingDelete.
     *
     * @see Sugarcrm\SugarcrmTests\Trigger\testAfterCallOrMeetingDelete
     * @return array
     */
    public static function afterCallOrMeetingDeleteProvider()
    {
        return array(
            'notMeetingOrCallAndTriggerConfigured' => array(
                'beanName' => 'Account',
                'triggerClientConfigured' => true,
                'expectedDelete' => false,
            ),
            'notMeetingOrCallAndTriggerNotConfigured' => array(
                'beanName' => 'Account',
                'triggerClientConfigured' => false,
                'expectedDelete' => false,
            ),
            'meetingBeanAndTriggerConfigured' => array(
                'beanName' => 'Meeting',
                'triggerClientConfigured' => true,
                'expectedDelete' => true,
            ),
            'meetingBeanAndTriggerNotConfigured' => array(
                'beanName' => 'Meeting',
                'triggerClientConfigured' => false,
                'expectedDelete' => true,
            ),
            'callBeanAndTriggerConfigured' => array(
                'beanName' => 'Call',
                'triggerClientConfigured' => true,
                'expectedDelete' => true,
            ),
            'callBeanAndTriggerNotConfigured' => array(
                'beanName' => 'Call',
                'triggerClientConfigured' => false,
                'expectedDelete' => true,
            ),
        );
    }

    /**
     * Should not do nothing if bean is not Call or Meeting otherwise deletes reminders for provided bean
     *
     * @dataProvider afterCallOrMeetingDeleteProvider
     * @covers Sugarcrm\Sugarcrm\Trigger\HookManager::afterCallOrMeetingDelete
     * @param string $beanName
     * @param bool $triggerClientConfigured
     * @param bool $expectedDelete
     */
    public function testAfterCallOrMeetingDelete($beanName, $triggerClientConfigured, $expectedDelete)
    {
        $this->triggerClient->method('isConfigured')->willReturn($triggerClientConfigured);
        if ($triggerClientConfigured) {
            $reminderManager = $this->triggerServerManager;
            $this->schedulerManager->expects($this->never())->method('deleteReminders');
        } else {
            $reminderManager = $this->schedulerManager;
            $this->triggerServerManager->expects($this->never())->method('deleteReminders');
        }

        /** @var \Call|\Meeting|\SugarBean|\PHPUnit_Framework_MockObject_MockObject $bean */
        $bean = $this->getMock($beanName);
        if ($expectedDelete) {
            $reminderManager->expects($this->once())
                ->method('deleteReminders')
                ->with($bean);
        } else {
            $reminderManager->expects($this->never())
                ->method('deleteReminders');
        }
        $this->hookManager->afterCallOrMeetingDelete($bean, 'update ' . rand(1000, 1999), array(rand(2000, 2999)));
    }

    /**
     * Data provider for testAfterUserPreferenceSave.
     *
     * @see Sugarcrm\SugarcrmTests\Trigger\testAfterUserPreferenceSave
     * @return array
     */
    public static function afterUserPreferenceSaveProvider()
    {
        return array(
            'notUserPreference' => array(
                'beanName' => 'Call',
                'beanCategory' => 'global',
                'arguments' => array(
                    'dataChanges' => array(
                        'contents' => array(
                            'before' => array(
                                'reminder_time' => base64_encode(serialize(900)),
                            ),
                            'after' => array(
                                'reminder_time' => base64_encode(serialize(1200)),
                            ),
                        ),
                    ),
                ),
                'expectedRecreateReminder' => false,
            ),
            'userPreferenceChangeReminderTime' => array(
                'beanName' => 'UserPreference',
                'beanCategory' => 'global',
                'arguments' => array(
                    'dataChanges' => array(
                        'contents' => array(
                            'before' => base64_encode(
                                serialize(array('reminder_time' => 900))
                            ),
                            'after' => base64_encode(
                                serialize(array('reminder_time' => 1200))
                            ),
                        ),
                    ),
                ),
                'expectedRecreateReminder' => true,
            ),
            'userPreferenceChangeReminderTimeNotGlobal' => array(
                'beanName' => 'UserPreference',
                'beanCategory' => 'local',
                'arguments' => array(
                    'dataChanges' => array(
                        'contents' => array(
                            'before' => base64_encode(
                                serialize(array('reminder_time' => 900))
                            ),
                            'after' => base64_encode(
                                serialize(array('reminder_time' => 1200))
                            ),
                        ),
                    ),
                ),
                'expectedRecreateReminder' => false,
            ),
            'userPreferenceNotChangeReminderTime' => array(
                'beanName' => 'UserPreference',
                'beanCategory' => 'global',
                'arguments' => array(
                    'dataChanges' => array(
                        'contents' => array(
                            'before' => base64_encode(
                                serialize(array('reminder_time' => 900))
                            ),
                            'after' => base64_encode(
                                serialize(array('reminder_time' => 900))
                            ),
                        ),
                    ),
                ),
                'expectedRecreateReminder' => false,
            ),
            'userPreferenceDataChangeEmpty' => array(
                'beanName' => 'UserPreference',
                'beanCategory' => 'global',
                'arguments' => array(
                    'dataChanges' => array(),
                ),
                'expectedRecreateReminder' => false,
            ),
            'userPreferenceChangeReminderTimeToZero' => array(
                'beanName' => 'UserPreference',
                'beanCategory' => 'global',
                'arguments' => array(
                    'dataChanges' => array(
                        'contents' => array(
                            'before' => base64_encode(
                                serialize(array('reminder_time' => 900))
                            ),
                            'after' => base64_encode(
                                serialize(array('reminder_time' => 0))
                            ),
                        ),
                    ),
                ),
                'expectedRecreateReminder' => true,
            ),
            'userPreferenceChangeReminderTimeToWrong' => array(
                'beanName' => 'UserPreference',
                'beanCategory' => 'global',
                'arguments' => array(
                    'dataChanges' => array(
                        'contents' => array(
                            'before' => base64_encode(
                                serialize(array('reminder_time' => 900))
                            ),
                            'after' => base64_encode(
                                serialize(array('reminder_time' => -1))
                            ),
                        ),
                    ),
                ),
                'expectedRecreateReminder' => true,
            ),
        );
    }

    /**
     * Should ask manager to recreate reminders for user if reminder was changed. Otherwise does nothing.
     *
     * @dataProvider afterUserPreferenceSaveProvider
     * @covers Sugarcrm\Sugarcrm\Trigger\HookManager::afterUserPreferenceSave
     * @param string $beanName
     * @param string $beanCategory
     * @param array $arguments
     * @param bool $expectedRecreateReminder
     */
    public function testAfterUserPreferenceSave($beanName, $beanCategory, $arguments, $expectedRecreateReminder)
    {
        $userId = create_guid();
        /** @var \SugarBean|\PHPUnit_Framework_MockObject_MockObject $bean */
        $bean = $this->getMock($beanName);
        $bean->category = $beanCategory;
        $bean->assigned_user_id = $userId;

        if ($expectedRecreateReminder) {
            $this->jobQueueManager->expects($this->once())
                ->method('RecreateUserRemindersJob')
                ->with($userId);
        } else {
            $this->jobQueueManager->expects($this->never())
                ->method('RecreateUserRemindersJob');
        }

        $this->hookManager->afterUserPreferenceSave($bean, 'update' . rand(1000, 1999), $arguments);
    }
}

/**
 * Class UserCRYS1307
 * @package Sugarcrm\SugarcrmTests\Trigger
 */
class UserCRYS1307 extends \User
{
    /** @var array */
    public static $fetchFromQueryReturn = array();

    /** @var array */
    public static $fetchFromQueryArguments = array();

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->emailAddress = array();
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function fetchFromQuery()
    {
        static::$fetchFromQueryArguments = func_get_args();
        return static::$fetchFromQueryReturn;
    }
}
