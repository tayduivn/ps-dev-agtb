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

                $fieldsData["id"] = $dhash["id"];
                $ids[] = $fieldsData;
            } else {
                $ids[] = $dhash["id"];
            }
        }

        $md5 = json_encode($ids);
        $md5 = md5($md5);
        $response = array();
        $response["md5"] = $md5;
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

    function handleGetRealtionship($objId, $relateName) {
        global $current_user;
        $auth = $this->getAuth();
        $this->isValidToken($auth);
        $deleted = 0;
        $relatedFields = array();

        if (array_key_exists("relatedfields", $_GET)) {
            $tmp = explode(",", $_GET["relatedfields"]);
            foreach ($tmp as $f) {
                if (!empty($f)) {
                    array_push($relatedFields, $f);
                }
            }
        }

        $obj = new SugarWebServiceImpl();

        /*

        $tmp = $obj->get_relationships($auth,
            $this->objName,
            $objId,
            $relateName,
            '',
            array('first_name', 'last_name', 'primary_address_city'),
            array(array('name' =>  'opportunities', 'value' => array('name', 'type', 'lead_source')),
            array('name' =>  'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address')))
            ,$deleted);

        /*
        * Retrieve a collection of beans that are related to the specified bean and optionally return relationship data for those related beans.
 * So in this API you can get contacts info for an account and also return all those contact's email address or an opportunity info also.
 *
 * @param String $session -- Session ID returned by a previous call to login.
 * @param String $module_name -- The name of the module that the primary record is from.  This name should be the name the module was developed under (changing a tab name is studio does not affect the name that should be passed into this method)..
 * @param String $module_id -- The ID of the bean in the specified module
 * @param String $link_field_name -- The name of the lnk field to return records from.  This name should be the name the relationship.
 * @param String $related_module_query -- A portion of the where clause of the SQL statement to find the related items.  The SQL query will already be filtered to only include the beans that are related to the specified bean. (IGNORED)
 * @param Array $related_fields - Array of related bean fields to be returned.
 * @param Array $related_module_link_name_to_fields_array - For every related bean returrned, specify link fields name to fields info for that bean to be returned. For ex.'link_name_to_fields_array' => array(array('name' =>  'email_addresses', 'value' => array('id', 'email_address', 'opt_out', 'primary_address'))).
 * @param Number $deleted -- false if deleted records should not be include, true if deleted records should be included.
 * @return Array 'entry_list' -- Array - The records that were retrieved
 *	     		 'relationship_list' -- Array - The records link field data. The example is if asked about accounts contacts email address then return data would look like Array ( [0] => Array ( [name] => email_addresses [records] => Array ( [0] => Array ( [0] => Array ( [name] => id [value] => 3fb16797-8d90-0a94-ac12-490b63a6be67 ) [1] => Array ( [name] => email_address [value] => hr.kid.qa@example.com ) [2] => Array ( [name] => opt_out [value] => 0 ) [3] => Array ( [name] => primary_address [value] => 1 ) ) [1] => Array ( [0] => Array ( [name] => id [value] => 403f8da1-214b-6a88-9cef-490b63d43566 ) [1] => Array ( [name] => email_address [value] => kid.hr@example.name ) [2] => Array ( [name] => opt_out [value] => 0 ) [3] => Array ( [name] => primary_address [value] => 0 ) ) ) ) )
* @exception 'SoapFault' -- The SOAP error, if any
*/

        $relateData = $obj->get_relationships($auth,
            $this->objName,
            $objId,
            $relateName,
            '',
            $relatedFields,
            array(),
            $deleted);

        if (!array_key_exists("entry_list", $relateData)) {
            $err = new RestError();
            $err->ReportError(404);
            exit;
        }

        $retData = array();
        foreach ($relateData["entry_list"] as $entry) {
            foreach ($relateData["name_value_list"] as $key => $value) {
                print "VALUE: {$value}"; die;
            }

            array_push($retData, $entry["id"]);

        }

        print_r($relateData); die;

        $tmp = json_encode($retData);
        print_r($tmp); die;
    }

    /**
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