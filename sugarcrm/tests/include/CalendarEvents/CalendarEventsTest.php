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

class CalendarEventsTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $meetingIds = array();

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        $this->meetingIds = array();
    }

    public function tearDown()
    {
        if (!empty($this->meetingIds)) {
            $ids = implode("','", $this->meetingIds);
            $GLOBALS['db']->query("DELETE FROM meetings WHERE id IN ('" . $ids . "')");
            $this->meetingIds = array();
        }
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestHelper::tearDown();
    }

    public function testCalendarEvents_Meeting_EventRecurring_NoRepeatType()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->repeat_type = null;
        $meeting->date_start = '2014-12-25 18:00:00';

        $result = $GLOBALS['calendarEvents']->isEventRecurring($meeting);

        $this->assertFalse($result, "Expected Meeting Event to be Non-Recurring");
    }

    public function testCalendarEvents_Meeting_EventRecurring_NoDateStart()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->repeat_type = 'Daily';
        $meeting->date_start = null;

        $result = $GLOBALS['calendarEvents']->isEventRecurring($meeting);

        $this->assertFalse($result, "Expected Meeting Event to be Non-Recurring");
    }

    public function testCalendarEvents_Meeting_EventRecurring_OK()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->repeat_type = 'Daily';
        $meeting->date_start = '2014-12-25 18:00:00';

        $result = $GLOBALS['calendarEvents']->isEventRecurring($meeting);

        $this->assertTrue($result, "Expected Meeting Event to be recognized as Recurring");
    }

    /**
     * @expectedException SugarException
     */
    public function testCalendarEvents_Account_EventRecurring_UnsupportedCalendarEventModule()
    {
        $account = BeanFactory::newBean('Accounts');
        $GLOBALS['calendarEvents']->isEventRecurring($account);
    }

    public function testCalendarEvents_SaveRecurringEvents_EventsSaved()
    {
        $args['date_start'] = '2030-08-15 13:00:00';
        $args['date_end']   = '2030-08-15 18:15:00';
        $args['name'] = "Test Meeting";
        $args['duration_hours'] = '1';
        $args['duration_minutes'] = '30';
        $args['repeat_type'] = 'Daily';
        $args['repeat_interval'] = 1;
        $args['repeat_count'] = 3;
        $args['repeat_until'] = null;
        $args['repeat_dow'] = null;

        $meeting = $this->newMeeting('', $args);

        $calEvents = new CalendarEventsTest_CalendarEvents();
        $calEvents->saveRecurringEvents($meeting, $args);

        $eventsCreated = $calEvents->getEventsCreated();
        $this->meetingIds = array_merge($this->meetingIds, array_keys($eventsCreated));

        $this->assertEquals($args['repeat_count'], count($eventsCreated) + 1, "Unexpected Number of Recurring Meetings Created");
    }

    /**
     * Instantiate a new Meeting and prepopulate values from args
     * Add Meeting to meetingIds array to ensure its deletion on teardown
     * @param string $id  meeting ID to assign
     * @param array $args assign field values to newly created meeting
     * @return Meeting
     */
    protected function newMeeting($id = '', $args=array())
    {
        global $current_user;
        $meeting = SugarTestMeetingUtilities::createMeeting($id, $current_user);
        if (!empty($args)) {
            foreach ($args AS $k => $v) {
                $meeting->$k = $v;
            }
            $meeting->save();
        }
        $this->meetingIds[] = $meeting->id;
        return $meeting;
    }
}


class CalendarEventsTest_CalendarEvents extends CalendarEvents
{
    protected $eventsCreated = array();

    public function getEventsCreated()
    {
        return $this->eventsCreated;
    }

    protected function saveRecurring(SugarBean $parentBean, array $repeatDateTimeArray)
    {
        $this->eventsCreated = parent::saveRecurring($parentBean, $repeatDateTimeArray);
    }
}

