<?php
require_once('include/externalAPI/cmis_repository_wrapper.php');
require_once('include/externalAPI/Base/ExternalAPIBase.php');
require_once('include/externalAPI/Base/WebDocument.php');
 class CMISBase extends ExternalAPIBase implements WebDocument{
    public $supportedModules = array('Documents', 'Notes');
    /**
     * @var CMIS client
     */
    protected $client;
     /**
      * @var The Url to the CMIS repository
      */
    protected $repoUrl;
    /**
     * @var string The root folder where documents will be uploaded to
     */
    protected $rootFolderPath = '/';
    /**
     * @var The root folder object
     */
    protected $rootFolder;
     
    public function __construct()
    {

    }

    public function checkLogin($eapmBean = null)
    {
        $reply = parent::checkLogin($eapmBean);

        if ( !$reply['success'] ) {
            return $reply;
        }
        $this->rootFolder = $this->client->getObjectByPath($this->rootFolderPath);
        if(!$this->checkResponse()){
            $reply['success'] = FALSE;
            $reply['errorMessage'] = "There was a problem with this request!\n";
        }
        return $reply;
    }
     
    public function loadEAPM($eapmBean)
    {
        parent::loadEAPM($eapmBean);
        $this->repoUrl = $this->getConnectorParam('url');

        $this->client = new CMISService($this->repoUrl, $this->account_name, $this->account_password);

        return true;
    }


    public function uploadDoc($bean, $fileToUpload, $docName, $mimeType)
    {
        $this->rootFolder = $this->client->getObjectByPath($this->rootFolderPath);
        $newDocument = $this->client->createDocument($this->rootFolder->id, $docName, array (), file_get_contents($fileToUpload), $mimeType);
       
        $result = array();
        if(!$this->checkResponse()){
            $result['success'] = FALSE;
		    $result['errorMessage'] = "There was a problem with this request!\n";;
        }else{
            $bean->doc_id = $newDocument->id;
            $result = array('success'=>TRUE);
        }
        return $result;
    }

    public function deleteDoc($document)
    {
        $this->client->deleteObject($document->doc_id);
        return array('success'=>TRUE);
    }

    public function searchDoc($keywords,$flushDocCache=false){
        //$this->client->query($document->doc_id);
    }
    public function downloadDoc($documentId, $documentFormat){}
    public function shareDoc($documentId, $emails){}

    private function checkResponse(){
        if ($this->client->getLastRequest()->code > 299){
            return false;
        }
        return true;
    }
 }
