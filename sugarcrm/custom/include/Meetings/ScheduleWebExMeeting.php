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

         $response = $meeting->scheduleMeeting(
            $bean->name,
            date('m/d/Y H:i:s', strtotime($bean->date_start)),
            $duration,
            $bean->password
         );
     }
   }
}
