<?php

include_once("include/rest/RestObjectInterface.php");
include_once("RestError.php");
include_once("RestUtils.php");
include_once("RestObject.php");
include_once("include/MetaDataManager/MetaDataManager.php");


/**
 * This is the MetaData Class for the rest API.  It handles all metadata requests and
 * responses.
 */
class MetaData extends RestObject implements IRestObject {

    private $requestData = null;
    private $verbID = null;

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
        $isMobile = false;
        $typeFiler = null;
        $filter = null;

        // hack, should make this better and reuseable later. //
        if (array_key_exists("mobile", $_GET)) {
            $isMobile = strtolower($_GET['mobile']);
            if ($isMobile == "true") {
                $isMobile = true;
            } else {
                $isMobile = false;
            }
        }

        if (array_key_exists("filter", $_GET) && !empty($_GET['filter'])) {
            $fdata = explode(",", $_GET['filter']);
            if ($fdata != false) {
                $filter = $fdata;
            }
        }

        if (array_key_exists("type", $_GET) && !empty($_GET['type'])) {
            $fdata = explode(",", $_GET['type']);
            if ($fdata != false) {

                $typeFiler = $fdata;
            }
        }

        $meta = new MetaDataManager($filter, $typeFiler, $isMobile);
        $data = $meta->getData();
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