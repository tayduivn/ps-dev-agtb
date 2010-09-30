<?php

require_once('WebMeeting.php');
require_once('WebExMeeting.php');

class WebMeetingFactory {

   static function getInstance($type) {
      $instance = new $type();
      return $instance;
   }
}
