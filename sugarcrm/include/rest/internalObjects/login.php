<?php

include_once("../include/rest/RestObjectInterface.php");
include_once("RestError.php");
include_once("RestUtils.php");


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

class Login implements IRestObject {
    function __construct() {

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

        $json = json_decode($raw_post);
        $err = json_last_error();


        ///print_r($json);

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