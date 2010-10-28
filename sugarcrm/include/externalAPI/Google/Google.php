<?php
require_once('include/externalAPI/Base/ExternalAPIBase.php');
require_once('include/externalAPI/Base/WebDocument.php');
require_once('Zend/Gdata/Docs.php');
require_once('Zend/Gdata/ClientLogin.php');

class Google extends ExternalAPIBase implements WebDocument {
    public $supportedModules = array('Documents', 'Notes');
    public $authMethods = array("password" => 1, "oauth" => 1);
    protected $scope = "https://www.google.com/m8/feeds/ http://docs.google.com/feeds/";
    protected $oauthReq ="https://www.google.com/accounts/OAuthGetRequestToken";
    protected $oauthAuth ="https://www.google.com/accounts/OAuthAuthorizeToken";
    protected $oauthAccess ="https://www.google.com/accounts/OAuthGetAccessToken";

	function __construct(){
		require_once('include/externalAPI/Google/GoogleXML.php');
		$this->oauthReq .= "?scope=".urlencode($this->scope);
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
//            $GLOBALS['log']->debug("REQ: ".var_export($this->httpClient->getLastRequest(), true));
//            $GLOBALS['log']->debug("REQ: ".var_export($this->httpClient->getLastResponse(), true));
        }

        return $reply;
    }

    protected function getClient(){
        if ( isset($this->httpClient) ) {
            // Already logged in
            return;
        }
		$service = Zend_Gdata_Docs::AUTH_SERVICE_NAME; // predefined service name for Google Documents
		if(isset($this->authData) && $this->authData->type == 'oauth') {
		    // FIXME: bail if auth token not set
            $this->httpClient = $this->authData->getHttpClient();
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

            preg_match(
                '/id=([\S]*)[&|$]/',
                $alternateLink,
                $matches
                );
			$bean->doc_id = $matches[1];
            $bean->doc_url = $alternateLink;
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
    }

    function deleteDoc($documentId) {
		$this->getClient();
    	$document = $this->gdClient->getDocument($documentId);
    	return  $document->delete();
    }

	function shareDoc($documentId, $emails){

	}

	function browseDoc($path){

	}

    function searchDoc($keywords){

    }
}