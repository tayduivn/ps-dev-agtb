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
            "err" => false,
            "err_str" => "",
            "data" => null
        );

        switch(json_last_error()) {
            case JSON_ERROR_NONE:
                $result["err"] = false;
                $result["data"] = $data;
            break;

            case JSON_ERROR_STATE_MISMATCH:
                $result["err"] = true;
                $result["err_str"] = "JSON: Invalid or malformed JSON";
            break;

            case JSON_ERROR_CTRL_CHAR:
                $result["err"] = true;
                $result["err_str"] = "JSON: Control character error, possibly incorrectly encoded";
            break;

            case JSON_ERROR_SYNTAX:
                $result["err"] = true;
                $result["err_str"] = "JSON: Syntax error";
            break;

            case JSON_ERROR_DEPTH:
                $result["err"] = true;
                $result["err_str"] = "JSON: The maximum stack depth has been exceeded";
            break;

            default:
                $result["err"] = true;
                $result["err_str"] = 'JSON: Unknown Error';
            break;
        }

        return $result;
    }

}