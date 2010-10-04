<?php

require_once('include/externalAPI/Base/WebMeeting.php');

class WebEx extends WebMeeting {

    protected $dateFormat = 'm/d/Y H:i:s';
    protected $urlExtension = '/WBXService/XMLService';
	
    public $useAuth = true;
    public $requireAuth = true;
    public $supportedModules = array('Meetings');


	function __construct() {
      require('include/externalAPI/WebEx/WebExXML.php');
      
      $this->schedule_xml = $schedule_xml;
      $this->unschedule_xml = $unschedule_xml;
      $this->details_xml = $details_xml;
      $this->listmeeting_xml = $listmeeting_xml;
      $this->invite_xml = $invite_xml;
      $this->uninvite_xml = $uninvite_xml;
      $this->joinmeeting_xml = $joinmeeting_xml;
      $this->hostmeeting_xml = $hostmeeting_xml;
      $this->edit_xml = $edit_xml;
   }
	
    function loadEAPM($eapmData) {
        $this->account_url = $eapmData['url'].$this->urlExtension;
        $this->account_name = $eapmData['name'];
        $this->account_password = $eapmData['password'];    
    }
    
	
	/**
	 * Create a new WebEx meeting.
	 * @param string $name
	 * @param string $startdate
	 * @param string $duration
	 * @param string $password
	 * return: The XML response from the WebEx server.
	 */
	function scheduleMeeting($bean) {
		global $current_user;
		
        if (!empty($bean->external_id)) {
            $doc = new SimpleXMLElement($this->edit_xml);
            $doc->body->bodyContent->meetingkey = $bean->external_id;
        } else {
            $doc = new SimpleXMLElement($this->schedule_xml);
        }
		$this->addAuthenticationInfo($doc);
		
		$doc->body->bodyContent->accessControl->meetingPassword = $bean->password;
		
		$doc->body->bodyContent->metaData->confName = $bean->name;
		$doc->body->bodyContent->metaData->agenda = '';
		
		$doc->body->bodyContent->participants->maxUserNumber = '1';		
        $attendee = $doc->body->bodyContent->participants->attendees->addChild('attendee', '');
		$person = $attendee->addChild('person');
		$person->addChild('name', $GLOBALS['current_user']->full_name);
		$person->addChild('email', $GLOBALS['current_user']->email1);

        // FIXME: Use TimeDate
        $startDate = date($this->dateFormat, strtotime($bean->date_start));

		$doc->body->bodyContent->schedule->startDate = $startDate;
		// TODO: what's openTime?
		$doc->body->bodyContent->schedule->openTime = '900';

        $duration = (60 * (int)($bean->duration_hours)) + ((int)($bean->duration_minutes));
		$doc->body->bodyContent->schedule->duration = $duration;
		//ID of 20 is GMT
		$doc->body->bodyContent->schedule->timeZoneID = '20';
      
        $reply = $this->postMessage($doc);
        
        if ($reply['success']) {
            if ( empty($bean->external_id) ) {
                $xp = new DOMXPath($reply['responseXML']);
                // Only get the external ID when I create a new meeting.
                $bean->external_id = $xp->query('/serv:message/serv:body/serv:bodyContent/meet:meetingkey')->item(0)->nodeValue;
                $GLOBALS['log']->fatal('External ID: '.print_r($bean->external_id,true));
            }

            // Figure out the join url
            $join_reply = $this->joinMeeting($bean->external_id);
            $xp = new DOMXPath($join_reply['responseXML']);
            $bean->join_url = $xp->query('/serv:message/serv:body/serv:bodyContent/meet:joinMeetingURL')->item(0)->nodeValue;
            $GLOBALS['log']->fatal('Join URL: '.print_r($bean->join_url,true));


            // Figure out the host url
            $host_reply = $this->hostMeeting($bean->external_id);
            $xp = new DOMXPath($host_reply['responseXML']);
            $bean->host_url = $xp->query('/serv:message/serv:body/serv:bodyContent/meet:hostMeetingURL')->item(0)->nodeValue;
            $GLOBALS['log']->fatal('Host URL: '.print_r($bean->host_url,true));

            $bean->creator = $this->account_name;
        } else {
            $bean->join_url = '';
            $bean->host_url = '';
            $bean->external_id = '';
            $bean->creator = '';
        }
        
        return $reply;
	}
	
	/**
	 * Edit an existing webex meeting
	 * @param string $name
	 * @param string $startdate
	 * @param string $duration
	 * @param string $password
	 * return: The XML response from the WebEx server.
	 */
   function editMeeting($bean) {
      return $this->scheduleMeeting($bean);
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
    * Get the url for joining the meeting with key $meeting as
    * attendee $attendeeName.
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
    * Get the url for hosting the meeting with key $meeting.
    * @param string meeting - The WebEx meeting key.
	 * return: The XML response from the WebEx server.
    */
   function hostMeeting($meeting) {
      $doc = new SimpleXMLElement($this->hostmeeting_xml);
      $this->addAuthenticationInfo($doc);
      $doc->body->bodyContent->sessionKey = $meeting;
      return $this->postMessage($doc);
   }
	
	/**
	 * Invite $attendee to the meeting with key $session.
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
      $body->addChild('emailInvitations', 'true');
      return $this->postMessage($doc);
	}

   /**
    * Uninvite the attendee with ID $attendeeID from the meeting.
    * Note: attendee ID is returned as part of the response to
    * inviteAtendee().  The attendee ID refers to a specific person
    * and a specific meeting. 
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
    * List all meetings created by this object's WebEx user.
    */
   function listMyMeetings() {
      $doc = new SimpleXMLElement($this->listmeeting_xml);
      $this->addAuthenticationInfo($doc);
      return $this->postMessage($doc);
   }

   /**
    * Get detailed information about the meeting
    * with key $meeting.
    * @param string meeting- The WebEx meeting key. 
	 * return: The XML response from the WebEx server.
    */
   function getMeetingDetails($meeting) {
      $doc = new SimpleXMLElement($this->details_xml);
      $this->addAuthenticationInfo($doc);
      $doc->body->bodyContent->meetingKey = $meeting;
      return $this->postMessage($doc);
   }
	
   /**
    * Adds values to the security context header for a
    * WebEx XML request.
    * @param SimpleXMLElement $doc
    */
	private function addAuthenticationInfo($doc) {
		$securityContext = $doc->header->securityContext;
      $securityContext->webExID = $this->account_name;
      $securityContext->password = $this->account_password;
      $siteName = substr($this->account_url, 0, strpos($this->account_url, '.'));
      $securityContext->siteName = $siteName;
	}

   /**
    * Sends a request to the WebEx XML API.
    * @param SimpleXMLElement $doc
    */
   private function postMessage($doc) {
      $host = substr($this->account_url, 0, strpos($this->account_url, "/"));
      $uri = strstr($this->account_url, "/");
      $xml = $doc->asXML();

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

      $GLOBALS['log']->fatal("Where: https://".$this->account_url);
      $GLOBALS['log']->fatal("Before:\n".print_r($xml,true));
      $response = curl_exec($ch);
      $GLOBALS['log']->fatal("After:\n".print_r($response,true));
      // $reply is an associative array that formats the basic information in a way that
      // callers can get most of the data out without having to understand any underlying formats.
      $reply = array();
      $reply['responseRAW'] = $response;
      $reply['responseXML'] = null;
      if ( empty($response) ) {
          $reply['success'] = FALSE;
          // FIXME: Translate
          $reply['errorMessage'] = 'No response from the server.';
      } else {
          // The namespaces seem to destroy SimpleXML.
          // $responseXML = new SimpleXMLElement(str_replace('serv:message','message',$response),NULL,false,'http://www.webex.com/schemas/2002/06/service');
          $responseXML = new DOMDocument();
          $responseXML->preserveWhiteSpace = false;
          $responseXML->strictErrorChecking = false;
          $responseXML->loadXML($response);
          if ( !is_object($responseXML) ) {
              $GLOBALS['log']->fatal("XML ERRORS:\n".print_r(libxml_get_errors(),true));
              // Looks like the XML processing didn't go so well.
              $reply['success'] = FALSE;
              // FIXME: Translate
              $reply['errorMessage'] = 'Server responded with an unknown message';
          } else {
              $reply['responseXML'] = $responseXML;
              $xpath = new DOMXPath($responseXML);
              // $status = (string)$responseXML->header->response->result;
              $status = (string)$xpath->query('/serv:message/serv:header/serv:response/serv:result')->item(0)->nodeValue;
              if ( $status == 'SUCCESS' ) {
                  $reply['success'] = TRUE;
                  $reply['errorMessage'] = '';
              } else {
                  $GLOBALS['log']->fatal("Status:\n".print_r($status,true));
                  $reply['success'] = FALSE;
                  // $reply['errorMessage'] = (string)$responseXML->header->response->reason;
                  $reply['errorMessage'] = (string)$xpath->query('/serv:message/serv:header/serv:response/serv:reason')->item(0)->nodeValue;
              }
          }
      }
      $GLOBALS['log']->fatal("Parsed Reply:\n".print_r($reply,true));
      return $reply;
   }

   function logoff() { }
	
}
