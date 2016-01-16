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

use Sugarcrm\Sugarcrm\Trigger\ReminderManager\Helper as ReminderHelper;

/**
 * Class HelperTest
 *
 * @covers Sugarcrm\Sugarcrm\Trigger\ReminderManager\Helper
 */
class HelperTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var \Call|\PHPUnit_Framework_MockObject_MockObject */
    protected $bean = null;

    /** @var \User|\PHPUnit_Framework_MockObject_MockObject */
    protected $user = null;

    /** @var null|\User */
    protected $currentUser = null;

    /** @var \TimeDate|\PHPUnit_Framework_MockObject_MockObject */
    protected $timeDate = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->bean = $this->getMock('Call');
        $this->bean->id = create_guid();
        $this->user = new \User();
        $this->currentUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = $this->user;
        $this->timeDate = $this->getMock('TimeDate');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $GLOBALS['current_user'] = $this->currentUser;
        parent::tearDown();
    }

    /**
     * Data provider for testIsInFuture.
     *
     * @see Sugarcrm\SugarcrmTests\Trigger\ReminderManager\HelperTest::testIsInFuture
     * @return array
     */
    public static function isInFutureProvider()
    {
        return array(
            'dateInFutureReturnTrue' => array(
               'dateInterval' => '+1 day',
               'dateTimeZone' => 'UTC',
               'sleepBeforeExecute' => 0,
               'expectedResult' => true,
            ),
            'dateInFutureNotUTCReturnTrue' => array(
                'dateInterval' => '10 minutes',
                'dateTimeZone' => 'Europe/Berlin',
                'sleepBeforeExecute' => 0,
                'expectedResult' => true,
            ),
            'dateInPastNotUTCReturnFalse' => array(
                'dateInterval' => '-30 minutes',
                'dateTimeZone' => 'Europe/Berlin',
                'sleepBeforeExecute' => 0,
                'expectedResult' => false,
            ),
            'dateIsNowReturnFalse' => array(
                'dateInterval' => 'now',
                'dateTimeZone' => 'UTC',
                'sleepBeforeExecute' => 1,
                'expectedResult' => false,
            ),
            'dateInPastReturnFalse' => array(
                'dateInterval' => '-1 day',
                'dateTimeZone' => 'UTC',
                'sleepBeforeExecute' => 0,
                'expectedResult' => false,
            ),
        );
    }

    /**
     * Should returns true if date is in future otherwise should return false.
     *
     * @dataProvider isInFutureProvider
     * @covers Sugarcrm\Sugarcrm\Trigger\ReminderManager\Helper::isInFuture
     * @param string $dateInterval
     * @param string $dateTimeZone
     * @param int $sleepBeforeExecute
     * @param bool $expectedResult
     */
    public function testIsInFuture($dateInterval, $dateTimeZone, $sleepBeforeExecute, $expectedResult)
    {
        $date = new \DateTime($dateInterval, new \DateTimeZone($dateTimeZone));
        \SugarTestReflection::setProtectedValue($this->timeDate, 'timedate', new \TimeDate());
        sleep($sleepBeforeExecute);//sleep for checking now value
        $this->assertEquals($expectedResult, ReminderHelper::isInFuture($date));
    }

    /**
     * Data provider for testCalculateReminderDateReturnsNull.
     *
     * @see Sugarcrm\SugarcrmTests\Trigger\ReminderManager\HelperTest::testCalculateReminderDateReturnsNull
     * @return array
     */
    public static function calculateReminderDateReturnsNullProvider()
    {
        $userId = create_guid();
        return array(
            'userIsAssignedBeanReminderTimeBelowZero' => array(
                'userId' => $userId,
                'userAssignedId' => $userId,
                'beanReminderTime' => -10,
                'userReminderTime' => 10,
            ),
            'userNotAssignedUserReminderTimeBelowZero' => array(
                'userId' => $userId,
                'userAssignedId' => create_guid(),
                'beanReminderTime' => 10,
                'userReminderTime' => -10,
            ),
        );
    }

    /**
     * Should returns null if user assigned and bean reminder time below zero.
     * Should returns null if user not assigned and preference reminder time below zero.
     *
     * @dataProvider calculateReminderDateReturnsNullProvider
     * @covers Sugarcrm\Sugarcrm\Trigger\ReminderManager\Helper::calculateReminderDateTime
     * @param string $userId
     * @param string $userAssignedId
     * @param int $beanReminderTime
     * @param int $userReminderTime
     */
    public function testCalculateReminderDateReturnsNull(
        $userId,
        $userAssignedId,
        $beanReminderTime,
        $userReminderTime
    ) {
        $this->bean->assigned_user_id = $userAssignedId;
        $this->bean->reminder_time = $beanReminderTime;
        $this->user->id = $userId;
        $this->user->setPreference('reminder_time', $userReminderTime);

        $this->assertNull(ReminderHelper::calculateReminderDateTime($this->bean, $this->user));
    }

    /**
     * Data provider for testCalculateReminderDateReturnReminderDate.
     *
     * @see Sugarcrm\SugarcrmTests\Trigger\ReminderManager\HelperTest::testCalculateReminderDateReturnReminderDate
     * @return array
     */
    public static function calculateReminderDateReturnReminderDateProvider()
    {
        $userId = create_guid();
        return array(
            'userAssignedCalculateReminderFromBeanPreferencesUTC' => array(
                'userId' => $userId,
                'userAssignedId' => $userId,
                'beanReminderTime' => 10,
                'userReminderTime' => 20,
                'beanDateStart' => '2015-01-18 14:00:00',
                'userDateFormat' => \TimeDate::DB_DATE_FORMAT,
                'userTimeFormat' => \TimeDate::DB_TIME_FORMAT,
                'userTimeZone' => 'UTC',
                'expectedReminderDate' => '2015-01-18 13:59:50',
            ),
            'userAssignedCalculateReminderFromBeanPreferencesNotUTC' => array(
                'userId' => $userId,
                'userAssignedId' => $userId,
                'beanReminderTime' => 120,
                'userReminderTime' => 20,
                'beanDateStart' => '2015-01-18 14:00:00',
                'userDateFormat' => \TimeDate::DB_DATE_FORMAT,
                'userTimeFormat' => \TimeDate::DB_TIME_FORMAT,
                'userTimeZone' => 'Europe/Berlin',
                'expectedReminderDate' => '2015-01-18 13:58:00',
            ),
            'userNotAssignedCalculateReminderFromUserPreferences' => array(
                'userId' => $userId,
                'userAssignedId' => create_guid(),
                'beanReminderTime' => 10,
                'userReminderTime' => 180,
                'beanDateStart' => '2015/01/18 14:05',
                'userDateFormat' => 'Y/m/d',
                'userTimeFormat' => 'H:i',
                'userTimeZone' => 'Europe/Berlin',
                'expectedReminderDate' => '2015-01-18 13:02:00',
            ),
        );
    }

    /**
     * Should calculate reminder time from event if user is assigned to bean otherwise use user preference value.
     *
     * @dataProvider calculateReminderDateReturnReminderDateProvider
     * @covers Sugarcrm\Sugarcrm\Trigger\ReminderManager\Helper::calculateReminderDateTime
     * @param string $userId
     * @param string $userAssignedId
     * @param int $beanReminderTime
     * @param int $userReminderTime
     * @param string $beanDateStart date in database format.
     * @param string $userDateFormat
     * @param string $userTimeFormat
     * @param string $userTimeZone
     * @param string $expectedReminderDate
     */
    public function testCalculateReminderDateReturnReminderDate(
        $userId,
        $userAssignedId,
        $beanReminderTime,
        $userReminderTime,
        $beanDateStart,
        $userDateFormat,
        $userTimeFormat,
        $userTimeZone,
        $expectedReminderDate
    ) {
        $this->user->id = $userId;
        $this->user->setPreference('datef', $userDateFormat);
        $this->user->setPreference('timef', $userTimeFormat);
        $this->user->setPreference('timezone', $userTimeZone);
        $this->user->setPreference('reminder_time', $userReminderTime);

        $this->bean->assigned_user_id = $userAssignedId;
        $this->bean->reminder_time = $beanReminderTime;
        $this->bean->date_start = $beanDateStart;

        $expectedDate = new \DateTime($expectedReminderDate, new \DateTimeZone('UTC'));

        $this->assertEquals($expectedDate, ReminderHelper::calculateReminderDateTime($this->bean, $this->user));
    }
}
