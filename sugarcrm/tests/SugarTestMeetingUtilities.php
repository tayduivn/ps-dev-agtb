<?php
require_once 'modules/Meetings/Meeting.php';

class SugarTestMeetingUtilities
{
    private static $_createdMeetings = array();

    private function __construct() {}

    public static function createMeeting() 
    {
        $time = mt_rand();
    	$name = 'Meeting';
    	$meeting = new Meeting();
        $meeting->name = $name . $time;
        $meeting->save();
        self::$_createdMeetings[] = $meeting;
        return $meeting;
    }

    public static function removeAllCreatedMeetings() 
    {
        $meeting_ids = self::getCreatedMeetingIds();
        $GLOBALS['db']->query('DELETE FROM meetings WHERE id IN (\'' . implode("', '", $meeting_ids) . '\')');
    }
    
    public static function removeMeetingContacts(){
    	$meeting_ids = self::getCreatedMeetingIds();
        $GLOBALS['db']->query('DELETE FROM meetings_contacts WHERE meeting_id IN (\'' . implode("', '", $meeting_ids) . '\')');
    }
    
    public static function getCreatedMeetingIds() 
    {
        $meeting_ids = array();
        foreach (self::$_createdMeetings as $meeting) {
            $meeting_ids[] = $meeting->id;
        }
        return $meeting_ids;
    }
}
?>