<?php

include_once("include/rest/RestObjectInterface.php");
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
        global $sugar_config;
        $auth = new AuthenticationController();
        $err = $auth->login($user, $pass);
        $user = null;
        $result = array();

        if ($err) {
            $user = new User();
            session_start();
            global $current_user;
            $current_user = $user;
            $current_user->loadPreferences();
            $_SESSION['is_valid_session']= true;
            $_SESSION['ip_address'] = query_client_ip();
            $_SESSION['user_id'] = $current_user->id;
            $_SESSION['type'] = 'user';
            //$_SESSION['avail_modules']= self::$helperObject->get_user_module_list($current_user);
            $_SESSION['authenticated_user_id'] = $current_user->id;
            $_SESSION['unique_key'] = $sugar_config['unique_key'];
            $current_user->call_custom_logic('after_login');
            $GLOBALS['log']->info('End: SugarWebServiceImpl->login - succesful login');
            $nameValueArray = array();
            global $current_language;
            $cur_id = $current_user->getPreference('currency');
            $currencyObject = new Currency();
            $currencyObject->retrieve($cur_id);
            $_SESSION['user_language'] = $current_language;
            $result = array('token' => session_id());
            $json = json_encode($result);
            $this->sendJSONResponse($json);

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

        print_r($data); die;

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