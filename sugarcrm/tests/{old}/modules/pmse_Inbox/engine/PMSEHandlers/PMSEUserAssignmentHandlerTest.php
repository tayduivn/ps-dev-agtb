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

use PHPUnit\Framework\TestCase;

class PMSEUserAssignmentHandlerTest extends TestCase
{
    /**
     * User bean
     * @var SugarBean
     */
    private $user;

    /**
     * Local cache that holds ids for table names that need hard deleting
     * @var array
     */
    private $deleteCache = [];

    protected function setUp(): void
    {
        \SugarTestHelper::init();
        $this->user = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        $db = \DBManagerFactory::getInstance();
        foreach ($this->deleteCache as $table => $ids) {
            $in = "'" . implode("','", $ids) . "'";
            $sql = "DELETE FROM $table WHERE id IN ($in)";
            $db->query($sql);
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        \SugarTestHelper::tearDown();
    }

    /**
     * Checks the user has Holidays set or not
     *
     * @covers ::userHasHoliday
     * @dataProvider userHasHolidayProvider
     * @param string $holidayDate Date to be checked
     * @param string $timeZone Timezone
     * @param bool $expect
     */
    public function testUserHasHoliday(string $holidayDate, string $timeZone, bool $expect)
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->setPreference('timezone', $timeZone);

        $holiday = BeanFactory::newBean('Holidays');
        $holiday->holiday_date = $holidayDate;
        $holiday->name = 'Good Holiday';
        $holiday->save();

        $this->addBeanToDeleteList($holiday);

        $user->load_relationship('holidays');
        $user->holidays->add($holiday);

        $userAssignmentHandlerMock = new PMSEUserAssignmentHandlerMock();

        $checkTime = new \SugarDateTime(
            '2020-04-23 17:00:00',
            new DateTimeZone('UTC')
        );

        $result = $userAssignmentHandlerMock->userHasHoliday($user, $checkTime);

        $this->assertSame($expect, $result);
    }

    /**
     * Provider for ::userHasHoliday
     * @return array
     */
    public function userHasHolidayProvider()
    {
        return [
            [
                'holidayDate' => '2020-04-23',
                'timezone' => 'UTC',
                'expect' => true,
            ],
            [
                'holidayDate' => '2020-04-22',
                'timezone' => 'UTC',
                'expect' => false,
            ],
            [
                'holidayDate' => '2020-04-24',
                'timezone' => 'Asia/Taipei',
                'expect' => true,
            ],
            [
                'holidayDate' => '2020-04-24',
                'timezone' => 'America/Los_Angeles',
                'expect' => false,
            ],
        ];
    }

    /**
     * Checks the user has Shift Exceptions set or not
     *
     * @covers ::userHasShiftExceptions
     * @dataProvider userHasShiftExceptionsProvider
     * @param string $startDate Start Date to be checked
     * @param string $endDate End Date to be checked
     * @param string $startHour Start Hour to be checked
     * @param string $endHour End Hour to be checked
     * @param string $timeZone Timezone
     * @param bool $allDay All Day or not
     * @param bool $expect
     */
    public function testUserHasShiftExceptions(
        string $startDate,
        string $endDate,
        string $startHour,
        string $endHour,
        string $timeZone,
        bool $allDay,
        bool $expect
    ) {
        $shiftException = BeanFactory::newBean('ShiftExceptions');
        $shiftException->start_date = $startDate;
        $shiftException->end_date = $endDate;
        $shiftException->start_hour = $startHour;
        $shiftException->end_hour = $endHour;
        $shiftException->enabled = true;
        $shiftException->all_day = $allDay;
        $shiftException->timezone = $timeZone;
        $shiftException->save();

        $this->addBeanToDeleteList($shiftException);

        $this->user->load_relationship('shift_exceptions');
        $this->user->shift_exceptions->add($shiftException);

        $userAssignmentHandlerMock = new PMSEUserAssignmentHandlerMock();

        $checkTime = new \SugarDateTime(
            '2020-04-23 15:00:00',
            new DateTimeZone('UTC')
        );

        $result = $userAssignmentHandlerMock->userHasShiftExceptions($this->user, $checkTime);

        $this->assertSame($expect, $result);
    }

    /**
     * Provider for ::userHasShiftExceptions
     * @return array
     */
    public function userHasShiftExceptionsProvider()
    {
        return [
            [
                'startDate' => '2020-04-22',
                'endDate' => '2020-04-23',
                'startHour' => '0',
                'endHour' => '0',
                'timezone' => 'UTC',
                'allDay' => true,
                'expect' => true,
            ],
            [
                'startDate' => '2020-04-22',
                'endDate' => '2020-04-23',
                'startHour' => '7',
                'endHour' => '14',
                'timezone' => 'UTC',
                'allDay' => false,
                'expect' => false,
            ],
            [
                'startDate' => '2020-04-22',
                'endDate' => '2020-04-23',
                'startHour' => '7',
                'endHour' => '18',
                'timezone' => 'Asia/Taipei',
                'allDay' => false,
                'expect' => false,
            ],
            [
                'startDate' => '2020-04-22',
                'endDate' => '2020-04-23',
                'startHour' => '0',
                'endHour' => '0',
                'timezone' => 'America/Los_Angeles',
                'allDay' => true,
                'expect' => true,
            ],
            [
                'startDate' => '2020-04-22',
                'endDate' => '2020-04-23',
                'startHour' => '9',
                'endHour' => '18',
                'timezone' => 'America/Los_Angeles',
                'allDay' => false,
                'expect' => true,
            ],
        ];
    }

    /**
     * Checks the user is available in Shifts set or not
     *
     * @covers ::userAvailableInShifts
     * @dataProvider userAvailableInShiftsProvider
     * @param string $checkTime Datetime to be checked
     * @param string $timeZone Timezone
     * @param bool $available
     */
    public function testUserAvailableInShifts(string $checkTime, string $timeZone, bool $available)
    {
        $shift = BeanFactory::newBean('Shifts');
        $shift->date_start = '2020-04-23';
        $shift->date_end = '2020-04-24';
        $shift->is_open_friday = true;
        $shift->friday_open_hour = '8';
        $shift->friday_open_minutes = '0';
        $shift->friday_close_hour = '17';
        $shift->friday_close_minutes = '30';
        $shift->timezone = $timeZone;
        $shift->save();

        $this->addBeanToDeleteList($shift);

        $this->user->load_relationship('shifts');
        $this->user->shifts->add($shift);

        $userAssignmentHandlerMock = new PMSEUserAssignmentHandlerMock();

        $checkTime = new \SugarDateTime(
            $checkTime,
            new DateTimeZone('UTC')
        );

        $result = $userAssignmentHandlerMock->userAvailableInShifts($this->user, $checkTime);

        $this->assertSame($available, $result);
    }

    /**
     * Provider for ::userAvailableInShifts
     * @return array
     */
    public function userAvailableInShiftsProvider()
    {
        return [
            [
                'checkTime' => '2020-04-24 12:00:00',
                'timezone' => 'UTC',
                'available' => true,
            ],
            [
                'checkTime' => '2020-04-25 12:00:00',
                'timezone' => 'UTC',
                'available' => false,
            ],
            [
                'checkTime' => '2020-04-23 12:00:00',
                'timezone' => 'Asia/Taipei',
                'available' => false,
            ],
            [
                'checkTime' => '2020-04-24 4:00:00',
                'timezone' => 'Asia/Taipei',
                'available' => true,
            ],
            [
                'checkTime' => '2020-04-24 12:00:00',
                'timezone' => 'America/Los_Angeles',
                'available' => false,
            ],
            [
                'checkTime' => '2020-04-24 23:00:00',
                'timezone' => 'America/Los_Angeles',
                'available' => true,
            ],
        ];
    }

    /**
     * Adds a bean id to the delete list for cleanup
     * @param SugarBean $bean The bean to delete
     */
    private function addBeanToDeleteList(\SugarBean $bean)
    {
        $this->deleteCache[$bean->getTableName()][$bean->id] = $bean->id;
    }
}

class PMSEUserAssignmentHandlerMock extends PMSEUserAssignmentHandler
{
    public function userHasHoliday($user, $checkTime)
    {
        return parent::userHasHoliday($user, $checkTime);
    }

    public function userHasShiftExceptions($user, $checkTime)
    {
        return parent::userHasShiftExceptions($user, $checkTime);
    }

    public function userAvailableInShifts($user, $checkTime)
    {
        return parent::userAvailableInShifts($user, $checkTime);
    }
}
