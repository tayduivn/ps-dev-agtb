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

    public function setUp()
    {
        parent::setUp();

        $this->api = SugarTestRestUtilities::getRestServiceMock();
        $this->api->user = SugarTestUserUtilities::createAnonymousUser(false, false);
        $this->api->user->id = 'foo';
        $GLOBALS['current_user'] = $this->api->user;

        $this->calendarEventsApi = new CalendarEventsApi();
    }

    public function tearDown()
    {
        BeanFactory::setBeanClass('Meetings');
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testDeleteRecord_NotRecurringMeeting_CallsDeleteMethod()
    {
        $calendarEventsApiMock = $this->getMock('CalendarEventsApi', array('deleteRecord', 'deleteRecordAndRecurrences'));
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

        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        BeanFactory::registerBean($meeting);

        $args = array(
            'module' => 'Meetings',
            'record' => $meeting->id,
        );

        $this->calendarEventsApi->deleteCalendarEvent($this->api, $args);

        BeanFactory::unregisterBean($meeting);
    }

    public function testDeleteRecord_RecurringMeeting_CallsDeleterRecurrenceMethod()
    {
        $calendarEventsApiMock = $this->getMock('CalendarEventsApi', array('deleteRecord', 'deleteRecordAndRecurrences'));
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

        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        BeanFactory::registerBean($meeting);

        $args = array(
            'module' => 'Meetings',
            'record' => $meeting->id,
            'all_recurrences' => 'true',
        );

        $this->calendarEventsApi->deleteCalendarEvent($this->api, $args);

        BeanFactory::unregisterBean($meeting);
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

        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        BeanFactory::registerBean($meeting);

        $args = array(
            'module' => 'Meetings',
            'record' => $meeting->id,
        );

        $this->calendarEventsApi->deleteRecordAndRecurrences($this->api, $args);

        BeanFactory::unregisterBean($meeting);
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

        $this->assertEquals($parentMeeting->id, $results['id'], 'The return id of the delete call should be the parent meeting id');

        $parentMeeting = BeanFactory::getBean('Meetings', $parentMeeting->id);
        $meeting1 = BeanFactory::getBean('Meetings', $meeting1->id);
        $meeting2 = BeanFactory::getBean('Meetings', $meeting2->id);

        $this->assertEquals($parentMeeting->deleted, 0, 'The parent meeting record should be deleted');
        $this->assertEquals($meeting1->deleted, 0, 'The meeting1 record should be deleted');
        $this->assertEquals($meeting2->deleted, 0, 'The meeting2 record should be deleted');
    }
}
