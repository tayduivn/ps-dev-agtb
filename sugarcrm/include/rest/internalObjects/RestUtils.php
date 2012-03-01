<?php

class RestUtils {

    /**
     * @static
     *
     * This function checks to see if a request header contains a content_type
     * of JSON.
     *
     * @return bool
     */
    public static function isJsonHeader() {
        $type = $_SERVER["CONTENT_TYPE"];

        if (empty($type)) {
            return false;
        }

        $type = strtolower($type);
        if (!preg_match('/json/', $type)) {
            return false;
        }

        return true;
    }


    public static function jsonErrorToStr($err) {
        $result = "";

        switch($err) {
            case JSON_ERROR_NONE:
                $result = "";
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $str = "JSON: Invalid or malformed JSON";
                break;
            case JSON_ERROR_CTRL_CHAR:
                $result = "JSON: Control character error, possibly incorrectly encoded";
                break;
            case JSON_ERROR_SYNTAX:
                $result = "JSON: Syntax error";
                break;
            case JSON_ERROR_DEPTH:
                $result = "JSON: The maximum stack depth has been exceeded";
                break;
            default:
                $result["err_str"] = 'JSON: Unknown Error';
                break;
        }

        return $result;
    }

    /**
     * @static
     *
     * This function checks to see if the source being passed to it is valid json or not.
     *
     * @param $src
     * @return array A hash containing the results of the check, and the php var if the data
     * was good will containe the data from the json.  "err" is false if there was no error, else
     * it is true on error.
     *
     *
     */
    public static function isValidJson($src) {
        $data = json_decode($src, true);
        $result = array(
            "err" => 0,
            "err_str" => "",
            "data" => null
        );

        switch(json_last_error()) {
            case JSON_ERROR_NONE:
                $result["err"] = 0;
                $result["data"] = $data;
            break;

            case JSON_ERROR_STATE_MISMATCH:
                $result["err"] = 1;
                $result["err_str"] = "JSON: Invalid or malformed JSON";
            break;

            case JSON_ERROR_CTRL_CHAR:
                $result["err"] = 1;
                $result["err_str"] = "JSON: Control character error, possibly incorrectly encoded";
            break;

            case JSON_ERROR_SYNTAX:
                $result["err"] = 1;
                $result["err_str"] = "JSON: Syntax error";
            break;

            case JSON_ERROR_DEPTH:
                $result["err"] = 1;
                $result["err_str"] = "JSON: The maximum stack depth has been exceeded";
            break;

            default:
                $result["err"] = 1;
                $result["err_str"] = 'JSON: Unknown Error';
            break;
        }

        return $result;
    }

    public static function get_user_module_list($user) {
        $GLOBALS['log']->info('Begin: SoapHelperWebServices->get_user_module_list');
        global $app_list_strings, $current_language;
        $app_list_strings = return_app_list_strings_language($current_language);
        $modules = query_module_access_list($user);
        ACLController :: filterModuleList($modules, false);
        global $modInvisList;

        foreach($modInvisList as $invis){
            $modules[$invis] = 'read_only';
        }

        $actions = ACLAction::getUserActions($user->id,true);
        foreach($actions as $key=>$value){
            if(isset($value['module']) && $value['module']['access']['aclaccess'] < ACL_ALLOW_ENABLED){
                if ($value['module']['access']['aclaccess'] == ACL_ALLOW_DISABLED) {
                    unset($modules[$key]);
                } else {
                    $modules[$key] = 'read_only';
                } // else
            } else {
                $modules[$key] = '';
            } // else
        } // foreach
        $GLOBALS['log']->info('End: SoapHelperWebServices->get_user_module_list');
        return $modules;
    }

}