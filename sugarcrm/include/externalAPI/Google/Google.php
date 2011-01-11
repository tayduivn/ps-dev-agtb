<?php
require_once('include/externalAPI/Base/ExternalAPIBase.php');
require_once('include/externalAPI/Base/WebDocument.php');
require_once('Zend/Gdata/Docs.php');
require_once('Zend/Gdata/Docs/Query.php');
require_once('Zend/Gdata/ClientLogin.php');

class Google extends ExternalAPIBase implements WebDocument {
    public $supportedModules = array('Documents', 'Notes');
    public $authMethod = 'password';
    public $connector = "ext_eapm_google";

    protected $scope = "https://docs.google.com/feeds/ http://docs.google.com/feeds/";
    protected $oauthReq ="https://www.google.com/accounts/OAuthGetRequestToken";
    protected $oauthAuth ="https://www.google.com/accounts/OAuthAuthorizeToken";
    protected $oauthAccess ="https://www.google.com/accounts/OAuthGetAccessToken";

    public $docSearch = true;
    public $needsUrl = false;
    public $sharingOptions = array('private'=>'LBL_SHARE_PRIVATE','linkable'=>'LBL_SHARE_LINKABLE','public'=>'LBL_SHARE_PUBLIC');

	function __construct(){
		require_once('include/externalAPI/Google/GoogleXML.php');
		$this->oauthReq .= "?scope=".urlencode($this->scope);
	}

    protected function getIdFromUrl($url) {
        preg_match(
            '/id=([\S]*)[&|$]/',
            $url,
            $matches
            );
        $id = $matches[1];

        return $id;
    }

    public function checkLogin($eapmBean = null)
    {
        parent::checkLogin($eapmBean);

        // Emulate a reply
        $reply['success'] = TRUE;

        try {
            $this->getClient();
		    // test documents access
		    $docs = $this->gdClient->getDocumentListFeed('http://docs.google.com/feeds/documents/private/full?title=TestTestTest');
        } catch (Exception $e) {
            $reply['success'] = FALSE;
            $reply['errorMessage'] = $e->getMessage();
            // $GLOBALS['log']->fatal("IKEA REQ: ".var_export($this->httpClient->getLastRequest(), true));
            // $GLOBALS['log']->fatal("IKEA RES: ".var_export($this->httpClient->getLastResponse(), true));
        }

        return $reply;
    }

    protected function getClient(){
        if ( isset($this->httpClient) ) {
            // Already logged in
            return;
        }
		$service = Zend_Gdata_Docs::AUTH_SERVICE_NAME; // predefined service name for Google Documents
		if( $this->authMethod == 'oauth') {
		    // FIXME: bail if auth token not set
            $this->httpClient = $this->authData->getHttpClient($this);
		} else {
		    $this->httpClient = Zend_Gdata_ClientLogin::getHttpClient($this->account_name, $this->account_password, $service);
		}
		$this->gdClient = new Zend_Gdata_Docs($this->httpClient, 'SugarCRM-GDocs-0.1');
    }

	function uploadDoc($bean, $fileToUpload, $docName, $mimeType){
		$this->getClient();
		$filenameParts = explode('.', $fileToUpload);
		$fileExtension = end($filenameParts);
		try{
            $newDocumentEntry = $this->gdClient->uploadFile($fileToUpload, $docName,
                                                            $mimeType,
                                                            Zend_Gdata_Docs::DOCUMENTS_LIST_FEED_URI);

            // Find the URL of the HTML view of this document.
            $alternateLink = $newDocumentEntry->getAlternateLink()->getHref();
//        'http://docs.google.com/document/edit?id=1ZXFfD5DMa6tcgv_9rDK34ZtPUIu5flXtdWMoy-0Ymu0&hl=en'
            $bean->doc_id = $this->getIdFromUrl($alternateLink);
            $bean->doc_url = $alternateLink;
            $bean->doc_direct_url = $alternateLink;
            $result['success'] = TRUE;
		}catch (Exception $e)
         {
             $result['success'] = FALSE;
             $result['errorMsg'] = $e->getMessage();
         }

        return $result;
	}

    function downloadDoc($documentId, $documentFormat){
		$this->getClient();
    	$format = 'txt';
    	$document = $this->gdClient->getDocument($documentId);
    	//var_dump(var_export($document));
    	if($this->authMethod == "password") {
    	    // FIXME: can't we just use the httpClient? It should add auth automatically
    		$sessionToken = $this->httpClient->getClientLoginToken();
    		$GLOBALS['log']->fatal('Session Token: '.$sessionToken);
    		$url = $document->content->getSrc();
	    	//$url = 'http://docs.google.com/feeds/download/documents/Export?docID='.$documentId;
    		$opts = array(
    		    'http' => array(
    		    'method' => 'GET',
    		    'header' => "Host: docs.google.com\r\n".
    		    			"GData-Version: 2.0\r\n".
    						"Content-type: application/x-www-form-urlencoded\r\n".
    		                "Authorization: $sessionToken"
    			)
    		);
    		if ($url != null) {
    		    $url =  $url . "&exportFormat=$format";
    		}
    		$GLOBALS['log']->fatal('Google Doc URL: '.$url);
    		echo file_get_contents($url, false, stream_context_create($opts));
    	} else {
    	    echo $this->httpClient->setUri($url)->request('GET')->getBody();
    	}
    }

    function deleteDoc($documentId) {
		$this->getClient();
    	$document = $this->gdClient->getDocument($documentId);
    	return  $document->delete();
    }

	function shareDoc($documentId, $emails){

	}
    function searchDoc($keywords, $flushDocCache = false){
		$this->getClient();

        if ( empty($keywords) ) {
            $feed = $this->gdClient->getDocumentListFeed('http://docs.google.com/feeds/documents/private/full/-/document');
        } else {
            $docsQuery = new Zend_Gdata_Docs_Query();
            $docsQuery->setQuery($keywords);
            $feed = $this->gdClient->getDocumentListFeed($docsQuery);
        }

        $rawResults = $feed->getEntry();

        $results = array();
        foreach ( $rawResults as $result ) {
            $alternateLink = $result->getAlternateLink()->getHref();
//        'http://docs.google.com/document/edit?id=1ZXFfD5DMa6tcgv_9rDK34ZtPUIu5flXtdWMoy-0Ymu0&hl=en'

            $curr['url'] = $alternateLink;
            $curr['name'] = $result->title->getText();
            $curr['date_modified'] = $result->updated->getText();

            $curr['id'] = $this->getIdFromUrl($alternateLink);


            $results[] = $curr;
        }

        return $results;
    }
}