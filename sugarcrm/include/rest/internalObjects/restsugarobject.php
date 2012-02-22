<?php

include_once("include/rest/RestObjectInterface.php");
include_once("include/modules.php");
include_once("RestError.php");
include_once("RestUtils.php");
include_once("RestObject.php");
include_once("include/MetaDataManager/MetaDataManager.php");
include_once("service/core/SoapHelperWebService.php");
include_once("service/core/SugarWebServiceImpl.php");

class RestSugarObject extends RestObject implements IRestObject {

    //public $helper = null;
    private $verbID = null;
    private $objName = null;
    private $user = null;

    function __construct($objName) {
        parent::__construct();

        $this->objName = ucfirst($objName);
        $this->verbID = $this->verbToId();
        $this->getAuth();
    }

    public function execute() {
        switch ($this->verbID) {
            case HTTP_GET:
                $this->handleGet();
                break;
            default:

                break;
        }
    }

    /**
     * This method gets all of the modules
     */
    private function handleGet() {
        global $current_user;
        $auth = $this->getAuth();
        $this->isValidToken($auth);
        print "FOO";

        print_r($_SESSION); die;



        print "USER: {$this->userLoggedin->id}"; die;

        $userModList = $this->helper->get_user_module_list($current_user);


        $auth = $this->getAuth();
        $this->isValidToken($auth);

        var_dump($current_user);
        print "USER: {$current_user->id} => {$tmp}"; die;


        print_r($current_user); die;

        $uriList = array();

        if ($auth == null) {
            $err = new RestError();
            $err->ReportError(403);
            exit;
        }

        $this->isValidToken($auth);
        $tmp = $this->helper->get_user_module_list($current_user);
        $UserModulList = array_keys($tmp);
        $tmp = null;

        if (!in_array($this->objName, $UserModulList)) {
            $err = new RestError();
            $err->ReportError(403, "\nCurrent user does not have access to this resource!\n");
            exit;
        }

        $obj = new SugarWebServiceImpl();
        $fields = $obj->get_module_fields($auth, $this->objName, array());
        $modFieldNames = array_keys($fields["module_fields"]);

//get_entry_list($session, $module_name, $query, $order_by,$offset, $select_fields, $link_name_to_fields_array, $max_results, $deleted )
        $entryList = $obj->get_entry_list("{$auth}", "{$this->objName}", '', '', 0, array(), array(), 0, -1);
        print_r($entryList); die;


        $tmp = $obj->get_entries($auth, $this->objName, array(), $fields, array());
        print_r($tmp); die;

        /*
        $json = json_encode($list);
        $this->sendJSONResponse($json);
        */
    }

}