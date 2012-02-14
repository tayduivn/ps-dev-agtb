<?php

include_once("../include/rest/RestObjectInterface.php");
include_once("RestError.php");
include_once("RestUtils.php");
include_once("RestObject.php");
/**
 *
 */
class MetaData extends RestObject implements IRestObject {

    function __construct() {
        parent::__construct();
    }

    public function execute() {
        if (strtolower($_SERVER["REQUEST_METHOD"]) != "get") {
            $err = new RestError();
            $err->ReportError(404);
            exit;
        }

        var_dump($_SERVER);

    }

}