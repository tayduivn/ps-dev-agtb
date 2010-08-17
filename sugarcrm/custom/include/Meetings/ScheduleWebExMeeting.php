<?php

require_once('data/SugarBean.php');
require_once('modules/Meetings/Meeting.php');
require_once('include/utils.php');

require_once('modules/Meetings/WebMeeting.php');
require_once('modules/Meetings/WebExMeeting.php');
require_once('modules/Meetings/WebMeetingFactory.php');

require_once('modules/EAPM/EAPM.php');

class ScheduleWebExMeeting {

   function schedule(&$bean, $event, $arguments) {
      if ($bean->type == 'WebEx') {
         $duration = (60 * (int)($bean->duration_hours)) +
            ((int)($bean->duration_minutes));

         $row = EAPM::getLoginInfo('webex');

         $url = $row['url'];
         if ($url[strlen($url)-1] == "/") {
         	$url = substr($url, 0, -1);
         }
         $url .= '/WBXService/XMLService';

         $meeting = WebMeetingFactory::getInstance(
            'WebExMeeting', 
            $url, 
            $row['name'],
            $row['password']
         );

         $meeting_response = $meeting->scheduleMeeting(
            $bean->name,
            date('m/d/Y H:i:s', strtotime($bean->date_start)),
            $duration,
            $bean->password
         );

         preg_match('/meetingkey.[0-9]+/', $meeting_response, $matches);
         $meeting_key= substr($matches[0], 11);

         $join_response = $meeting->joinMeeting($meeting_key, '');
         preg_match('/joinMeetingURL.[^<]+/', $join_response, $join_matches);
         $join_url = substr($join_matches[0], 15);
         $bean->join_url = $join_url;

         $host_response = $meeting->hostMeeting($meeting_key);
         preg_match('/hostMeetingURL.[^<]+/', $host_response, $host_matches);
         $host_url = substr($host_matches[0], 15);
         $bean->host_url = $host_url;

         $invitees = $this->getInviteesArray($bean->users_arr);
         foreach ($invitees as $invitee) {
            $meeting->inviteAttendee($meeting_key, $invitee);
         }

         $bean->creator = $row['name'];
      }
   }

   private function getInviteesArray($ids) {
      $rtn = array();
      foreach ($ids as $id) {
         $user = new User();
         $user->retrieve($id);
         $rtn[] = array('user_name' => $user->name, 'email' => $user->email1);
      }
      return $rtn;
   }

}
