<?php

require_once('WebMeeting.php');

class WebExMeeting extends WebMeeting {
	
	function WebExMeeting($account_url, $account_name, $account_password) {
      require_once('WebExXML.php');
      
      $this->account_url = $account_url;
      $this->account_name = $account_name;
      $this->account_password = $account_password;
      $this->schedule_xml = $schedule_xml;
      $this->unschedule_xml = $unschedule_xml;
      $this->details_xml = $details_xml;
      $this->listmeeting_xml = $listmeeting_xml;
      $this->invite_xml = $invite_xml;
      $this->uninvite_xml = $uninvite_xml;
      $this->joinmeeting_xml = $joinmeeting_xml;
   }
	
	
	/**
	 * Create a new WebEx meeting with one attendee: the 
	 * current user.
	 * @param string $name
	 * @param string $startdate
	 * @param string $duration
	 * @param string $password
	 * return: The XML response from the WebEx server.
	 */
	function scheduleMeeting($name, $startDate, $duration, $password) {
		global $current_user;
		
      $doc = new SimpleXMLElement($this->schedule_xml);
		$this->addAuthenticationInfo($doc);
		
		$doc->body->bodyContent->accessControl->meetingPassword = $password;
		
		$doc->body->bodyContent->metaData->confName = $name;
		$doc->body->bodyContent->metaData->agenda = '';
		
		$doc->body->bodyContent->participants->maxUserNumber = '1';		
      $attendee = 
         $doc->body->bodyContent->participants->attendees->addChild('attendee', '');
		$person = $attendee->addChild('person');
		$person->addChild('name', $GLOBALS['current_user']->full_name);
		$person->addChild('email', $GLOBALS['current_user']->email1);

		$doc->body->bodyContent->schedule->startDate = $startDate;
		// TODO: what's openTime?
		$doc->body->bodyContent->schedule->openTime = '900';
		$doc->body->bodyContent->schedule->duration = $duration;
		//ID of 20 is GMT
		$doc->body->bodyContent->schedule->timeZoneID = '20';
		
		return $this->postMessage($doc);
	}
	
	/**
	 * Delete an existing WebEx meeting.
	 * @param string $meeting - The WebEx meeting key.
	 * return: The XML response from the WebEx server.
	 */
	function unscheduleMeeting($meeting) {
		$doc = new SimpleXMLElement($this->unschedule_xml);
		$this->addAuthenticationInfo($doc);
		$doc->body->bodyContent->meetingKey = $meeting;
		return $this->postMessage($doc);
	}
	
   /**
    *
    * @param string meeting - The WebEx meeting key.
    * @param string attendeeName - Name of joining attendee
	 * return: The XML response from the WebEx server.
    */
	function joinMeeting($meeting, $attendeeName) {
      $doc = new SimpleXMLElement($this->joinmeeting_xml);
      $this->addAuthenticationInfo($doc);
      $doc->body->bodyContent->sessionKey = $meeting;
      $doc->body->bodyContent->attendeeName = $attendeeName;
      return $this->postMessage($doc);
	}
	
	/**
	 * 
	 * @param string $meeting - The WebEx session key. 
	 * @param array $attendee - An array with entries for 'name' and 'email'
	 * return: The XML response from the WebEx server.
	 */
	function inviteAttendee($session, $attendee) {
      $doc = new SimpleXMLElement($this->invite_xml);
      $this->addAuthenticationInfo($doc);
      $body = $doc->body->bodyContent;
      $person = $body->addChild('person', '');
      $person->addChild('name', $attendee['name']);
      $person->addChild('email', $attendee['email']);
      $body->addChild('sessionKey', $session);
      return $this->postMessage($doc);
	}

   /**
    *
    * @param array $attendeeID - WebEx attendee ID.
	 * return: The XML response from the WebEx server.
    */
   function uninviteAttendee($attendeeID) {
      $doc = new SimpleXMLElement($this->uninvite_xml);
      $this->addAuthenticationInfo($doc);
      $doc->body->bodyContent->attendeeID = $attendeeID;
      return $this->postMessage($doc);
   }

   /**
    * 
    *
    */
   function listMyMeetings() {
      $doc = new SimpleXMLElement($this->listmeeting_xml);
      $this->addAuthenticationInfo($doc);
      return $this->postMessage($doc);
   }

   /**
    * @param string meeting- The WebEx meeting key. 
	 * return: The XML response from the WebEx server.
    */
   function getMeetingDetails($meeting) {
      $doc = new SimpleXMLElement($this->details_xml);
      $this->addAuthenticationInfo($doc);
      $doc->body->bodyContent->meetingKey = $meeting;
      return $this->postMessage($doc);
   }
	
	private function addAuthenticationInfo($doc) {
		$securityContext = $doc->header->securityContext;
      $securityContext->webExID = $this->account_name;
      $securityContext->password = $this->account_password;
      $siteName = substr($this->account_url, 0, strpos($this->account_url, '.'));
      $securityContext->siteName = $siteName;
	}

   private function postMessage($doc) {
      $host = substr($this->account_url, 0, strpos($this->account_url, "/"));
      $uri = strstr($this->account_url, "/");
      $xml = $doc->asXML();
      //echo "<br /><br />$xml<br /><br />";
      $content_length = strlen($xml);
      $headers = array(
         "POST $uri HTTP/1.0",
         "Host: $host",
         "User-Agent: PostIt",
         "Content-Type: application/x-www-form-urlencoded",
         "Content-Length: ".$content_length,
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
