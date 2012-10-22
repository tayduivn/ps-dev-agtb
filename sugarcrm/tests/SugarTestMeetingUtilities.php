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
require_once 'modules/Meetings/Meeting.php';

class SugarTestMeetingUtilities
{
    private static $_createdMeetings = array();

    private function __construct() {}

    public static function createMeeting($id = '')
    {
        $time = mt_rand();
        $name = 'Meeting';
        $meeting = new Meeting();
        $meeting->name = $name . $time;
        $meeting->duration_hours = '0';
        $meeting->duration_minutes = '15';
        if(!empty($id))
        {
            $meeting->new_with_id = true;
            $meeting->id = $id;
        }
        $meeting->save();
        self::$_createdMeetings[] = $meeting;
        return $meeting;
    }

    public static function removeAllCreatedMeetings() 
    {
        $meeting_ids = self::getCreatedMeetingIds();
        $GLOBALS['db']->query(sprintf("DELETE FROM meetings WHERE id IN ('%s')", implode("', '", $meeting_ids)));
    }
    
    public static function removeMeetingContacts()
    {
        $meeting_ids = self::getCreatedMeetingIds();
        $GLOBALS['db']->query(sprintf("DELETE FROM meetings_contacts WHERE meeting_id IN ('%s')", implode("', '", $meeting_ids)));
    }

    public static function addMeetingLeadRelation($meeting_id, $lead_id) {
        $id = create_guid();
        $GLOBALS['db']->query("INSERT INTO meetings_leads (id, meeting_id, lead_id) values ('{$id}', '{$meeting_id}', '{$lead_id}')");
        return $id;
    }

    public static function addMeetingUserRelation($meeting_id, $user_id) {
        $id = create_guid();
        $GLOBALS['db']->query("INSERT INTO meetings_users (id, meeting_id, user_id) values ('{$id}', '{$meeting_id}', '{$user_id}')");
        return $id;
    }

    public static function deleteMeetingLeadRelation($id) {
        $GLOBALS['db']->query("delete from meetings_leads where id='{$id}'");
    }

    public static function addMeetingParent($meeting_id, $lead_id) {
        $sql = "update meetings set parent_type='Leads', parent_id='{$lead_id}' where id='{$meeting_id}'";
        $GLOBALS['db']->query($sql);
    }

    public static function removeMeetingUsers()
    {
        $meeting_ids = self::getCreatedMeetingIds();
        $GLOBALS['db']->query(sprintf("DELETE FROM meetings_users WHERE meeting_id IN ('%s')", implode("', '", $meeting_ids)));
    }

    public static function getCreatedMeetingIds()
    {
        $meeting_ids = array();
        foreach (self::$_createdMeetings as $meeting)
        {
            $meeting_ids[] = $meeting->id;
        }
        return $meeting_ids;
    }
}
