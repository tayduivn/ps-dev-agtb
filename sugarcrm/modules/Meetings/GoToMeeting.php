<?php

require_once('WebMeeting.php');

class GoToMeeting extends WebMeeting {

   private $login_key;

	function GoToMeeting($account_url, $account_name, $account_password) {
      require_once('GoToXML.php');

      $this->account_url = $account_url;
      $this->account_name = $account_name;
      $this->account_password = $account_password;

      $this->login_xml = $login_xml;
      $this->schedule_xml = $schedule_xml;
      $this->host_xml = $host_xml;
      $this->logoff_xml = $logoff_xml;
      $this->edit_xml = $edit_xml;
   }

   function login() {
      $doc = new SimpleXMLElement($this->login_xml);
      $namespaces = $doc->getDocNamespaces();
      $body = $doc->children($namespaces['soap']);
      $impl = $body[0]->children($namespaces['impl']);
      $child = $impl[0];
      $child->id = $this->account_name;
      $child->password = $this->account_password;

      $response = $this->postMessage($doc);
      preg_match(
         '/logonReturn xsi:type="xsd:string">[0-9A-Z:]+/', 
         $response, 
         $matches
      );
      $this->login_key = substr($matches[0], 34); 

      return $response;
   }

   function logoff() {
      $doc = new SimpleXMLElement($this->logoff_xml);
      $namespaces = $doc->getDocNamespaces();
      $body = $doc->children($namespaces['soap']);
      $impl = $body[0]->children($namespaces['impl']);
      $child = $impl[0];
      $child->connectionId = $this->login_key;

      return $this->postMessage($doc);
   }

   function scheduleMeeting($name, $startDate, $duration, $password) { 
      if (!isset($this->login_key)) {
         $this->login();
      }

      $doc = new SimpleXMLElement($this->schedule_xml);
      $namespaces = $doc->getDocNamespaces();
      $body = $doc->children($namespaces['soap']);
      $impl = $body[0]->children($namespaces['impl']);
      $createMeeting = $impl[0];
      $createMeeting->connectionId = $this->login_key;
      $createMeeting->meetingParameters->subject = $name;
      $createMeeting->meetingParameters->startTime = $startDate;

      /* From the GoToMeeting API docs:
       *
       * 'Note: If passwordRequired is set to True then a password will be 
       * requested of the organizer when starting the meeting. The password is 
       * selected by the organizer and communicated to the attendees before the 
       * meeting is started. The organizer must properly enter the password 
       * communicated, otherwise the authentication will be different and the 
       * attendees will not be able to join the meeting until the proper password
       * is provided by the organizer.'
       *
       * Since the password is chosen at the start of the meeting, the value
       * passed here can't be used.
       * If a non-empty string is passed, the meeting will be created with
       * the password option on.
       */
      if ($password != '') {
         $createMeeting->meetingParameters->passwordRequired = 'true';
      } else {
         $createMeeting->meetingParameters->passwordRequired = 'false';
      }

      return $this->postMessage($doc);
   }

   function editMeeting($meeting_keys, $params) {
      if (!isset($this->login_key)) {
         $this->login();
      }

      $doc = new SimpleXMLElement($this->edit_xml);
      $namespaces = $doc->getDocNamespaces();
      $body = $doc->children($namespaces['soap']);
      $impl = $body[0]->children($namespaces['impl']);
      $updateMeeting = $impl[0];

      $updateMeeting->connectionId = $this->login_key;
      $updateMeeting->meetingId = $meeting_keys[0];
      $updateMeeting->uniqueMeetingId = $meeting_keys[1];

      $updateMeeting->meetingParameters->subject = $params['subject']; 
      $updateMeeting->meetingParameters->startTime = $params['startTime']; 
      if ($params['password'] != '') {
         $updateMeeting->meetingParameters->passwordRequired = 'true';
      } else {
         $updateMeeting->meetingParameters->passwordRequired = 'false';
      }

      return $this->postMessage($doc);
   }

   function unscheduleMeeting($meeting){
   }
	
   function joinMeeting($meeting, $attendeeName){
   }

   function hostMeeting($meeting_keys){
      if (!isset($this->login_key)) {
         $this->login();
      }
      
      $doc = new SimpleXMLElement($this->host_xml);
      $namespaces = $doc->getDocNamespaces();
      $body = $doc->children($namespaces['soap']);
      $impl = $body[0]->children($namespaces['impl']);
      $startMeeting = $impl[0];
      $startMeeting->connectionId = $this->login_key;
      $startMeeting->meetingId = $meeting_keys[0];
      $startMeeting->uniqueMeetingId = $meeting_keys[1];

      return $this->postMessage($doc);
   }
	
   function inviteAttendee($meeting, $attendee){
   }
	
   function uninviteAttendee($attendee){
   }
	
   function listMyMeetings(){
   }
	
   function getMeetingDetails($meeting){
   }

   private function postMessage($doc) {
      $host = substr($this->account_url, 0, strpos($this->account_url, "/"));
      $uri = strstr($this->account_url, "/");
      $xml = $doc->asXML();
      echo "<br /><br />$xml<br /><br />";
      $content_length = strlen($xml);
      $headers = array(
         "POST $uri HTTP/1.1",
         "Host: $host",
         "User-Agent: SugarCRM",
         "Content-Type: text/xml; charset=utf-8",
         "Content-Length: ".$content_length,
         'SOAPAction: ""'
      );

      $ch = curl_init('https://' . $this->account_url);
      curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

      $response = curl_exec($ch);
      return $response;  
   }

}
