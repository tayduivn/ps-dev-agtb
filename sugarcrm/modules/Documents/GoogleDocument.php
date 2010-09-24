<?php
require_once('WebDocument.php');
require_once('Zend/Gdata/Docs.php');
require_once('Zend/Gdata/ClientLogin.php');

class GoogleDocument extends WebDocument{
	function GoogleDocument($account_url, $account_name, $account_password){
		require_once('GoogleXML.php');
		$this->account_url = $account_url;
		$this->account_name = $account_name;
		$this->account_password = $account_password;
		$this->getClient();
	}
	
	protected function getClient(){
		$service = Zend_Gdata_Docs::AUTH_SERVICE_NAME; // predefined service name for Google Documents	
		$this->httpClient = Zend_Gdata_ClientLogin::getHttpClient($this->account_name, $this->account_password, $service);
		$this->gdClient = new Zend_Gdata_Docs($this->httpClient, 'SugarCRM-GDocs-0.1');
	}
			
	function uploadDoc($fileToUpload, $docName, $mimeType){
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
			return $matches[1];
		}catch (Exception $e)
		{
			throw $e;
		}
	}

    function downloadDoc($documentId, $documentFormat){
    	$format = 'txt';
    	$document = $this->gdClient->getDocument($documentId);
    	//var_dump(var_export($document));
		$sessionToken = $this->httpClient->getClientLoginToken();
		var_dump($sessionToken);
		$url = $document->content->getSrc();
		//$url = 'http://docs.google.com/feeds/download/documents/Export?docID='.$documentId;
		$opts = array(  
		    'http' => array(  
		    'method' => 'GET',  
		    'header' => "Host: docs.google.com\r\n".
		    			"GData-Version: 2.0\r\n". 
						"Content-type: application/x-www-form-urlencoded\r\n". 
		                "Authorization: $sessionToken"  
		
//			    'header' => "Authorization: \"$sessionToken\"\r\n"  
			)  
		);  
		if ($url != null) {  
		    $url =  $url . "&exportFormat=$format";  
		}
		echo $url;
		echo file_get_contents($url, false, stream_context_create($opts)); 
    }
    
    function deleteDoc($documentId) {
    	$document = $this->gdClient->getDocument($documentId);
    	return  $document->delete();
    }
	
	function shareDoc($documentId, $emails){
		
	}
	
	function browseDoc($meeting, $attendeeName){
		
	}

    function searchDoc($keywords){
    	
    }
}