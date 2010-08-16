<?php

require_once('data/SugarBean.php');
require_once('modules/Meetings/Meeting.php');
require_once('include/utils.php');

require_once('modules/Meetings/WebMeeting.php');
require_once('modules/Meetings/WebExMeeting.php');
require_once('modules/Meetings/WebMeetingFactory.php');

class ScheduleWebExMeeting {
   function schedule(&$bean, $event, $arguments) {
      if ($bean->type == 'WebEx') {
         echo 'it is a webex meeting';
         $factory = new WebMeetingFactory();

         $duration = (60 * (int)($bean->duration_hours)) +
            ((int)($bean->duration_minutes));
         
         $meeting = $factory->getInstance('WebExMeeting', $bean->webexurl);
         // TODO: how to give password?
         $response = $meeting->scheduleMeeting(
            $bean->name, date('m/d/Y H:i:s', strtotime($bean->date_start)), $duration,'password123'); 
         print_r($response);
         $GLOBALS['log']->fatal($response);
     }
     die();

   }
}
