<?php

include_once("include/rest/RestObjectInterface.php");
include_once("RestError.php");
include_once("RestUtils.php");
include_once("RestObject.php");
include_once("include/rest/SoapHelperWebService.php");
include_once("soap/SoapError.php");

class logout extends RestObject implements IRestObject {

    public $helper = null;

    function __construct() {
        parent::__construct();

        $this->verbID = $this->verbToId();
        $this->helper = new SoapHelperWebServices();
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

    /**
     * @param $id The session id
     * @return mixed
     */
    private function logout($id) {
        $result = false;

        $result = $this->helper->validate_authenticated($id);
        if ($result) {
            global $current_user;
            $webser = new SugarWebServiceImpl();
            $webser->logout($id);
        } else {
            $err = new RestError();
            $err->ReportError(403);
            exit;
        }
    }

    /**
     * This method handles post requests for this object.
     */
    private function handlePost() {
        $isvalid = false;

        $raw_post = file_get_contents("php://input");
        $result = RestUtils::isValidJson($raw_post);
        if ($result["err"] != false) {
            $err = new RestError();
            $err->ReportError(415, $result["err_str"]);
            exit;
        }

        $this->logout($result['data']['token']);
    }
}