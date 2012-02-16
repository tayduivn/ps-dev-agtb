<?php

include_once("include/rest/RestObjectInterface.php");
include_once("RestError.php");
include_once("RestUtils.php");
include_once("RestObject.php");
include_once("service/core/SoapHelperWebService.php");
include_once("soap/SoapError.php");

class logout extends RestObject implements IRestObject {

    private $helper = null;

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

            $GLOBALS['log']->info('Begin: SugarWebServiceImpl->logout');
            $error = new SoapError();
            LogicHook::initialize();
            if (!$this->helper->checkSessionAndModuleAccess($id, 'invalid_session', '', '', '', $error)) {
                $GLOBALS['logic_hook']->call_custom_logic('Users', 'after_logout');
                $GLOBALS['log']->info('End: SugarWebServiceImpl->logout');
                exit;
            }

            $current_user->call_custom_logic('before_logout');
            session_destroy();
            $GLOBALS['logic_hook']->call_custom_logic('Users', 'after_logout');
            $GLOBALS['log']->info('End: SugarWebServiceImpl->logout');
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