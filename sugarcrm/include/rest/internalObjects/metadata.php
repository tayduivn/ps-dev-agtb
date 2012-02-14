<?php

include_once("../include/rest/RestObjectInterface.php");
include_once("RestError.php");
include_once("RestUtils.php");
include_once("RestObject.php");
include_once("../include/MetaDataManager/MetaDataManager.php");

/**
 *
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

    private function handleGET() {
        print_r($_GET); die;
        $md = new MetaDataManager();
    }


}