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
                $this->handleGET();
            break;

            default:
                $err = new RestError();
                $err->ReportError(404);
                exit;
            break;
        }
    }

    /**
     * This method handles all GET requests for this class.
     */
    private function handleGET() {
        global $current_user;
        $auth = $this->getAuth();
        $this->isValidToken($auth);
        $userModList = $this->helper->get_user_module_list($current_user);

        // Default to mobile for now
        $platform = 'base';
        if (!empty($_REQUEST['platform'])) {
            $platform = $_REQUEST['platform'];
        }
        

        $moduleFilter = $userModList;
        if (!empty($_REQUEST['modules'])) {
            // Use str_getcsv here so that commas can be escaped, I pity the fool that has commas in his module names.
            $modules = str_getcsv($_REQUEST['modules'],',','');
            if (!empty($modules) ) {
                foreach ($modules as $modName) {
                    if (isset($userModList[$modName])) {
                        array_push($moduleFilter, $modName);
                    }
                }
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


        $meta = new MetaDataManager();
        $data = $meta->getData($moduleFilter, $typeFilter, $platform);
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