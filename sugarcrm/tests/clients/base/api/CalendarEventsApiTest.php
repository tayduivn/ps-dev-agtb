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

/**
 * @group api
 * @group calendarevents
 */
class CalendarEventsApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $api,
        $calendarEventsApi;

    private $meetingIds = array();

    public function setUp()
    {
        parent::setUp();
        $this->meetingIds = array();

        $this->api = SugarTestRestUtilities::getRestServiceMock();
        $this->api->user = $GLOBALS['current_user']->getSystemUser();
        $GLOBALS['current_user'] = $this->api->user;

        $this->calendarEventsApi = new CalendarEventsApi();
    }

    public function tearDown()
    {
        BeanFactory::setBeanClass('Meetings');
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestHelper::tearDown();
        if (!empty($this->meetingIds)) {
            $ids = implode("','", $this->meetingIds);
            $GLOBALS['db']->query("DELETE FROM meetings WHERE id IN ('" . $ids . "')");
            $this->meetingIds = array();
        }
        parent::tearDown();
    }

    public function testDeleteRecord_NotRecurringMeeting_CallsDeleteMethod()
    {
        $calendarEventsApiMock = $this->getMock(
            'CalendarEventsApi',
            array('deleteRecord', 'deleteRecordAndRecurrences')
        );
        $calendarEventsApiMock->expects($this->once())
            ->method('deleteRecord');
        $calendarEventsApiMock->expects($this->never())
            ->method('deleteRecordAndRecurrences');

        $this->calendarEventsApi = $calendarEventsApiMock;

        $mockMeeting = $this->getMock('Meeting', array('ACLAccess'));
        $mockMeeting->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(true));

        BeanFactory::setBeanClass('Meetings', get_class($mockMeeting));

        $mockMeeting->id = create_guid();
        BeanFactory::registerBean($mockMeeting);

        $args = array(
            'module' => 'Meetings',
            'record' => $mockMeeting->id,
        );

        $this->calendarEventsApi->deleteCalendarEvent($this->api, $args);

        BeanFactory::unregisterBean($mockMeeting);
    }

    public function testDeleteRecord_RecurringMeeting_CallsDeleterRecurrenceMethod()
    {
        $calendarEventsApiMock = $this->getMock(
            'CalendarEventsApi',
            array('deleteRecord', 'deleteRecordAndRecurrences')
        );
        $calendarEventsApiMock->expects($this->never())
            ->method('deleteRecord');
        $calendarEventsApiMock->expects($this->once())
            ->method('deleteRecordAndRecurrences');

        $this->calendarEventsApi = $calendarEventsApiMock;

        $mockMeeting = $this->getMock('Meeting', array('ACLAccess'));
        $mockMeeting->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(true));

        BeanFactory::setBeanClass('Meetings', get_class($mockMeeting));

        $mockMeeting->id = create_guid();
        BeanFactory::registerBean($mockMeeting);

        $args = array(
            'module' => 'Meetings',
            'record' => $mockMeeting->id,
            'all_recurrences' => 'true',
        );

        $this->calendarEventsApi->deleteCalendarEvent($this->api, $args);

        BeanFactory::unregisterBean($mockMeeting);
    }

    /**
     * @expectedException     SugarApiExceptionNotAuthorized
     */
    public function testDeleteRecordAndRecurrences_NoAccess_ThrowsException()
    {
        $mockMeeting = $this->getMock('Meeting', array('ACLAccess'));
        $mockMeeting->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(false));

        BeanFactory::setBeanClass('Meetings', get_class($mockMeeting));

        $mockMeeting->id = create_guid();
        BeanFactory::registerBean($mockMeeting);

        $args = array(
            'module' => 'Meetings',
            'record' => $mockMeeting->id,
        );

        $this->calendarEventsApi->deleteRecordAndRecurrences($this->api, $args);

        BeanFactory::unregisterBean($mockMeeting);
    }

    public function testDeleteRecordAndRecurrences_RetrievesParentRecord_DeletesAllMeetings()
    {
        $parentMeeting = SugarTestMeetingUtilities::createMeeting('', $this->api->user);

        $meeting1 = SugarTestMeetingUtilities::createMeeting('', $this->api->user);
        $meeting1->repeat_parent_id = $parentMeeting->id;
        $meeting1->save();

        $meeting2 = SugarTestMeetingUtilities::createMeeting('', $this->api->user);
        $meeting2->repeat_parent_id = $parentMeeting->id;
        $meeting2->save();

        $args = array(
            'module' => 'Meetings',
            'record' => $meeting1->id,
        );

        $results = $this->calendarEventsApi->deleteRecordAndRecurrences($this->api, $args);

        $this->assertEquals(
            $parentMeeting->id,
            $results['id'],
            'The return id of the delete call should be the parent meeting id'
        );

        $parentMeeting = BeanFactory::getBean('Meetings', $parentMeeting->id);
        $meeting1 = BeanFactory::getBean('Meetings', $meeting1->id);
        $meeting2 = BeanFactory::getBean('Meetings', $meeting2->id);

        $this->assertEquals($parentMeeting->deleted, 0, 'The parent meeting record should be deleted');
        $this->assertEquals($meeting1->deleted, 0, 'The meeting1 record should be deleted');
        $this->assertEquals($meeting2->deleted, 0, 'The meeting2 record should be deleted');
    }

    public function dataProviderForCheckRequiredParams_ApiMethods_ExceptionThrownIfMissing()
    {
        $dateStart = $this->dateTimeAsISO('2014-08-01 14:30:00');
        return array(
            array(
                "createCalendarEvent",
                array(
                    'duration_hours' => '9',
                    'duration_minutes' => '9',
                ),
            ),
            array(
                "createCalendarEvent",
                array(
                    'date_start' => $dateStart,
                    'duration_minutes' => '9',
                ),
            ),
            array(
                "createCalendarEvent",
                array(
                    'date_start' => $dateStart,
                    'duration_hours' => '9',
                ),
            ),
            array(
                "updateCalendarEvent",
                array(
                    'duration_hours' => '9',
                    'duration_minutes' => '9',
                ),
            ),
            array(
                "updateCalendarEvent",
                array(
                    'date_start' => $dateStart,
                    'duration_minutes' => '9',
                ),
            ),
            array(
                "updateCalendarEvent",
                array(
                    'date_start' => $dateStart,
                    'duration_hours' => '9',
                ),
            ),
       );
    }

    /**
     * @dataProvider dataProviderForCheckRequiredParams_ApiMethods_ExceptionThrownIfMissing
     * @param $args
     */
    public function testRequiredArgsPresent_MissingArgument_ExceptionThrown($apiMethod, $args)
    {
        $this->setExpectedException('SugarApiExceptionMissingParameter');
        $this->calendarEventsApi->$apiMethod($this->api, $args);
    }

    public function testCreateRecord_NotRecurringMeeting_CallsCreateMethod()
    {
        $calendarEventsApiMock = $this->getMock(
            'CalendarEventsApi',
            array('createRecord', 'generateRecurringCalendarEvents')
        );
        $calendarEventsApiMock->expects($this->once())
            ->method('createRecord');
        $calendarEventsApiMock->expects($this->never())
            ->method('generateRecurringCalendarEvents');

        $this->calendarEventsApi = $calendarEventsApiMock;
        $args = array(
            'module' => 'Meetings',
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
            'duration_hours' => '1',
            'duration_minutes' => '30',
        );

        $this->calendarEventsApi->createCalendarEvent($this->api, $args);
    }

    public function testCreateRecord_RecurringMeeting_CallsGenerateRecurringCalendarEventsMethod()
    {
        $meeting = SugarTestMeetingUtilities::createMeeting('', $this->api->user);

        $meeting->name = 'Test Meeting';
        $meeting->repeat_type = 'Daily';
        $meeting->date_start = '2014-08-01 13:00:00';
        $meeting->date_end = '2014-08-01 14:30:00';
        $meeting->duration_hours = 1;
        $meeting->duration_minutes = 30;
        $meeting->save();

        $args = array();
        $args['module'] = 'Meetings';
        $args['name'] = $meeting->name;
        $args['repeat_type'] = $meeting->repeat_type;
        $args['date_start'] = $meeting->date_start;
        $args['date_end'] = $meeting->date_end;
        $args['duration_hours'] = $meeting->duration_hours;
        $args['duration_minutes'] = $meeting->date_end;

        $calendarEventsApiMock = $this->getMock(
            'CalendarEventsApi',
            array('createRecord', 'generateRecurringCalendarEvents')
        );
        $calendarEventsApiMock->expects($this->once())
            ->method('createRecord')
            ->will($this->returnValue($meeting->toArray()));
        $calendarEventsApiMock->expects($this->once())
            ->method('generateRecurringCalendarEvents');

        $this->calendarEventsApi = $calendarEventsApiMock;

        $this->calendarEventsApi->createCalendarEvent($this->api, $args);
    }

    public function testCreateRecord_RecurringMeeting_ScheduleMeetingSeries_OK()
    {
        $args = array();
        $args['module'] = 'Meetings';
        $args['name'] = 'Test Meeting';
        $args['repeat_type'] = 'Daily';
        $args['repeat_interval'] = '1';
        $args['repeat_count'] = '3';
        $args['repeat_until'] = '';
        $args['repeat_dow'] = '';
        $args['repeat_parent_id'] = '';
        $args['date_start'] = $this->dateTimeAsISO('2014-12-25 13:00:00');
        $args['date_end'] = $this->dateTimeAsISO('2014-12-25 14:30:00');
        $args['duration_hours'] = 1;
        $args['duration_minutes'] = 30;

        $GLOBALS['calendarEvents'] = new CalendarEventsApiTest_CalendarEvents();
        $result = $this->calendarEventsApi->createCalendarEvent($this->api, $args);

        $this->assertFalse(empty($result['id']), "createRecord API Failed to Create Meeting");
        $this->meetingIds[] = $result['id'];

        $eventsCreated = $GLOBALS['calendarEvents']->getEventsCreated();
        $this->meetingIds = array_merge($this->meetingIds, array_keys($eventsCreated));

        $this->assertEquals($args['repeat_count'], count($eventsCreated) + 1, "Unexpected Number of Recurring Meetings");
    }

    public function testSendInviteEmails_SendsOneEmailPerParticipant()
    {
        $participants = array('foo', 'bar', 'biz', 'baz');

        $meeting = $this->getMock('Meeting', array('get_notification_recipients', 'send_assignment_notifications'));
        $meeting->expects($this->once())
            ->method('get_notification_recipients')
            ->will($this->returnValue($participants));
        $meeting->expects($this->exactly(count($participants)))->method('send_assignment_notifications');
        $meeting->id = create_guid();

        $api = $this->getMock('CalendarEventsApi', array('getLoadedAndFormattedBean', 'loadBean'));
        $api->expects($this->once())->method('getLoadedAndFormattedBean');
        $api->expects($this->once())->method('loadBean')->will($this->returnValue($meeting));

        $args = array(
            'module' => 'Meetings',
            'record' => $meeting->id,
        );

        $api->sendInviteEmails($this->api, $args);
    }

    public function testSendInviteEmails_ReturnsTheBean()
    {
        $meeting = $this->getMock('Meeting', array('get_notification_recipients'));
        $meeting->expects($this->once())->method('get_notification_recipients')->will($this->returnValue(array()));
        $meeting->id = create_guid();
        BeanFactory::setBeanClass('Meetings', get_class($meeting));
        BeanFactory::registerBean($meeting);

        $api = $this->getMock('CalendarEventsApi', array('loadBean'));
        $api->expects($this->any())->method('loadBean')->will($this->returnValue($meeting));

        $args = array(
            'module' => 'Meetings',
            'record' => $meeting->id,
        );

        $actual = $api->sendInviteEmails($this->api, $args);
        $this->assertEquals($meeting->id, $actual['id'], 'The returned bean should have the same ID');

        BeanFactory::unregisterBean($meeting);
    }

    private function dateTimeAsISO($dbDateTime)
    {
        global $timedate;
        return $timedate->asIso($timedate->fromDB($dbDateTime));
    }
}

class CalendarEventsApiTest_CalendarEvents extends CalendarEvents
{
    protected $eventsCreated = array();

    public function getEventsCreated()
    {
        return $this->eventsCreated;
    }

    protected function saveRecurring(SugarBean $parentBean, array $repeatDateTimeArray, array $args = array())
    {
        $this->eventsCreated = parent::saveRecurring($parentBean, $repeatDateTimeArray, $args);
    }
}
