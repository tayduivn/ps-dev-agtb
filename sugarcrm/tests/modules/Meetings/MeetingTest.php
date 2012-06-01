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

class MeetingTest extends Sugar_PHPUnit_Framework_TestCase
{
	var $meeting = null;
	
	public function setUp()
    {
        global $current_user, $currentModule ;
		$mod_strings = return_module_language($GLOBALS['current_language'], "Meetings");
		$current_user = SugarTestUserUtilities::createAnonymousUser();

		$meeting = new Meeting();
		$meeting->id = uniqid();
        $meeting->name = 'Test Meeting';
        $meeting->save();
		$this->meeting = $meeting;
	}
	
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['mod_strings']);
        
        $GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$this->meeting->id}'");
        unset($this->meeting);
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
		
		require_once("modules/Activities/EmailReminder.php");
		$er = new EmailReminder();
		$to_remind = $er->getMeetingsForRemind();

		$this->assertTrue(in_array($meeting->id,$to_remind));
		$GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$meeting->id}'");
	}

}
?>
