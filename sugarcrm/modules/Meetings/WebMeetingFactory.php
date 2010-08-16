<?php

require_once('WebMeeting.php');
require_once('WebExMeeting.php');

class WebMeetingFactory {

   function getInstance($type, $url) {
      $instance = new $type($url);
      return $instance;
   }
}
