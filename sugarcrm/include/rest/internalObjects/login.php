<?php

include_once("../include/rest/RestObjectInterface.php");
include_once("RestError.php");
include_once("RestUtils.php");
include_once("RestObject.php");


/**
 *
 *
 * POST REQUEST BODY:
 * {
 *     "username": "trampus",
 *     "password": "somepass",
 *     "type": "text",
 *     "client-info": {
 *         "uuid": "xyz".
 *         "model": "iPhone3,1",
 *         "osVersion": "5.0.1",
 *         "carrier": "att",
 *        "appVersion": "SugarMobile 1.0",
 *        "ismobile": true
 *    }
 * }
 *
 */

class Login extends RestObject implements IRestObject {

    private $verbID = null;

    function __construct() {
        parent::__construct();

        $this->verbID = $this->verbToId();
    }

    public function execute() {

        switch($this->verbID) {
            case HTTP_POST:
                $this->handlePost();
                break;
            default:
                $err = new RestError();
                $err->ReportError(404);
                exit;
                break;
        }

        /*
         * Here is where we need to be logging into the app.
         */

    }

    private function handlePost() {
        $raw_post = file_get_contents("php://input");
        $result = RestUtils::isValidJson($raw_post);
        if ($result["err"] != false) {
            $err = new RestError();
            $err->ReportError(415, $result["err_str"]);
            exit;
        }
    }


    /**
     *
     * @static
     *
     * This method checks to see if the current login is still valid.
     *
     * @param $loginInfo A hash.
     */
    public static function checkValidLogin($loginInfo) {

    }
}