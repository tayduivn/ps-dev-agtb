<?php 
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
require_once('modules/Meetings/Meeting.php');
require_once('modules/Meetings/MeetingFormBase.php');
require_once("modules/Activities/EmailReminder.php");


class MeetingTest extends Sugar_PHPUnit_Framework_TestCase
{
	public $meeting = null;
	public $contact = null;
	public $lead = null;

	public function setUp()
    {
        global $current_user, $currentModule ;
		$mod_strings = return_module_language($GLOBALS['current_language'], "Meetings");
		$current_user = SugarTestUserUtilities::createAnonymousUser();

		$meeting = BeanFactory::newBean('Meetings');
		$meeting->id = uniqid();
        $meeting->name = 'Test Meeting';
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
	}
	
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['mod_strings']);
        
        $GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$this->meeting->id}'");
        unset($this->meeting);

		$GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$this->contact->id}'");
        unset($this->contact);

		$GLOBALS['db']->query("DELETE FROM leads WHERE id = '{$this->lead->id}'");
        unset($this->lead);

        unset($_POST);
    }
	
	function testMeetingTypeSaveDefault() {
		// Assert doc type default is 'Sugar'
    	$this->assertEquals($this->meeting->type, 'Sugar');
	}

    function testMeetingTypeSaveDefaultInDb() {
        $query = "SELECT * FROM meetings WHERE id = '{$this->meeting->id}'";
        $result = $GLOBALS['db']->query($query);
    	while($row = $GLOBALS['db']->fetchByAssoc($result))
		// Assert doc type default is 'Sugar'
    	$this->assertEquals($row['type'], 'Sugar');
	}
	
	function testRecurringFromOutlook(){
		$meeting = new Meeting();
		$meeting->id = uniqid();
		$meeting->name = 'Test Meeting Recurring';
		
		$meeting->recurring_source = 'Outlook';
        // can't edit
		$this->assertFalse($meeting->ACLAccess('edit'));
		
		$meeting->recurring_source = '';
		// can edit
		$this->assertTrue($meeting->ACLAccess('edit'));
	}
	
	function testEmailReminder(){
		$meeting = new Meeting();
		$meeting->email_reminder_time = "20";
		$meeting->name = 'Test Email Reminder';
		$meeting->status = "Planned";
		$meeting->date_start = $GLOBALS['timedate']->nowDb();
		$meeting->save();
		
		$er = new EmailReminder();
		$to_remind = $er->getMeetingsForRemind();

		$this->assertTrue(in_array($meeting->id,$to_remind));
		$GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$meeting->id}'");
	}

	public function testMeetingFormBaseRelationshipsSetTest() {
		global $db;
		// setup $_POST
		$_POST = array();
		$_POST['name'] = 'MeetingTestMeeting';
		$_POST['lead_invitees'] = $this->lead->id;
		$_POST['contact_invitees'] = $this->contact->id;
		$_POST['assigned_user_id'] = $GLOBALS['current_user']->id;
		// call handleSave
		$mfb = new MeetingFormBase();
		$meeting = $mfb->handleSave(null,false, false);
		// verify the relationships exist
		$q = "SELECT mu.contact_id FROM meetings_contacts mu WHERE mu.meeting_id = '{$meeting->id}'";
        $r = $db->query($q);
        $a = $db->fetchByAssoc($r);
        $this->assertEquals($this->contact->id, $a['contact_id'], "Contact wasn't set as an invitee");

        $q = "SELECT mu.lead_id FROM meetings_leads mu WHERE mu.meeting_id = '{$meeting->id}'";
        $r = $db->query($q);
        $a = $db->fetchByAssoc($r);
        $this->assertEquals($this->lead->id, $a['lead_id'], "Lead wasn't set as an invitee");

		$q = "SELECT mu.accept_status FROM meetings_users mu WHERE mu.meeting_id = '{$meeting->id}' AND user_id = '{$GLOBALS['current_user']->id}'";
        $r = $db->query($q);
        $a = $db->fetchByAssoc($r);
        $this->assertEquals('accept', $a['accept_status'], "Meeting wasn't accepted by the User");


	}
}
?>
