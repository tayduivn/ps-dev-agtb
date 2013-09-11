<?php

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'modules/Meetings/MeetingFormBase.php';


class Bug58011Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected function setup()
    {
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown()
    {
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestHelper::tearDown();
    }

    public function testAcceptanceAfterDateUpdate()
    {
        global $current_user, $db;

        $meeting = SugarTestMeetingUtilities::createMeeting();
        $user = SugarTestUserUtilities::createAnonymousUser();

        SugarTestMeetingUtilities::addMeetingUserRelation($meeting->id, $current_user->id);
        SugarTestMeetingUtilities::addMeetingUserRelation($meeting->id, $user->id);

        // set this to 'accept' before handleSave and make sure it gets set to 'none' after handleSave
        $meeting->set_accept_status($user, 'accept');
        $meeting->save();

        $_POST['record'] = $_REQUEST['record'] = $meeting->id;
        $_POST['user_invitees'] = $current_user->id . ',' . $user->id;
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
