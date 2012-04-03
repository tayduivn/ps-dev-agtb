<?php

require_once("include/rest/RestObjectInterface.php");
require_once("include/rest/internalObjects/RestError.php");
require_once("include/rest/internalObjects/RestUtils.php");
require_once("include/rest/internalObjects/RestObject.php");


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
    private $requiredParams = array(
        "username",
        "password",
        "type");
    private $optionalParams = array(
        "client-info" => array (
            "uuid",
            "model",
            "osversion",
            "carrier",
            "appversion",
            "ismobile"
        )
    );

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
    }

    private function handlePost() {
        $isvalid = false;
        $raw_post = file_get_contents("php://input");
        $result = RestUtils::isValidJson($raw_post);

        if ($result["err"] != false) {
            $err = new RestError();
            $err->ReportError(415, $result["err_str"]);
            exit;
        }

        $this->login($result['data']['username'], $result['data']['password']);
    }

    private function login($user, $pass) {
        require_once("include/rest/SugarWebServiceImpl.php");
        $result = array();
        global $current_user;

        $webser = new SugarWebServiceImpl();
        $result = $webser->login(array("user_name" => $user, "password" => $pass, "encryption" => "PLAIN"), "none", array());
        if (array_key_exists("id", $result)) {
            $data = array("token" => $result["id"]);
            $data = json_encode($data);
            $this->sendJSONResponse($data);
            exit;
        } else {
            $err = new RestError();
            $err->ReportError(401, "\nLogin Failed\n");
            exit;
        }
    }

    /**
     * This method checks to make sure that all params passed to Login are valid.
     * Any missing params will cause a REST error to be raised and this scrtip will
     * exit.
     *
     * @param $data
     * @return bool
     */
    private function checkLoginParams($data) {
        $valid = false;

        return $valid;
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