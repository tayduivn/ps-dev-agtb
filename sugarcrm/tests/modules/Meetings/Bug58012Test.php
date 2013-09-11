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


class Bug58012Test extends Sugar_PHPUnit_Framework_TestCase
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
        SugarTestHelper::tearDown();
    }

    public function testOrganizerDefaultAcceptance()
    {
        global $current_user, $db;

        $_POST['user_invitees'] = $current_user->id;
        $_POST['module'] = 'Meetings';
        $_POST['action'] = 'Save';
        $_POST['assigned_user_id'] = $current_user->id;

        $formBase = new MeetingFormBase();
        $meeting = $formBase->handleSave('', false, false);

        $sql = "SELECT accept_status FROM meetings_users WHERE meeting_id='{$meeting->id}' AND user_id='{$current_user->id}'";
        $result = $db->query($sql);
        if ($row = $db->fetchByAssoc($result)) {
            $this->assertEquals('accept', $row['accept_status'], 'Should be accepted for the organizer.');
        }
    }
}
