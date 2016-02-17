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

require_once "modules/Calendar/CalendarUtils.php";
require_once('modules/Meetings/Meeting.php');

class CalendarUtilsTest extends Sugar_PHPUnit_Framework_TestCase {

   	/**
	 * @var TimeDate
	 */
	protected $time_date;

	protected $meeting_id = null;

	public function setUp()
	{
		global $current_user;
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
        $this->time_date = new TimeDate();

		$meeting = BeanFactory::newBean('Meetings');
		$meeting->name = 'Test Meeting';
		$meeting->assigned_user_id = $current_user->id;
		$meeting->save();
		$this->meeting = $meeting;

		$contact = BeanFactory::newBean('Contacts');
		$contact->first_name = 'MeetingTest';
		$contact->last_name = 'Contact';
		$contact->save();
		$this->contact = $contact;

		$lead = BeanFactory::newBean('Leads');
		$lead->first_name = 'MeetingTest';
		$lead->last_name = 'Lead';
		$lead->account_name = 'MeetingTest Lead Account';
		$lead->save();
		$this->lead = $lead;

        $addressee = BeanFactory::newBean('Addressees');
        $addressee->first_name = 'AddresseeTest';
        $addressee->last_name = 'Addressee';
        $addressee->save();
        $this->addressee = $addressee;
	}

	public function tearDown(){

		unset($GLOBALS['current_user']);
		unset($GLOBALS['mod_strings']);

		$GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$this->meeting->id}'");
		unset($this->meeting);

		$GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$this->contact->id}'");
		unset($this->contact);

		$GLOBALS['db']->query("DELETE FROM leads WHERE id = '{$this->lead->id}'");
		unset($this->lead);

        $GLOBALS['db']->query("DELETE FROM addressees WHERE id = " . $GLOBALS['db']->quoted($this->addressee->id));
        unset($this->addressee);

        SugarTestHelper::tearDown();
	}

	/**
	 *
	 */
	public function testGetInvites()
	{
		global $current_user;
        global $locale;
        /** @var Meeting $meeting */
		$meeting = \BeanFactory::getBean('Meetings');
		$meeting->id = create_guid();
		$meeting->new_with_id = false;
		$meeting->processed = true;
		$meeting->email_reminder_time = "20";
		$meeting->name = 'Test Email Reminder';
		$meeting->assigned_user_id = $current_user->id;
		$meeting->status = "Planned";
		$meeting->date_start = '2015-11-18 18:00:00';
		$meeting->save();

		$relate_values = array('contact_id'=>$this->contact->id,'meeting_id'=> $meeting->id);
		$data_values = array('accept_status'=> 'none');
		$meeting->set_relationship($meeting->rel_contacts_table, $relate_values, true, true, $data_values);

		$invitesBefore = CalendarUtils::getInvitees($meeting);

		$relate_values = array('lead_id'=>$this->lead->id,'meeting_id'=> $meeting->id);
		$data_values = array('accept_status'=> 'accept');
		$meeting->set_relationship($meeting->rel_leads_table, $relate_values, true, true, $data_values);

		$relate_values = array('user_id'=>$current_user->id,'meeting_id'=> $meeting->id);
		$data_values = array('accept_status'=> 'accept');
		$meeting->set_relationship($meeting->rel_users_table, $relate_values, true, true, $data_values);

        $relate_values = array('addressee_id' => $this->addressee->id, 'meeting_id' => $meeting->id);
        $data_values = array('accept_status' => 'accept');
        $meeting->set_relationship($meeting->rel_addressees_table, $relate_values, true, true, $data_values);

		$invitesAfter = CalendarUtils::getInvitees($meeting);

        $invitesBeforeExpected = array (
            array(
                'Contacts',
                $this->contact->id,
                '',
                'none',
                $locale->formatName($this->contact)
            ),
        );

        $invitesAfterExpected = array (
            array(
                'Contacts',
                $this->contact->id,
                '',
                'none',
                $locale->formatName($this->contact)
            ),
            array(
                'Leads',
                $this->lead->id,
                '',
                'accept',
                $locale->formatName($this->lead)
            ),
            array(
                'Users',
                $current_user->id,
                '',
                'accept',
                $locale->formatName($current_user)
            ),
            array(
                'Addressees',
                $this->addressee->id,
                '',
                'accept',
                $locale->formatName($this->addressee)
            ),
        );

		$this->assertEquals($invitesBeforeExpected, $invitesBefore);
		$this->assertEquals($invitesAfterExpected, $invitesAfter);
	}
}
