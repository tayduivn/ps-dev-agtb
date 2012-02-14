<?php

include_once("../include/rest/RestObjectInterface.php");
include_once("RestError.php");
include_once("RestUtils.php");
include_once("RestObject.php");

/**
 *
 */
class MetaData extends RestObject implements IRestObject {

    private $requestData = null;

    function __construct() {
        parent::__construct();
        $this->requestData = $this->getRequestData();
    }

    public function execute() {
        if ($this->requestData["request_method"] != "get") {
            $err = new RestError();
            $err->ReportError(404);
            exit;
        }

        var_dump($_SERVER);

    }

}