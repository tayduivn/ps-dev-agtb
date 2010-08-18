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

   function scheduleMeeting($name, $startDate, $duration, $password) { 
      $doc = new SimpleXMLElement($this->schedule_xml);
      $namespaces = $doc->getDocNamespaces();
      $body = $doc->children($namespaces['soap']);
      $impl = $body[0]->children($namespaces['impl']);
      $createMeeting = $impl[0];
      $createMeeting->connectionId = $this->login_key;
      $createMeeting->meetingParameters->subject = $name;
      $createMeeting->meetingParameters->startTime = $startDate;
      if ($password != '') {
         $createMeeting->meetingParameters->passwordRequired = 'true';
      } 

      return $this->postMessage($doc);
   }

   function unscheduleMeeting($meeting){
   }
	
   function joinMeeting($meeting, $attendeeName){
   }

   function hostMeeting($meeting){
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
