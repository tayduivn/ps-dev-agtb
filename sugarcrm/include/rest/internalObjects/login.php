<?php

include_once("../include/rest/RestObjectInterface.php");
include_once("RestError.php");


/**
 *
 *
 * POST BODY:
{
"username": "trampus",
"password": "somepass",
"type": "text",
"client-info": {
"uuid": "xyz".
"model": "iPhone3,1",
"osVersion": "5.0.1",
"carrier": "att",
"appVersion": "SugarMobile 1.0",
"ismobile": true
}
}
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