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
 * This is a generic class for handeling opperations for all sugar objects.
 */
class RestSugarObject extends RestObject implements IRestObject {

    private $verbID = null;
    private $objName = null;
    private $user = null;

    function __construct($objName) {
        parent::__construct();

        $this->objName = ucfirst($objName);
        $this->verbID = $this->verbToId();
        $this->getAuth();
    }

    /**
     *
     */
    public function execute() {
        $result = null;

        switch ($this->verbID) {
            case HTTP_GET:
                $result = $this->handleGet();
                break;
            case HTTP_PUT:
                $result = $this->handlePut();
                break;
            case HTTP_POST:
                $result = $this->handlePost();
                break;
            case HTTP_DELETE:
                $result = $this->handleDelete();
                break;
            default:
                break;
        }

        return $result;
    }


    /**
     *
     */
    private function handleDelete() {
        global $current_user;
        $auth = $this->getAuth();
        $this->isValidToken($auth);
        $data = $this->getURIData();
        $id = $data[1];

        $tmpdata = array(
            array(
                "name" => "id",
                "value" => "{$id}"
            ),
            array(
                "name" => "deleted",
                "value" => true
            )
        );

        $webser = new SugarWebServiceImpl();
        $result = $webser->set_entry($auth, $this->objName, $tmpdata);
        if ($result["error"] != 0) {
            $err = new RestError();
            $err->ReportError($result["error"], $result["err_msg"]);
        }
    }

    /**
     * This method handles posts to a sugar object.
     */
    private function handlePost() {
        global $current_user;
        $auth = $this->getAuth();
        $this->isValidToken($auth);
        //$data = $this->getRequestData();

        $result = $this->getRequestData();
        $userData = RestUtils::isValidJson($result["raw_post_data"]);
        if ($userData["err"]) {
            $err = new RestError();
            $err->ReportError(400, $userData["err_str"]);
            exit;
        }

        $postData = array();
        foreach ($userData["data"] as $key => $value) {
            $tmp = array(
                "name" => $key,
                "value" => $value
            );

            array_push($postData, $tmp);
        }

        $websrv = new SugarWebServiceImpl();
        $result = $websrv->set_entry($auth, $this->objName, $postData);
        if ($result["error"] != 0) {
            $err = new RestError();
            $err->ReportError($result["error"], $result["err_msg"]);
            exit;
        }

        $resData = json_encode(array("id" => $result["id"]), true);
        print $resData;
    }

    /**
     * This method handles reading data from a Sugar object.
     *
     * URL params supported:
     * "deleted": true or false, tells sugar to either get deleted reocrds or non-deleted records.
     * "fields": a list od fields to return data for.
     * "maxresult": the max number of results to return.
     * "offset": the offset into the result set for this object.
     *
     * Notes:
     * HTTP errors will be thrown if
     *
     */
    private function getRecordIds() {
        global $current_user;
        $deleted = false;
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
                $deleted = true;
            } else {
                $deleted = false;
            }
        }

        if (array_key_exists("maxresult", $_GET)) {
            $maxresult = (int)$_GET["maxresult"];
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
            $offset = (int)$_GET["offset"];
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

        if ($entryList["error"] != 0) {
            $err = new RestError();
            $err->ReportError($entryList["error"], $entryList["err_msg"]);
            exit;
        }

        $ids = array();

        foreach ($entryList["entry_list"] as $dhash) {
            if (array_key_exists("name_value_list", $dhash)) {
                $fieldsData = array();
                foreach (array_keys($dhash["name_value_list"]) as $dkey) {
                    $fieldsData[$dkey] = $dhash["name_value_list"][$dkey]["value"];
                }

                $fieldsData["id"] = $dhash["id"];
                $ids[] = $fieldsData;
            } else {
                $ids[] = $dhash["id"];
            }
        }


        $response = array();
        $response["next_offset"] = $entryList["next_offset"];
        $response["result_count"] = $entryList["result_count"];
        $response["records"] = $ids;
        $t = json_encode($response);
        $this->sendJSONResponse($t);
    }

    /**
     * @param $objId
     */
    private function handleObject($objId) {
        global $current_user;
        $userFields = array();
        $auth = $this->getAuth();
        $this->isValidToken($auth);
        $uridata = $this->getURIData();
        $result = array();
        $obj = null;

        if (array_key_exists("fields", $_GET)) {
            $tmpfields = explode(",", $_GET["fields"]);
            foreach ($tmpfields as $f) {
                array_push($userFields, $f);
            }
        }

        $obj = new SugarWebServiceImpl();
        $data = $obj->get_entry($auth, $this->objName, $uridata[1], $userFields, array());
        if ($data["error"] != 0) {
            $err = new RestError();
            $err->ReportError(404, "\n{$data["err_msg"]}\n");
            exit;
        }

        foreach (array_keys($data["entry_list"][0]["name_value_list"]) as $key) {
            $result[$key] = $data["entry_list"][0]["name_value_list"][$key]["value"];
        }

        $md5 = serialize($result);
        $md5 = md5($md5);
        $result["md5"] = $md5;
        $res = json_encode($result);
        $this->sendJSONResponse($res);
    }

    /**
     * This function handles record updates.
     */
    private function handlePut() {
        $uridata = $this->getURIData();
        $count = count($uridata);

        if ($count > 1) {
            $this->updateRecord($uridata[1]);
        } else {
            $err = new RestError();
            $err->ReportError(404);
            exit;
        }
    }

    /**
     * @param $objId
     */
    private function updateRecord($objId) {
        global $current_user;
        $userFields = array();
        $auth = $this->getAuth();
        $this->isValidToken($auth);
        $uridata = $this->getURIData();

        $result = $this->getRequestData();
        $data = RestUtils::isValidJson($result["raw_post_data"]);
        if ($data["err"] != false) {
            $err = new RestError();
            $err->ReportError(415, $result["err_str"]);
            exit;
        }

        $tmpdata = array();
        array_push($tmpdata, array(
            "name" => "id",
            "value" => $objId
        ));
        foreach ($data["data"] as $key => $value) {
            $t = array (
                "name" => "{$key}",
                "value" => "{$value}"
            );
            array_push($tmpdata, $t);
        }

        $websrv = new SugarWebServiceImpl();
        $result = $websrv->set_entry($auth, $this->objName, $tmpdata);
        if ($result["error"] != 0) {
            $err = new RestError();
            $err->ReportError($result["error"], $result["err_msg"]);
            exit;
        }

        $json = json_encode($result);
        $this->sendJSONResponse($json);
    }

    /**
     * This method gets all relationships for a given object id for the parent bean/module type.
     *
     * Example URI that returns default fields:
     * http://localhost:8888/sugar/ent/sugarcrm/rest/Accounts/a7365389-020c-d7f4-7513-4f5a84280ac0/Contacts
     *
     * Exmaple URI that returns wanted fields:
     * http://localhost:8888/sugar/ent/sugarcrm/rest/Accounts/a7365389-020c-d7f4-7513-4f5a84280ac0/Contacts?fields=last_name,first_name,title
     *
     *
     * @param $objId, the sugar guid
     * @param $relateName, the name of the relationship: Example => Contacts
     */
    function handleGetRealtionship($objId, $relateName) {
        global $current_user;
        $auth = $this->getAuth();
        $this->isValidToken($auth);
        $deleted = 0;
        $relatedFields = array();
        $fields = array();
        $where = "";

        if (array_key_exists("fields", $_GET)) {
            $tmp = explode(",", $_GET["fields"]);
            foreach ($tmp as $f) {
                if (!empty($f)) {
                    array_push($fields, $f);
                }
            }
        }

        if (array_key_exists("where", $_GET)) {
            $where = $_GET["where"];
        }

        $obj = new SugarWebServiceImpl();
        $relateData = $obj->get_relationships($auth,
            $this->objName,
            $objId,
            $relateName,
            $where,
            $fields,
            array(),
            $deleted);

        if (!array_key_exists("entry_list", $relateData)) {
            $err = new RestError();
            $err->ReportError(404);
            exit;
        }

        $retData = array();
        foreach ($relateData["entry_list"] as $entry) {
            $keys = array_keys($entry);
            $tmpData = array();

            // this inner loop is needed to remove the unneeded nesting of hashes where name="name" &
            // value="value" so the data is a proper hash //
            foreach ($keys as $key) {
                if ($key != "name_value_list") {
                    $tmpData[$key] = $entry[$key];
                } else {
                    $value = $entry[$key];
                    foreach ($value as $listData) {
                        $tmpData[$listData["name"]] = $listData["value"];
                    }
                }
            }
            array_push($retData, $tmpData);
        }

        $tmp = json_encode($retData);
        $this->sendJSONResponse($tmp);
    }

    /**
     * This method is a handler that decides what helper function should be called based on
     * the type of GET request from the client.  This is currently done by counting uri path
     * sections.
     *
     */
    private function handleGet() {
        $uridata = $this->getURIData();
        $count = count($uridata);

        switch ($count) {
            case 1:
                $this->getRecordIds();
                break;
            case 2:
                $this->handleObject($uridata[1]);
                break;
            case 3:
                $this->handleGetRealtionship($uridata[1], $uridata[2]);
                break;
            default:
                $err = new RestError();
                $err->ReportError(404);
                exit;
                break;
        }
    }
}