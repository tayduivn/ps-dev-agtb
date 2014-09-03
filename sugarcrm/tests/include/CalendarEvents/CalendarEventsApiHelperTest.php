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

require_once('modules/Meetings/MeetingsApiHelper.php');

class CalendarEventsApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $api;

    public function happyPathProvider()
    {
        return array(
            array(1, 1),
            array('1', '1'),
            array(0, 0),
            array('0', '0'),
        );
    }

    public function throwsMissingParameterExceptionProvider()
    {
        $now = $GLOBALS['timedate']->nowDb();
        return array(
            array(null, 1, 1),
            array($now, null, 1),
            array($now, 1, null),
            array($now, '', 1),
            array($now, 1, ''),
        );
    }

    public function throwsInvalidParameterExceptionProvider()
    {
        return array(
            array('a', 1),
            array(1, 'a'),
            array(-1, 1),
            array('-1', '1'),
            array(1, -1),
            array('1', '-1'),
            array(1.5, 1),
            array('1.5', '1'),
            array(1, 1.5),
            array('1', '1.5'),
        );
    }

    public function setUp()
    {
        parent::setUp();
        $this->api = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @group bug
     * @group SI49195
     */
    public function testPopulateFromApi_ShouldNotUpdateVcal()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->date_start = $GLOBALS['timedate']->nowDb();
        $meeting->duration_hours = 1;
        $meeting->duration_minutes = '0';
        $meeting->assigned_user_id = 1;

        $helper = $this->getMock('MeetingsApiHelper', array('getInvitees'), array($this->api));
        $helper->method('getInvitees')->will($this->returnValue(array()));

        $helper->populateFromApi($meeting, array());
        $this->assertFalse($meeting->update_vcal, 'Should have been set to false');
    }

    public function testPopulateFromApi_TheExistingInviteesAreAddedToTheBean()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->date_start = $GLOBALS['timedate']->nowDb();
        $meeting->duration_hours = 1;
        $meeting->duration_minutes = '0';
        $meeting->assigned_user_id = $GLOBALS['current_user']->id;

        $users = array_map('create_guid', array_fill(0, 5, null));
        $leads = array_map('create_guid', array_fill(0, 5, null));
        $contacts = array_map('create_guid', array_fill(0, 5, null));

        $map = array(
            array($meeting, 'users', $users),
            array($meeting, 'leads', $leads),
            array($meeting, 'contacts', $contacts),
        );
        $helper = $this->getMock('MeetingsApiHelper', array('getInvitees'), array($this->api));
        $helper->method('getInvitees')->will($this->returnValueMap($map));

        $helper->populateFromApi($meeting, array());
        $this->assertCount(
            count($users) + 1,
            $meeting->users_arr,
            'Should have the number of generated users plus the assigned user'
        );
        $this->assertCount(count($leads), $meeting->leads_arr, 'Should have the number of generated leads');
        $this->assertCount(count($contacts), $meeting->contacts_arr, 'Should have the number of generated contacts');
    }

    public function testPopulateFromApi_TheEventIsNew_TheAssignedUserIsNotTheCurrentUser_BothUsersAreInvited()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->new_with_id = true;
        $meeting->date_start = $GLOBALS['timedate']->nowDb();
        $meeting->duration_hours = 1;
        $meeting->duration_minutes = '0';
        $meeting->assigned_user_id = create_guid();

        $helper = $this->getMock('MeetingsApiHelper', array('getInvitees'), array($this->api));
        $helper->method('getInvitees')->will($this->returnValue(array()));

        $helper->populateFromApi($meeting, array());
        $this->assertCount(2, $meeting->users_arr, 'Should include both the assigned user and current user');
    }

    public function testPopulateFromApi_TheEventIsExisting_TheAssignedUserIsNotTheCurrentUser_TheCurrentUserIsNotInvited()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->date_start = $GLOBALS['timedate']->nowDb();
        $meeting->duration_hours = 1;
        $meeting->duration_minutes = '0';
        $meeting->assigned_user_id = create_guid();

        $helper = $this->getMock('MeetingsApiHelper', array('getInvitees'), array($this->api));
        $helper->method('getInvitees')->will($this->returnValue(array()));

        $helper->populateFromApi($meeting, array());
        $this->assertCount(1, $meeting->users_arr, 'Should only contain the assigned user');
        $this->assertContains($meeting->assigned_user_id, $meeting->users_arr, 'The assigned user was not found');
    }

    /**
     * @dataProvider happyPathProvider
     */
    public function testPopulateFromApi_ReturnsTrue($hours, $minutes)
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->date_start = $GLOBALS['timedate']->nowDb();
        $meeting->duration_hours = $hours;
        $meeting->duration_minutes = $minutes;

        $helper = new MeetingsApiHelper($this->api);
        $actual = $helper->populateFromApi($meeting, array());
        $this->assertTrue($actual, 'The happy path should have returned true');
    }

    /**
     * @dataProvider throwsMissingParameterExceptionProvider
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testPopulateFromApi_ThrowsMissingParameterException($starts, $hours, $minutes)
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->date_start = $starts;
        $meeting->duration_hours = $hours;
        $meeting->duration_minutes = $minutes;

        $helper = new MeetingsApiHelper($this->api);
        $helper->populateFromApi($meeting, array());
    }

    /**
     * @dataProvider throwsInvalidParameterExceptionProvider
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testPopulateFromApi_ThrowsInvalidParameterException($hours, $minutes)
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->date_start = $GLOBALS['timedate']->nowDb();
        $meeting->duration_hours = $hours;
        $meeting->duration_minutes = $minutes;

        $helper = new MeetingsApiHelper($this->api);
        $helper->populateFromApi($meeting, array());
    }

    public function testFormatForApi_MeetingIsRelatedToAContact_TheNameOfTheContactIsAddedToTheResponse()
    {
        $meeting = $this->getMock('Meeting', array('ACLAccess'));
        $meeting->method('ACLAccess')->will($this->returnValue(true));
        BeanFactory::setBeanClass('Meetings', get_class($meeting));
        $meeting->id = create_guid();
        BeanFactory::registerBean($meeting);

        $contact = SugarTestContactUtilities::createContact();
        $meeting->contact_id = $contact->id;

        $helper = new MeetingsApiHelper($this->api);
        $data = $helper->formatForApi($meeting);
        $this->assertEquals($data['contact_name'], $contact->full_name, "The contact's name does not match");

        BeanFactory::unregisterBean($meeting);
        BeanFactory::setBeanClass('Meetings');
    }
}
