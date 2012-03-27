<?php

require_once("include/rest/RestObjectInterface.php");
require_once("include/rest/internalObjects/RestError.php");
require_once("include/rest/internalObjects/RestUtils.php");
require_once("include/rest/internalObjects/RestObject.php");
require_once("include/MetaDataManager/MetaDataManager.php");
require_once("include/rest/SoapHelperWebService.php");


/**
 * This is the MetaData Class for the rest API.  It handles all metadata requests and
 * responses.
 */
class MetaData extends RestObject implements IRestObject {

    private $requestData = null;
    private $verbID = null;
    public $helper = null;

    function __construct() {
        parent::__construct();
        $this->requestData = $this->getRequestData();
        $this->verbID = $this->verbToId();
    }

    /**
     *
     */
    public function execute() {

        switch($this->verbID) {
            case HTTP_GET:
                $this->handleGetMetadata(false);
            break;
            case HTTP_POST:
                $this->handleGetMetadata(true);
            break;

            default:
                $err = new RestError();
                $err->ReportError(404);
                exit;
            break;
        }
    }

    /**
     * This method handles all GET/POST requests for this class.
     */
    private function handleGetMetadata($isPost=false) {
        global $current_user;
        $auth = $this->getAuth();
        $this->isValidToken($auth);

        // Default to mobile for now
        $platform = 'base';
        if (!empty($_REQUEST['platform'])) {
            $platform = $_REQUEST['platform'];
        }
        

        $moduleFilter = array();
        if (!empty($_REQUEST['modules'])) {
            // Use str_getcsv here so that commas can be escaped, I pity the fool that has commas in his module names.
            // The modules are filtered for security by the MetaDataManager, so let's just pass everything the user wants along.
            $modules = str_getcsv($_REQUEST['modules'],',','');
            if ( $modules != false ) {
                $moduleFilter = $modules;
            }
        }

        $typeFilter = '';
        if (!empty($_REQUEST['metadataType'])) {
            // Explode is fine here, we control the list of types
            $types = explode(",", $_REQUEST['metadataType']);
            if ($types != false) {
                $typeFilter = $types;
            }
        }

        $clientHashes = array();
        if ( $isPost ) {
            $postData = $this->getRequestData();
            $postData = RestUtils::isValidJson($postData['raw_post_data']);
            if ($postData["err"] != false) {
                $err = new RestError();
                $err->ReportError(415, $postData["err_str"]);
                exit;
            }
            $clientHashes = $postData['data'];
        }
        
        $options = array();
        if ( isset($_REQUEST['onlyHash']) && $_REQUEST['onlyHash'] == 'true' ) {
            $options['onlyHash'] = true;
        }

        $meta = new MetaDataManager();
        $data = $meta->getData($clientHashes,$moduleFilter, $typeFilter, $platform, $options);
        $json = json_encode($data);
        $err = json_last_error();

        if ($err != JSON_ERROR_NONE) {
            $err = RestUtils::jsonErrorToStr($err);
            $e = new RestError();
            $e->ReportError(415, "\n\nJSON ERROR: '{$e}'\n'");
            exit;
        } else {
            $this->sendJSONResponse($json);
        }
    }
}