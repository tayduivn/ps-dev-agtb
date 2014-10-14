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
                "createRecord",
                array(
                    'duration_hours' => '9',
                    'duration_minutes' => '9',
                ),
            ),
            array(
                "createRecord",
                array(
                    'date_start' => $dateStart,
                    'duration_minutes' => '9',
                ),
            ),
            array(
                "createRecord",
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

        $this->calendarEventsApi->createRecord($this->api, $args);
    }

    public function testCreateRecord_RecurringMeeting_CallsGenerateRecurringCalendarEventsMethod()
    {
        $id = create_guid();
        $this->meetingIds[] = $id;

        $args = array();
        $args['module'] = 'Meetings';
        $args['id'] = $id;
        $args['name'] = 'Test Meetings';
        $args['repeat_type'] = 'Daily';
        $args['date_start'] = $this->dateTimeAsISO('2014-12-25 13:00:00');
        $args['date_end'] = $this->dateTimeAsISO('2014-12-25 14:30:00');
        $args['duration_hours'] = 1;
        $args['duration_minutes'] = 30;

        $calendarEventsApiMock = $this->getMock(
            'CalendarEventsApi',
            array('generateRecurringCalendarEvents')
        );
        $calendarEventsApiMock->expects($this->once())
            ->method('generateRecurringCalendarEvents');

        $this->calendarEventsApi = $calendarEventsApiMock;
        $result = $this->calendarEventsApi->createBean($this->api, $args);
        $this->meetingIds[] = $result->id;
    }

    public function testCreateRecord_RecurringMeeting_ScheduleMeetingSeries_OK()
    {
        $id = create_guid();
        $this->meetingIds[] = $id;

        $args = array();
        $args['module'] = 'Meetings';
        $args['id'] = $id;
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
        $result = $this->calendarEventsApi->createBean($this->api, $args);

        $this->assertFalse(empty($result->id), "createRecord API Failed to Create Meeting");

        $eventsCreated = $GLOBALS['calendarEvents']->getEventsCreated();
        $this->meetingIds = array_merge($this->meetingIds, array_keys($eventsCreated));

        $this->assertEquals($args['repeat_count'], count($eventsCreated) + 1, "Unexpected Number of Recurring Meetings");
    }

    public function testUpdateCalendarEvent_RecurringAndAllRecurrences_UpdatesAllRecurrences()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $args = array(
            'module' => 'Meetings',
            'record' => $meeting->id,
            'all_recurrences' => 'true',
        );

        $this->mockCalendarEventsIsEventRecurring(true);

        $this->mockCalendarEventsApiForUpdate();
        $this->calendarEventsApi->expects($this->any())
            ->method('loadBean')
            ->will($this->returnValue($meeting));
        $this->calendarEventsApi->expects($this->never())
            ->method('updateRecord');
        $this->calendarEventsApi->expects($this->once())
            ->method('updateRecurringCalendarEvent');

        $this->calendarEventsApi->updateCalendarEvent($this->api, $args);
    }

    public function testUpdateCalendarEvent_RecurringAndNotAllRecurrences_UpdatesSingleEventNoRecurrenceFields()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $argsExpected = array(
            'module' => 'Meetings',
            'record' => $meeting->id,
        );
        $args = array_merge($argsExpected, array(
            'repeat_type' => 'foo1',
            'repeat_interval' => 'foo2',
            'repeat_dow' => 'foo3',
            'repeat_until' => 'foo4',
            'repeat_count' => 'foo5',
        ));

        $this->mockCalendarEventsIsEventRecurring(true);

        $this->mockCalendarEventsApiForUpdate();
        $this->calendarEventsApi->expects($this->any())
            ->method('loadBean')
            ->will($this->returnValue($meeting));
        $this->calendarEventsApi->expects($this->once())
            ->method('updateRecord')
            ->with($this->api, $argsExpected);
        $this->calendarEventsApi->expects($this->never())
            ->method('updateRecurringCalendarEvent');

        $this->calendarEventsApi->updateCalendarEvent($this->api, $args);
    }

    public function testUpdateCalendarEvent_NonRecurring_UpdatesSingleEvent()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $args = array(
            'module' => 'Meetings',
            'record' => $meeting->id,
        );

        $this->mockCalendarEventsIsEventRecurring(false);

        $this->mockCalendarEventsApiForUpdate();
        $this->calendarEventsApi->expects($this->any())
            ->method('loadBean')
            ->will($this->returnValue($meeting));
        $this->calendarEventsApi->expects($this->once())
            ->method('updateRecord');
        $this->calendarEventsApi->expects($this->never())
            ->method('updateRecurringCalendarEvent');
        $this->calendarEventsApi->expects($this->never())
            ->method('generateRecurringCalendarEvents');

        $this->calendarEventsApi->updateCalendarEvent($this->api, $args);
    }

    public function testUpdateCalendarEvent_NonRecurringChangedToRecurring_UpdatesEventGeneratesRecurring()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $args = array(
            'module' => 'Meetings',
            'record' => $meeting->id,
        );

        $this->mockCalendarEventsIsEventRecurring(false);
        //second time called will return true
        $GLOBALS['calendarEvents']->expects($this->at(1))
            ->method('isEventRecurring')
            ->will($this->returnValue(true));

        $this->mockCalendarEventsApiForUpdate();
        $this->calendarEventsApi->expects($this->any())
            ->method('loadBean')
            ->will($this->returnValue($meeting));
        $this->calendarEventsApi->expects($this->once())
            ->method('updateRecord');
        $this->calendarEventsApi->expects($this->never())
            ->method('updateRecurringCalendarEvent');
        $this->calendarEventsApi->expects($this->once())
            ->method('generateRecurringCalendarEvents');

        $this->calendarEventsApi->updateCalendarEvent($this->api, $args);
    }

    public function testUpdateRecurringCalendarEvent_RecurringAfterUpdate_SavesRecurringEvents()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->repeat_parent_id = '';

        $args = array(
            'module' => 'Meetings',
            'record' => $meeting->id,
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
            'duration_hours' => '1',
            'duration_minutes' => '30',
        );

        $this->calendarEventsApi = $this->getMock(
            'CalendarEventsApi',
            array('updateBean', 'getLoadedAndFormattedBean')
        );
        $this->calendarEventsApi->expects($this->once())
            ->method('updateBean');
        $this->calendarEventsApi->expects($this->once())
            ->method('getLoadedAndFormattedBean')
            ->will($this->returnValue(array()));

        $GLOBALS['calendarEvents'] = $this->getMock(
            'CalendarEvents',
            array('isEventRecurring', 'saveRecurringEvents')
        );
        $GLOBALS['calendarEvents']->expects($this->any())
            ->method('isEventRecurring')
            ->will($this->returnValue(true));
        $GLOBALS['calendarEvents']->expects($this->once())
            ->method('saveRecurringEvents');

        $this->calendarEventsApi->updateRecurringCalendarEvent($meeting, $this->api, $args);
    }

    public function testUpdateRecurringCalendarEvent_NonRecurringAfterUpdate_RemovesRecurringEvents()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->repeat_parent_id = '';

        $args = array(
            'module' => 'Meetings',
            'record' => $meeting->id,
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
            'duration_hours' => '1',
            'duration_minutes' => '30',
        );

        $this->calendarEventsApi = $this->getMock(
            'CalendarEventsApi',
            array('updateBean', 'deleteRecurrences', 'getLoadedAndFormattedBean')
        );
        $this->calendarEventsApi->expects($this->once())
            ->method('updateBean');
        $this->calendarEventsApi->expects($this->once())
            ->method('deleteRecurrences');
        $this->calendarEventsApi->expects($this->once())
            ->method('getLoadedAndFormattedBean')
            ->will($this->returnValue(array()));

        $GLOBALS['calendarEvents'] = $this->getMock(
            'CalendarEvents',
            array('isEventRecurring', 'saveRecurringEvents')
        );
        $GLOBALS['calendarEvents']->expects($this->any())
            ->method('isEventRecurring')
            ->will($this->returnValue(false));
        $GLOBALS['calendarEvents']->expects($this->never())
            ->method('saveRecurringEvents');

        $this->calendarEventsApi->updateRecurringCalendarEvent($meeting, $this->api, $args);
    }

    /**
     * @expectedException SugarApiException
     */
     public function testUpdateRecurringCalendarEvent_UsingChildRecord_ThrowsException()
     {
         $meeting                   = BeanFactory::newBean('Meetings');
         $meeting->id               = create_guid();
         $meeting->repeat_parent_id = 'foo';

         $args = array(
             'module'           => 'Meetings',
             'record'           => $meeting->id,
             'date_start'       => $this->dateTimeAsISO('2014-12-25 13:00:00'),
             'duration_hours'   => '1',
             'duration_minutes' => '30',
         );

         $this->calendarEventsApi = $this->getMock(
             'CalendarEventsApi',
             array('updateBean')
         );
         $this->calendarEventsApi->expects($this->never())
             ->method('updateBean');

         $this->calendarEventsApi->updateRecurringCalendarEvent(
             $meeting,
             $this->api,
             $args
         );
     }

    public function testCreateRecord_CreateRecordFails_rebuildFBCacheNotInvoked()
    {
        $calendarEventsApiMock = $this->getMock(
            'CalendarEventsApi',
            array('createRecord',)
        );
        $calendarEventsApiMock->expects($this->once())
            ->method('createRecord')
            ->will($this->returnValue(array()));

        $this->calendarEventsApi = $calendarEventsApiMock;
        $args = array(
            'module' => 'Meetings',
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
            'duration_hours' => '1',
            'duration_minutes' => '30',
        );
        $this->calendarEventsApi->createRecord($this->api, $args);
    }

    public function testCreateRecord_NotRecurring_rebuildFBCacheInvoked()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $this->meetingIds[] = $meeting->id;

        $GLOBALS['calendarEvents'] = $this->getMock(
            'CalendarEvents',
            array('isEventRecurring', 'rebuildFreeBusyCache')
        );
        $GLOBALS['calendarEvents']->expects($this->any())
            ->method('isEventRecurring')
            ->will($this->returnValue(false));
        $GLOBALS['calendarEvents']->expects($this->once())
            ->method('rebuildFreeBusyCache');

        $calendarEventsApiMock = $this->getMock(
            'CalendarEventsApi',
            array('loadBean', 'generateRecurringCalendarEvents')
        );
        $calendarEventsApiMock->expects($this->once())
            ->method('loadBean')
            ->will($this->returnValue($meeting));
        $calendarEventsApiMock->expects($this->never())
            ->method('generateRecurringCalendarEvents');

        $this->calendarEventsApi = $calendarEventsApiMock;
        $args = array(
            'module' => 'Meetings',
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
            'duration_hours' => '1',
            'duration_minutes' => '30',
        );
        $this->calendarEventsApi->createRecord($this->api, $args);
    }

    public function testCreateRecord_Recurring_rebuildFBCacheNotInvoked()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $this->meetingIds[] = $meeting->id;

        $GLOBALS['calendarEvents'] = $this->getMock(
            'CalendarEvents',
            array('isEventRecurring', 'rebuildFreeBusyCache')
        );
        $GLOBALS['calendarEvents']->expects($this->any())
            ->method('isEventRecurring')
            ->will($this->returnValue(true));
        $GLOBALS['calendarEvents']->expects($this->never())
            ->method('rebuildFreeBusyCache');

        $calendarEventsApiMock = $this->getMock(
            'CalendarEventsApi',
            array('loadBean', 'generateRecurringCalendarEvents')
        );
        $calendarEventsApiMock->expects($this->once())
            ->method('loadBean')
            ->will($this->returnValue($meeting));
        $calendarEventsApiMock->expects($this->once())
            ->method('generateRecurringCalendarEvents');

        $this->calendarEventsApi = $calendarEventsApiMock;
        $args = array(
            'module' => 'Meetings',
            'date_start' => $this->dateTimeAsISO('2014-12-25 13:00:00'),
            'duration_hours' => '1',
            'duration_minutes' => '30',
        );
        $this->calendarEventsApi->createRecord($this->api, $args);
    }

    /**
     * @expectedException     SugarApiExceptionMissingParameter
     */
    public function testUpdateCalendarEvent_EventIdMissing_rebuildFBCacheNotInvoked()
    {
        $args = array();
        $this->calendarEventsApi->updateCalendarEvent($this->api, $args);
    }

    /**
     * @expectedException     SugarApiExceptionNotFound
     */
    public function testUpdateCalendarEvent_EventNotFound_rebuildFBCacheNotInvoked()
    {
        $args = array();
        $args['module'] = 'Meetings';
        $args['record'] = create_guid();
        $this->calendarEventsApi->updateCalendarEvent($this->api, $args);
    }

    public function testUpdateCalendarEvent_isRecurringAndAllRecurrences_rebuildFBCacheNotInvoked()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $args = array(
            'module' => 'Meetings',
            'record' => $meeting->id,
            'all_recurrences' => 'true',
        );

        $GLOBALS['calendarEvents'] = $this->getMock(
            'CalendarEvents',
            array('isEventRecurring', 'rebuildFreeBusyCache')
        );
        $GLOBALS['calendarEvents']->expects($this->any())
            ->method('isEventRecurring')
            ->will($this->returnValue(true));
        $GLOBALS['calendarEvents']->expects($this->never())
            ->method('rebuildFreeBusyCache');

        $this->mockCalendarEventsApiForUpdate();
        $this->calendarEventsApi->expects($this->any())
            ->method('loadBean')
            ->will($this->returnValue($meeting));
        $this->calendarEventsApi->expects($this->once())
            ->method('updateRecurringCalendarEvent')
            ->will($this->returnValue(array()));

        $this->calendarEventsApi->updateCalendarEvent($this->api, $args);
    }

    public function testUpdateCalendarEvent_isRecurringAndNotAllRecurrences_rebuildFBCacheInvoked()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $args = array(
            'module' => 'Meetings',
            'record' => $meeting->id,
            'all_recurrences' => 'false',
        );

        $GLOBALS['calendarEvents'] = $this->getMock(
            'CalendarEvents',
            array('isEventRecurring', 'rebuildFreeBusyCache')
        );
        $GLOBALS['calendarEvents']->expects($this->any())
            ->method('isEventRecurring')
            ->will($this->returnValue(true));
        $GLOBALS['calendarEvents']->expects($this->once())
            ->method('rebuildFreeBusyCache');

        $this->mockCalendarEventsApiForUpdate();
        $this->calendarEventsApi->expects($this->any())
            ->method('loadBean')
            ->will($this->returnValue($meeting));
        $this->calendarEventsApi->expects($this->once())
            ->method('updateRecord')
            ->will($this->returnValue(array()));

        $this->calendarEventsApi->updateCalendarEvent($this->api, $args);
    }

    public function testUpdateCalendarEvent_NonRecurringChangedToRecurring_rebuildFBCacheNotInvoked()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $args = array(
            'module' => 'Meetings',
            'record' => $meeting->id,
        );

        $GLOBALS['calendarEvents'] = $this->getMock(
            'CalendarEvents',
            array('isEventRecurring', 'rebuildFreeBusyCache')
        );

        //first time called will return false
        $GLOBALS['calendarEvents']->expects($this->at(0))
            ->method('isEventRecurring')
            ->will($this->returnValue(false));
        //second time called will return true
        $GLOBALS['calendarEvents']->expects($this->at(1))
            ->method('isEventRecurring')
            ->will($this->returnValue(true));
        $GLOBALS['calendarEvents']->expects($this->never())
            ->method('rebuildFreeBusyCache');

        $this->mockCalendarEventsApiForUpdate();
        $this->calendarEventsApi->expects($this->any())
            ->method('loadBean')
            ->will($this->returnValue($meeting));
        $this->calendarEventsApi->expects($this->once())
            ->method('updateRecord');
        $this->calendarEventsApi->expects($this->never())
            ->method('updateRecurringCalendarEvent');
        $this->calendarEventsApi->expects($this->once())
            ->method('generateRecurringCalendarEvents');

        $this->calendarEventsApi->updateCalendarEvent($this->api, $args);
    }

    public function testUpdateCalendarEvent_NonRecurring_rebuildFBCacheInvoked()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $args = array(
            'module' => 'Meetings',
            'record' => $meeting->id,
        );

        $GLOBALS['calendarEvents'] = $this->getMock(
            'CalendarEvents',
            array('isEventRecurring', 'rebuildFreeBusyCache')
        );

        //first time called will return false
        $GLOBALS['calendarEvents']->expects($this->exactly(2))
            ->method('isEventRecurring')
            ->will($this->returnValue(false));
        $GLOBALS['calendarEvents']->expects($this->once())
            ->method('rebuildFreeBusyCache');

        $this->mockCalendarEventsApiForUpdate();
        $this->calendarEventsApi->expects($this->exactly(2))
            ->method('loadBean')
            ->will($this->returnValue($meeting));
        $this->calendarEventsApi->expects($this->once())
            ->method('updateRecord');
        $this->calendarEventsApi->expects($this->never())
            ->method('updateRecurringCalendarEvent');
        $this->calendarEventsApi->expects($this->never())
            ->method('generateRecurringCalendarEvents');

        $this->calendarEventsApi->updateCalendarEvent($this->api, $args);
    }

    public function testDeleteRecord_SingleOccurrence_rebuildFBCacheInvoked()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $args = array(
            'module' => 'Meetings',
            'record' => $meeting->id,
            'all_recurrences' => 'false',
        );

        $GLOBALS['calendarEvents'] = $this->getMock(
            'CalendarEvents',
            array('rebuildFreeBusyCache')
        );
        $GLOBALS['calendarEvents']->expects($this->once())
            ->method('rebuildFreeBusyCache');

        $calendarEventsApiMock = $this->getMock(
            'CalendarEventsApi',
            array('deleteRecord', 'deleteRecordAndRecurrences')
        );
        $calendarEventsApiMock->expects($this->once())
            ->method('deleteRecord');
        $calendarEventsApiMock->expects($this->never())
            ->method('deleteRecordAndRecurrences');

        $this->calendarEventsApi = $calendarEventsApiMock;
        $this->calendarEventsApi->deleteCalendarEvent($this->api, $args);
    }

    public function testDeleteRecord_AllOccurrences_rebuildFBCacheInvoked()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();

        $args = array(
            'module' => 'Meetings',
            'record' => $meeting->id,
            'all_recurrences' => 'true',
        );

        $GLOBALS['calendarEvents'] = $this->getMock(
            'CalendarEvents',
            array('rebuildFreeBusyCache')
        );
        $GLOBALS['calendarEvents']->expects($this->once())
            ->method('rebuildFreeBusyCache');

        $calendarEventsApiMock = $this->getMock(
            'CalendarEventsApi',
            array('deleteRecord', 'deleteRecordAndRecurrences')
        );
        $calendarEventsApiMock->expects($this->never())
            ->method('deleteRecord');
        $calendarEventsApiMock->expects($this->once())
            ->method('deleteRecordAndRecurrences');

        $this->calendarEventsApi = $calendarEventsApiMock;
        $this->calendarEventsApi->deleteCalendarEvent($this->api, $args);
    }

    public function testBuildSearchParams_ConvertsRestArgsToLegacyParams()
    {
        $this->calendarEventsApi = new CalendarEventsApiTest_CalendarEventsApi();

        $args = array(
            'q' => 'woo',
            'module_list' => 'Foo,Bar',
            'search_fields' => 'foo_search_field,bar_search_field',
            'fields' => 'foo_field,bar_field',
        );

        $expectedParams = array(
            array(
                'modules' => array('Foo', 'Bar'),
                'group' => 'or',
                'field_list' => array(
                    'foo_field',
                    'bar_field',
                    'foo_search_field',
                    'bar_search_field',
                ),
                'conditions' => array(
                    array(
                        'name' => 'foo_search_field',
                        'op' => 'starts_with',
                        'value' => 'woo',
                    ),
                    array(
                        'name' => 'bar_search_field',
                        'op' => 'starts_with',
                        'value' => 'woo',
                    ),
                ),
            ),
        );

        $this->assertEquals(
            $expectedParams,
            $this->calendarEventsApi->publicBuildSearchParams($args),
            'Rest API args should be transformed correctly into legacy query params'
        );
    }

    public function testTransformInvitees_ConvertsLegacyResultsToUnifiedSearchForm()
    {
        $this->calendarEventsApi = new CalendarEventsApiTest_CalendarEventsApi();
        $args = array(
            'q' => 'bar',
            'fields' => 'first_name,last_name,email,account_name',
            'search_fields' => 'first_name,last_name,email,account_name',
        );

        $bean = new SugarBean(); //dummy, mocking out formatBean anyway
        $formattedBean = array(
            '_module' => 'Contacts',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'account_name' => 'Baz Inc',
            'email' => array(
                array('email_address' => 'foo@baz.com'),
                array('email_address' => 'bar@baz.com'),
            ),
        );

        $this->calendarEventsApi = $this->getMock(
            'CalendarEventsApiTest_CalendarEventsApi',
            array('formatBean')
        );
        $this->calendarEventsApi->expects($this->once())
            ->method('formatBean')
            ->will($this->returnValue($formattedBean));

        $searchResults = array(
            'result' => array(
                'list' => array(
                    array('bean' => $bean)
                ),
            ),
        );

        $expectedInvitee = array_merge($formattedBean, array(
            '_search' => array(
                'highlighted' => array(
                    'last_name' => array(
                        'text' => 'Bar',
                        'module' => 'Contacts',
                        'label' => 'LBL_LAST_NAME',
                    ),
                ),
            ),
        ));

        $expectedInvitees = array(
            'next_offset' => -1,
            'records' => array(
                $expectedInvitee
            ),
        );;

        $this->assertEquals(
            $expectedInvitees,
            $this->calendarEventsApi->publicTransformInvitees($this->api, $args, $searchResults),
            'Legacy search results should be transformed correctly into unified search format'
        );
    }

    public function testGetMatchedFields_MatchesRegularFieldsCorrectly()
    {
        $this->calendarEventsApi = new CalendarEventsApiTest_CalendarEventsApi();

        $args = array(
            'q' => 'foo',
            'search_fields' => 'first_name,last_name,email,account_name',
        );

        $record = array(
            '_module' => 'Contacts',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'account_name' => 'Baz Inc',
            'email' => array(
                array('email_address' => 'woo@baz.com'),
                array('email_address' => 'bar@baz.com'),
            ),
        );

        $expectedMatchedFields = array(
            'first_name' => array(
                'text' => 'Foo',
                'module' => 'Contacts',
                'label' => 'LBL_FIRST_NAME',
            ),
        );

        $this->assertEquals(
            $expectedMatchedFields,
            $this->calendarEventsApi->publicGetMatchedFields($args, $record, 1),
            'Should match search query to field containing search text'
        );
    }

    public function testGetMatchedFields_MatchesEmailFieldCorrectly()
    {
        $this->calendarEventsApi = new CalendarEventsApiTest_CalendarEventsApi();

        $args = array(
            'q' => 'woo',
            'search_fields' => 'first_name,last_name,email,account_name',
        );

        $record = array(
            '_module' => 'Contacts',
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'account_name' => 'Baz Inc',
            'email' => array(
                array('email_address' => 'woo@baz.com'),
                array('email_address' => 'bar@baz.com'),
            ),
        );

        $expectedMatchedFields = array(
            'email' => array(
                'text' => 'woo@baz.com',
                'module' => 'Contacts',
                'label' => 'LBL_ANY_EMAIL',
            ),
        );

        $this->assertEquals(
            $expectedMatchedFields,
            $this->calendarEventsApi->publicGetMatchedFields($args, $record, 1),
            'Should match search query to field containing search text'
        );
    }

    private function dateTimeAsISO($dbDateTime)
    {
        global $timedate;
        return $timedate->asIso($timedate->fromDB($dbDateTime));
    }

    private function mockCalendarEventsApiForUpdate()
    {
        $calendarEventsApiMock = $this->getMock(
            'CalendarEventsApi',
            array(
                'updateRecord',
                'updateRecurringCalendarEvent',
                'loadBean',
                'generateRecurringCalendarEvents',
            )
        );
        $this->calendarEventsApi = $calendarEventsApiMock;
    }

    private function mockCalendarEventsIsEventRecurring($isRecurring)
    {
        $GLOBALS['calendarEvents'] = $this->getMock(
            'CalendarEvents',
            array('isEventRecurring')
        );
        $GLOBALS['calendarEvents']->expects($this->at(0))
            ->method('isEventRecurring')
            ->will($this->returnValue($isRecurring));
    }
}

class CalendarEventsApiTest_CalendarEvents extends CalendarEvents
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

class CalendarEventsApiTest_CalendarEventsApi extends CalendarEventsApi
{
    public function publicBuildSearchParams($args)
    {
        return $this->buildSearchParams($args);
    }

    public function publicTransformInvitees($api, $args, $searchResults)
    {
        return $this->transformInvitees($api, $args, $searchResults);
    }

    public function publicGetMatchedFields($args, $record, $maxFields)
    {
        return $this->getMatchedFields($args, $record, $maxFields);
    }
}

