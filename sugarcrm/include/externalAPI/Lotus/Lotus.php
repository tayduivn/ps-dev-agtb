<?php

require_once('include/externalAPI/Base/ExternalAPIPlugin.php');
require_once('include/externalAPI/Base/WebMeeting.php');

class Lotus implements ExternalAPIPlugin,WebMeeting,WebDocument {

    protected $lotusURL = "https://eval-cloud2.castiron.com/envq/Production/";
    public $useAuth = true;
    public $requireAuth = true;
    public $supportedModules = array('Meetings');
    public $supportMeetingPassword = true;
    protected $joinURL = "https://apps.lotuslive.com/meetings/join?id=611-107";
	protected $hostURL = "https://apps.lotuslive.com/meetings/host";
	function __construct() {
   }
	
    public function loadEAPM($eapmData) {
        $this->account_url = $eapmData['url'].$this->urlExtension;
        $this->account_name = $eapmData['name'];
        $this->account_password = $eapmData['password'];    
    }

    public function checkLogin() {
        return true;
    }
	
	/**
	 * Create a new Lotus meeting.
	 * @param string $name
	 * @param string $startdate
	 * @param string $duration
	 * @param string $password
	 * return: boolean
	 */
	function scheduleMeeting($bean) {
		//TODO: call on API and get URL add meeting tags based on bean->id;
		global $current_user;
		$bean->join_url = $this->joinURL;
		$bean->host_url = $this->hostURL;
		$bean->creator = $this->account_name;
        return true;
	}
	
	/**
	 * Edit an existing Lotus meeting
	 * @param string $name
	 * @param string $startdate
	 * @param string $duration
	 * @param string $password
	 * return: boolean
	 */
   function editMeeting($bean) {
      return $this->scheduleMeeting($bean);
   }

	/**
	 * Delete an existing Lotus meeting.
	 * @param string $meeting - The Lotus meeting key.
	 * return: boolean
	 */
	function unscheduleMeeting($meeting) {
		//TODO: will need to untag meeting
		return true;
	}
	
   /**
    * Get the url for joining the meeting with key $meeting as
    * attendee $attendeeName.
    * @param string meeting - The Lotus meeting key.
    * @param string attendeeName - Name of joining attendee
	 * return: URL.
    */
	function joinMeeting($meeting, $attendeeName) {
    	return $this->joinURL;
	}


   /**
    * Get the url for hosting the meeting with key $meeting.
    * @param string meeting - The Lotus meeting key.
	 * return: URL.
    */
   function hostMeeting($meeting) {
     	return $this->hostURL;
   }
	
	/**
	 * NOT SUPPORTED BY LOTUS
	 * Invite $attendee to the meeting with key $session.
	 * @param string $meeting - The Lotus session key. 
	 * @param array $attendee - An array with entries for 'name' and 'email'
	 * return: boolean.
	 */
	function inviteAttendee($session, $attendee) {
     	return true;
	}

   /**
   	* NOT SUPPORTED BY LOTUS
    * Uninvite the attendee with ID $attendeeID from the meeting.
    * Note: attendee ID is returned as part of the response to
    * inviteAtendee().  The attendee ID refers to a specific person
    * and a specific meeting. 
    * @param array $attendeeID - Lotus attendee ID.
	 * return: boolean.
    */
   function uninviteAttendee($attendeeID) {
     	return true;
   }

   /**
    * List all meetings created by this object's Lotus user.
    */
   function listMyMeetings() {
      return array();
   }

   /**
    * Get detailed information about the meeting
    * with key $meeting.
    * @param string meeting- The Lotus meeting key. 
	 * return: The XML response from the Lotus server.
    */
   function getMeetingDetails($meeting) {
      return array();
   }
	
  
   function logoff() { }
   
   
   	public function uploadDoc($fileToUpload, $docName, $mineType){}

    public function downloadDoc($documentId, $documentFormat){}
	
	public function shareDoc($documentId, $emails){}
	
	public function browseDoc($path){}
	
	public function deleteDoc($documentId){}

    public function searchDoc($keywords){}
	
}
