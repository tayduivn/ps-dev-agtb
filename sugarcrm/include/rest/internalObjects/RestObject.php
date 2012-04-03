<?php

include_once("include/rest/RestData.php");
include_once("RestError.php");
include_once("include/rest/SoapHelperWebService.php");
include_once("soap/SoapError.php");

/**
 * This class is os a base for all RestObjects.
 */
abstract class RestObject implements IRestObject {

    private $requestData = array();
    private $httpVerb = null;
    private $uriData = null;
    private $auth = null;
    public $helper = null;
    public $userLoggedin = null;

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
        $this->setAuth();
        $this->helper = new SoapHelperWebServices();
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

    private function setAuth() {

    }

    /**
     * Checks to see if the OAUTH TOKEN exists in the header info.
     *
     * Returns null when the token doesn't exist else the token is returned.
     *
     * @return null
     */
    protected function getAuth() {
        $auth = null;

        if (!array_key_exists("HTTP_OAUTH_TOKEN", $_SERVER)) {
            $auth = null;
        } else {
            $auth = $_SERVER["HTTP_OAUTH_TOKEN"];
        }

        return $auth;
    }

    /**
     *
     * returns true if the token is valid else throws an http error.
     *
     * @param $token
     * @return bool
     */
    protected function isValidToken($token) {
        $isValid = $this->helper->validate_authenticated($token);

        if (!$isValid) {
            $err = new RestError();
            $err->ReportError(401);
            exit;
        } else {
            return true;
        }
    }

    public function getRequestData() {
        return $this->requestData;
    }

    public function setURIData($data) {
        $count = count($data) -1;

        // this case handles if there is a trailing "/" on the end of the request //
        if (empty($data[$count])) {
            $data = array_pop($data);
        }

        $this->uriData = $data;
    }

    public function getURIData() {
        return $this->uriData;
    }

    /**
     * The typical URI data that is handed in to setURIData has been run through
     * strtolower(), this makes matching Module names, or even ID's with uppercase
     * / lowercase letters in them near impossible. This function grabs an unaltered
     * version of the URI data
     */
    protected function getRealURIData() {
        static $realURIData;

        if (isset($realURIData)) {
            return $realURIData;
        }

        $path = parse_url($_SERVER["REQUEST_URI"]);
        $uri_data = explode('/',$path['path']);
        $found_rest = false;
        $uri_tmp = array();

        foreach ($uri_data as $d) {
            if ($found_rest != true && $d == "rest") {
                $found_rest = true;
                continue;
            }

            if ($found_rest && !empty($d)) {
                array_push($uri_tmp, $d);
            }
        }

        $realURIData = $uri_tmp;

        return $realURIData;
        
    }

}