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
    protected $calendarEventsService;

    protected $meetingIds = array();

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        $this->calendarEventsService = new CalendarEvents();
        $this->meetingIds = array();
    }

    public function tearDown()
    {
        if (!empty($this->meetingIds)) {
            $ids = implode("','", $this->meetingIds);
            $GLOBALS['db']->query("DELETE FROM meetings_users WHERE meeting_id IN ('" . $ids . "')");
            $GLOBALS['db']->query("DELETE FROM meetings WHERE id IN ('" . $ids . "')");
            $this->meetingIds = array();
        }
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestHelper::tearDown();
    }

    public function testCalendarEvents_Meeting_EventRecurring_NoRepeatType()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->repeat_type = null;
        $meeting->date_start = '2014-12-25 18:00:00';

        $result = $this->calendarEventsService->isEventRecurring($meeting);

        $this->assertFalse($result, "Expected Meeting Event to be Non-Recurring");
    }

    public function testCalendarEvents_Meeting_EventRecurring_NoDateStart()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->repeat_type = 'Daily';
        $meeting->date_start = null;

        $result = $this->calendarEventsService->isEventRecurring($meeting);

        $this->assertFalse($result, "Expected Meeting Event to be Non-Recurring");
    }

    public function testCalendarEvents_Meeting_EventRecurring_OK()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->repeat_type = 'Daily';
        $meeting->date_start = '2014-12-25 18:00:00';

        $result = $this->calendarEventsService->isEventRecurring($meeting);

        $this->assertTrue($result, "Expected Meeting Event to be recognized as Recurring");
    }

    /**
     * @expectedException SugarException
     */
    public function testCalendarEvents_Account_EventRecurring_UnsupportedCalendarEventModule()
    {
        $account = BeanFactory::newBean('Accounts');
        $this->calendarEventsService->isEventRecurring($account);
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
        $calEvents->saveRecurringEvents($meeting);

        $eventsCreated = $calEvents->getEventsCreated();
        foreach($eventsCreated as $eventCreated) {
            $this->meetingIds[] = $eventCreated['id'];
        }
        $this->assertEquals($args['repeat_count'], count($eventsCreated) + 1, "Unexpected Number of Recurring Meetings Created");
    }

    public function testCalendarEvents_SaveRecurringEvents_CurrentAssignedUserAutoAccepted()
    {
        global $current_user;
        $args['date_start'] = '2030-08-15 13:00:00';
        $args['date_end']   = '2030-08-15 18:15:00';
        $args['name'] = "Test Meeting";
        $args['duration_hours'] = '1';
        $args['duration_minutes'] = '30';
        $args['repeat_type'] = 'Daily';
        $args['repeat_interval'] = 1;
        $args['repeat_count'] = 2;
        $args['repeat_until'] = null;
        $args['repeat_dow'] = null;
        $args['assigned_user_id'] = $current_user->id;

        $meeting = $this->newMeeting('', $args);

        $calEvents = new CalendarEventsTest_CalendarEvents();
        $calEvents->saveRecurringEvents($meeting);

        $eventsCreated = $calEvents->getEventsCreated();
        foreach($eventsCreated as $eventCreated) {
            $this->meetingIds[] = $eventCreated['id'];
        }

        $parentMeetingAcceptStatus = $meeting->users->rows[$current_user->id]['accept_status'];

        $childMeeting = BeanFactory::getBean('Meetings', $eventsCreated[0]['id']);
        $childMeeting->load_relationship('users');
        $childMeeting->users->load();
        $childMeetingAcceptStatus = $childMeeting->users->rows[$current_user->id]['accept_status'];

        $this->assertEquals($parentMeetingAcceptStatus, 'accept', 'Current user should have auto-accepted in parent meeting');
        $this->assertEquals($childMeetingAcceptStatus, 'accept', 'Current user should have auto-accepted in child meeting');
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

