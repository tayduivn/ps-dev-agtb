<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Customer_Center/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTests\Trigger\Job;

use Sugarcrm\Sugarcrm\Trigger\Job\RecreateUserRemindersJob;
use Sugarcrm\Sugarcrm\Trigger\ReminderManager as TriggerReminderManager;
use Sugarcrm\Sugarcrm\Trigger\Client as TriggerClient;

/**
 * Class RecreateUserRemindersJobTest
 *
 * @package Sugarcrm\SugarcrmTests\Trigger\Job
 * @covers Sugarcrm\Sugarcrm\Trigger\Job\RecreateUserRemindersJob
 */
class RecreateUserRemindersJobTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|RecreateUserRemindersJob */
    protected $recreateUserRemindersJob = null;

    /** @var TriggerReminderManager\TriggerServer|\PHPUnit_Framework_MockObject_MockObject */
    protected $triggerServerManager = null;

    /** @var TriggerClient|\PHPUnit_Framework_MockObject_MockObject $triggerClient */
    protected $triggerClient = null;

    /** @var \Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler|\PHPUnit_Framework_MockObject_MockObject */
    protected $schedulerManager = null;

    /** @var \User */
    protected $defaultCurrentUser = null;

    /** @var \User */
    protected $currentUser = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        \BeanFactory::setBeanClass('Users', 'Sugarcrm\SugarcrmTests\Trigger\Job\UserCRYS1302');
        \BeanFactory::setBeanClass('Calls', 'Sugarcrm\SugarcrmTests\Trigger\Job\CallCRYS1302');
        \BeanFactory::setBeanClass('Meetings', 'Sugarcrm\SugarcrmTests\Trigger\Job\MeetingCRYS1302');

        $this->currentUser = new UserCRYS1302();
        $this->currentUser->id = create_guid();
        $this->currentUser->setPreference('reminder_time', 900);
        $this->currentUser->setPreference('datef', 'Y/m/d');
        $this->currentUser->setPreference('timef', 'H:i');
        UserCRYS1302::$fetchedUsers[$this->currentUser->id] = $this->currentUser;


        $this->triggerClient = $this->getMock('Sugarcrm\Sugarcrm\Trigger\Client');
        $this->schedulerManager = $this->getMock('Sugarcrm\Sugarcrm\Trigger\ReminderManager\Scheduler');
        $this->triggerServerManager = $this->getMock('Sugarcrm\Sugarcrm\Trigger\ReminderManager\TriggerServer');
        $this->recreateUserRemindersJob = $this->getMock(
            'Sugarcrm\Sugarcrm\Trigger\Job\RecreateUserRemindersJob',
            array(
                'getTriggerClient',
                'getSchedulerManager',
                'getTriggerServerManager',
            ),
            array($this->currentUser->id)
        );

        $this->recreateUserRemindersJob->method('getTriggerClient')->willReturn($this->triggerClient);
        $this->recreateUserRemindersJob->method('getTriggerServerManager')->willReturn($this->triggerServerManager);
        $this->recreateUserRemindersJob->method('getSchedulerManager')->willReturn($this->schedulerManager);

        $this->defaultCurrentUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = $this->currentUser;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        \BeanFactory::setBeanClass('Users');
        \BeanFactory::setBeanClass('Calls');
        \BeanFactory::setBeanClass('Meetings');
        $GLOBALS['current_user'] = $this->defaultCurrentUser;
        UserCRYS1302::$fetchedUsers = array();
        CallCRYS1302::$fetchFromQueryReturn = array();
        CallCRYS1302::$fetchFromQueryArguments = array();
        MeetingCRYS1302::$fetchFromQueryReturn = array();
        MeetingCRYS1302::$fetchFromQueryArguments = array();
        parent::tearDown();
    }

    /**
     * Data provider for testRun.
     *
     * @see Sugarcrm\SugarcrmTests\Trigger\Job\RecreateUserRemindersJobTest::testRun
     * @return array
     */
    public static function runProvider()
    {
        $callsId = array(
            0 => create_guid(),
            1 => create_guid(),
            2 => create_guid(),
            3 => create_guid(),
        );

        $meetingsId = array(
            0 => create_guid(),
            1 => create_guid(),
            2 => create_guid(),
            3 => create_guid(),
        );

        return array(
            'triggerServerConfiguredBerlin' => array(
                'currentUserTimeZone' => 'Europe/Berlin',
                'calls' => array(
                    'featureCallInUTC' => array(
                        'id' => $callsId[0],
                        'date_start' => '2025-12-31 16:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                    'featureCallInUserFormatAndBerlinTimezone' => array(
                        'id' => $callsId[1],
                        'date_start' => '2025/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => create_guid(),
                    ),
                    'pastCallInUTC' => array(
                        'id' => $callsId[2],
                        'date_start' => '2011-12-31 16:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                    'pastCallInUserFormatAndBerlinTimezone' => array(
                        'id' => $callsId[3],
                        'date_start' => '2011/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                ),
                'meetings' => array(
                    'featureMeetingInUTC' => array(
                        'id' => $meetingsId[0],
                        'date_start' => '2025-12-31 15:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => create_guid()
                    ),
                    'featureMeetingInUserFormatAndBerlinTimezone' => array(
                        'id' => $meetingsId[1],
                        'date_start' => '2025/12/31 15:00',
                        'reminder_time' => '1800',
                        'assigned_user_id' => 0,
                    ),
                    'pastMeetingInUTC' => array(
                        'id' => $meetingsId[2],
                        'date_start' => '2010-12-31 15:00:00',
                        'reminder_time' => '1800',
                        'assigned_user_id' => 0,
                    ),
                    'pastMeetingInUserFormatAndBerlinTimezone' => array(
                        'id' => $meetingsId[3],
                        'date_start' => '2011/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                ),
                'isTriggerConfigured' => true,
                'expectedReminders' => array(
                    1 => array(
                        'beanName' => 'Call',
                        'id' => $callsId[0],
                        'reminderTime' => '2025-12-31 15:40:00',
                    ),
                    2 => array(
                        'beanName' => 'Call',
                        'id' => $callsId[1],
                        'reminderTime' => '2025-12-31 14:45:00',
                    ),
                    3 => array(
                        'beanName' => 'Meeting',
                        'id' => $meetingsId[0],
                        'reminderTime' => '2025-12-31 14:45:00',
                    ),
                    4 => array(
                        'beanName' => 'Meeting',
                        'id' => $meetingsId[1],
                        'reminderTime' => '2025-12-31 13:30:00',
                    ),
                ),
            ),
            'triggerServerNotConfiguredBerlin' => array(
                'currentUserTimeZone' => 'Europe/Berlin',
                'calls' => array(
                    'featureCallInUTC' => array(
                        'id' => $callsId[0],
                        'date_start' => '2025-12-31 16:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                    'featureCallInUserFormatAndBerlinTimezone' => array(
                        'id' => $callsId[1],
                        'date_start' => '2025/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => create_guid(),
                    ),
                    'pastCallInUTC' => array(
                        'id' => $callsId[2],
                        'date_start' => '2011-12-31 16:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                    'pastCallInUserFormatAndBerlinTimezone' => array(
                        'id' => $callsId[3],
                        'date_start' => '2011/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                ),
                'meetings' => array(
                    'featureMeetingInUTC' => array(
                        'id' => $meetingsId[0],
                        'date_start' => '2025-12-31 15:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => create_guid()
                    ),
                    'featureMeetingInUserFormatAndBerlinTimezone' => array(
                        'id' => $meetingsId[1],
                        'date_start' => '2025/12/31 15:00',
                        'reminder_time' => '1800',
                        'assigned_user_id' => 0,
                    ),
                    'pastMeetingInUTC' => array(
                        'id' => $meetingsId[2],
                        'date_start' => '2010-12-31 15:00:00',
                        'reminder_time' => '1800',
                        'assigned_user_id' => 0,
                    ),
                    'pastMeetingInUserFormatAndBerlinTimezone' => array(
                        'id' => $meetingsId[3],
                        'date_start' => '2011/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                ),
                'isTriggerConfigured' => false,
                'expectedReminders' => array(
                    1 => array(
                        'beanName' => 'Call',
                        'id' => $callsId[0],
                        'reminderTime' => '2025-12-31 15:40:00',
                    ),
                    2 => array(
                        'beanName' => 'Call',
                        'id' => $callsId[1],
                        'reminderTime' => '2025-12-31 14:45:00',
                    ),
                    3 => array(
                        'beanName' => 'Meeting',
                        'id' => $meetingsId[0],
                        'reminderTime' => '2025-12-31 14:45:00',
                    ),
                    4 => array(
                        'beanName' => 'Meeting',
                        'id' => $meetingsId[1],
                        'reminderTime' => '2025-12-31 13:30:00',
                    ),
                ),
            ),
            'triggerServerConfiguredMexico' => array(
                'currentUserTimeZone' => 'America/Mexico_City',
                'calls' => array(
                    'featureCallInUTC' => array(
                        'id' => $callsId[0],
                        'date_start' => '2025-12-31 16:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                    'featureCallInUserFormatAndMexicoTimezone' => array(
                        'id' => $callsId[1],
                        'date_start' => '2025/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => create_guid(),
                    ),
                    'pastCallInUTC' => array(
                        'id' => $callsId[2],
                        'date_start' => '2011-12-31 16:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                    'pastCallInUserFormatAndMexicoTimezone' => array(
                        'id' => $callsId[3],
                        'date_start' => '2011/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                ),
                'meetings' => array(
                    'featureMeetingInUTC' => array(
                        'id' => $meetingsId[0],
                        'date_start' => '2025-12-31 15:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => create_guid()
                    ),
                    'featureMeetingInUserFormatAndMexicoTimezone' => array(
                        'id' => $meetingsId[1],
                        'date_start' => '2025/12/31 15:00',
                        'reminder_time' => '1800',
                        'assigned_user_id' => 0,
                    ),
                    'pastMeetingInUTC' => array(
                        'id' => $meetingsId[2],
                        'date_start' => '2010-12-31 15:00:00',
                        'reminder_time' => '1800',
                        'assigned_user_id' => 0,
                    ),
                    'pastMeetingInUserFormatAndMexicoTimezone' => array(
                        'id' => $meetingsId[3],
                        'date_start' => '2011/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                ),
                'isTriggerConfigured' => true,
                'expectedReminders' => array(
                    1 => array(
                        'beanName' => 'Call',
                        'id' => $callsId[0],
                        'reminderTime' => '2025-12-31 15:40:00',
                    ),
                    2 => array(
                        'beanName' => 'Call',
                        'id' => $callsId[1],
                        'reminderTime' => '2025-12-31 21:45:00',
                    ),
                    3 => array(
                        'beanName' => 'Meeting',
                        'id' => $meetingsId[0],
                        'reminderTime' => '2025-12-31 14:45:00',
                    ),
                    4 => array(
                        'beanName' => 'Meeting',
                        'id' => $meetingsId[1],
                        'reminderTime' => '2025-12-31 20:30:00',
                    ),
                ),
            ),
            'triggerServerNotConfiguredMexico' => array(
                'currentUserTimeZone' => 'America/Mexico_City',
                'calls' => array(
                    'featureCallInUTC' => array(
                        'id' => $callsId[0],
                        'date_start' => '2025-12-31 16:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                    'featureCallInUserFormatAndMexicoTimezone' => array(
                        'id' => $callsId[1],
                        'date_start' => '2025/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => create_guid(),
                    ),
                    'pastCallInUTC' => array(
                        'id' => $callsId[2],
                        'date_start' => '2011-12-31 16:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                    'pastCallInUserFormatAndMexicoTimezone' => array(
                        'id' => $callsId[3],
                        'date_start' => '2011/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                ),
                'meetings' => array(
                    'featureMeetingInUTC' => array(
                        'id' => $meetingsId[0],
                        'date_start' => '2025-12-31 15:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => create_guid(),
                    ),
                    'featureMeetingInUserFormatAndMexicoTimezone' => array(
                        'id' => $meetingsId[1],
                        'date_start' => '2025/12/31 15:00',
                        'reminder_time' => '1800',
                        'assigned_user_id' => 0,
                    ),
                    'pastMeetingInUTC' => array(
                        'id' => $meetingsId[2],
                        'date_start' => '2010-12-31 15:00:00',
                        'reminder_time' => '1800',
                        'assigned_user_id' => 0,
                    ),
                    'pastMeetingInUserFormatAndMexicoTimezone' => array(
                        'id' => $meetingsId[3],
                        'date_start' => '2011/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                ),
                'isTriggerConfigured' => false,
                'expectedReminders' => array(
                    1 => array(
                        'beanName' => 'Call',
                        'id' => $callsId[0],
                        'reminderTime' => '2025-12-31 15:40:00',
                    ),
                    2 => array(
                        'beanName' => 'Call',
                        'id' => $callsId[1],
                        'reminderTime' => '2025-12-31 21:45:00',
                    ),
                    3 => array(
                        'beanName' => 'Meeting',
                        'id' => $meetingsId[0],
                        'reminderTime' => '2025-12-31 14:45:00',
                    ),
                    4 => array(
                        'beanName' => 'Meeting',
                        'id' => $meetingsId[1],
                        'reminderTime' => '2025-12-31 20:30:00',
                    ),
                ),
            ),
            'triggerServerConfiguredJohannesburg' => array(
                'currentUserTimeZone' => 'Africa/Johannesburg',
                'calls' => array(
                    'featureCallInUTC' => array(
                        'id' => $callsId[0],
                        'date_start' => '2025-12-31 16:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                    'featureCallInUserFormatAndJohannesburgTimezone' => array(
                        'id' => $callsId[1],
                        'date_start' => '2025/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => create_guid(),
                    ),
                    'pastCallInUTC' => array(
                        'id' => $callsId[2],
                        'date_start' => '2011-12-31 16:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                    'pastCallInUserFormatAndJohannesburgTimezone' => array(
                        'id' => $callsId[3],
                        'date_start' => '2011/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                ),
                'meetings' => array(
                    'featureMeetingInUTC' => array(
                        'id' => $meetingsId[0],
                        'date_start' => '2025-12-31 15:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => create_guid()
                    ),
                    'featureMeetingInUserFormatAndJohannesburgTimezone' => array(
                        'id' => $meetingsId[1],
                        'date_start' => '2025/12/31 15:00',
                        'reminder_time' => '1800',
                        'assigned_user_id' => 0,
                    ),
                    'pastMeetingInUTC' => array(
                        'id' => $meetingsId[2],
                        'date_start' => '2010-12-31 15:00:00',
                        'reminder_time' => '1800',
                        'assigned_user_id' => 0,
                    ),
                    'pastMeetingInUserFormatAndJohannesburgTimezone' => array(
                        'id' => $meetingsId[3],
                        'date_start' => '2011/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                ),
                'isTriggerConfigured' => true,
                'expectedReminders' => array(
                    1 => array(
                        'beanName' => 'Call',
                        'id' => $callsId[0],
                        'reminderTime' => '2025-12-31 15:40:00',
                    ),
                    2 => array(
                        'beanName' => 'Call',
                        'id' => $callsId[1],
                        'reminderTime' => '2025-12-31 13:45:00',
                    ),
                    3 => array(
                        'beanName' => 'Meeting',
                        'id' => $meetingsId[0],
                        'reminderTime' => '2025-12-31 14:45:00',
                    ),
                    4 => array(
                        'beanName' => 'Meeting',
                        'id' => $meetingsId[1],
                        'reminderTime' => '2025-12-31 12:30:00',
                    ),
                ),
            ),
            'triggerServerNotConfiguredJohannesburg' => array(
                'currentUserTimeZone' => 'Africa/Johannesburg',
                'calls' => array(
                    'featureCallInUTC' => array(
                        'id' => $callsId[0],
                        'date_start' => '2025-12-31 16:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                    'featureCallInUserFormatAndJohannesburgTimezone' => array(
                        'id' => $callsId[1],
                        'date_start' => '2025/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => create_guid(),
                    ),
                    'pastCallInUTC' => array(
                        'id' => $callsId[2],
                        'date_start' => '2011-12-31 16:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                    'pastCallInUserFormatAndJohannesburgTimezone' => array(
                        'id' => $callsId[3],
                        'date_start' => '2011/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                ),
                'meetings' => array(
                    'featureMeetingInUTC' => array(
                        'id' => $meetingsId[0],
                        'date_start' => '2025-12-31 15:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => create_guid()
                    ),
                    'featureMeetingInUserFormatAndJohannesburgTimezone' => array(
                        'id' => $meetingsId[1],
                        'date_start' => '2025/12/31 15:00',
                        'reminder_time' => '1800',
                        'assigned_user_id' => 0,
                    ),
                    'pastMeetingInUTC' => array(
                        'id' => $meetingsId[2],
                        'date_start' => '2010-12-31 15:00:00',
                        'reminder_time' => '1800',
                        'assigned_user_id' => 0,
                    ),
                    'pastMeetingInUserFormatAndJohannesburgTimezone' => array(
                        'id' => $meetingsId[3],
                        'date_start' => '2011/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                ),
                'isTriggerConfigured' => false,
                'expectedReminders' => array(
                    1 => array(
                        'beanName' => 'Call',
                        'id' => $callsId[0],
                        'reminderTime' => '2025-12-31 15:40:00',
                    ),
                    2 => array(
                        'beanName' => 'Call',
                        'id' => $callsId[1],
                        'reminderTime' => '2025-12-31 13:45:00',
                    ),
                    3 => array(
                        'beanName' => 'Meeting',
                        'id' => $meetingsId[0],
                        'reminderTime' => '2025-12-31 14:45:00',
                    ),
                    4 => array(
                        'beanName' => 'Meeting',
                        'id' => $meetingsId[1],
                        'reminderTime' => '2025-12-31 12:30:00',
                    ),
                ),
            ),
            'triggerServerConfiguredSingapore' => array(
                'currentUserTimeZone' => 'Asia/Singapore',
                'calls' => array(
                    'featureCallInUTC' => array(
                        'id' => $callsId[0],
                        'date_start' => '2025-12-31 16:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                    'featureCallInUserFormatAndSingaporeTimezone' => array(
                        'id' => $callsId[1],
                        'date_start' => '2025/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => create_guid(),
                    ),
                    'pastCallInUTC' => array(
                        'id' => $callsId[2],
                        'date_start' => '2011-12-31 16:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                    'pastCallInUserFormatAndSingaporeTimezone' => array(
                        'id' => $callsId[3],
                        'date_start' => '2011/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                ),
                'meetings' => array(
                    'featureMeetingInUTC' => array(
                        'id' => $meetingsId[0],
                        'date_start' => '2025-12-31 15:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => create_guid()
                    ),
                    'featureMeetingInUserFormatAndSingaporeTimezone' => array(
                        'id' => $meetingsId[1],
                        'date_start' => '2025/12/31 15:00',
                        'reminder_time' => '1800',
                        'assigned_user_id' => 0,
                    ),
                    'pastMeetingInUTC' => array(
                        'id' => $meetingsId[2],
                        'date_start' => '2010-12-31 15:00:00',
                        'reminder_time' => '1800',
                        'assigned_user_id' => 0,
                    ),
                    'pastMeetingInUserFormatAndSingaporeTimezone' => array(
                        'id' => $meetingsId[3],
                        'date_start' => '2011/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                ),
                'isTriggerConfigured' => true,
                'expectedReminders' => array(
                    1 => array(
                        'beanName' => 'Call',
                        'id' => $callsId[0],
                        'reminderTime' => '2025-12-31 15:40:00',
                    ),
                    2 => array(
                        'beanName' => 'Call',
                        'id' => $callsId[1],
                        'reminderTime' => '2025-12-31 07:45:00',
                    ),
                    3 => array(
                        'beanName' => 'Meeting',
                        'id' => $meetingsId[0],
                        'reminderTime' => '2025-12-31 14:45:00',
                    ),
                    4 => array(
                        'beanName' => 'Meeting',
                        'id' => $meetingsId[1],
                        'reminderTime' => '2025-12-31 06:30:00',
                    ),
                ),
            ),
            'triggerServerNotConfiguredSingapore' => array(
                'currentUserTimeZone' => 'Asia/Singapore',
                'calls' => array(
                    'featureCallInUTC' => array(
                        'id' => $callsId[0],
                        'date_start' => '2025-12-31 16:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                    'featureCallInUserFormatAndSingaporeTimezone' => array(
                        'id' => $callsId[1],
                        'date_start' => '2025/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => create_guid(),
                    ),
                    'pastCallInUTC' => array(
                        'id' => $callsId[2],
                        'date_start' => '2011-12-31 16:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                    'pastCallInUserFormatAndSingaporeTimezone' => array(
                        'id' => $callsId[3],
                        'date_start' => '2011/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                ),
                'meetings' => array(
                    'featureMeetingInUTC' => array(
                        'id' => $meetingsId[0],
                        'date_start' => '2025-12-31 15:00:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => create_guid()
                    ),
                    'featureMeetingInUserFormatAndSingaporeTimezone' => array(
                        'id' => $meetingsId[1],
                        'date_start' => '2025/12/31 15:00',
                        'reminder_time' => '1800',
                        'assigned_user_id' => 0,
                    ),
                    'pastMeetingInUTC' => array(
                        'id' => $meetingsId[2],
                        'date_start' => '2010-12-31 15:00:00',
                        'reminder_time' => '1800',
                        'assigned_user_id' => 0,
                    ),
                    'pastMeetingInUserFormatAndSingaporeTimezone' => array(
                        'id' => $meetingsId[3],
                        'date_start' => '2011/12/31 16:00',
                        'reminder_time' => '1200',
                        'assigned_user_id' => 0,
                    ),
                ),
                'isTriggerConfigured' => false,
                'expectedReminders' => array(
                    1 => array(
                        'beanName' => 'Call',
                        'id' => $callsId[0],
                        'reminderTime' => '2025-12-31 15:40:00',
                    ),
                    2 => array(
                        'beanName' => 'Call',
                        'id' => $callsId[1],
                        'reminderTime' => '2025-12-31 07:45:00',
                    ),
                    3 => array(
                        'beanName' => 'Meeting',
                        'id' => $meetingsId[0],
                        'reminderTime' => '2025-12-31 14:45:00',
                    ),
                    4 => array(
                        'beanName' => 'Meeting',
                        'id' => $meetingsId[1],
                        'reminderTime' => '2025-12-31 06:30:00',
                    ),
                ),
            ),
        );
    }

    /**
     * Should get all user's Calls and Meetings and create new reminders for each future beans.
     * Before creation should remove all reminders for user.
     *
     * @dataProvider runProvider
     * @covers Sugarcrm\Sugarcrm\Trigger\Job\RecreateUserRemindersJob::run
     * @param string $currentUserTimeZone
     * @param array $calls
     * @param array $meetings
     * @param bool $isTriggerConfigured
     * @param array $expectedReminders
     * @throws \Exception
     */
    public function testRun($currentUserTimeZone, $calls, $meetings, $isTriggerConfigured, $expectedReminders)
    {
        $this->currentUser->setPreference('timezone', $currentUserTimeZone);
        $timeDate = \TimeDate::getInstance();

        $expectedCallsQuery = new \SugarQuery();
        $expectedCallsQuery->from(\BeanFactory::getBean('Calls'));
        $expectedCallsQuery->where()
            ->queryAnd()
            ->equals($expectedCallsQuery->join('users')->joinName() . '.id', $this->currentUser->id)
            ->notEquals('assigned_user_id', $this->currentUser->id)
            ->gt('date_start', $timeDate->getNow()->asDb());

        $expectedMeetingsQuery = new \SugarQuery();
        $expectedMeetingsQuery->from(\BeanFactory::getBean('Meetings'));
        $expectedMeetingsQuery->where()
            ->queryAnd()
            ->equals($expectedMeetingsQuery->join('users')->joinName() . '.id', $this->currentUser->id)
            ->notEquals('assigned_user_id', $this->currentUser->id)
            ->gt('date_start', $timeDate->getNow()->asDb());

        $createdCalls = array();
        $createdMeetings = array();
        $this->triggerClient->method('isConfigured')->willReturn($isTriggerConfigured);
        if ($isTriggerConfigured) {
            $reminderManager = $this->triggerServerManager;
            $this->schedulerManager->expects($this->never())->method('deleteReminders');
            $this->schedulerManager->expects($this->never())->method('addReminderForUser');
        } else {
            $reminderManager = $this->schedulerManager;
            $this->triggerServerManager->expects($this->never())->method('deleteReminders');
            $this->triggerServerManager->expects($this->never())->method('addReminderForUser');
        }

        foreach ($calls as $call) {
            $bean = new \Call();
            $bean->id = $call['id'];
            $bean->date_start = $call['date_start'];
            $bean->reminder_time = $call['reminder_time'];
            if ($call['assigned_user_id']) {
                $bean->assigned_user_id = $call['assigned_user_id'];
            } else {
                $bean->assigned_user_id = $this->currentUser->id;
            }
            $createdCalls[$bean->id] = $bean;
        }
        CallCRYS1302::$fetchFromQueryReturn = $createdCalls;

        foreach ($meetings as $meeting) {
            $bean = new \Meeting();
            $bean->id = $meeting['id'];
            $bean->date_start = $meeting['date_start'];
            $bean->reminder_time = $meeting['reminder_time'];
            if ($meeting['assigned_user_id']) {
                $bean->assigned_user_id = $meeting['assigned_user_id'];
            } else {
                $bean->assigned_user_id = $this->currentUser->id;
            }
            $createdMeetings[$bean->id] = $bean;
        }
        MeetingCRYS1302::$fetchFromQueryReturn = $createdMeetings;

        foreach ($expectedReminders as $index => $reminderOptions) {
            $expectedBean = null;
            if ($reminderOptions['beanName'] == 'Call') {
                $expectedBean = $createdCalls[$reminderOptions['id']];
            } elseif ($reminderOptions['beanName'] == 'Meeting') {
                $expectedBean = $createdMeetings[$reminderOptions['id']];
            } else {
                throw new \Exception("bean name '{$reminderOptions['beanName']}' in data provider is wrong");
            }
            $expectedTime = new \DateTime($reminderOptions['reminderTime'], new \DateTimeZone('UTC'));

            $reminderManager->expects($this->at($index))
                ->method('addReminderForUser')
                ->with(
                    $this->equalTo($expectedBean),
                    $this->equalTo($this->currentUser),
                    $this->equalTo($expectedTime)
                );
        }

        $reminderManager->expects($this->once())
            ->method('deleteReminders')
            ->with($this->currentUser);

        $recreateResult = $this->recreateUserRemindersJob->run();
        $this->assertEquals(\SchedulersJob::JOB_SUCCESS, $recreateResult);
        $this->assertEquals(array($expectedCallsQuery), CallCRYS1302::$fetchFromQueryArguments);
        $this->assertEquals(array($expectedMeetingsQuery), MeetingCRYS1302::$fetchFromQueryArguments);
    }
}

/**
 * Class UserCRYS1302
 * @package Sugarcrm\SugarcrmTests\Trigger\Job
 */
class UserCRYS1302 extends \User
{
    /** @var array */
    public static $fetchedUsers = array();

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
     * @param string $id
     * @param bool|true $encode
     * @param bool|true $deleted
     * @return UserCRYS1302
     */
    public function retrieve($id = '-1', $encode = true, $deleted = true)
    {
        $this->id = $id;
        if (isset(static::$fetchedUsers[$id])) {
            $this->user_preferences = static::$fetchedUsers[$id]->user_preferences;
        }
        return $this;
    }
}

/**
 * Class MeetingCRYS1302
 * @package Sugarcrm\SugarcrmTests\Trigger\Job
 */
class MeetingCRYS1302 extends \Meeting
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
        $this->added_custom_field_defs = false;
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

/**
 * Class CallCRYS1302
 * @package Sugarcrm\SugarcrmTests\Trigger\Job
 */
class CallCRYS1302 extends \Call
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
        $this->added_custom_field_defs = false;
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
