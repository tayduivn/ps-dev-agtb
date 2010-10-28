<?php

require_once('include/externalAPI/Base/ExternalAPIBase.php');
require_once('include/externalAPI/Base/WebMeeting.php');

class LotusLive extends ExternalAPIBase implements WebMeeting,WebDocument {

    protected $dateFormat = 'm/d/Y H:i:s';
//    protected $urlExtension = '/envq/Production/';
    protected $url = 'eval-cloud2.castiron.com/envq/Production/';

    public $authMethods = array("password" => 1, "oauth" => 1);
    public $supportedModules = array('Meetings','Notes', 'Documents');
    public $supportMeetingPassword = false;
    public $docSearch = true;
	protected $meetingID;
    protected $joinURL;
	protected $hostURL = "https://apps.lotuslive.com/meetings/host";
	protected $oauthReq = "https://apps.lotuslive.com/manage/oauth/getRequestToken";
    protected $oauthAuth = 'https://apps.lotuslive.com/manage/oauth/authorizeToken';
    protected $oauthAccess = 'https://apps.lotuslive.com/manage/oauth/getAccessToken';

    public function loadEAPM($eapmBean)
    {
        parent::loadEAPM($eapmBean);

        if ( !empty($eapmBean->api_data) ) {
            $api_data = json_decode(base64_decode($eapmBean->api_data),true);
            if ( isset($api_data['meetingID']) ) {
                $this->meetingID = $api_data['meetingID'];
                $this->hostURL = $api_data['hostURL'];
                $this->joinURL = $api_data['joinURL'];
                // FIXME: Need to figure out how we want to handle collections
                $this->collectionID = '3CAA8D80D29311DFA08B9C830A060702';
            }
        }
    }

    public function checkLogin($eapmBean = null)
    {
        parent::checkLogin($eapmBean);
        $reply = $this->makeRequest('GetMeeting', array());

        if ( $reply['success'] == TRUE ) {
            $this->authData->api_data = base64_encode(json_encode(array(
                'meetingID'=>$reply['responseJSON']['feed']['entry']['meetingID'],
                'hostURL'=>$reply['responseJSON']['feed']['entry']['hostURL'],
                'joinURL'=>$reply['responseJSON']['feed']['entry']['joinURL'],)));
        }

        return $reply;
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
		global $current_user;
		$bean->join_url = $this->joinURL.'&TagCode=SugarCRM&TagID='.$bean->id;
		$bean->host_url = $this->hostURL.'?TagCode=SugarCRM&TagID='.$bean->id;
		$bean->creator = $this->account_name;
        return array('success'=>TRUE);
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
        // There is nothing to do here.
        return array('success'=>TRUE);
	}

	/**
	 * NOT SUPPORTED BY LOTUS
	 * Invite $attendee to the meeting with key $session.
	 * @param string $meeting - The Lotus session key.
	 * @param array $attendee - An array with entries for 'name' and 'email'
	 * return: boolean.
	 */
	function inviteAttendee($meetingID, $attendee) {
        // There is nothing to do here, this is not supported by Lotus Live
        return array('success'=>TRUE);
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
        // There is nothing to do here, this is not supported by Lotus Live
        return array('success'=>TRUE);
    }

    /**
     * List all meetings created by this object's Lotus user.
     */
    function listMyMeetings() {
        // There is nothing to do here, this is not supported by Lotus Live
        return array('success'=>TRUE);
    }

    /**
     * Get detailed information about the meeting
     * with key $meeting.
     * @param string meeting- The Lotus meeting key.
	 * return: The XML response from the Lotus server.
     */
    function getMeetingDetails($meeting) {
        // TODO: Implement this, get the meeting information from the provided tags.
        return array('success'=>TRUE);
    }


    function logOff() { }


    public function uploadDoc($bean, $fileToUpload, $docName, $mineType) {
        $result = $this->makeRequest('uploadfile',array('file'=>'@'.$fileToUpload),
                              array('collectionid'=>$this->collectionId,
                                    'fileid'=>$bean->id));

        $bean->doc_id = $bean->id;
        $bean->doc_url = 'https://apps.lotuslive.com/files/filer2/home.do#files.do?subContent=fileDetails.do?fileId='.$bean->doc_id;

        return array('success'=>TRUE);
    }

    public function downloadDoc($documentId, $documentFormat){}
    public function shareDoc($documentId, $emails){}
    public function deleteDoc($documentId){}
    public function searchDoc($keywords){}

    // Internal functions
    protected function makeRequest($requestMethod, $data = array(), $urlParams = array() ) {
        $dataString = json_encode($data);

        $urlParams['ciUser'] = 'admin@LL_SugarCRM';
        $urlParams['ciPassword'] = 'changeIt!';
        if($this->authData->type == 'oauth') {
             $urlParams['OAuthConsumerKey'] = $this->authData->consumer_key;
             $urlParams['OAuthConsumerSecret'] = $this->authData->consumer_secret;
             $urlParams['OAuthToken'] = $this->authData->oauth_token;
             $urlParams['OAuthTokenSecret'] = $this->authData->oauth_secret;
        } else {
            $urlParams['UserName'] = $this->account_name;
            $urlParams['Password'] = $this->account_password;
        }
        $url = 'https://' . $this->url . $requestMethod . '?';
        foreach($urlParams as $key => $value ) {
            // FIXME: urlencode the ciUser and ciPassword once they are ready for it
            if ( $key == 'ciUser' || $key == 'ciPassword' ) {
                $url .= $key .'='. $value .'&';
            } else {
                $url .= $key .'='. urlencode($value) .'&';
            }
        }
        $url = rtrim($url,'&');

        $headers = array(
            "User-Agent: SugarCRM",
            "Content-Type: application/x-www-form-urlencoded",
            "Content-Length: ".strlen($dataString),
            );

        $rawResponse = $this->postData($url, $dataString, $headers);

        $reply = array();
        $reply['responseRAW'] = $rawResponse;
        $reply['responseJSON'] = null;

        $response = json_decode($rawResponse,true);
        if ( empty($rawResponse) || !is_array($response) ) {
            $reply['success'] = FALSE;
            // FIXME: Translate
            $reply['errorMessage'] = 'No response from the server.';
        } else {
            $GLOBALS['log']->fatal("Decoded:\n".print_r($response,true));
            $reply['responseJSON'] = $response;

            if ( $reply['responseJSON']['status'] == 'OK' ) {
                $reply['success'] = TRUE;
            }
        }

        return $reply;
    }

}
