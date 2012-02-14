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

    function __construct() {
        parent::__construct();
    }

    public function execute() {
        if (strtolower($_SERVER["REQUEST_METHOD"]) != "post") {
            $err = new RestError();
            $err->ReportError(404);
            exit;
        }

        if (!RestUtils::isJsonHeader()) {
            $err = new RestError();
            $err->ReportError(415);
            exit;
        }

        $raw_post = file_get_contents("php://input");
        $result = RestUtils::isValidJson($raw_post);

        if ($result["err"] != false) {
            $err = new RestError();
            $err->ReportError(415, $result["err_str"]);
            exit;
        }



        /*
         * Here is where we need to be logging into the app.
         */

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