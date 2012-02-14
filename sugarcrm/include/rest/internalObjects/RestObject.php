<?php

abstract class RestObject implements IRestObject {

    private $requestData = array();

    function __construct() {
        $this->requestData["request_method"] = $_SERVER["REQUEST_METHOD"];
        $this->requestData["encoding_type"] = explode(",", $_SERVER[HTTP_ACCEPT_ENCODING]);
        $this->requestData["raw_post_data"] = $raw_post = file_get_contents("php://input");
    }

    public function execute() {

    }

    protected function processResuestData() {

    }

    public function getRequestData() {
        return $this->requestData;
    }

}