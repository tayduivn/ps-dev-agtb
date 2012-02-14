<?php

include_once("include/rest/RestData.php");

abstract class RestObject implements IRestObject {

    private $requestData = array();
    private $httpVerb = null;

    function __construct() {
        $this->requestData["request_method"] = strtolower($_SERVER["REQUEST_METHOD"]);
        $this->requestData["raw_post_data"] = $raw_post = file_get_contents("php://input");

        if (array_key_exists("HTTP_ACCEPT_ENCODING", $_SERVER)) {
            $tmp = explode(",", $_SERVER["HTTP_ACCEPT_ENCODING"]);
            if (count($tmp) > 0 && !empty($tmp[0])) {
                $this->requestData["encoding_type"] = $tmp[0];
            } else {
                $this->requestData["encoding_type"] = null;
            }
        } else {
            $this->requestData["encoding_type"] = null;
        }

        if (array_key_exists("CONTENT_TYPE", $_SERVER)) {
            $this->requestData["content_type"] = $_SERVER["CONTENT_TYPE"];
        } else {
            $this->requestData["content_type"] = null;
        }

        $this->verbToId();
    }

    public function execute() {

    }

    protected function sendJSONResponse($payload) {
        // see rfc rfc4627 //
        // see http://en.wikipedia.org/wiki/Internet_media_type //
        header("Content-type: application/json");

        // should be something like: gzip, compress, etc... //
        if ($this->requestData["encoding_type"] == "gzip") {
            header("Content-Encoding: {$this->requestData["encoding_type"]}");
            $payload = gzencode($payload, 9);
        }

        echo $payload;
    }

    protected function processResuestData() {

    }

    /**
     * Converts the current REQUEST_METHOD into an ID matching the HTTP_{verb} in RestData.php.
     *
     * @return mixed|null
     */
    protected function verbToId() {
        $id = null;
        $verb = "HTTP_" . $this->requestData["request_method"];
        $verb = strtoupper($verb);

        $id = constant($verb);
        return $id;
    }

    public function getRequestData() {
        return $this->requestData;
    }

}