<?php

include_once("include/rest/RestObjectInterface.php");
include_once("include/modules.php");
include_once("RestError.php");
include_once("RestUtils.php");
include_once("RestObject.php");
include_once("include/MetaDataManager/MetaDataManager.php");
include_once("include/rest/SoapHelperWebService.php");
include_once("include/rest/SugarWebServiceImpl.php");

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
        $deleted = -1;
        $offset = 0;
        $maxresult = 0;
        $userFields = array();
        $uriList = array();
        $auth = $this->getAuth();
        $this->isValidToken($auth);

        if (array_key_exists("deleted", $_GET)) {
            $deleted = $_GET["deleted"];
            $deleted = strtolower($deleted);

            if ($deleted == "true") {
                $deleted = 1;
            } else {
                $deleted = -1;
            }
        }

        if (array_key_exists("maxresult", $_GET)) {
            $maxresult = $_GET["maxresult"];
            if (!is_numeric($maxresult)) {
                $err = new RestError();
                $err->ReportError(415, "\nThe value for 'offset' '{$maxresult}' is not a numeric value!\n");
                exit;
            }

            if ($maxresult < 0) {
                $maxresult = 0;
            }
        }

        if (array_key_exists("offset", $_GET)) {
            $offset = $_GET["offset"];
            if (!is_numeric($offset)) {
                $err = new RestError();
                $err->ReportError(415, "\nThe value for 'offset' '{$offset}' is not a numeric value!\n");
                exit;
            }

            if ($offset < 0) {
                $offset = 0;
            }
        }

        if (array_key_exists("fields", $_GET)) {
            $tmpfields = explode(",", $_GET["fields"]);
            foreach ($tmpfields as $f) {
                array_push($userFields, $f);
            }
        }

        if (!in_array("id", $userFields)) {
            array_push($userFields, "id");
        }

        $userModList = $this->helper->get_user_module_list($current_user);
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
        // check to make sure all requested fields by the user are valid for this object //
        foreach ($userFields as $ufield) {
            if (!in_array($ufield, $modFieldNames)) {
                $err = new RestError();
                $msg = "\nRequest field: '{$ufield}' is not a valid field name for the '{$this->objName}'".
                    "module!\n";
                $err->ReportError(415, $msg);
                exit;
            }
        }

        $entryList = $obj->get_entry_list("{$auth}", "{$this->objName}", '', '', $offset, $userFields,
            array(), $maxresult, $deleted);

        $ids = array();
        foreach ($entryList["entry_list"] as $dhash) {
            if (array_key_exists("name_value_list", $dhash)) {
                $fieldsData = array();
                foreach (array_keys($dhash["name_value_list"]) as $dkey) {
                    // don't need this is the hash's data since it is the key for the hash data //
                    if ($dkey == "id") {
                        continue;
                    }

                    $fieldsData[$dkey] = $dhash["name_value_list"][$dkey]["value"];
                }
                $ids["{$dhash["id"]}"] = $fieldsData;
            } else {
                $ids["{$dhash["id"]}"] = "{$this->objName}/\{{$dhash["id"]}\}";
            }
        }

        $md5 = json_encode($ids);
        $md5 = md5($md5);
        $ids["md5"] = $md5;
        $ids["next_offest"] = $entryList["next_offset"];
        $ids["result_count"] = $entryList["result_count"];
        $t = json_encode($ids);
        $this->sendJSONResponse($t);
    }

}