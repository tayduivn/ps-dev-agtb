<?php

require_once('WebMeeting.php');
require_once('WebExMeeting.php');

class WebMeetingFactory {

   static function getInstance($type, $url, $name, $password) {
      $instance = new $type($url, $name, $password);
      return $instance;
   }
}
