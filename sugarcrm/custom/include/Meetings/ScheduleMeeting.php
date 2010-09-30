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
      if (isset($bean->fetched_row) && $bean->fetched_row['type'] != $bean->type ) {
          $bean->join_url = '';
          $bean->host_url = '';
          $bean->external_id = '';
          $bean->creator = '';
      }

      if ($bean->type == 'Other' || empty($bean->type)) {
          $bean->join_url = '';
          $bean->host_url = '';
          $bean->external_id = '';
          $bean->creator = '';

          return;
      }

      // FIXME: Get the class name mapping correct so I don't have to put this switch in here
      if ( $bean->type == 'WebEx' ) {
          $meetingClassName = 'WebExMeeting';
      } else {
          $meetingClassName = $bean->type;
      }
      
      $this->meeting = WebMeetingFactory::getInstance($meetingClassName);
      $response = $this->meeting->scheduleMeeting($bean);
      if ( $response['success'] == TRUE ) {
          /*
          $invitees = $this->getInviteesArray($bean->users_arr);
          
          foreach ($invitees as $invitee) {
              $this->meeting->inviteAttendee($bean->external_id, $invitee);
          }
          */
      } else {
          // FIXME: Translate
          $_SESSION['administrator_error'] = 'Error during plugin save: '.$response['errorMessage'];
      }
      $this->meeting->logoff();
          

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
