<?php

include_once("../include/rest/RestObjectInterface.php");
include_once("RestError.php");
include_once("RestUtils.php");
/**
 *
 */
class MetaData implements IRestObject {

    function __construct() {

    }

    public function execute() {
        if (strtolower($_SERVER["REQUEST_METHOD"]) != "get") {
            $err = new RestError();
            $err->ReportError(404);
            exit;
        }


    }

}