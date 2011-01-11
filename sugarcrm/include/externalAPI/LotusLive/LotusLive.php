<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('include/externalAPI/Base/OAuthPluginBase.php');
require_once('include/externalAPI/Base/WebMeeting.php');
require_once('include/externalAPI/Base/WebDocument.php');


class LotusLive extends OAuthPluginBase implements WebMeeting,WebDocument {

    protected $dateFormat = 'm/d/Y H:i:s';
//    protected $urlExtension = '/envq/Production/';
    protected $url = 'eval-cloud2.castiron.com/envq/Production/';

    public $authMethod = 'oauth';
    public $supportedModules = array('Meetings','Notes', 'Documents');
    public $supportMeetingPassword = false;
    public $docSearch = true;
    public $connector = "ext_eapm_lotuslive";

	protected $meetingID;
    protected $joinURL;
    protected $baseURL = 'https://apps.test.lotuslive.com/';
    public $hostURL;
    protected $oauthReq;
    protected $oauthAuth;
    protected $oauthAccess;
    protected $oauthParams = array(
    	'signatureMethod' => 'PLAINTEXT',
    );

    public $canInvite = false;
    public $sendsInvites = false;
    public $needsUrl = false;
    // public $sharingOptions = array('private'=>'LBL_SHARE_PRIVATE','company'=>'LBL_SHARE_COMPANY','public'=>'LBL_SHARE_PUBLIC');
    public $sharingOptions = null;

    function __construct() {
        $this->hostURL = $this->baseURL.'meetings/host';
        $this->oauthReq = $this->baseURL.'manage/oauth/getRequestToken';
        $this->oauthAuth = $this->baseURL.'manage/oauth/authorizeToken';
        $this->oauthAccess = $this->baseURL.'manage/oauth/getAccessToken';

        // return parent::__construct();
    }


    public function loadEAPM($eapmBean)
    {
        parent::loadEAPM($eapmBean);

        if ( !empty($eapmBean->api_data) ) {
            $api_data = json_decode(base64_decode($eapmBean->api_data),true);
            if ( isset($api_data['subscriberID']) ) {
                $this->meetingID = $api_data['meetingID'];
                $this->hostURL = $api_data['hostURL'];
                $this->joinURL = $api_data['joinURL'];
                $this->subscriberID = $api_data['subscriberID'];
            }
        }
    }

    public function checkLogin($eapmBean = null)
    {
        $reply = parent::checkLogin($eapmBean);
        if ( !$reply['success'] ) {
            return $reply;
        }
        $reply = $this->makeRequest('GetSubscriberId/OAuth');
        if ( ! $reply['success'] ) {
            return $reply;
        }

        $reply2 = $this->makeRequest('GetMeeting/OAuth');
        if ( ! $reply2['success'] ) {
            return $reply2;
        }

        $apiData = array(
            'meetingID'=>$reply2['responseJSON']['feed']['entry']['meetingID'],
            'hostURL'=>$reply2['responseJSON']['feed']['entry']['hostURL'],
            'joinURL'=>$reply2['responseJSON']['feed']['entry']['joinURL'],
            'subscriberID'=>$reply['responseJSON']['subscriber_id'],
            );
        $GLOBALS['log']->fatal('IKEA (api_data): '.print_r($apiData,true));
        $this->eapmBean->api_data = base64_encode(json_encode($apiData));

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
	function unscheduleMeeting($bean) {
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
	function inviteAttendee($bean, $attendee, $sendInvites = false) {
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
    function uninviteAttendee($bean, $attendeeID) {
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
    function getMeetingDetails($bean) {
        // TODO: Implement this, get the meeting information from the provided tags.
        return array('success'=>TRUE);
    }


    public function uploadDoc($bean, $fileToUpload, $docName, $mimeType) {
        // FIXME: don't try uploading a file yet.
//        $result = $this->makeRequest('uploadfile',array('file'=>'@'.$fileToUpload),
//                              array('collectionid'=>$this->collectionId,
//                                    'fileid'=>$bean->id));

        $result = $this->makeRequest('FileUpload/OAuth',base64_encode(file_get_contents($fileToUpload)),
                                     array('filename' => $docName,
//                                           'mimetype' => $mimeType,
                                           'mimetype' => 'application/octet-stream',
                                           'subscriberid' => $this->subscriberID,
                                         ));

        // die('IKEA uploading file: '.$fileToUpload.': <pre>'.print_r($result,true));
        if ( $result['success'] != TRUE ) {
            return $result;
        }
        $bean->doc_id = $result['responseJSON']['FileId'];

        $bean->doc_direct_url = $this->baseURL.'files/basic/cmis/repository/p!'.$this->subscriberID.'/object/snx:file!'.$bean->doc_id.'/stream/'.$bean->doc_id;

        $bean->doc_url = $this->baseURL.'files/filer2/home.do#files.do?subContent=fileDetails.do?fileId='.$bean->doc_id;

        // Refresh the document cache
        $this->loadDocCache(true);

        return $result;
    }

    public function downloadDoc($documentId, $documentFormat){}
    public function shareDoc($documentId, $emails){}
    public function deleteDoc($documentId){}

    public function loadDocCache($forceReload = false) {
        global $db, $current_user;

        create_cache_directory('/include/externalAPI/');
        $cacheFileBase = 'cache/include/externalAPI/docCache_'.$current_user->id.'_LotusLive';
        if ( !$forceReload && file_exists($cacheFileBase.'.php') ) {
            // File exists
            include_once($cacheFileBase.'.php');
            if ( abs(time()-$docCache['loadTime']) < 3600 ) {
                // And was last updated an hour or less ago
                return $docCache['results'];
            }
        }

        $reply = $this->makeRequest('GetFileList/OAuth',null,array('subscriberid' => $this->subscriberID));

        if ( $reply['success'] != true ) {
            return array();
        }

        if ( !is_array($reply['responseJSON']['file_list']) ) {
            $reply['responseJSON']['file_list'] = array();
        }

        $results = array();
        foreach ( $reply['responseJSON']['file_list'] as $remoteFile ) {
            $result['id'] = $remoteFile['file_id'];
            $result['name'] = $remoteFile['file_name'];
            $result['date_modified'] = preg_replace('/^([^T]*)T([^.]*)\....Z$/','\1 \2',$remoteFile['date_modified']);
            $result['direct_url'] = $this->baseURL.'files/basic/cmis/repository/p!'.$this->subscriberID.'/object/snx:file!'.$remoteFile['file_id'].'/stream/'.$remoteFile['file_id'];

            $result['url'] = $this->baseURL.'files/filer2/home.do#files.do?subContent=fileDetails.do?fileId='.$remoteFile['file_id'];

            $results[] = $result;
        }


        $docCache['loadTime'] = time();
        $docCache['results'] = $results;
        $fd = fopen($cacheFileBase.'_tmp.php','w');
        fwrite($fd,'<'."?php\n// This file was auto generated by include/externalAPI/LotusLive/LotusLive.php do not overwrite.\n\n".'$docCache = '.var_export($docCache,true).";\n");
        fclose($fd);
        rename($cacheFileBase.'_tmp.php',$cacheFileBase.'.php');

        return $results;
    }

    public function searchDoc($keywords,$flushDocCache=false){
        $docList = $this->loadDocCache($flushDocCache);

        $results = array();

        $searchLen = strlen($keywords);

        foreach ( $docList as $doc ) {
            if ( strncmp($keywords,$doc['name'],$searchLen) === 0 ) {
                // It matches
                $results[] = $doc;

                if ( count($results) > 15 ) {
                    // Only return the first 15 results
                    break;
                }
            }
        }

        return $results;
    }

    // Internal functions
    protected function makeRequest($requestMethod, $data = '', $urlParams = array() ) {

        $urlParams['ciUser'] = 'admin@LL_SugarCRM';
        $urlParams['ciPassword'] = 'changeIt!';
        $urlParams['csKey'] = $this->oauthParams['consumerKey'];
        $urlParams['csSecret'] = $this->oauthParams['consumerSecret'];
        $urlParams['oAuthKey'] = $this->oauth_token;
        $urlParams['oAuthSecret'] = $this->oauth_secret;

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
            "Content-Length: ".strlen($data),
            );

        $rawResponse = $this->postData($url, $data, $headers);

        $reply = array();
        $reply['responseRAW'] = $rawResponse;
        $reply['responseJSON'] = null;

        // IKEA: Ugly hack (for GetSubscriberId)
        // $rawResponse = preg_replace('/^{ {([^}]*)},{([^}]*)} }$/','{\1,\2}',$rawResponse);

        $response = json_decode($rawResponse,true);

        // IKEA: Ugly hack #2 (for GetFileList)
        // if( isset($response['GetFileList']) ) {
        //     $response = $response['GetFileList'];
        //}

        if ( empty($rawResponse) || !is_array($response) ) {
            $reply['success'] = FALSE;
            // FIXME: Translate
            $reply['errorMessage'] = 'No response from the server.';
        } else {
            $GLOBALS['log']->fatal("Decoded:\n".print_r($response,true));
            $reply['responseJSON'] = $response;

            if ( strtoupper($reply['responseJSON']['status']) == 'OK' ) {
                $reply['success'] = TRUE;
            } else {
                $reply['success'] = FALSE;
                // FIXME: Translate
                $reply['errorMessage'] = 'Request denied by server.';
            }
        }

        return $reply;
    }

}
