<?php

/**
 * This class is going to be used for all classes for passing back results so that any classing using the
 * sub classes can have a stanrdard error.  This will allow for a soap api to be created on top of this code, and
 * let the soap class & rest class handle all error codes as they see fit.
 *
 * Note:
 *  Currently this class is not in use.
 *
 */
class Result {

    private $data = null;

    function __construct($errNum, $errMsg, $data) {
        $data["error"] = $errNum;

        if ($errMsg == null || empty($errMsg)) {
            $data["err_msg"] = "";
        }

        $data["data"] = $data;
    }

}