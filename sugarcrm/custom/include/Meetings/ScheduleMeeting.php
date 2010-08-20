<?php

require_once('data/SugarBean.php');
require_once('modules/Meetings/Meeting.php');
require_once('include/utils.php');

require_once('modules/Meetings/WebMeeting.php');
require_once('modules/Meetings/WebExMeeting.php');
require_once('modules/Meetings/WebMeetingFactory.php');
require_once('modules/Meetings/GoToMeeting.php');

require_once('modules/EAPM/EAPM.php');

class ScheduleMeeting {

   private $eapm_appname;
   private $url_extension;
   private $meeting_classname;
   private $date_format;
   private $meeting;

   function schedule(&$bean, $event, $arguments) {
      if ($bean->type == 'Other') return;

      if ($bean->type == 'WebEx') {
         $this->eapm_appname = 'webex';
         $this->url_extension = '/WBXService/XMLService';
         $this->meeting_classname = 'WebExMeeting';
         $this->date_format = 'm/d/Y H:i:s';
         $meeting_response = $this->schedule_meeting($bean, $event, $arguments);

         preg_match('/meetingkey.[0-9]+/', $meeting_response, $matches);
         $meeting_key= substr($matches[0], 11);

         $join_response = $this->meeting->joinMeeting($meeting_key, '');
         preg_match('/joinMeetingURL.[^<]+/', $join_response, $join_matches);
         $join_url = substr($join_matches[0], 15);
         $bean->join_url = $join_url;

         $host_response = $this->meeting->hostMeeting($meeting_key);
         preg_match('/hostMeetingURL.[^<]+/', $host_response, $host_matches);
         $host_url = substr($host_matches[0], 15);
         $bean->host_url = $host_url;

         $invitees = $this->getInviteesArray($bean->users_arr);
         foreach ($invitees as $invitee) {
            $this->meeting->inviteAttendee($meeting_key, $invitee);
         }

         $bean->external_id = $meeting_key;
      } elseif ($bean->type == 'GoToMeeting') {
         $this->eapm_appname = 'gotomeeting';
         $this->url_extension = '/axis/services/G2M_Organizers';
         $this->meeting_classname = 'GoToMeeting';
         $this->date_format = 'Y-m-d\TH:i:s';
         $meeting_response = $this->schedule_meeting($bean, $event, $arguments);

         preg_match(
            '/joinURL<.name>[\s]*<value xsi:type="xsd:string">([^<]+)/', 
            $meeting_response, 
            $matches
         );
         echo '----------------------';
         echo $meeting_response;
         $bean->join_url = $matches[1];
         echo '++++++++++++++++++++++++';
         echo $bean->join_url;

         $pattern = 
            '/uniqueMeetingId<.name>[\s]*<value xsi:type="xsd:string">([0-9]+)/';
         preg_match($pattern, $meeting_response, $ukey_matches);
         $unique_meeting_id = $ukey_matches[1];
         echo '__________________________________';
         echo $unique_meeting_id;

         $pattern = '/id="id5"[^>]+>([0-9]+)/';
         preg_match($pattern, $meeting_response, $key_matches);
         $meeting_id = $key_matches[1];

         $keys = array($meeting_id, $unique_meeting_id);
         $host_response = $this->meeting->hostMeeting($keys);
         echo ']]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]]';
         echo $host_response;

         $pattern = '/startMeetingReturn\sxsi:type="xsd:string">([^<]+)/';
         preg_match($pattern, $host_response, $host_matches);
         echo '[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[';
         $bean->host_url = $host_matches[1];
         echo $bean->host_url;

         $this->meeting->logoff();

         // The GoToMeeting API requires 'meetingId' and 'uniqueMeetingId',
         // two separate ids, to edit a meeting.  So I'm saving them both,
         // separated by a dash.
         $bean->external_id = $keys[0] . '-' . $keys[1];

         //$bean->host_url = $host_matches[1];

      }
   }

   private function schedule_meeting(&$bean, $event, $arguments) {
      $duration = (60 * (int)($bean->duration_hours)) +
         ((int)($bean->duration_minutes));

      $row = EAPM::getLoginInfo($this->eapm_appname);

      $url = $row['url'];
      if ($url[strlen($url)-1] == "/") {
      	$url = substr($url, 0, -1);
      }
      $url .= $this->url_extension;

      $this->meeting = WebMeetingFactory::getInstance(
         $this->meeting_classname, 
         $url, 
         $row['name'],
         $row['password']
      );

      $meeting_response = $this->meeting->scheduleMeeting(
         $bean->name,
         date($this->date_format, strtotime($bean->date_start)),
         $duration,
         $bean->password
      );

      $bean->creator = $row['name'];

      return $meeting_response;
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
