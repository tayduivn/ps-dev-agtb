<?php

include_once("include/rest/RestObjectInterface.php");
include_once("include/modules.php");
include_once("RestError.php");
include_once("RestUtils.php");
include_once("RestObject.php");
include_once("include/MetaDataManager/MetaDataManager.php");
include_once("include/rest/SoapHelperWebService.php");
include_once("include/rest/SugarWebServiceImpl.php");

/**
 * This class handles all requests to the serverinfo object.
 */
class serverinfo extends RestObject implements IRestObject {

    function __construct() {
        parent::__construct();

        $this->verbID = $this->verbToId();
        $this->helper = new SoapHelperWebServices();
    }

    public function execute() {

        switch($this->verbID) {
            case HTTP_GET:
                $result = $this->handleGet();
                break;
            default:
                $err = new RestError();
                $err->ReportError(404);
                $result["error"] = 404;
                $result["err_msg"] = "";
                exit;
                break;
        }
    }

    /**
     * This function handles all get requests to this object.
     *
     */
    private function handleGet() {
        global $current_user;
        $auth = $this->getAuth();
        $this->isValidToken($auth);
        $info = null;
        $data = array();
        $websrv = new SugarWebServiceImpl();

        $info = $websrv->get_server_info();
        $data["time_type"] = "gmt";
        $data["time"] = $info["gmt_time"];
        $data["version"] = $info["version"];
        $data["flavor"] = $info["flavor"];
        $md5 = json_encode($data);
        $md5 = md5($md5);
        $data["md5"] = $md5;
        $json = json_encode($data);
        $this->sendJSONResponse($json);
    }
}