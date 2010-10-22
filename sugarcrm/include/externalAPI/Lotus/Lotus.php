<?php

require_once('include/externalAPI/Base/ExternalAPIPlugin.php');
require_once('include/externalAPI/Base/WebMeeting.php');
require_once('include/externalAPI/Base/WebDocument.php');

class Lotus implements ExternalAPIPlugin,WebMeeting,WebDocument {

    protected $dateFormat = 'm/d/Y H:i:s';
    protected $urlExtension = '/envq/Production/';
	
    public $useAuth = true;
    public $requireAuth = true;
    public $supportedModules = array('Meetings','Documents');
    public $supportMeetingPassword = true;

    // ExternalAPIPlugin Functions
	function __construct() {
    }

    public function loadEAPM($eapmData) {
        $this->account_host = $eapmData->url;
        $this->account_url = $eapmData->url.$this->urlExtension;
        $this->account_name = $eapmData->name;
        $this->account_password = $eapmData->password;    
    }

    public function checkLogin($eapmBean) {
        // FIXME: Stub
        $reply = array();
        $reply['success'] = true;
        
        return $reply;
    }
    
    public function logOff() {
    }

    // Web Meeting Functions
	public function scheduleMeeting($bean) {
        $reply = $this->makeRequest('GetMeeting', array());
        // $reply = $this->makeRequest('GetMeetingSummary', array(),array('extSFOpportunityId'=>10001));
        // $reply = $this->makeRequest('CreateCollection', array(),array('name'=>'MyTestCollection'));
        // $reply = $this->makeRequest('GetCollectionDetails', array(),array('id'=>'3CAA8D80D29311DFA08B9C830A060702'));
        // $reply = $this->makeRequest('uploadfile', array(),array('collectionid'=>'EB366350CC5911DFA2325E610A060702','fileid'=>'D6286FC0060D11DFAE6C3BA70A060702'));
        // $reply = $this->makeRequest('uploadfile', array(),array('collectionid'=>'3CAA8D80D29311DFA08B9C830A060702','fileid'=>'D6286FC0060D11DFAE6C3BA70A060702'));

        die('Attempted a schedule: <pre>'.print_r($reply,true));

        $bean->external_id = $reply['responseJSON']['feed']['entry']['meetingID'];
        $bean->host_url = $reply['responseJSON']['feed']['entry']['hostURL'];
        $bean->join_url = $reply['responseJSON']['feed']['entry']['joinURL'];

        return $reply;
    }
	public function unscheduleMeeting($meeting) {}
	public function joinMeeting($meeting, $attendeeName) {}
    public function hostMeeting($meeting) {}
	public function inviteAttendee($meeting, $attendee) {}
	public function uninviteAttendee($attendee) {}
	public function listMyMeetings() {}
	public function getMeetingDetails($meeting) {}

    // Web Document Functions
	public function uploadDoc($bean, $fileToUpload, $docName, $mineType) {}
    public function downloadDoc($documentId, $documentFormat) {}
	public function shareDoc($documentId, $emails) {}
	public function browseDoc($path) {}
	public function deleteDoc($documentId) {}
    public function searchDoc($keywords) {}

    // Internal functions
    protected function makeRequest($requestMethod, $data = array(), $urlParams = array() ) {
        $dataString = json_encode($data);

        $urlParams['ciUser'] = 'admin@LL_SugarCRM';
        $urlParams['ciPassword'] = 'changeIt!';
        $urlParams['UserName'] = $this->account_name;
        $urlParams['Password'] = $this->account_password;
        
        $url = 'https://' . $this->account_url . $requestMethod . '?';
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

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        
        $GLOBALS['log']->fatal("Where: ".$url);
        $GLOBALS['log']->fatal("Sent:\n".print_r($data,true));
        $rawResponse = curl_exec($ch);
        $GLOBALS['log']->fatal("Got:\n".print_r($rawResponse,true));

        // FIXME: This is a kludge until the JSON comes back correctly.
        $rawResponse = preg_replace('/(activityLink": ".*)/','\1,',$rawResponse);
        $rawResponse = preg_replace('/(todoItemLink": ".*)/','\1",',$rawResponse);
        $rawResponse = preg_replace('/(fileDetails": ".*)/','\1",',$rawResponse);
//        die("IKEA: <pre>".print_r($rawResponse,true));
        
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

            // The JSON appears to be a pretty direct translation from XML.. so I'm going to sort it out a bit.
            $reply['success'] = TRUE;
        }
        
        return $reply;
    }

}
