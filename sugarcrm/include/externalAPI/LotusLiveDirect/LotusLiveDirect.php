<?php

require_once('include/externalAPI/Base/ExternalAPIBase.php');
require_once('include/externalAPI/Base/WebMeeting.php');
require_once('include/externalAPI/Base/WebDocument.php');

class LotusLiveDirect extends ExternalAPIBase implements WebMeeting,WebDocument {

    protected $dateFormat = 'm/d/Y H:i:s';

    public $authMethods = array("oauth" => 1, "password" => 1);
    public $supportedModules = array('Meetings','Notes', 'Documents');
    public $supportMeetingPassword = false;
    public $docSearch = true;

    protected $oauthReq = "/manage/oauth/getRequestToken";
    protected $oauthAuth = '/manage/oauth/authorizeToken';
    protected $oauthAccess = '/manage/oauth/getAccessToken';
    protected $oauthParams = array('signatureMethod' => 'PLAINTEXT');
    protected $url = 'https://apps.lotuslive.com/';

    public function loadEAPM($eapmBean)
    {
        parent::loadEAPM($eapmBean);

        if($eapmBean->url) {
            $this->url = $eapmBean->url;
        }

        if ( !empty($eapmBean->api_data) ) {
            $this->api_data = json_decode(base64_decode($eapmBean->api_data), true);
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
		$bean->join_url = $this->api_data['joinURL'].'&TagCode=SugarCRM&TagID='.$bean->id;
		$bean->host_url = $this->api_data['hostURL'].'?TagCode=SugarCRM&TagID='.$bean->id;
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

    /**
     * Get HTTP client for communication with Lotus
     *
     * Creates and setup the http client object, including authorization data if needed
     *
     * @return Zend_Http_Client
     */
    protected function getClient()
    {
        if($this->authData->type == 'oauth') {
            $client = $this->authData->getHttpClient($this);
        } else {
            $client = new Zend_Http_Client();
            $client->setAuth($this->account_name, $this->account_password);
        }
        $client->setHeaders('Accept-Encoding', 'identity');
        return $client;
    }

    public function uploadDoc($bean, $fileToUpload, $docName, $mimeType)
    {
        $client = $this->getClient();
        $url = $this->url."files/basic/cmis/repository/p!{$this->api_data['subscriberId']}/folderc/snx:files!{$this->api_data['subscriberId']}";
        $GLOBALS['log']->debug("LOTUS REQUEST: $url");
        $rawResponse = $client->setUri($url)
            ->setRawData(file_get_contents($fileToUpload), $mimeType?$mimeType:"application/octet-stream")
            ->setHeaders("slug", $docName)
            ->request("POST");
        $reply = array('rawResponse' => $rawResponse->getBody());
//        $GLOBALS['log']->debug("REQUEST: ".var_export($client->getLastRequest(), true));
//        $GLOBALS['log']->debug("RESPONSE: ".var_export($rawResponse, true));
        if(!$rawResponse->isSuccessful() || empty($reply['rawResponse'])) {
            $reply['success'] = false;
            $reply['errorMessage'] = 'Bad response from the server: '.$rawResponse->getMessage();
            return;
        }
        // parse XML response
        $xml = simplexml_load_string($reply['rawResponse']);
        if($xml == false) {
            $reply['success'] = false;
            $reply['errorMessage'] = 'Bad response from the server';
            return;
        }
        // find atom:link attribute with rel=self
        $els = $xml->children('atom', true);
        foreach($els as $el) {
            $attr = $el->attributes('');
            if($attr['rel'] != 'self') continue;
            $bean->doc_url = (string)$attr['href'];
            $attrc = $el->attributes('cmisra', true);
            $cmsid = (string)$attrc['id']; // looks like snx:file!5288F1B0E38B11DF834C785E0A060702
            list($prefix, $id) = explode('!', $cmsid);
            $bean->doc_id = $id;
            break;
        }

        return array('success'=>TRUE);
    }

    public function deleteDoc($document)
    {
        $client = $this->getClient();
        $url = $this->url."files/basic/cmis/repository/p!{$this->api_data['subscriberId']}/object/snx:file!{$document->doc_id}";
        $GLOBALS['log']->debug("LOTUS REQUEST: $url");
        $rawResponse = $client->setUri($url)
            ->request("DELETE");
        $reply = array('rawResponse' => $rawResponse->getBody());
//        $GLOBALS['log']->debug("REQUEST: ".var_export($client->getLastRequest(), true));
//        $GLOBALS['log']->debug("RESPONSE: ".var_export($rawResponse, true));
        return array('success'=>TRUE);
    }

    public function downloadDoc($documentId, $documentFormat){}
    public function shareDoc($documentId, $emails){}
    public function searchDoc($keywords){
        global $db;

        $sql = "
SELECT doc_id AS id, doc_url AS url, filename AS name, date_modified AS date_modified FROM notes WHERE filename LIKE '".$db->quote($keywords)."%' OR name LIKE '".$db->quote($keywords)."%' AND doc_type = 'LotusLiveDirect'
UNION ALL
SELECT doc_id AS id, doc_url AS url, filename AS name, date_modified AS date_modified FROM document_revisions WHERE filename LIKE '".$db->quote($keywords)."%' AND doc_type = 'LotusLiveDirect' ORDER BY date_modified DESC";

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
        $client = $this->getClient();
        $url = rtrim($this->url,"/")."/".ltrim($urlReq, "/");
        $GLOBALS['log']->debug("REQUEST: $url");
        $rawResponse = $client->setUri($url)->request($method);
        $reply = array('rawResponse' => $rawResponse->getBody());
        $GLOBALS['log']->debug("RESPONSE: ".var_export($rawResponse, true));
        if($json) {
            $response = json_decode($reply['rawResponse'],true);
            $GLOBALS['log']->debug("RESPONSE-JSON: ".var_export($response, true));
            if ( empty($rawResponse) || !is_array($response) ) {
                $reply['success'] = FALSE;
                // FIXME: Translate
                $reply['errorMessage'] = 'Bad response from the server';
            } else {
                $reply['responseJSON'] = $response;
                $reply['success'] = TRUE;
            }
        } else {
            $reply['success'] = true;
        }

        return $reply;
    }

    public function getOauthRequestURL()
    {
        return rtrim($this->url,"/")."/".ltrim($this->oauthReq, "/");
    }

    public function getOauthAuthURL()
    {
        return rtrim($this->url,"/")."/".ltrim($this->oauthAuth, "/");
    }

    public function getOauthAccessURL()
    {
        return rtrim($this->url,"/")."/".ltrim($this->oauthAccess, "/");
    }
}
