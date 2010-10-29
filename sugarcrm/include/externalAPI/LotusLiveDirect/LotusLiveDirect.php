<?php

require_once('include/externalAPI/Base/ExternalAPIBase.php');
require_once('include/externalAPI/Base/WebMeeting.php');

class LotusLiveDirect extends ExternalAPIBase implements WebMeeting,WebDocument {

    protected $dateFormat = 'm/d/Y H:i:s';

    public $authMethods = array("oauth" => 1);
    public $supportedModules = array('Meetings','Notes', 'Documents');
    public $supportMeetingPassword = false;
    public $docSearch = true;
	protected $meetingID;
    protected $joinURL;
	protected $hostURL = "https://apps.test.lotuslive.com/meetings/host";
	protected $oauthReq = "https://apps.test.lotuslive.com/manage/oauth/getRequestToken";
    protected $oauthAuth = 'https://apps.test.lotuslive.com/manage/oauth/authorizeToken';
    protected $oauthAccess = 'https://apps.test.lotuslive.com/manage/oauth/getAccessToken';
    protected $oauthParams = array('signatureMethod' => 'PLAINTEXT');
    protected $url = 'https://apps.test.lotuslive.com/';

    public function loadEAPM($eapmBean)
    {
        parent::loadEAPM($eapmBean);

        if($eapmBean->url) {
            $this->url = $eapmBean->url;
        }

        if ( !empty($eapmBean->api_data) ) {
            $this->api_data = json_decode(base64_decode($eapmBean->api_data),true);
//            if ( isset($api_data['meetingID']) ) {
//                $this->meetingID = $api_data['meetingID'];
//                $this->hostURL = $api_data['hostURL'];
//                $this->joinURL = $api_data['joinURL'];
//                // FIXME: Need to figure out how we want to handle collections
//                $this->collectionID = '3CAA8D80D29311DFA08B9C830A060702';
//            }
        }
    }

    public function checkLogin($eapmBean = null)
    {
        parent::checkLogin($eapmBean);
        try {
            // get meeting details
            $reply = $this->makeRequest('/meetings/api/getMeetingDetails');
            if ( $reply['success'] == true ) {
                if ( $reply['responseJSON']['status'] != 'ok') {
                    $reply['success'] = false;
                    $reply['errorMessage'] = $reply['responseJSON']['details'];
                    return $reply;
                }
                $this->api_data = array(
                	'meetingID'=>$reply['responseJSON']['details']['meetingID'],
                    'hostURL'=>$reply['responseJSON']['details']['hostURL'],
                    'joinURL'=>$reply['responseJSON']['details']['joinURL'],
                );
            } else {
                return $reply;
            }
            // get user details
            $reply = $this->makeRequest('/shindig-server/social/rest/people/@me/@self');
            if ( $reply['success'] == true ) {
                $this->api_data['subscriberId'] = $reply['responseJSON']['entry']['objectId'];
            } else {
                return $reply;
            }
        } catch(Exception $e) {
            $reply['success'] = FALSE;
            $reply['errorMessage'] = $e->getMessage();
            return $reply;
        }

        $this->authData->api_data = base64_encode(json_encode($this->api_data));

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
    public function searchDoc($keywords){
        global $db;

        $sql = "
SELECT doc_id AS id, doc_url AS url, filename AS name, date_modified AS date_modified FROM notes WHERE filename LIKE '".$db->quote($keywords)."%' OR name LIKE '".$db->quote($keywords)."%' AND doc_type = 'LotusLiveDirect'
UNION ALL
SELECT doc_id AS id, doc_url AS url, filename AS name, date_modified AS date_modified FROM document_revisions WHERE filename LIKE '".$db->quote($keywords)."%' AND doc_type = 'LotusLiveDirect' ORDER BY date_modified DESC";

        $GLOBALS['log']->fatal('IKEA: SearchDoc: '.$sql);

        $ret = $db->query($sql,true);

        $results = array();

        while ( $row = $db->fetchByAssoc($ret) ) {
            $results[] = $row;
        }

        return $results;
    }

    /**
     * Make request to a service
     * @param unknown_type $url
     * @param unknown_type $method
     * @param unknown_type $json
     */
    protected function makeRequest($urlReq, $method = 'GET', $json = true)
    {
        $client = $this->authData->getHttpClient($this);
        $client->setHeaders('Accept-Encoding', 'identity');
        $url = $this->url.$urlReq;
        $GLOBALS['log']->debug("REQUEST: $url");
        $rawResponse = $client->setUri($url)->request($method);
        $reply = array('rawResponse' => $rawResponse->getBody());
        $GLOBALS['log']->debug("RESPONSE: ".var_export($reply, true));
        if($json) {
            $response = json_decode($reply['rawResponse'],true);
            $GLOBALS['log']->debug("RESPONSE-JSON: ".var_export($response, true));
            if ( empty($rawResponse) || !is_array($response) ) {
                $reply['success'] = FALSE;
                // FIXME: Translate
                $reply['errorMessage'] = 'Bad response from the server.';
            } else {
                $reply['responseJSON'] = $response;
                $reply['success'] = TRUE;
            }
        } else {
            $reply['success'] = true;
        }

        return $reply;
    }

}
