<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once('modules/Meetings/MeetingFormBase.php');


class Bug58011Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setup()
    {
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestHelper::tearDown();
    }

    public function testAcceptanceAfterDateUpdate()
    {
        $this->markTestIncomplete('Errors out on OutboundEmailConfiguration::getHost()');
        global $current_user;
        global $db;

        $meeting = SugarTestMeetingUtilities::createMeeting();
        $user = SugarTestUserUtilities::createAnonymousUser();

        SugarTestMeetingUtilities::addMeetingUserRelation($meeting->id, $current_user->id);
        SugarTestMeetingUtilities::addMeetingUserRelation($meeting->id, $user->id);

        // set this to 'accept' before handleSave and make sure it gets set to 'none' after handleSave
        $meeting->set_accept_status($user, 'accept');
        $meeting->save();

        $_POST['record'] = $_REQUEST['record'] = $meeting->id;
        $_POST['user_invitees'] = $current_user->id.','.$user->id;
        $_POST['module'] = 'Meetings';
        $_POST['action'] = 'Save';
        $_POST['assigned_user_id'] = $current_user->id;
        $_POST['send_invites'] = $_REQUEST['send_invites'] = 1;
        $_POST['date_start'] = $GLOBALS['timedate']->getNow()->asDb();
        $_POST['date_end'] = $GLOBALS['timedate']->getNow()->modify("+900 seconds")->asDb();

        $formBase = new MeetingFormBase();
        $formBase->handleSave('', false, false);

        $sql = "SELECT accept_status FROM meetings_users WHERE meeting_id='{$meeting->id}' AND user_id='{$user->id}'";
        $result = $db->query($sql);
        if ($row = $db->fetchByAssoc($result)) {
            $this->assertEquals('none', $row['accept_status'], 'Should be none after date changed and invite sent.');
        }
    }
}
